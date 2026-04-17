<?php

declare( strict_types = 1 );

namespace Ocolin\CalixAxos\Tests\Integration;

use Ocolin\CalixAxos\Client;
use Ocolin\CalixAxos\Response;
use Ocolin\EasyEnv\Env;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class DeviceTest extends TestCase
{
    public static Client $client;
    private static ?int $id = null;

    #[Group('integration')]
    public function test_create_ONT() : void
    {
        $result = self::$client->post(
            endpoint: '/config/device/{device-name}/ont',
            query: [ 'device-name' => '877_E7_1' ],
            body: [
                'ont-id' => 9999,
                'ont-reg-id' => 9999,
                'ont-type' => 'Residential',
                'ont-profile-id' => '844G'
            ]
        );
        $this->assertSame( 200, $result->status );
        $this->assertIsArray( $result->body );
        $this->assertNotEmpty( $result->body );
        $this->assertIsObject( $result->body[0] );
        $this->assertObjectHasProperty( 'resourceId', $result->body[0] );
        self::$id = (int)$result->body[0]->resourceId;
    }

    #[Group('integration')]
    public function test_get_ONT() : void
    {
        $result = self::$client->get(
            endpoint: '/config/device/{device-name}/ont',
            query: [ 'device-name' => '877_E7_1', 'ont-id' => self::$id ],
        );
        $this->assertSame( 200, $result->status );
        $this->assertIsObject( $result->body );
        $this->assertObjectHasProperty( 'ont-id', $result->body );
        $this->assertSame( (string)self::$id, $result->body->{'ont-id'} );
    }


    #[Group('integration')]
    public function test_delete_ONT() : void
    {
        $result = self::delete_ONT( self::$id );
        $this->assertSame( 200, $result->status );
        $this->assertIsArray( $result->body );
        $this->assertNotEmpty( $result->body );
        $this->assertIsObject( $result->body[0] );
        $this->assertObjectHasProperty( 'userMessage', $result->body[0] );
    }


    public static function setUpBeforeClass(): void
    {
        Env::load( files: __DIR__ . '/../../.env' );
        self::$client = new Client();
    }

    public static function tearDownAfterClass(): void
    {
        if( self::$id !== null ) {
            self::delete_ONT(  (int)self::$id );
            self::$id = null;

        }
    }

    private static function delete_ONT( int $id ) : Response
    {
        return self::$client->delete(
            endpoint: '/config/device/{device-name}/ont',
            query: [ 'device-name' => '877_E7_1', 'ont-id' => self::$id ],
        );
    }
}