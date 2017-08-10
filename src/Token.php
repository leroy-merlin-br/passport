<?php

namespace Laravel\Passport;

use Illuminate\Contracts\Auth\Authenticatable;
use MongolidLaravel\MongolidModel as Model;

class Token extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $collection = 'oauth_access_tokens';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'scopes' => 'array',
        'revoked' => 'bool',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_at',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the client that the token belongs to.
     *
     * @return Client|null
     */
    public function client()
    {
        return $this->referencesOne(Client::class, 'client_id');
    }

    /**
     * Get the user that the token belongs to.
     *
     * @return Authenticatable|null
     */
    public function user()
    {
        $provider = config('auth.guards.api.provider');

        return $this->referencesOne(config('auth.providers.'.$provider.'.model'), 'user_id');
    }

    /**
     * Determine if the token has a given scope.
     *
     * @param  string  $scope
     * @return bool
     */
    public function can($scope)
    {
        return in_array('*', $this->scopes) ||
               array_key_exists($scope, array_flip($this->scopes));
    }

    /**
     * Determine if the token is missing a given scope.
     *
     * @param  string  $scope
     * @return bool
     */
    public function cant($scope)
    {
        return ! $this->can($scope);
    }

    /**
     * Revoke the token instance.
     *
     * @return void
     */
    public function revoke()
    {
        $this->fill(['revoked' => true], true);
        $this->save();
    }

    /**
     * Determine if the token is a transient JWT token.
     *
     * @return bool
     */
    public function transient()
    {
        return false;
    }
}
