<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */

namespace Gaobinzhan\Coroutine\Http;

class Client
{
    protected $client;

    protected $host;

    protected $path;

    protected $query;

    protected $port = 80;

    protected $ssl = false;

    protected $headers = [];

    public function __construct($url)
    {
        $urlInfo = $this->parseUrl($url);
        $this->host = $urlInfo['host'];
        $this->path = $urlInfo['path'] ?? '/';
        $this->query = $urlInfo['query'] ?? '';
        if ($urlInfo['scheme'] == 'https') {
            $this->port = 443;
            $this->ssl = true;
        }
        $this->client = new \Swoole\Coroutine\Http\Client($this->host, $this->port, $this->ssl);
    }

    public function setHeader($key, $value)
    {
        $this->headers += [$key => $value];
        $this->client->setHeaders($this->headers);
    }

    public function setHeaders(array $headers)
    {
        $this->headers += $headers;
        $this->client->setHeaders($headers);
    }

    public function get()
    {
        $this->client->get($this->path . '?' . $this->query);
        return new Response($this->client);
    }

    public function post($body)
    {
        $this->client->post($this->path, $body);
        return new Response($this->client);
    }

    public function put($body)
    {
        $this->client->setMethod('PUT');
        $this->client->setData($body);
        $this->client->execute($this->path);
        return new Response($this->client);
    }


    private function parseUrl($url)
    {
        if (strpos($url, 'http') === false && strpos($url, 'https') === false) {
            $url = 'http://' . $url;
        }
        return parse_url($url);
    }
}