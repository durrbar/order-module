<?php

declare(strict_types=1);

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('ordered_files')]
#[Unguarded]
class OrderedFile extends Model
{
    use HasUuids;

    public function file(): BelongsTo
    {
        return $this->belongsTo(DigitalFile::class, 'digital_file_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'tracking_number', 'tracking_number');
    }
}
