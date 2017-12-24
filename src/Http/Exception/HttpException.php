<?php

namespace Bizurkur\Bitty\Http\Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpException extends \Exception
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

        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Gets the request object.
     *
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Gets the response object.
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Gets the exception title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Gets the exception description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
