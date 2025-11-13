<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Company extends Model implements HasMedia
{
      use InteractsWithMedia;
       protected $fillable = [
        'company_name',
        'email',
        'phone',
        'address',
        'avatar'
    ];
    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute()
    {
        $url= $this->getFirstMediaUrl('avatar');
         if (str_contains($url, '/api/')) {
        $url = str_replace('/api/', '/', $url);
    }
    return $url;
    }

}
