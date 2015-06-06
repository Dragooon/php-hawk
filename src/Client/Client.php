<?php

namespace Dragooon\Hawk\Client;

use Dragooon\Hawk\Credentials\CredentialsInterface;
use Dragooon\Hawk\Crypto\Artifacts;
use Dragooon\Hawk\Crypto\Crypto;
use Dragooon\Hawk\Header\HeaderFactory;
use Dragooon\Hawk\Nonce\NonceProviderInterface;
use Dragooon\Hawk\Time\TimeProviderInterface;
use Dragooon\Hawk\Message\Message;

class Client implements ClientInterface
{
    private $crypto;
    private $timeProvider;
    private $nonceProvider;
    private $localtimeOffset;

    /**
     * @param Crypto $crypto
     * @param TimeProviderInterface $timeProvider
     * @param NonceProviderInterface $nonceProvider
     * @param int $localtimeOffset
     */
    public function __construct(
        Crypto $crypto,
        TimeProviderInterface $timeProvider,
        NonceProviderInterface $nonceProvider,
        $localtimeOffset
    )
    {
        $this->crypto = $crypto;
        $this->timeProvider = $timeProvider;
        $this->nonceProvider = $nonceProvider;
        $this->localtimeOffset = $localtimeOffset;
    }

    /**
     * {@inheritDoc}
     */
    public function createRequest(CredentialsInterface $credentials, $uri, $method, array $options = array())
    {
        if (empty($method) || !is_string($method)) {
            throw new \InvalidArgumentException('Specified method is invalid');
        } elseif (!$credentials->key() || !$credentials->id() || !$credentials->algorithm()) {
            throw new \InvalidArgumentException('Specified credentials is invalid');
        }

        $timestamp = isset($options['timestamp']) ? $options['timestamp'] : $this->timeProvider->createTimestamp();
        if ($this->localtimeOffset) {
            $timestamp += $this->localtimeOffset;
        }

        list ($host, $resource, $port) = $this->parseURI($uri);

        $nonce = isset($options['nonce']) ? $options['nonce'] : $this->nonceProvider->createNonce();

        if (isset($options['payload'])) {
            $payload = $options['payload'];
            $contentType = !empty($options['content_type']) ? $options['content_type'] : '';
            $hash = $this->crypto->calculatePayloadHash($payload, $credentials->algorithm(), $contentType);
        } else {
            $payload = null;
            $contentType = null;
            $hash = null;
        }

        $ext = isset($options['ext']) ? $options['ext'] : null;
        $app = isset($options['app']) ? $options['app'] : null;
        $dlg = isset($options['dlg']) ? $options['dlg'] : null;

        $artifacts = new Artifacts(
            $method,
            $host,
            $port,
            $resource,
            $timestamp,
            $nonce,
            $ext,
            $payload,
            $contentType,
            $hash,
            $app,
            $dlg
        );

        $attributes = array(
            'id' => $credentials->id(),
            'ts' => $artifacts->timestamp(),
            'nonce' => $artifacts->nonce(),
        );

        if (null !== $hash) {
            $attributes['hash'] = $hash;
        }

        if (null !== $ext) {
            $attributes['ext'] = $ext;
        }

        $attributes['mac'] = $this->crypto->calculateMac('header', $credentials, $artifacts);

        if (null !== $app) {
            $attributes['app'] = $app;
        }

        if (null !== $dlg) {
            $attributes['dlg'] = $dlg;
        }

        return new Request(HeaderFactory::create('Authorization', $attributes), $artifacts);
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(
        CredentialsInterface $credentials,
        Request $request,
        $headerObjectOrString,
        array $options = array()
    )
    {
        $header = HeaderFactory::createFromHeaderObjectOrString(
            'Server-Authorization',
            $headerObjectOrString,
            function () {
                throw new \InvalidArgumentException(
                    'Header must either be a string or an instance of "Dragooon\Hawk\Header\Header"'
                );
            }
        );

        if (isset($options['payload'])) {
            $payload = $options['payload'];
            $contentType = !empty($options['content_type']) ? $options['content_type'] : '';
        } else {
            $payload = null;
            $contentType = null;
        }

        if ($ts = $header->attribute('ts')) {
            // @todo do something with ts
        }

        $artifacts = new Artifacts(
            $request->artifacts()->method(),
            $request->artifacts()->host(),
            $request->artifacts()->port(),
            $request->artifacts()->resource(),
            $request->artifacts()->timestamp(),
            $request->artifacts()->nonce(),
            $header->attribute('ext'),
            $payload,
            $contentType,
            $header->attribute('hash'),
            $request->artifacts()->app(),
            $request->artifacts()->dlg()
        );

        $mac = $this->crypto->calculateMac('response', $credentials, $artifacts);
        if (!$this->crypto->fixedTimeComparison($mac, $header->attribute('mac'))) {
            return false;
        }

        if (!$payload) {
            return true;
        }

        if (!$artifacts->hash()) {
            return false;
        }

        $hash = $this->crypto->calculatePayloadHash($payload, $credentials->algorithm(), $contentType);
        return $this->crypto->fixedTimeComparison($hash, $artifacts->hash());
    }

    /**
     * {@inheritDoc}
     */
    public function createBewit(CredentialsInterface $credentials, $uri, $ttlSec, array $options = array())
    {
        $timestamp = isset($options['timestamp']) ? $options['timestamp'] : $this->timeProvider->createTimestamp();
        if ($this->localtimeOffset) {
            $timestamp += $this->localtimeOffset;
        }

        list ($host, $resource, $port) = $this->parseURI($uri);

        $ext = isset($options['ext']) ? $options['ext'] : null;

        $exp = $timestamp + $ttlSec;

        $artifacts = new Artifacts(
            'GET',
            $host,
            $port,
            $resource,
            $exp,
            '',
            $ext
        );

        $bewit = implode('\\', array(
            $credentials->id(),
            $exp,
            $this->crypto->calculateMac('bewit', $credentials, $artifacts),
            $ext,
        ));

        return str_replace(
            array('+', '/', '=', "\n"),
            array('-', '_', '', ''),
            base64_encode($bewit)
        );
    }

    /**
     * {@inheritDpc}
     */
    public function createMessage(CredentialsInterface $credentials, $host, $port, $message, array $options = array())
    {
        if (empty($host) || empty($port) || !is_numeric($port)) {
            throw new \InvalidArgumentException('Invalid host or port specified');
        } elseif (!$credentials->key() || !$credentials->id() || !$credentials->algorithm()) {
            throw new \InvalidArgumentException('Specified credentials is invalid');
        } elseif (empty($message) || !is_string($message)) {
            throw new \InvalidArgumentException('Specified message is not valid');
        }

        $timestamp = isset($options['timestamp']) ? $options['timestamp'] : $this->timeProvider->createTimestamp();
        if ($this->localtimeOffset) {
            $timestamp += $this->localtimeOffset;
        }

        $artifacts = new Artifacts(
            '',
            $host,
            $port,
            '',
            $timestamp,
            !empty($options['nonce']) ? $options['nonce'] : $this->nonceProvider->createNonce(),
            '',
            '',
            '',
            $this->crypto->calculatePayloadHash($message, $credentials->algorithm(), '')
        );

        $result = new Message(
            $credentials->id(),
            $timestamp,
            $artifacts->nonce(),
            $artifacts->hash(),
            $this->crypto->calculateMac('message', $credentials, $artifacts)
        );

        return $result;
    }

    /**
     * @param string $uri
     * @return array(host, resource, port)
     *
     * @throws \InvalidArgumentException
     */
    protected function parseURI($uri)
    {
        $parsed = parse_url($uri);

        if (!$parsed || empty($parsed['host'])) {
            throw new \InvalidARgumentException('Specified URI is invalid');
        }

        $host = $parsed['host'];
        $resource = isset($parsed['path']) ? $parsed['path'] : '';

        if (isset($parsed['query'])) {
            $resource .= '?' . $parsed['query'];
        }

        $port = isset($parsed['port']) ? $parsed['port'] : ($parsed['scheme'] === 'https' ? 443 : 80);

        return array($host, $resource, $port);
    }
}
