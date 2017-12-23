<?php

namespace Bizurkur\Bitty\Http;

use Bizurkur\Bitty\Http\Response;

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
        $headers = []
    ) {
        $headers['Content-type'] = ['application/json'];
        $json = json_encode($body);

        parent::__construct($json, $statusCode, $headers);
    }
}
