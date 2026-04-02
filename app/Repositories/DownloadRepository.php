<?php

declare(strict_types=1);

namespace Modules\Order\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Order\Models\OrderedFile;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class DownloadRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return OrderedFile::class;
    }

    public function boot(): void
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
            //
        }
    }
}
