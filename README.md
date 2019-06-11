# dto-handler-bundle

This version of the bundle requires Symfony 3.4+.

[![Package version](https://img.shields.io/packagist/v/chaplean/dto-handler-bundle.svg)](https://packagist.org/packages/chaplean/dto-handler-bundle)
[![Build Status](https://img.shields.io/travis/chaplean/dto-handler-bundle.svg?branch=master)](https://travis-ci.org/chaplean/dto-handler-bundle?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/chaplean/dto-handler-bundle/badge.svg?branch=master)](https://coveralls.io/github/chaplean/dto-handler-bundle?branch=master)
[![contributions welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat)](https://github.com/chaplean/dto-handler-bundle/issues)
[![License](https://img.shields.io/badge/license-MIT-red.svg)](LICENSE.md)

The dto-handler-bundle loads the content of a request into a Data Transfer Object (DTO), mapping its properties such as entities from the database.
It uses the `ParamConverterInterface` provided by the [SensioLabs Framework Extra Bundle](https://symfony.com/doc/4.0/bundles/SensioFrameworkExtraBundle/index.html) to automatically map the request content to the appropriate variables.

The dto-handler-bundle is simple to use as it requires almost no configuration. To use it in a controller, simply declare the variable in the controller's argument:

```php
public function postAction(DummyDataTransferObject $dto): Response
{
   // ...
}
```

And add the `DTO` annotation to your DTO:

```php
/**
 * @DTO
 */
final class DummyDataTransferObject
{
    // ...
}
```


## Table of content

1. [Installation](#1-installation)
2. [Getting started](#2-getting-started)
    - [Set up the Data Transfer Object](Doc/DataTransferObject.md)
    - [Configure the ParamConverter](Doc/ParamConverter.md)
3. [Example](#3-example)
4. [Versioning](#4-versioning)
5. [Contributing](#5-contributing)
6. [Hacking](#6-hacking)
7. [License](#7-license)


## 1. Installation

This bundle required at least Symfony 3.4.

You can use [composer](https://getcomposer.org) to install dto-handler-bundle:
```bash
composer require chaplean/dto-handler-bundle
```

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Chaplean\Bundle\DtoHandlerBundle\ChapleanDtoHandlerBundle(),
        ];

        // ...
    }

    // ...
}
```


## 2. Getting started

- [Set up the Data Transfer Object](Doc/DataTransferObject.md)
- [Configure the ParamConverter](Doc/ParamConverter.md)

## 3. Example

In the following example, the data contained in the request will be loaded in the DTO. 

The `$property1` and the `$property2` will be not changed, so their values will be the one set in the request.

The `$property3` will be mapped to the appropriate entity using the field `keyname`, so the value of the `property3` field in the request should be a `keyname` of the DummyEntity.

The `$property4` will be an array of the entities mapped by `id`, so the `property4` value in the request should be an array of `id`.

Controller:

```php
/**
 * ...
 *
 * @ParamConverter(
 *     name="dtoVariable",
 *     converter="data_transfer_object_converter",
 *     options={"validations": "violationsList"}
 * )
 *
 * @param DummyDataTransferObject          $dummyDataTransferObject
 * @param ConstraintViolationListInterface $violationsList
 *
 * @return Response
 */
public function postAction(
    DummyDataTransferObject $dummyDataTransferObject,
    ConstraintViolationListInterface $violationsList
): Response {
    // ...
}
```

Data Transfer Object (DummyDataTransferObject):

```php
/**
 * Class DummyDataTransferObject.
 *
 * @DTO
 */
final class DummyDataTransferObject
{
    /**
     * @var string
     */
    public $property1;

    /**
     * @var integer
     *
     * @Assert\Type("integer")
     */
    public $property2;

    /**
     * @var DummyEntity
     *
     * @Assert\Type("Chaplean\Bundle\DtoHandlerBundle\Tests\Resources\Entity\DummyEntity")
     * @MapTo("keyname")
     */
    public $property3;

    /**
     * @var DummyEntity
     *
     * @Assert\All(
     *    @Assert\Type("Chaplean\Bundle\DtoHandlerBundle\Tests\Resources\Entity\DummyEntity")
     * )
     */
    public $property4;
}
```

## 4. Versioning

dto-handler-bundle follows [semantic versioning](https://semver.org/). In short the scheme is MAJOR.MINOR.PATCH where
1. MAJOR is bumped when there is a breaking change,
2. MINOR is bumped when a new feature is added in a backward-compatible way,
3. PATCH is bumped when a bug is fixed in a backward-compatible way.

Versions bellow 1.0.0 are considered experimental and breaking changes may occur at any time.


## 5. Contributing

Contributions are welcomed! There are many ways to contribute, and we appreciate all of them. Here are some of the major ones:

* [Bug Reports](https://github.com/chaplean/dto-handler-bundle/issues): While we strive for quality software, bugs can happen and we can't fix issues we're not aware of. So please report even if you're not sure about it or just want to ask a question. If anything the issue might indicate that the documentation can still be improved!
* [Feature Request](https://github.com/chaplean/dto-handler-bundle/issues): You have a use case not covered by the current api? Want to suggest a change or add something? We'd be glad to read about it and start a discussion to try to find the best possible solution.
* [Pull Request](https://github.com/chaplean/dto-handler-bundle/merge_requests): Want to contribute code or documentation? We'd love that! If you need help to get started, GitHub as [documentation](https://help.github.com/articles/about-pull-requests/) on pull requests. We use the ["fork and pull model"](https://help.github.com/articles/about-collaborative-development-models/) were contributors push changes to their personnal fork and then create pull requests to the main repository. Please make your pull requests against the `master` branch.

As a reminder, all contributors are expected to follow our [Code of Conduct](CODE_OF_CONDUCT.md).


## 6. Hacking

You might find the following commands usefull when hacking on this project:

```bash
# Install dependencies
composer install

# Run tests
bin/phpunit
```


## 7. License

dto-handler-bundle is distributed under the terms of the MIT license.

See [LICENSE](LICENSE.md) for details.
