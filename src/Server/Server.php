<?php

namespace Dragooon\Hawk\Server;

use Dragooon\Hawk\Credentials\Credentials;
use Dragooon\Hawk\Credentials\CredentialsInterface;
use Dragooon\Hawk\Credentials\CredentialsNotFoundException;
use Dragooon\Hawk\Credentials\CredentialsProviderInterface;
use Dragooon\Hawk\Crypto\Artifacts;
use Dragooon\Hawk\Crypto\Crypto;
use Dragooon\Hawk\Header\HeaderFactory;
use Dragooon\Hawk\Header\NotHawkAuthorizationException;
use Dragooon\Hawk\Message\Message;
use Dragooon\Hawk\Nonce\NonceValidatorInterface;
use Dragooon\Hawk\Time\TimeProviderInterface;

class Server implements ServerInterface
{
    private $crypto;
    private $credentialsProvider;
    private $timeProvider;
    private $nonceValidator;
    private $timestampSkewSec;
    private $localtimeOffsetSec;

    /**
     * @param Crypto $crypto
     * @param CredentialsProviderInterface $credentialsProvider
     * @param TimeProviderInterface $timeProvider
     * @param NonceValidatorInterface $nonceValidator
     * @param int $timestampSkewSec
     * @param int $localtimeOffsetSec
     */
    public function __construct(
        Crypto $crypto,
        CredentialsProviderInterface $credentialsProvider,
        TimeProviderInterface $timeProvider,
        NonceValidatorInterface $nonceValidator,
        $timestampSkewSec,
        $localtimeOffsetSec
    )
    {
        $this->crypto = $crypto;
        $this->credentialsProvider = $credentialsProvider;
        $this->timeProvider = $timeProvider;
        $this->nonceValidator = $nonceValidator;
        $this->timestampSkewSec = $timestampSkewSec;
        $this->localtimeOffsetSec = $localtimeOffsetSec;
    }

    /**
     * {@inheritDoc}
     */
    public function checkRequestForHawk($headerObjectOrString)
    {
        try {
            HeaderFactory::createFromHeaderObjectOrString(
                'Authorization',
                $headerObjectOrString,
                function() {
                    throw new UnauthorizedException('Not a hawk request');
                }
            );

            return true;
        } catch (UnauthorizedException $e) {
            return false;
        } catch (NotHawkAuthorizationException $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(
        $method,
        $host,
        $port,
        $resource,
        $contentType = null,
        $payload = null,
        $headerObjectOrString = null
    )
    {
        if (null === $headerObjectOrString) {
            throw new UnauthorizedException("Missing Authorization header");
        }

        $header = HeaderFactory::createFromHeaderObjectOrString(
            'Authorization',
            $headerObjectOrString,
            function () {
                throw new UnauthorizedException("Invalid Authorization header");
            }
        );

        // Measure now before any other processing
        $now = $this->timeProvider->createTimestamp() + $this->localtimeOffsetSec;

        $artifacts = new Artifacts(
            $method,
            $host,
            $port,
            $resource,
            $header->attribute('ts'),
            $header->attribute('nonce'),
            $header->attribute('ext'),
            $payload,
            $contentType,
            $header->attribute('hash'),
            $header->attribute('app'),
            $header->attribute('dlg')
        );

        foreach (['id', 'ts', 'nonce', 'mac'] as $requiredAttribute) {
            if (strlen($header->attribute($requiredAttribute)) == 0) {
                throw new UnauthorizedException('Missing attributes');
            }
        }

        $credentials = $this->loadCredentialsById($header->attribute('id'));

        $calculatedMac = $this->crypto->calculateMac('header', $credentials, $artifacts);

        if (!$this->crypto->fixedTimeComparison($calculatedMac, $header->attribute('mac'))) {
            throw new UnauthorizedException('Bad MAC');
        }

        if (null !== $artifacts->payload()) {
            if (null === $artifacts->hash()) {
                // Should this ever happen? Difficult to get a this far if
                // hash is missing as the MAC will probably be wrong anyway.
                throw new UnauthorizedException('Missing required payload hash');
            }

            $calculatedHash = $this->crypto->calculatePayloadHash(
                $artifacts->payload(),
                $credentials->algorithm(),
                $artifacts->contentType()
            );

            if (!$this->crypto->fixedTimeComparison($calculatedHash, $artifacts->hash())) {
                throw new UnauthorizedException('Bad payload hash');
            }
        }

        if (!$this->nonceValidator->validateNonce($credentials->key(), $artifacts->nonce(), $artifacts->timestamp())) {
            throw new UnauthorizedException('Invalid nonce');
        }

        if (abs($header->attribute('ts') - $now) > $this->timestampSkewSec) {
            $ts = $this->timeProvider->createTimestamp() + $this->localtimeOffsetSec;
            $tsm = $this->crypto->calculateTsMac($ts, $credentials);

            throw new UnauthorizedException('Stale timestamp', ['ts' => $ts, 'tsm' => $tsm]);
        }

        return new Response($credentials, $artifacts);
    }

    /**
     * {@inheritDoc}
     */
    public function createHeader(CredentialsInterface $credentials, Artifacts $artifacts, array $options = [])
    {
        if (!$credentials->key()) {
            throw new \InvalidARgumentException('Invalid credentials (missing key)');
        }

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

        $responseArtifacts = new Artifacts(
            $artifacts->method(),
            $artifacts->host(),
            $artifacts->port(),
            $artifacts->resource(),
            $artifacts->timestamp(),
            $artifacts->nonce(),
            $ext,
            $payload,
            $contentType,
            $hash,
            $artifacts->app(),
            $artifacts->dlg()
        );

        $attributes = [
            'mac' => $this->crypto->calculateMac('response', $credentials, $responseArtifacts),
        ];

        if ($hash !== null) {
            $attributes['hash'] = $hash;
        }

        if ($ext) {
            $attributes['ext'] = $ext;
        }

        return HeaderFactory::create('Server-Authorization', $attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function authenticatePayload(
        CredentialsInterface $credentials,
        $payload,
        $contentType,
        $hash
    )
    {
        $calculatedHash = $this->crypto->calculatePayloadHash($payload, $credentials->algorithm(), $contentType);

        return $this->crypto->fixedTimeComparison($calculatedHash, $hash);
    }

    /**
     * {@inheritDoc}
     */
    public function authenticateBewit($host, $port, $resource)
    {
        // Measure now before any other processing
        $now = $this->timeProvider->createTimestamp() + $this->localtimeOffsetSec;

        if (!preg_match(
            '/^(\/.*)([\?&])bewit\=([^&$]*)(?:&(.+))?$/',
            $resource,
            $resourceParts
        )
        ) {
            // TODO: Should this do something else?
            throw new UnauthorizedException('Malformed resource or does not contan bewit');
        }

        $bewit = base64_decode(str_replace(
            ['-', '_', '', ''],
            ['+', '/', '=', "\n"],
            $resourceParts[3]
        ));

        list ($id, $exp, $mac, $ext) = explode('\\', $bewit);

        if ($exp < $now) {
            throw new UnauthorizedException('Access expired');
        }

        $resource = $resourceParts[1];
        if (isset($resourceParts[4])) {
            $resource .= $resourceParts[2] . $resourceParts[4];
        }

        $artifacts = new Artifacts(
            'GET',
            $host,
            $port,
            $resource,
            $exp,
            '',
            $ext
        );

        $credentials = $this->loadCredentialsById($id);

        $calculatedMac = $this->crypto->calculateMac(
            'bewit',
            $credentials,
            $artifacts
        );

        if (!$this->crypto->fixedTimeComparison($calculatedMac, $mac)) {
            throw new UnauthorizedException('Bad MAC');
        }

        return new Response($credentials, $artifacts);
    }

    /**
     * {@inheritDoc}
     */
    public function authenticateMessage($host, $port, $message, Message $authorization)
    {
        if (!$authorization->id() || !$authorization->timestamp() || !$authorization->nonce()
            || !$authorization->hash() || !$authorization->mac()
        ) {
            throw new UnauthorizedException('Bad authorization');
        }

        $credentials = $this->loadCredentialsById($authorization->id());

        $artifacts = new Artifacts(
            null,
            $host,
            $port,
            null,
            $authorization->timestamp(),
            $authorization->nonce(),
            null,
            null,
            null,
            $authorization->hash()
        );

        if (!$this->nonceValidator->validateNonce($credentials->key(), $artifacts->nonce(), $artifacts->timestamp())) {
            throw new UnauthorizedException('Invalid nonce');
        }

        $calculatedMac = $this->crypto->calculateMac('message', $credentials, $artifacts);
        if (!$this->crypto->fixedTimeComparison($calculatedMac, $authorization->mac())) {
            throw new UnauthorizedException('Bad MAC');
        }

        $calculatedHash = $this->crypto->calculatePayloadHash($message, $credentials->algorithm(), '');
        if (!$this->crypto->fixedTimeComparison($calculatedHash, $authorization->hash())) {
            throw new UnauthorizedException('Bad payload hash');
        }

        $now = $this->timeProvider->createTimestamp() + $this->localtimeOffsetSec;

        if (abs($artifacts->timestamp() - $now) > $this->timestampSkewSec) {
            $ts = $this->timeProvider->createTimestamp() + $this->localtimeOffsetSec;
            $tsm = $this->crypto->calculateTsMac($ts, $credentials);

            throw new UnauthorizedException('Stale timestamp', ['ts' => $ts, 'tsm' => $tsm]);
        }

        return new Response($credentials, $artifacts);
    }

    /**
     * Loads a credential by ID
     *
     * @param int $id
     * @return Credentials
     * @throws UnauthorizedException
     */
    protected function loadCredentialsById($id)
    {
        try {
            $credentials = $this->credentialsProvider->loadCredentialsById($id);

            if (!$credentials->key()) {
                throw new UnauthorizedException('Credentials invalid');
            }

            return $credentials;
        } catch (CredentialsNotFoundException $e) {
            throw new UnauthorizedException('Credentials not found');
        }
    }
}
