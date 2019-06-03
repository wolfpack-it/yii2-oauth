<?php

namespace WolfpackIT\oauth\components;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use yii\web\Request as YiiRequest;

/**
 * Functions are implemented at the moment they are needed.
 *
 * Class Request
 * @package WolfpackIT\oauth\components
 */
class Request
    extends YiiRequest
    implements RequestInterface
{
    public function getProtocolVersion()
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getProtocolVersion() method.
    }

    public function withProtocolVersion($version)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withProtocolVersion() method.
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader($name)
    {
        return $this->headers->offsetExists($name);
    }

    /**
     * @param string $name
     * @return array
     */
    public function getHeader($name)
    {
        $result = $this->headers->get($name);
        return is_array($result) ? $result : [$result];
    }

    public function getHeaderLine($name)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getHeaderLine() method.
    }

    public function withHeader($name, $value)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withHeader() method.
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

    public function getBody()
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getBody() method.
    }

    public function withBody(StreamInterface $body)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withBody() method.
    }

    public function getRequestTarget()
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getRequestTarget() method.
    }

    public function withRequestTarget($requestTarget)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withRequestTarget() method.
    }

    public function withMethod($method)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withMethod() method.
    }

    public function getUri()
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getUri() method.
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withUri() method.
    }

    /**
     * @return mixed
     */
    public function getServerParams()
    {
        return $_SERVER;
    }

    public function getCookieParams()
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getCookieParams() method.
    }

    public function withCookieParams(array $cookies)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withCookieParams() method.
    }

    public function withQueryParams(array $query)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withQueryParams() method.
    }

    public function getUploadedFiles()
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getUploadedFiles() method.
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withUploadedFiles() method.
    }

    public function getParsedBody()
    {
        return $this->bodyParams;
    }

    public function withParsedBody($data)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withParsedBody() method.
    }

    public function getAttributes()
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getAttributes() method.
    }

    public function getAttribute($name, $default = null)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement getAttribute() method.
    }

    public function withAttribute($name, $value)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withAttribute() method.
    }

    public function withoutAttribute($name)
    {
        throw new \Exception('Not implemented yet');
        // TODO: Implement withoutAttribute() method.
    }
}