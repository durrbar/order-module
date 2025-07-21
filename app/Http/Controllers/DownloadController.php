<?php

namespace Modules\Order\Http\Controllers;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Core\Exceptions\DurrbarException;
use Modules\Core\Http\Controllers\CoreController;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Models\Variation;
use Modules\Order\Models\DownloadToken;
use Modules\Order\Models\OrderedFile;
use Modules\Order\Repositories\DownloadRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DownloadController extends CoreController
{
    public $repository;

    public function __construct(DownloadRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * fetchDownloadableFiles
     *
     * @param  mixed  $request
     * @return void
     *
     * @throws DurrbarException
     */
    public function fetchDownloadableFiles(Request $request)
    {
        $limit = isset($request->limit) ? $request->limit : 15;

        return $this->fetchFiles($request)->paginate($limit)->loadMorph('file.fileable', [
            Product::class => ['shop'],
            Variation::class => ['product.shop'],
        ])->withQueryString();
    }

    /**
     * fetchFiles
     *
     * @param  mixed  $request
     * @return mixed
     *
     * @throws DurrbarException
     */
    public function fetchFiles(Request $request)
    {
        try {
            $user = $request->user();
            if ($user) {
                return OrderedFile::where('customer_id', $user->id)->with(['order']);
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (DurrbarException $th) {
            throw new DurrbarException(NOT_AUTHORIZED);
        }
    }

    /**
     * generateDownloadableUrl
     *
     * @param  mixed  $request
     * @return void
     *
     * @throws DurrbarException
     */
    public function generateDownloadableUrl(Request $request)
    {
        try {
            $user = $request->user();
            $orderedFiles = OrderedFile::where('digital_file_id', $request->digital_file_id)->where('customer_id', $user->id)->get();
            if (count($orderedFiles)) {
                $dataArray = [
                    'user_id' => $user->id,
                    'token' => Str::random(16),
                    'digital_file_id' => $request->digital_file_id,
                ];
                $newToken = DownloadToken::create($dataArray);

                return route('download_url.token', ['token' => $newToken->token]);
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (DurrbarException $e) {
            throw new DurrbarException(NOT_AUTHORIZED);
        }
    }

    /**
     * downloadFile
     *
     * @param  mixed  $token
     * @return void
     *
     * @throws DurrbarException
     */
    public function downloadFile($token)
    {
        try {
            try {
                $downloadToken = DownloadToken::with('file')->where('token', $token)->first();
                if ($downloadToken) {
                    $downloadToken->delete();
                } else {
                    return ['message' => TOKEN_NOT_FOUND];
                }
            } catch (Exception $e) {
                throw new HttpException(404, TOKEN_NOT_FOUND);
            }
            try {
                $mediaItem = Media::where('model_id', $downloadToken->file->attachment_id)->first();
            } catch (Exception $e) {
                return ['message' => NOT_FOUND];
            }

            return $mediaItem;
        } catch (DurrbarException $e) {
            throw new DurrbarException(NOT_FOUND);
        }
    }
}
