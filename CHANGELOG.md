# Changelog

## 1.0.1

Bug fixes:
 * Validates only the top level Data Transfer Object (DTO), not the nested DTO.
 * Fix the creation of a collection of a DTO.
 * Fix the `UniqueEntityClass` service when no `EntityManager` is configured.

## 1.0.0

Features:
 * Add ParamConverter for Data Transfer Objects (DTO) that automatically sets the DTO fields based on the request content.
 * Add UniqueEntityData validator designed for DTO.
