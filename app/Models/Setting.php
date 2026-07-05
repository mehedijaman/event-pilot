<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'smtp_password' => 'encrypted',
        ];
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon ? Storage::disk('public')->url($this->favicon) : null;
    }
}
