<?php

declare(strict_types=1);

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('download_tokens')]
#[Unguarded]
class DownloadToken extends Model
{
    use HasUuids;

    public function file(): BelongsTo
    {
        return $this->belongsTo(DigitalFile::class, 'digital_file_id');
    }
}
