<?php

namespace WolfpackIT\oauth\interfaces;

use League\OAuth2\Server\Entities\UserEntityInterface as LeagueUserEntityInterface;

/**
 * Interface UserEntityInterface
 * @package WolfpackIT\oauth\interfaces
 */
interface UserEntityInterface extends LeagueUserEntityInterface
{
    /**
     * Find a user by username and password. It should return the user when there is a valid password.
     * Otherwise return null.
     *
     * @param string $username
     * @param string $password
     * @return UserEntityInterface|null
     */
    public static function findByUsernamePassword(string $username, string $password): ?UserEntityInterface;

    /**
     * The attribute that can be used for displaying and searching
     *
     * @return string
     */
    public function displayAttribute(): string;

    /**
     * @return string
     */
    public function getId(): string;
}