Client
------

### Building a Client

The `Client` has a few required dependencies. It is generally easier to
construct a `Client` by using the `ClientBuilder`. A `Client` can be built
without setting anything to get sane defaults.

#### Simple ClientBuilder Example

```php
<?php

// Simple example
$client = Dragooon\Hawk\Client\ClientBuilder::create()
    ->build()
```

#### Complete ClientBuilderExample

```php
<?php

// A complete example
$client = Dragooon\Hawk\Client\ClientBuilder::create()
    ->setCrypto($crypto)
    ->setTimeProvider($timeProvider)
    ->setNonceProvider($nonceProvider)
    ->setLocaltimeOffset($localtimeOffset)
    ->build()
```

### Creating a Request

In order for a client to be able to sign a request, it needs to know the
credentials for the user making the request, the URL, method, and optionally
payload and content type of the request.

All available options include:

 * **payload**: The body of the request
 * **content_type**: The content-type for the request
 * **nonce**: If a specific nonce should be used in favor of one being generated
   automatically by the nonce provider.
 * **ext**: An ext value specific for this request
 * **app**: The app for this request ([Oz][3] specific)
 * **dlg**: The delegated-by value for this request ([Oz][3] specific)


#### Create Request Example

```php
<?php

$request = $client->createRequest(
    $credentials,
    'http://example.com/foo/bar?whatever',
    'POST',
    array(
        'payload' => 'hello world!',
        'content_type' => 'text/plain',
    )
);

// Assuming a hypothetical $headers object that can be used to add new headers
// to an outbound request, we can add the resulting 'Authorization' header
// for this Hawk request by doing:
$headers->set(
    $request->header()->fieldName(), // 'Authorization'
    $request->header()->fieldValue() // 'Hawk id="12345", mac="ad8c9f', ...'
);

```

#### The Client Request Object

The `Request` represents everything the client needs to know about a request
including a header and the artifacts that were used to create the request.

 * **header()**: A `Header` instance that represents the request
 * **artifacts()**: An `Artifacts` instance that contains the values that were
   used in creating the request

The **header** is required to be able to get the properly formatted Hawk
authorization header to send to the server. The **artifacts** are useful in the
case that authentication will be done on the server response.


### Authenticate Server Response

Hawk provides the ability for the client to authenticate a server response to
ensure that the response sent back is from the intended target.

All available options include:

 * **payload**: The body of the response
 * **content_type**: The content-type for the response


#### Authenticate Response Example

```php
<?php

// Assuming a hypothetical $headers object that can be used to get headers sent
// back as the response of a user agent request, we can get the value for the
// 'Server-Authorization' header.
$header = $headers->get('Server-Authorization');

// We need to use the original credentials, the original request, the value
// for the 'Server-Authorization' header, and optionally the payload and
// content type of the response from the server.
$isAuthenticatedResponse = $client->authenticate(
    $credentials,
    $request,
    $header,
    array(
        'payload' => '{"message": "good day, sir!"}',
        'content_type' => 'application/json',
    )
);
```

### Complete Client Example

```php
<?php

// Create a set of Hawk credentials
$credentials = new Dragooon\Hawk\Credentials\Credentials(
    'afe89a3x',  // shared key
    'sha256',    // default: sha256
    '12345'      // identifier, default: null
);

// Create a Hawk client
$client = Dragooon\Hawk\Client\ClientBuilder::create()
    ->build();

// Create a Hawk request based on making a POST request to a specific URL
// using a specific user's credentials. Also, we're expecting that we'll
// be sending a payload of 'hello world!' with a content-type of 'text/plain'.
$request = $client->createRequest(
    $credentials,
    'http://example.com/foo/bar?whatever',
    'POST',
    array(
        'payload' => 'hello world!',
        'content_type' => 'text/plain',
    )
);

// Ask a really useful fictional user agent to make a request; note that the
// request we are making here matches the details that we told the Hawk client
// about our request.
$response = Fictional\UserAgent::makeRequest(
    'POST',
    'http://example.com/foo/bar?whatever',
    array(
        'content_type' => 'text/plain',
        $request->header()->fieldName() => $request->header()->fieldValue(),
    ),
    'hello world!'
);

// This part is optional but recommended! At this point if we have a successful
// response we could just look at the content and be done with it. However, we
// are given the tools to authenticate the response to ensure that the response
// we were given came from the server we were expecting to be talking to.
$isAuthenticatedResponse = $client->authenticate(
    $credentials,
    $request,
    $response->headers->get('Server-Authorization'),
    array(
        'payload' => $response->getContent(),
        'content_type' => $response->headers->get('content-type'),
    )
);

if (!$isAuthenticatedResponse) {
    die("The server did a very bad thing...");
}

// Huzzah!
```