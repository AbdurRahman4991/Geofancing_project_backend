<?php
namespace App\Models;
use Spatie\Permission\Models\Role as SpatieRole;
class Role extends SpatieRole
{
    protected $fillable = ['name', 'guard_name', 'company_id'];
    public function company() { return $this->belongsTo(Company::class); }
}
