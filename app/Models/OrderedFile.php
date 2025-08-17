<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderedFile extends Model
{
    use HasUuids;
    
    protected $table = 'ordered_files';

    public $guarded = [];

    public function file(): BelongsTo
    {
        return $this->belongsTo(DigitalFile::class, 'digital_file_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'tracking_number', 'tracking_number');
    }
}
