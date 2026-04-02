<?php

declare(strict_types=1);

namespace Modules\Order\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class OrderByCreatedAtDescScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy('created_at', 'desc');
    }
}
