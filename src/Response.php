<?php

declare( strict_types = 1 );

namespace Ocolin\CalixAxos;

readonly class Response
{
    /**
     * @param int $status HTTP status code.
     * @param string $statusMessage HTTP status message.
     * @param array<string, string[]> $headers HTTP response headers.
     * @param array<mixed>|object|string $body HTTP body.
     */
    public function __construct(
        public int                  $status,
        public string               $statusMessage,
        public array                $headers,
        public array|object|string  $body,
    ) {}
}