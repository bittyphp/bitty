<?php

namespace Bizurkur\Bitty\Http;

use Bizurkur\Bitty\Http\Response;

class RedirectResponse extends Response
{
    /**
     * Creates a response that redirects to a new URI.
     *
     * @param string $uri URI to redirect to.
     * @param int $statusCode HTTP status code.
     * @param string[] $headers List of headers.
     */
    public function __construct($uri, $statusCode = 302, array $headers = [])
    {
        $headers['Location'] = [$uri];

        parent::__construct('', $statusCode, $headers);
    }
}
