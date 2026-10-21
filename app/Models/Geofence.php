<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Builder;

class Geofence extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [        
        'hierarchy_node_id',
        'company_id',
        'user_id',
        'firm_name',
        'address',
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

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hierarchyNode()
    {
        return $this->belongsTo(HierarchyNode::class, 'hierarchy_node_id');
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
