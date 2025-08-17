<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DigitalFile extends Model
{
    use HasUuids;
    
    protected $table = 'digital_files';

    public $guarded = [];

    protected $hidden = [
        'url',
    ];

    /**
     * Get the parent fileable model (user or post).
     */
    public function fileable()
    {
        return $this->morphTo(__FUNCTION__, 'fileable_type', 'fileable_id');
    }
}
