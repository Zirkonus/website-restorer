<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'credits',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Check that the Auth user role is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        if (isset($this->role->role) && $this->role->role == 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Check that the Auth user role is user
     *
     * @return bool
     */
    public function isUser()
    {
        if (isset($this->role->role) && $this->role->role == 'user') {
            return true;
        }

        return false;
    }

    /**
     * Get all related History models
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function history()
    {
        return $this->hasMany(History::class);
    }

    /**
     * Get all related Project models
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get related Role model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get all related Transaction models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function addCredits($count)
    {
        $this->credits = $this->credits + $count;
        if ($this->save()) {
            return true;
        }

        return false;
    }

    public function removeCredits($count)
    {
        if ($this->checkCredits($count)) {
            if ($this->save()) {
                return true;
            }
        }

        return false;
    }

    public function checkCredits($count)
    {
        $this->credits = $this->credits - $count;
        if ($this->credits < 0) {
            return false;
        }

        return true;
    }
}
