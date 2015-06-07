Credentials
-----------

### Dragooon\Hawk\Credentials\CredentialsInterface

Represents a valid set of credentials.

 * **key()**: Used to calculate the MAC
 * **algorithm()**: The algorithm used to calculate hashes
 * **id()**: An identifier (e.g. username) for whom the key belongs

In some contexts only the key may be known.

### Dragooon\Hawk\Credentials\Credentials

A simple implementation of `CredentialsInterface`.

```php
<?php

$credentials = new Dragooon\Hawk\Credentials\Credentials(
    $key,        // shared key
    $algorithm,  // default: sha256
    $id          // identifier, default: null
);

```