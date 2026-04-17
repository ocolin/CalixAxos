<?php


declare(strict_types=1);

namespace Ocolin\CalixAxos\Tests\Unit;

use GuzzleHttp\ClientInterface as GuzzleInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Ocolin\CalixAxos\Client;
use Ocolin\CalixAxos\Config;
use Ocolin\CalixAxos\Http;
use Ocolin\CalixAxos\Response;
use Ocolin\CalixAxos\Exceptions\ConfigException;
use Ocolin\CalixAxos\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;

class CalixAxosTest extends TestCase
{

    /* CONFIG TESTS
    ----------------------------------------------------------------------------- */

    public function test_config_accepts_direct_parameters(): void
    {
        $config = new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            username: 'testuser',
            password: 'testpass'
        );

        $this->assertSame('https://fake.example.com:18443/rest/v1/', $config->host);
        $this->assertSame('testuser', $config->username);
        $this->assertSame('testpass', $config->password);
    }


    public function test_config_reads_from_env(): void
    {
        $_ENV['SMX_AXOS_HOST'] = 'https://env.example.com:18443/rest/v1/';
        $_ENV['SMX_AXOS_USERNAME'] = 'envuser';
        $_ENV['SMX_AXOS_PASSWORD'] = 'envpass';

        $config = new Config();

        $this->assertSame('https://env.example.com:18443/rest/v1/', $config->host);
        $this->assertSame('envuser', $config->username);
        $this->assertSame('envpass', $config->password);

        unset(
            $_ENV['SMX_AXOS_HOST'],
            $_ENV['SMX_AXOS_USERNAME'],
            $_ENV['SMX_AXOS_PASSWORD']
        );
    }


    public function test_config_throws_on_missing_host(): void
    {
        unset($_ENV['SMX_AXOS_HOST']);

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Missing Calix SMX host name.');

        new Config(
            username: 'testuser',
            password: 'testpass'
        );
    }


    public function test_config_throws_on_missing_username(): void
    {
        unset($_ENV['SMX_AXOS_USERNAME']);

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Missing Calix SMx username.');

        new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            password: 'testpass'
        );
    }


    public function test_config_throws_on_missing_password(): void
    {
        unset($_ENV['SMX_AXOS_PASSWORD']);

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Missing Calix SMx password.');

        new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            username: 'testuser'
        );
    }


    public function test_config_stores_options(): void
    {
        $config = new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            username: 'testuser',
            password: 'testpass',
            options: ['timeout' => 60]
        );

        $this->assertSame(60, $config->options['timeout']);
    }


    /* RESPONSE TESTS
    ----------------------------------------------------------------------------- */

    public function test_response_stores_all_properties(): void
    {
        $response = new Response(
            status: 200,
            statusMessage: 'OK',
            headers: ['Content-Type' => ['application/json']],
            body: ['id' => '123']
        );

        $this->assertSame(200, $response->status);
        $this->assertSame('OK', $response->statusMessage);
        $this->assertSame(['Content-Type' => ['application/json']], $response->headers);
        $this->assertSame(['id' => '123'], $response->body);
    }


    public function test_response_accepts_object_body(): void
    {
        $body = new \stdClass();
        $body->id = '123';

        $response = new Response(
            status: 200,
            statusMessage: 'OK',
            headers: [],
            body: $body
        );

        $this->assertInstanceOf(\stdClass::class, $response->body);
    }


    public function test_response_accepts_string_body(): void
    {
        $response = new Response(
            status: 200,
            statusMessage: 'OK',
            headers: [],
            body: 'raw string response'
        );

        $this->assertSame('raw string response', $response->body);
    }


    /* HTTP TESTS
    ----------------------------------------------------------------------------- */

    public function test_http_returns_response_on_200(): void
    {
        $guzzle = $this->createStub(GuzzleInterface::class);
        $guzzle->method('request')->willReturn(
            new GuzzleResponse(
                status: 200,
                headers: ['Content-Type' => ['application/json']],
                body: json_encode(['id' => '123'])
            )
        );

        $config = new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            username: 'testuser',
            password: 'testpass'
        );

        $http = new Http(config: $config, guzzle: $guzzle);
        $response = $http->request(path: 'ems/subscribers');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->status);
        $this->assertSame('OK', $response->statusMessage);
    }


    public function test_http_returns_response_on_404(): void
    {
        $guzzle = $this->createStub(GuzzleInterface::class);
        $guzzle->method('request')->willReturn(
            new GuzzleResponse(status: 404, body: json_encode([]))
        );

        $config = new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            username: 'testuser',
            password: 'testpass'
        );

        $http = new Http(config: $config, guzzle: $guzzle);
        $response = $http->request(path: 'ems/subscribers/nonexistent');

        $this->assertSame(404, $response->status);
        $this->assertSame('Not Found', $response->statusMessage);
    }


    public function test_http_returns_response_on_429(): void
    {
        $guzzle = $this->createStub(GuzzleInterface::class);
        $guzzle->method('request')->willReturn(
            new GuzzleResponse(status: 429, body: json_encode([]))
        );

        $config = new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            username: 'testuser',
            password: 'testpass'
        );

        $http = new Http(config: $config, guzzle: $guzzle);
        $response = $http->request(path: 'ems/subscribers');

        $this->assertSame(429, $response->status);
        $this->assertSame('Too Many Requests', $response->statusMessage);
    }


    public function test_http_throws_on_invalid_method(): void
    {
        $guzzle = $this->createStub(GuzzleInterface::class);

        $config = new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            username: 'testuser',
            password: 'testpass'
        );

        $http = new Http(config: $config, guzzle: $guzzle);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid HTTP method: PATCH');

        $http->request(path: 'ems/subscribers', method: 'PATCH');
    }


    public function test_http_method_is_case_insensitive(): void
    {
        $guzzle = $this->createStub(GuzzleInterface::class);
        $guzzle->method('request')->willReturn(
            new GuzzleResponse(status: 200, body: json_encode([]))
        );

        $config = new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            username: 'testuser',
            password: 'testpass'
        );

        $http = new Http(config: $config, guzzle: $guzzle);
        $response = $http->request(path: 'ems/subscribers', method: 'get');

        $this->assertSame(200, $response->status);
    }


    public function test_http_body_falls_back_to_string_on_non_json(): void
    {
        $guzzle = $this->createStub(GuzzleInterface::class);
        $guzzle->method('request')->willReturn(
            new GuzzleResponse(status: 200, body: 'plain text response')
        );

        $config = new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            username: 'testuser',
            password: 'testpass'
        );

        $http = new Http(config: $config, guzzle: $guzzle);
        $response = $http->request(path: 'ems/subscribers');

        $this->assertSame('plain text response', $response->body);
    }


    public function test_http_substitutes_path_tokens(): void
    {
        $guzzle = $this->createMock(GuzzleInterface::class);
        $guzzle->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('ems/subscribers/sub-123'),
                $this->anything()
            )
            ->willReturn(new GuzzleResponse(status: 200, body: json_encode([])));

        $config = new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            username: 'testuser',
            password: 'testpass'
        );

        $http = new Http(config: $config, guzzle: $guzzle);
        $http->request(
            path: 'ems/subscribers/{id}',
            query: ['id' => 'sub-123']
        );
    }


    public function test_http_removes_token_from_query_after_substitution(): void
    {
        $guzzle = $this->createMock(GuzzleInterface::class);
        $guzzle->expects($this->once())
            ->method('request')
            ->with(
                $this->anything(),
                $this->equalTo('ems/subscribers/sub-123'),
                $this->callback(function (array $options) {
                    return !isset($options['query']['id']);
                })
            )
            ->willReturn(new GuzzleResponse(status: 200, body: json_encode([])));

        $config = new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            username: 'testuser',
            password: 'testpass'
        );

        $http = new Http(config: $config, guzzle: $guzzle);
        $http->request(
            path: 'ems/subscribers/{id}',
            query: ['id' => 'sub-123']
        );
    }


    /* CLIENT TESTS
    ----------------------------------------------------------------------------- */

    public function test_client_get_calls_http_with_get_method(): void
    {
        $http = $this->createMock(Http::class );
        $http->expects( $this->once())
            ->method('request' )
            ->with(
                path: 'ems/subscribers',
                method: 'GET',
                query: [],
                body: []
            )
            ->willReturn(new Response(
                status: 200,
                statusMessage: 'OK',
                headers: [],
                body: []
            ));

        $client = new Client( config: $this->makeConfig(), http: $http );
        $response = $client->get(endpoint: 'ems/subscribers');

        $this->assertSame(200, $response->status);
    }


    public function test_client_post_calls_http_with_post_method(): void
    {
        $http = $this->createMock(Http::class);
        $http->expects($this->once())
            ->method('request')
            ->with(
                path: 'ems/subscribers',
                method: 'POST',
                query: [],
                body: ['name' => 'Test Subscriber']
            )
            ->willReturn(new Response(
                status: 201,
                statusMessage: 'Created',
                headers: [],
                body: []
            ));

        $client = new Client( config: $this->makeConfig(), http: $http);
        $response = $client->post(
            endpoint: 'ems/subscribers',
            body: ['name' => 'Test Subscriber']
        );

        $this->assertSame(201, $response->status);
    }


    public function test_client_put_calls_http_with_put_method(): void
    {
        $http = $this->createMock(Http::class);
        $http->expects($this->once())
            ->method('request')
            ->with(
                path: 'ems/subscribers/sub-123',
                method: 'PUT',
                query: [],
                body: ['name' => 'Updated Name']
            )
            ->willReturn(new Response(
                status: 200,
                statusMessage: 'OK',
                headers: [],
                body: []
            ));

        $client = new Client( config: $this->makeConfig(), http: $http);
        $client->put(
            endpoint: 'ems/subscribers/sub-123',
            body: ['name' => 'Updated Name']
        );
    }


    public function test_client_delete_calls_http_with_delete_method(): void
    {
        $http = $this->createMock(Http::class);
        $http->expects($this->once())
            ->method('request')
            ->with(
                path: 'ems/subscribers/sub-123',
                method: 'DELETE',
                query: [],
                body: []
            )
            ->willReturn(new Response(
                status: 200,
                statusMessage: 'OK',
                headers: [],
                body: []
            ));

        $client = new Client( config: $this->makeConfig(), http: $http);
        $client->delete(endpoint: 'ems/subscribers/sub-123');
    }


    public function test_client_accepts_object_query_parameter(): void
    {
        $http = $this->createMock(Http::class);
        $http->expects($this->once())
            ->method('request')
            ->with(
                path: 'ems/subscribers',
                method: 'GET',
                query: ['limit' => 10],
                body: []
            )
            ->willReturn(new Response(
                status: 200,
                statusMessage: 'OK',
                headers: [],
                body: []
            ));

        $query = new \stdClass();
        $query->limit = 10;

        $client = new Client ( config: $this->makeConfig(), http: $http);
        $client->get(endpoint: 'ems/subscribers', query: $query);
    }

    private function makeConfig() : Config
    {
        return new Config(
            host: 'https://fake.example.com:18443/rest/v1/',
            username: 'testuser',
            password: 'testpass'
        );
    }
}