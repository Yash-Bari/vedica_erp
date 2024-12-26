<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Project;

class Client extends Model
{
    protected $fillable = [
        'name', 
        'contact_person', 
        'email', 
        'phone', 
        'address', 
        'gst_number', 
        'source', 
        'notes',
        'status'
    ];

    protected $casts = [
        'source' => 'string'
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    // Method to get source options
    public static function getSourceOptions()
    {
        return [
            'IndiaMart' => 'IndiaMart',
            'Justdial' => 'Justdial', 
            'TIC' => 'TIC', 
            'Other' => 'Other'
        ];
    }
}
