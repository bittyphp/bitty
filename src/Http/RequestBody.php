<?php

namespace Bitty\Http;

use Bitty\Http\Stream;

class RequestBody extends Stream
{
    /**
     * Create a wrapper around Stream that automatically graps the input stream.
     */
    public function __construct()
    {
        $stream = fopen('php://temp', 'w+');
        $input  = fopen('php://input', 'r');
        stream_copy_to_stream($input, $stream);

        parent::__construct($stream);
    }
}
