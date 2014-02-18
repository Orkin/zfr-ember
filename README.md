# ZfrEmber

ZfrEmber is a ZF2 module that integrates with ZfrRest to easily output data that is compliant with the expected
format of Ember-Data.

This version is compatible with version 1.0.0-beta6 of Ember-Data. Ember-Data is supposed to implement the open
format [JSON-API](http://jsonapi.org). When this is the case, I will rather move this implementation to ZfrRest
itself.

## Requirements

- PHP 5.4 or higher
- [ZfrRest](https://github.com/zf-fr/zfr-rest)

## Installation

Installation is only officially supported using Composer:

```sh
php composer.phar require zfr/zfr-ember:0.1.*
```

## Documentation

ZfrEmber outputs data that is compliant with Ember-Data JSON format. Currently, Ember-Data format looks like
[JSON-API](http://jsonapi.org) format, but has subtle differences that currently prevent us to create a more
generic adapter.

### Worfklow

ZfrRest architecture makes your data following a specific workflow. The resource renderer occurs at the very
end of the process. The task of the renderer is **not to** modify the data that make your payload, but rather
reformat them to fit into a specific format. For instance, it may wrap the whole data around a key.

Therefore, the task of this module is to automatize the transformation of a "natural" format into a specific
format that can be read by the client.

### Input data

Let's assume those two entities (public properties are only here for example, use getters/setters instead):

```php
class User
{
    public $id;

    public $firstName;

    public $lastName;

    public $tweets;
}
```

```php
class Tweet
{
    public $id;

    public $content;
}
```

Your user hydrator may return the data in different form.

#### Embedding associations

If you embed associations, this simply means that you put the content of the associated entities (here, tweets) in
the same array:

```php
[
    'id' => 5,
    'firstName' => 'Bruce',
    'lastName'  => 'Lee',
    'tweets' => [
        [
            'id' => 6,
            'content' => 'I love karate',
        ],
        [
            'id' => 94,
            'content' => 'I love Hong-Kong'
        ]
    ]
]
```

However, this format is NOT understandable by Ember-Data. This module will transform the payload returned by
your hydrator into a format understandable by Ember-Data:

```php
[
    'user' => [
        'id' => 5,
        'firstName' => 'Bruce',
        'lastName'  => 'Lee',
        'tweets' => [6, 94]
    ],
    'tweets' => [
        [
            'id' => 6,
            'content' => 'I love karate',
        ],
        [
            'id' => 94,
            'content' => 'I love Hong-Kong'
        ]
    ]
]
```

It also work with a single valued association. Additionally, ZfrEmber will pluralize the key of the association,
as required by Ember-Data.

Also note that if you return a collection of users, ZfrEmber will pluralize the top key `user` to `users`.

#### Embedding identifiers for associations

If you only output identifiers using your hydrators:

```php
[
    'id' => 5,
    'firstName' => 'Bruce',
    'lastName'  => 'Lee',
    'tweets' => [
        [
            'id' => 6,
            'content' => 'I love karate',
        ],
        [
            'id' => 94,
            'content' => 'I love Hong-Kong'
        ]
    ]
]
```

ZfrEmber will output the following:

```php
[
    'user' => [
        'id' => 5,
        'firstName' => 'Bruce',
        'lastName'  => 'Lee',
        'tweets' => [6, 94]
    ]
]
```

#### Using links

If your hydrator returns data but omit any values for an association that is exposed (ie.: it contains the
`@REST\Assocation` annotation), then ZfrEmber will automatically generate a link to it.

As a consequence, if your hydrator returns:

```php
[
    'id' => 5,
    'firstName' => 'Bruce',
    'lastName'  => 'Lee'
]
```

ZfrEmber will output:

```php
[
    'user' => [
        'id' => 5,
        'firstName' => 'Bruce',
        'lastName'  => 'Lee',
        'links' => [
            'tweets' => '/users/5/tweets'
        ]
    ]
]
```

#### Metadata

Ember-Data expects metadata (like pagination info) to be put into a `meta` top key. If you return a Paginator,
the following data will be added into the `meta` top-key:

* `limit`: how many elements are retrieved per page
* `offset`: by how many elements the current query is offsetted.
* `total`: the total count of elements.
