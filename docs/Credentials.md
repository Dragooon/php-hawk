Credentials
-----------

Credentials are used to identify any user with valid keys to authenticate via Hawk. See
`Dragooon\Hawk\Credentials\CredentialsInterface` for the field's information

### Simple Exmaple

A simple implementation of `CredentialsInterface`.

```php
<?php

$credentials = new Dragooon\Hawk\Credentials\Credentials(
    $key,        // shared key
    $algorithm,  // default: sha256
    $id          // identifier, default: null
);

```

### Credentials Provider

When a credential is required, a credentials provider will be called to load that credential.
See `\Dragooon\Hawk\Credentials\CredentialsProviderInterface`

For example:

```php
<?php

$credentialsProvider = new Dragooon\Hawk\Credentials\CallbackCredentialsProvider(
    function($id) {
        $user = MyApp::loadUser($id);
        return new Credentials(
            $user->hawkKey,
            $user->hawkAlgo,
            $user->id
        );
    }
);
```
