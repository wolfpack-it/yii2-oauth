<?php

namespace WolfpackIT\oauth\interfaces;

use League\OAuth2\Server\Entities\ClientEntityInterface as LeagueClientEntityInterface;

/**
 * Interface ClientEntityInterface
 * @package WolfpackIT\oauth\interfaces
 */
interface ClientEntityInterface extends LeagueClientEntityInterface
{
    /**
     * @return int
     */
    public function getId(): string;
}