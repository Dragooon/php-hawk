Server
------

### Building a Server

The `Server` has a few required dependencies. It is generally easier to
construct a `Server` by using the `ServerBuilder`. A `Server` can be built
without setting anything but the crendetials provider to get sane defaults.

#### Simple ServerBuilder Example

```php
<?php

$credentialsProvider = function ($id) {
    if ('12345' === $id) {
        return new Dragooon\Hawk\Credentials\Credentials(
            'afe89a3x',  // shared key
            'sha256',    // default: sha256
            '12345'      // identifier, default: null
        );
    }
};

// Simple example
$server = Dragooon\Hawk\Server\ServerBuilder::create($credentialsProvider)
    ->build()
```

#### Complete ServerBuilderExample

```php
<?php

$credentialsProvider = function ($id) {
    if ('12345' === $id) {
        return new Dragooon\Hawk\Credentials\Credentials(
            'afe89a3x',  // shared key
            'sha256',    // default: sha256
            '12345'      // identifier, default: null
        );
    }
};

// A complete example
$server = Dragooon\Hawk\Server\ServerBuilder::create($credentialsProvider)
    ->setCrypto($crypto)
    ->setTimeProvider($timeProvider)
    ->setNonceValidator($nonceValidator)
    ->setTimestampSkewSec($timestampSkewSec)
    ->setLocaltimeOffsetSec($localtimeOffsetSec)
    ->build()
```

### Authenticating a Request

In order for a server to be able to authenticate a request, it needs to be able
to build the same MAC that the client did. It does this by getting the same
information about the request that the client knew about when it signed the
request.

In particular, the authorization header should include the ID. This ID is used
to retrieve the credentials (notably the key) in order to calculate the MAC
based on the rest of the request information.

#### Authenticate Example

```php
<?php

// Get the authorization header for the request; it should be in the form
// of 'Hawk id="...", mac="...", [...]'
$authorization = $headers->get('Authorization');

try {
    $response = $server->authenticate(
        'POST',
        'example.com',
        80,
        '/foo/bar?whatever',
        'text/plain',
        'hello world!'
        $authorization
    );
} catch(Dragooon\Hawk\Server\UnauthorizedException $e) {
    // If authorization is incorrect (invalid mac, etc.) we can catch an
    // unauthorized exception.
    throw $e;
}

// The credentials associated with this request. This is where one could access
// the ID for the user that made this request.
$credentials = $response->credentials();

// The artifacts associated with this request. This is where one could access
// things like the 'ext', 'app', and 'dlg' values sent with the request.
$artifacts = $response->artifacts();
```

#### The Server Response Object

The `Response` represents everything the server needs to know about a request
including the credentials and artifacts that are associated with the request.

 * **credentials()**
 * **artifacts()**


### Creating a Response Header

Hawk provides the ability for the server to sign the response to privde the
client with a way to authenticate a server response.

All available options include:

 * **payload**: The body of the request
 * **content_type**: The content-type for the request
 * **ext**: An ext value specific for this request


#### Create Response Header Example

```php
<?php

// Using the same credentials and artifacts from the server authenticate
// response, we can create a 'Server-Authorization' header.
$header = $server->createHeader($credentials, $artifacts, array(
    'payload' => '{"message": "good day, sir!"}',
    'content_type' => 'application/json',
));

// Set the header using PHP's header() function.
header(sprintf("%s: %s", $header->fieldName(), $header->fieldValue()));
```

### Complete Server Example

```php
<?php

// Create a simple credentials provider
$credentialsProvider = function ($id) {
    if ('12345' === $id) {
        return new Dragooon\Hawk\Credentials\Credentials(
            'afe89a3x',  // shared key
            'sha256',    // default: sha256
            '12345'      // identifier, default: null
        );
    }
};

// Create a Hawk server
$server = Dragooon\Hawk\Server\ServerBuilder::create($credentialsProvider)
    ->build()

// Get the authorization header for the request; it should be in the form
// of 'Hawk id="...", mac="...", [...]'
$authorization = $headers->get('Authorization');

try {
    $response = $server->authenticate(
        'POST',
        'example.com',
        80,
        '/foo/bar?whatever',
        'text/plain',
        'hello world!'
        $authorization
    );
} catch(Dragooon\Hawk\Server\UnauthorizedException $e) {
    // If authorization is incorrect (invalid mac, etc.) we can catch an
    // unauthorized exception.
    throw $e;
}

// Huzzah! Do something at this point with the request as we now know that
// it is an authenticated Hawk request.
//
// ...
//
// Ok we are done doing things! Assume based on what we did we ended up deciding
// the following payload and content type should be used:

$payload = '{"message": "good day, sir!"}';
$contentType = 'application/json';

// Create a Hawk header to sign our response
$header = $server->createHeader($credentials, $artifacts, array(
    'payload' => $payload,
    'content_type' => $contentType,
));

// Send some headers
header(sprintf("%s: %s", 'Content-Type', 'application/json'));
header(sprintf("%s: %s", $header->fieldName(), $header->fieldValue()));

// Output our payload
print $payload;
```