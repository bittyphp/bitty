<?php

namespace Bizurkur\Bitty\Http;

use Bizurkur\Bitty\Http\Response;

class JsonResponse extends Response
{
    /**
     * @param mixed $body Any value that can be JSON encoded.
     * @param string[] $headers
     * @param int $statusCode
     * @param string $reasonPhrase
     * @param string $protocolVersion
     */
    public function __construct(
        $body = '',
        $headers = [],
        $statusCode = 200,
        $reasonPhrase = '',
        $protocolVersion = '1.0'
    ) {
        $headers['Content-type'] = ['application/json'];
        $json = json_encode($body);

        parent::__construct($json, $headers, $statusCode, $reasonPhrase, $protocolVersion);
    }
}
