<?php

namespace Gaobinzhan\Http;

Use Gaobinzhan\Config;
Use Gaobinzhan\Http\Request;
Use Gaobinzhan\Http\Response;

final class Client
{
    private static function withHeader(\Gaobinzhan\Coroutine\Http\Client $client)
    {
        $client->setHeader('User-Agent', self::userAgent());
    }

    public static function get($url, array $headers = array())
    {
        $client = new \Gaobinzhan\Coroutine\Http\Client($url);
        $client->setHeaders($headers);
        self::withHeader($client);
        $response = $client->get();
        return new \Gaobinzhan\Http\Response($response);
    }

    public static function delete($url, array $headers = array())
    {
        $request = new Request('DELETE', $url, $headers);
        return self::sendRequest($request);
    }

    public static function post($url, $body, array $headers = array())
    {
        $client = new \Gaobinzhan\Coroutine\Http\Client($url);
        $client->setHeaders($headers);
        self::withHeader($client);
        $response = $client->post($body);
        return new \Gaobinzhan\Http\Response($response);
    }

    public static function PUT($url, $body, array $headers = array())
    {
        $client = new \Gaobinzhan\Coroutine\Http\Client($url);
        $client->setHeaders($headers);
        self::withHeader($client);
        $response = $client->put($body);
        return new \Gaobinzhan\Http\Response($response);
    }

    public static function multipartPost(
        $url,
        $fields,
        $name,
        $fileName,
        $fileBody,
        $mimeType = null,
        array $headers = array()
    )
    {
        $data = array();
        $mimeBoundary = md5(microtime());

        foreach ($fields as $key => $val) {
            array_push($data, '--' . $mimeBoundary);
            array_push($data, "Content-Disposition: form-data; name=\"$key\"");
            array_push($data, '');
            array_push($data, $val);
        }

        array_push($data, '--' . $mimeBoundary);
        $finalMimeType = empty($mimeType) ? 'application/octet-stream' : $mimeType;
        $finalFileName = self::escapeQuotes($fileName);
        array_push($data, "Content-Disposition: form-data; name=\"$name\"; filename=\"$finalFileName\"");
        array_push($data, "Content-Type: $finalMimeType");
        array_push($data, '');
        array_push($data, $fileBody);

        array_push($data, '--' . $mimeBoundary . '--');
        array_push($data, '');

        $body = implode("\r\n", $data);
        // var_dump($data);exit;
        $contentType = 'multipart/form-data; boundary=' . $mimeBoundary;
        $headers['Content-Type'] = $contentType;

        $client = new \Gaobinzhan\Coroutine\Http\Client($url);
        $client->setHeaders($headers);
        self::withHeader($client);
        $response = $client->post($body);
        return new \Gaobinzhan\Http\Response($response);
    }

    private static function userAgent()
    {
        $sdkInfo = "QiniuPHP/" . Config::SDK_VER;

        $systemInfo = php_uname("s");
        $machineInfo = php_uname("m");

        $envInfo = "($systemInfo/$machineInfo)";

        $phpVer = phpversion();

        $ua = "$sdkInfo $envInfo PHP/$phpVer";
        return $ua;
    }

    private static function parseHeaders($raw)
    {
        $headers = array();
        $headerLines = explode("\r\n", $raw);
        foreach ($headerLines as $line) {
            $headerLine = trim($line);
            $kv = explode(':', $headerLine);
            if (count($kv) > 1) {
                $kv[0] = self::ucwordsHyphen($kv[0]);
                $headers[$kv[0]] = trim($kv[1]);
            }
        }
        return $headers;
    }

    private static function escapeQuotes($str)
    {
        $find = array("\\", "\"");
        $replace = array("\\\\", "\\\"");
        return str_replace($find, $replace, $str);
    }

    private static function ucwordsHyphen($str)
    {
        return str_replace('- ', '-', ucwords(str_replace('-', '- ', $str)));
    }
}
