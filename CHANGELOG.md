# Changelog

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
