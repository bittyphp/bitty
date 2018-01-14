<?php

namespace Bitty\Http\Exception;

use Bitty\Http\Exception\HttpExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpException extends \Exception implements HttpExceptionInterface
{
    /**
     * The request object, if available.
     *
     * @var ServerRequestInterface
     */
    protected $request = null;

    /**
     * The response object, if available.
     *
     * @var ResponseInterface
     */
    protected $response = null;

    /**
     * Title of the exception, e.g. "404 Not Found"
     *
     * @var string
     */
    protected $title = '';

    /**
     * Description of the exception.
     *
     * @var string
     */
    protected $description = '';

    /**
     * @param string|null $message
     * @param int $code
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param \Exception $previous
     */
    public function __construct(
        $message = null,
        $code = 0,
        ServerRequestInterface $request = null,
        ResponseInterface $response = null,
        \Exception $previous = null
    ) {
        if (null === $message) {
            $message = $this->message;
        }

        if (0 === $code) {
            $code = $this->code;
        }

        parent::__construct($message, $code, $previous);

        $this->request  = $request;
        $this->response = $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->description;
    }
}
