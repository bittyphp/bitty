<?php

namespace Bizurkur\Bitty\Http;

use Bizurkur\Bitty\Collection;

class ServerCollection extends Collection
{
    /**
     * Gets the server headers.
     *
     * @return array[]
     */
    public function getHeaders()
    {
        $contentHeaders = [
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5' => 'Content-MD5',
            'CONTENT_TYPE' => 'Content-Type',
        ];

        $headers = [];
        foreach ($this->data as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $name = $this->normalizeHeader(substr($key, 5));
                $headers[$name] = [$value];
            } elseif (isset($contentHeaders[$key])) {
                $name = $contentHeaders[$key];
                $headers[$name] = [$value];
            }
        }

        return $headers;
    }

    /**
     * Normalizers a header name.
     *
     * @param string $header
     *
     * @return string
     */
    protected function normalizeHeader($header)
    {
        return implode('-',
            array_map('ucfirst',
                explode('_', strtolower($header))
            )
        );
    }
}
