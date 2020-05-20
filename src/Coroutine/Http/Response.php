<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace Gaobinzhan\Coroutine\Http;


class Response
{
    protected $client;

    public function __construct(\Swoole\Coroutine\Http\Client $client)
    {
        $this->client = $client;
    }

    public function getBody()
    {
        return $this->client->getBody();
    }


    public function getStatusCode()
    {
        return $this->client->getStatusCode();
    }

    public function getHeaders()
    {
        return $this->client->getHeaders();
    }


    public function getErrorMsg()
    {
        if ($this->client->errCode === 0) {
            return null;
        }
        return socket_strerror($this->client->errCode);
    }

    public function getJsonData(){
        return json_decode($this->client->getBody(),true) ?? null;
    }
}