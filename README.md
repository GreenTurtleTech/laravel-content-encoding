# Laravel Content Encoding Middleware

## About

Middleware that encodes response content.

Reduces data sent out, reduces bandwidth used.

## Installation

Require the `` package in your composer.json and update your dependencies:

## Configuration

The defaults are set in `config/content-encoding.php`.  
To publish a copy to your own config, use the following:

```text
php artisan vendor:publish --tag="green-turtle-content-encoding"
```

### Encode Unknown Types

Sometimes the `Content-Type` header may be missing.
You may specify in your config whether you still wish to try encoding data.

By default, it is set to false.

```php
'encode_unknown_type' => false,
```

### Allowed Types

These are the types of content allowed to be encoded.  
Each type is a string that will be used as a regex pattern.

Example, any text format is acceptable:

```php
'allowed_types' => [ '#^(text\/.*)(;.*)?$#' ]
```

#### Encoders

These are the encoders determine what encodings are supported.

The built-in Encoders are enabled by default:

```php
'encoders' => [
    Gzip::class,
    Deflate::class,
]
```

You may create more by implementing the following interface:

```text
GreenTurtle\Middleware\Encoder\ContentEncoder
```

## Global Usage

To enable this middleware globally, add the following to your `middleware` array, found within  `app/Http/Kernel.php`:

For example:

```php
protected $middleware = [
  // other middleware...
  \GreenTurtle\Middleware\ContentEncoding::class
  // other middleware...
];
```
