<?php

namespace Bitty\Http;

use Bitty\Http\Response;

class JsonResponse extends Response
{
    /**
     * @param mixed $body Any value that can be JSON encoded.
     * @param int $statusCode
     * @param string[] $headers
     */
    public function __construct(
        $body = '',
        $statusCode = 200,
        array $headers = []
    ) {
        $json = json_encode($body);
        parent::__construct($json, $statusCode, $headers);

        // forcibly override content type
        $this->headers = $this->withHeader('Content-Type', 'application/json')->getHeaders();
    }
}
