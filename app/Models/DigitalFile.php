<?php

declare(strict_types=1);

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('digital_files')]
#[Unguarded]
#[Hidden(['url'])]
class DigitalFile extends Model
{
    use HasUuids;

    /**
     * Get the parent fileable model (user or post).
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'fileable_type', 'fileable_id');
    }
}
