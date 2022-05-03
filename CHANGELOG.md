# Changelog

## 3.0.0

Breaking Change:
 * The DTO annotation has been removed.
 * You now need to implement the `DataTransferObjectInterface` to your DTO
 * `DtoUtility::loadArrayToDto` is now available through the Symfony Serializer using the denormalizer.
 * `DtoUtility::updateEntityList` is now available at `EntityUtils::updateCollection`
 * You no longer need to declare the DTO as a service
 
New feature:
 * Implementation of a DTO normalizer/denormalizer for Symfony's Serializer

## 2.3.4

Bug fix:
 * Relax ext-json version requirement to allow php 7.4 compatibility (ext-json versionning strategy changed to follow php version, so ext-json went from 1.5 to 7.4)

## 2.3.3

New feature:
 * Added a new parameter to the DtoUtility::updateEntityList() function. It allows to give the list of properties on which the comparison of the entities is made.

## 2.3.2

Bug fix:
 * Fix the top level DTO validation again. Encapsulate the DataTransferObjectParamConverter request into cloned request to avoid working on original request.

## 2.3.1

Bug fix:
 * Fix the top level DTO validation, where it previously validated all sub DTO instead of validating the top level DTO and let the validator cascading on the properties.

## 2.3.0

New feature:
 * The DTO handler can now also bind data from a json file in a multipart/form-data request. It now loads the content with the following priority: `Request > File > Attributes > Query`. The json file in the body is identified by the mime type `application/json` or `text/json`. The DTO handler will also remove these files from the Request object to leave you with only the other files in your multipart body (such as uploaded images).

## 2.2.4

Bug fix:
 * Fix unintended breaking change introduced in the previous release.

## 2.2.3

Bug fix:
 * Don't treat null as a valid value when converting a value to an entity.

## 2.2.2

Bug fix:
 * Handles the exception returned by the manager during the conversion, and transforms it into violations.

## 2.2.1

Bug fix:
 * Fix default values in the bundle configuration definition.

## 2.2.0

New features:
 * The ability to set validation groups linked to an HTTP status code ([doc](Doc/ParamConverter.md#global-validation-groups)).
 * Add raw input data validation using the `dto_raw_input_validation` validation group ([doc](Doc/ParamConverter.md#pre-validation-brefore-data-conversion)).
 * Add the `Field annotation` ([doc](Doc/DataTransferObject.md#field-annotation)).
 * Add the utility to bind an array to a DTO using the ParamConverter ([doc](Doc/Utilities.md#load-an-array-into-a-dto-using-the-magic-of-the-paramconverter)).

## 2.1.2

Bug fix:
 * The `@Assert\DateTime` and `@Assert\Date` will now transform the property type in `DateTime` if no type is set.
 * You can now add a type to bypass the exception thrown by a `ParamConverter`. Check the [documentation](Doc/ParamConverter.md#bypass-paramconverter-exception-for-specific-classes).


## 2.1.1

Behaviour Change:
 * The DTO handler can now also bind data from the cookie of the `Request`. It now uses the following priority: `Request > Attributes > Query > Cookies`.


## 2.1.0

New features:
 * The DTO handler can now also bind data from the attributes and query of the `Request` object. It loads the content with the following priority: `Request > Attributes > Query`.


## 2.0.0

New features:
 * Add a static function to update the Collection of entities with an input array.
 * Changing the `except` option for the `UniqueEntityData` constraint to support the Expression language.
 * In case of violations from the ParamConverter, the returned value is directly an array of violations instead of a message with the violations formatted in JSON.
 * It is possible to use the tag `app.data_transfer_object` to declare DTO instead of using the `@DTO` annotation.
 
Breaking changes:
 * By changing the `except` option for the `UniqueEntityData` constraint, it is now mandatory to use `this` to target a property withing the DTO (e.g. `this.targetEntity`).
 * In case of violations, the returned value changed from a message with a JSON encoded string to an array of violations.


## 1.0.1

Bug fixes:
 * Validates only the top level Data Transfer Object (DTO), not the nested DTO.
 * Fix the creation of a collection of a DTO.
 * Fix the `UniqueEntityClass` service when no `EntityManager` is configured.
 

## 1.0.0

Features:
 * Add ParamConverter for Data Transfer Objects (DTO) that automatically sets the DTO fields based on the request content.
 * Add UniqueEntityData validator designed for DTO.
