<?php

namespace WolfpackIT\oauth\components;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use yii\web\Response as YiiResponse;

/**
 * Functions are implemented at the moment they are needed.
 *
 * Class Response
 * @package WolfpackIT\oauth\components
 */
class Response
    implements ResponseInterface
{
    /**
     * @var YiiResponse
     */
    protected $response;

    /**
     * Response constructor.
     * @param YiiResponse $response
     */
    public function __construct(YiiResponse $response)
    {
        $this->response = $response;
    }

    public function getResponse(): YiiResponse
    {
        return $this->response;
    }

    /**
     * @return array|\string[][]
     */
    public function getHeaders()
    {
        return $this->response->headers->toArray();
    }

    public function getProtocolVersion()
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getProtocolVersion() method.
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->response->statusCode;
    }

    public function withProtocolVersion($version)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withProtocolVersion() method.
    }

    public function hasHeader($name)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement hasHeader() method.
    }

    public function getHeader($name)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getHeader() method.
    }

    public function getHeaderLine($name)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getHeaderLine() method.
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @return $this|ResponseInterface
     */
    public function withHeader($name, $value)
    {
        $this->response->headers->set($name, $value);
        return $this;
    }

    public function withAddedHeader($name, $value)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withAddedHeader() method.
    }

    public function withoutHeader($name)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withoutHeader() method.
    }

    /**
     * Temp solution to be able to use the OAuth2 implementation
     * @return $this|StreamInterface
     */
    public function getBody()
    {
        return $this;
    }

    public function withBody(StreamInterface $body)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withBody() method.
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $this->response->statusCode = $code;
        $this->response->statusText = $reasonPhrase;
        return $this;
    }

    public function getReasonPhrase()
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getReasonPhrase() method.
    }

    /**
     * Temp solution to be able to use the OAuth2 implementation
     */
    public function write($content)
    {
        $this->response->data = json_decode($content, true);
        return $this;
    }
}