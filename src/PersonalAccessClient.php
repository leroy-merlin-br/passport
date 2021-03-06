<?php

namespace Laravel\Passport;

use MongolidLaravel\MongolidModel as Model;

class PersonalAccessClient extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $collection = 'oauth_personal_access_clients';

    /**
     * Get all of the authentication codes for the client.
     *
     * @return Client|null
     */
    public function client()
    {
        return $this->referencesOne(Client::class, 'client_id');
    }
}
