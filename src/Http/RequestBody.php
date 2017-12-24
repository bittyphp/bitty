<?php

namespace Bizurkur\Bitty\Http;

use Bizurkur\Bitty\Http\Stream;

class RequestBody extends Stream
{
    /**
     * Create a wrapper around Stream that automatically graps the input stream.
     */
    public function __construct()
    {
        $stream = fopen('php://temp', 'w+');
        stream_copy_to_stream(fopen('php://input', 'r'), $stream);

        parent::__construct($stream);
    }
}
