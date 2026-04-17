<?php

declare( strict_types = 1 );

namespace Ocolin\CalixAxos;

use Ocolin\CalixAxos\Exceptions\ConfigException;
use Ocolin\GlobalType\ENV;

readonly class Config
{
    /**
     * @var ?string Hostname of SMx server.
     */
    public ?string $host;

    /**
     * @var ?string Username of SMx account.
     */
    public ?string $username;

    /**
     * @var ?string Password for SMx account.
     */
    public ?string $password;

    /**
     * @var array<string, mixed> List of Guzzle options.
     */
    public array $options;

    /**
     * @param ?string $host Hostname of SMx server.
     * @param ?string $username Username of SMx account.
     * @param ?string $password Password for SMx account.
     * @param array<string, mixed> $options
     * @throws ConfigException
     */
    public function __construct(
        ?string $host     = null,
        ?string $username = null,
        ?string $password = null,
          array $options  = [],
    )
    {
        $this->host     = $host     ?? ENV::getStringNull( name: 'SMX_AXOS_HOST' );
        $this->username = $username ?? ENV::getStringNull( name: 'SMX_AXOS_USERNAME' );
        $this->password = $password ?? ENV::getStringNull( name: 'SMX_AXOS_PASSWORD' );
        $this->options  = $options;

        $this->validateHost();
        $this->validateUsername();
        $this->validatePassword();
    }


/* VALIDATE HOST
----------------------------------------------------------------------------- */

    /**
     * Check that Calix SMx server hostname has been provided.
     *
     * @return void
     * @throws ConfigException
     */
    private function validateHost() : void
    {
        if(  $this->host === null ) {
            throw new ConfigException( message: "Missing Calix SMX host name." );
        }
    }


/* VALIDATE USERNAME
----------------------------------------------------------------------------- */

    /**
     * Check that Calix SMx server username has been provided.
     *
     * @return void
     * @throws ConfigException
     */
    private function validateUsername() : void
    {
        if(  $this->username === null ) {
            throw new ConfigException( message: "Missing Calix SMx username." );
        }
    }


/* VALIDATE PASSWORD
----------------------------------------------------------------------------- */

    /**
     * Check that Calix SMx server password has been provided.
     *
     * @return void
     * @throws ConfigException
     */
    private function validatePassword() : void
    {
        if(  $this->password === null ) {
            throw new ConfigException( message: "Missing Calix SMx password." );
        }
    }
}