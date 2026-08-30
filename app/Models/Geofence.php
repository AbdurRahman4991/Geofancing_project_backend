<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Spatie\MediaLibrary\HasMedia;
// use Spatie\MediaLibrary\InteractsWithMedia;

// class Geofence extends Model implements HasMedia
// {
//     use InteractsWithMedia;
//         protected $fillable = [
//         'company_id',
//         'user_id',
//         'firm_name',
//         'latitude',
//         'longitude',
//         'radius',
//     ];

//      public function company()
//     {
//         return $this->belongsTo(Company::class);
//     }

//     public function user()
//     {
//         return $this->belongsTo(User::class);
//     }
// }


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Geofence extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        //'area_id',
        'company_id',
        'user_id',
        'firm_name',
        'latitude',
        'longitude',
        'radius',
    ];

    protected $appends = [
        'image_url',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Upload or Replace Image
     */
    public function uploadImage($file)
    {
        $this->clearMediaCollection('geofence');

        $this->addMedia($file)
            ->toMediaCollection('geofence');
    }

    /**
     * Get Image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('geofence');
    }
}