<?php

namespace Modules\Order\Http\Controllers;

use Modules\Core\Exceptions\DurrbarException;
use Modules\Core\Http\Controllers\CoreController;
use Modules\Order\Http\Requests\CheckoutVerifyRequest;
use Modules\Order\Repositories\CheckoutRepository;

class CheckoutController extends CoreController
{
    public $repository;

    public function __construct(CheckoutRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Verify the checkout data and calculate tax and shipping.
     *
     * @return array
     */
    public function verify(CheckoutVerifyRequest $request)
    {
        try {
            return $this->repository->verify($request);
        } catch (DurrbarException $th) {
            throw new DurrbarException(SOMETHING_WENT_WRONG);
        }
    }
}
