<?php

declare( strict_types = 1 );

namespace Ocolin\CalixAxos;

use GuzzleHttp\Client AS GuzzleClient;
use GuzzleHttp\ClientInterface AS GuzzleInterface;
use GuzzleHttp\Exception\GuzzleException;
use Ocolin\CalixAxos\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface;

class HTTP
{
    /**
     * @var GuzzleInterface Guzzle client interface.
     */
    private GuzzleInterface $guzzle;

    /**
     * @var Config Configuration data object.
     */
    private Config $config;

    /**
     * Valid HTTP methods.
     */
    private const array VALID_METHODS = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
    ];

    /**
     * Default Guzzle options.
     */
    private const array DEFAULT_OPTIONS = [
        'timeout'         => 20,
        'connect_timeout' => 20,
        'verify'          => false,
    ];


/* CONSTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * @param Config $config Configuration data object.
     * @param GuzzleInterface|null $guzzle Guzzle interface for mocking.
     */
    public function __construct(
                  Config $config,
        ?GuzzleInterface $guzzle = null,
    )
    {
        $this->config = $config;
        $this->guzzle = $guzzle ?? new GuzzleClient( array_merge(
            self::DEFAULT_OPTIONS,
            $this->config->options,
            [
                'base_uri'    => rtrim(
                        string: (string)$this->config->host, characters: '/'
                    ) . '/',
                'auth' => [ $this->config->username, $this->config->password ],
                'http_errors' => false,
                'headers'     => [
                    'Accept'  => 'application/json; charset=utf-8',
                ],
            ]
        ));
    }



/* API REQUEST
----------------------------------------------------------------------------- */

    /**
     * @param string $path Endpoint URI path.
     * @param string $method HTTP method.
     * @param array<string, string|int|float|bool> $query Query and path parameters.
     * @param array<string, mixed> $body POST body content.
     * @return Response Client API response object.
     * @throws HttpException Invalid HTTP method.
     * @throws GuzzleException Any HTTP related errors.
     */
    public function request(
        string $path,
        string $method = 'GET',
         array $query  = [],
         array $body   = [],
    ) : Response
    {
        $method = strtoupper( $method );
        if( !in_array(
            needle: $method, haystack: self::VALID_METHODS, strict: true )
        ) {
            throw new HttpException(  message: "Invalid HTTP method: {$method}" );
        }

        $path = self::buildPath( path: $path, query: $query );
        $options = [];
        if( !empty( $body ))  { $options['json']  = $body; }
        if( !empty( $query )) { $options['query'] = $query; }

        return self::buildResponse( $this->guzzle->request(
             method: $method,
                uri: $path,
            options: $options,
        ));
    }



/* BUILD URI PATH
----------------------------------------------------------------------------- */

    /**
     * Replaces any variable tokens in URI path and replaces with values.
     *
     * @param string $path HTTP URI path.
     * @param array<string, string|int|float|bool> $query HTTP query and path
        parameters.
     * @return string Interpolated URI path.
     */
    private static function buildPath( string $path, array &$query ): string
    {
        $path = ltrim( string: $path, characters: '/' );
        if( !str_contains( haystack: $path, needle: '{' )) {  return $path; }

        foreach( $query as $key => $value )
        {
            if( str_contains( haystack: $path, needle: "{{$key}}" )) {
                $path = str_replace(
                    search: "{{$key}}", replace: (string)$value, subject: $path
                );
                unset( $query[$key] );
            }
        }

        return $path;
    }



/* CONVERT GUZZLE RESPONSE TO CLIENT RESPONSE
----------------------------------------------------------------------------- */

    /**
     * @param ResponseInterface $response Guzzle HTTP response object.
     * @return Response Client API response object.
     */
    private static function buildResponse( ResponseInterface $response ): Response
    {
        $content = $response->getBody()->getContents();
        $body = json_decode( $content, false );
        if( !is_object( $body ) AND !is_array( $body )) { $body = $content; }

        return new Response(
                   status: $response->getStatusCode(),
            statusMessage: $response->getReasonPhrase(),
                  headers: $response->getHeaders(),
                     body: $body
        );
    }
}