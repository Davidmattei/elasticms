<?php

namespace App\CLI\Helper;

use App\CLI\Client\WebToElasticms\Helper\Url;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpClient\HttplugClient;

class TikaClient
{
    final public const TIKA_BASE_URL = 'http://localhost:9998/';
    private readonly Url $serverUrl;
    private ?HttplugClient $client = null;

    public function __construct(string $serverBaseUrl = self::TIKA_BASE_URL)
    {
        $this->serverUrl = new Url($serverBaseUrl);
    }

    public function meta(StreamInterface $asset, string $mimeType): TikaMetaResponse
    {
        return new TikaMetaResponse($this->putAcceptJson('meta', $asset, $mimeType));
    }

    public function text(StreamInterface $asset, string $mimeType): AsyncResponse
    {
        return $this->putAcceptText('tika', $asset, $mimeType);
    }

    public function html(StreamInterface $asset, string $mimeType): AsyncResponse
    {
        return $this->putAcceptHtml('tika', $asset, $mimeType);
    }

    private function getClient(): HttplugClient
    {
        if (null !== $this->client) {
            return $this->client;
        }
        $this->client = new HttplugClient();

        return $this->client;
    }

    private function putAcceptText(string $url, StreamInterface $asset, string $mimeType): AsyncResponse
    {
        $this->rewind($asset);
        $request = $this->getClient()->createRequest('PUT', $this->serverUrl->getUrl($url), [
            'Accept' => 'text/plain',
            'Content-Type' => $mimeType,
        ], $asset);

        return new AsyncResponse($this->getClient()->sendAsyncRequest($request));
    }

    private function putAcceptJson(string $url, StreamInterface $asset, string $mimeType): AsyncResponse
    {
        $this->rewind($asset);
        $request = $this->getClient()->createRequest('PUT', $this->serverUrl->getUrl($url), [
            'Accept' => 'application/json',
            'Content-Type' => $mimeType,
        ], $asset);

        return new AsyncResponse($this->getClient()->sendAsyncRequest($request));
    }

    private function putAcceptHtml(string $url, StreamInterface $asset, string $mimeType): AsyncResponse
    {
        $this->rewind($asset);
        $request = $this->getClient()->createRequest('PUT', $this->serverUrl->getUrl($url), [
            'Accept' => 'text/html',
            'Content-Type' => $mimeType,
        ], $asset);

        return new AsyncResponse($this->getClient()->sendAsyncRequest($request));
    }

    private function rewind(StreamInterface $asset): void
    {
        if ($asset->isSeekable() && $asset->tell() > 0) {
            $asset->rewind();
        }
    }
}
