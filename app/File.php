<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pid',
        'version_id',
        'file_name',
        'file_ext',
        'file_content_type',
        'file_size',
        'title',
        'is_local',
        'web_path',
        'storage_path',
        'replace_url',
        'status'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get all related Version model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function version()
    {
        return $this->belongsTo(Version::class);
    }
}
