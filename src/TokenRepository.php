<?php

namespace Laravel\Passport;

use MongoDB\BSON\UTCDateTime;
use MongolidLaravel\MongolidModel as Model;

class TokenRepository
{
    /**
     * Creates a new Access Token.
     *
     * @param  array $attributes
     *
     * @return \Laravel\Passport\Token
     */
    public function create($attributes)
    {
        $token = new Token();

        $token->fill($attributes);
        $token->save();

        return $token;
    }

    /**
     * Get a token by the given ID.
     *
     * @param  string $id
     *
     * @return \Laravel\Passport\Token|null
     */
    public function find($id)
    {
        return Token::first($id);
    }

    /**
     * Get a token by the given user ID and token ID.
     *
     * @param  string $id
     * @param  int    $userId
     *
     * @return \Laravel\Passport\Token|null
     */
    public function findForUser($id, $userId)
    {
        return Token::first(['_id' => $id, 'user_id' => $userId]);
    }

    /**
     * Get the token instances for the given user ID.
     *
     * @param  mixed $userId
     *
     * @return \Mongolid\Cursor\Cursor
     */
    public function forUser($userId)
    {
        return Token::where(['user_id' => $userId]);
    }

    /**
     * Get a valid token instance for the given user and client.
     *
     * @param  Model                    $user
     * @param  \Laravel\Passport\Client $client
     *
     * @return \Laravel\Passport\Token|null
     */
    public function getValidToken($user, $client)
    {
        return $client->tokens(
            [
                'user_id' => $user->getKey(),
                'revoked' => 0,
                'expires_at' => ['$gt' => new UTCDateTime()],
            ]
        )
            ->first();
    }

    /**
     * Store the given token instance.
     *
     * @param  \Laravel\Passport\Token $token
     *
     * @return void
     */
    public function save(Token $token)
    {
        $token->save();
    }

    /**
     * Revoke an access token.
     *
     * @param  string $id
     *
     * @return mixed
     */
    public function revokeAccessToken($id)
    {
        $token = Token::first($id);
        $token->revoked = true;

        return $token->update();
    }

    /**
     * Check if the access token has been revoked.
     *
     * @param  string $id
     *
     * @return bool Return true if this token has been revoked
     */
    public function isAccessTokenRevoked($id)
    {
        if ($token = $this->find($id)) {
            return (bool) $token->revoked;
        }

        return true;
    }

    /**
     * Find a valid token for the given user and client.
     *
     * @param  Model                    $user
     * @param  \Laravel\Passport\Client $client
     *
     * @return \Laravel\Passport\Token|null
     */
    public function findValidToken($user, $client)
    {
        return $client->tokens(
            [
                'user_id' => $user->getKey(),
                'revoked' => 0,
                'expires_at' => ['$gt' => new UTCDateTime()],
            ]
        )
            ->sort(['expires_at' => -1])
            ->first();
    }
}
