<?php

declare( strict_types = 1 );

namespace Ocolin\CalixAxos;

use Ocolin\CalixAxos\Exceptions\HttpException;
use GuzzleHttp\Exception\GuzzleException;

class Client
{
    private HTTP $http;

/* CONSTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * @param ?Config $config Configuration data object.
     * @param ?HTTP $http Guzzle client for mocking.
     */
    public function __construct(
        ?Config $config = null,
          ?HTTP $http   = null
    )
    {
        $config = $config ?? new Config();
        $this->http = $http ?? new HTTP( config: $config );
    }



/* GET REQUEST
----------------------------------------------------------------------------- */

    /**
     * @param string $endpoint API endpoint URI.
     * @param array<string, string|int|float|bool>|object $query Query and
     *  path parameters.
     * @return Response Client response object.
     * @throws HttpException HTTP method error.
     * @throws GuzzleException HTTP errors.
     */
    public function get( string $endpoint, array|object $query = [] ) : Response
    {
        $query = (array)$query;

        return $this->http->request( path: $endpoint, query: $query );
    }



/* POST METHOD
----------------------------------------------------------------------------- */

    /**
     * @param string $endpoint API endpoint URI.
     * @param array<string, string|int|float|bool>|object $query Query and
     *  path parameters.
     * @param array<mixed>|object $body HTTP body parameters.
     * @return Response Client response object.
     * @throws HttpException HTTP method error.
     * @throws GuzzleException HTTP errors.
     */
    public function post(
              string $endpoint,
        array|object $query = [],
        array|object $body  = [],
    ) : Response
    {
        $query = (array)$query;
        $body  = (array)$body;

        return $this->http->request(
              path: $endpoint,
            method: 'POST',
             query: $query,
              body: $body
        );
    }



/* PUT METHOD
----------------------------------------------------------------------------- */

    /**
     * @param string $endpoint API endpoint URI.
     * @param array<string, string|int|float|bool>|object $query Query and
     *  path parameters.
     * @param array<mixed>|object $body HTTP body parameters.
     * @return Response Client response object.
     * @throws HttpException HTTP method error.
     * @throws GuzzleException HTTP errors.
     */
    public function put(
        string $endpoint,
        array|object $query = [],
        array|object $body  = [],
    ) : Response
    {
        $query = (array)$query;
        $body  = (array)$body;

        return $this->http->request(
              path: $endpoint,
            method: 'PUT',
             query: $query,
              body: $body
        );
    }



/* DELETE METHOD
----------------------------------------------------------------------------- */

    /**
     * @param string $endpoint API endpoint URI.
     * @param array<string, string|int|float|bool>|object $query Query and
     *  path parameters.
     * @return Response Client response object.
     * @throws HttpException HTTP method error.
     * @throws GuzzleException HTTP errors.
     */
    public function delete( string $endpoint, array|object $query = [] ) : Response
    {
        $query = (array)$query;

        return $this->http->request(
              path: $endpoint,
            method: 'DELETE',
             query: $query
        );
    }



/* GENERAL REQUEST METHOD
----------------------------------------------------------------------------- */

    /**
     * @param string $endpoint API endpoint URI.
     * @param string $method HTTP method.
     * @param array<string, string|int|float|bool>|object $query Query and
     *  path parameters.
     * @param array<mixed>|object $body HTTP body parameters.
     * @return Response Client response object.
     * @throws HttpException HTTP method error.
     * @throws GuzzleException HTTP errors.
     */
    public function request(
        string $endpoint,
        string $method,
        array|object $query = [],
        array|object $body  = [],
    ) : Response
    {
        $query = (array)$query;
        $body  = (array)$body;

        return $this->http->request(
              path: $endpoint,
            method: $method,
             query: $query,
              body: $body
        );
    }
}