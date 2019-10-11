# ParamConverter configuration

The `data_transfer_object_converter` maps all public variables from the DTO to the appropriate `ParamConverter`.

The binded values are the content of the request, or the attributes, or the query with this respective priority.

## Tagged DTO

To recognize the DTO, the ParamConverter required the `@DTO` annotation or the class to be tagged with `app.data_transfer_object`. To learn more about the annotation, please go [here](DataTransferObject.md#mandatory-annotations).

To declare all classes in a folder as DTO, you can set the following configuration. For the concerned class, you will no longer need to set the `@DTO` annotation.

```yaml
services:
    ...
    
    AppBundle\DataTransferObject\:
        resource: '../src/AppBundle/DataTransferObject/*'
        tags: ['app.data_transfer_object']
```

## Validation

### Default behaviour

By default, the converter will validate the output and throw a `400 Bad Request` code if there is any violation. The content of the message will be the list of violations.

### Global validation groups

The DTO Handler supports the configuration of the default validation groups to check. Moreover, it can be linked to a HTTP status code returned when there is at least one violation during the validation. Just add the following configuration to your application. Don't forget to add the `Default` validation group to validate it and throw a `400 Bad Request` if you want to keep the default behaviour.

```yaml
chaplean_dto_handler:
    http_code_validation_groups:
        - { validation_group: http_conflict_exception, http_status_code: 409, priority : -1 }
        - { validation_group: Default, http_status_code: 400 }
```

The validation group with the highest priority is validated first and so on. The default values are:

```yaml
{ validation_group: null, http_status_code: 400, priority : 0 }
```

### Specific validation Groups

To define the validation groups, you need to explicitly declare the `ParamConverter` in the controller action and set the option `groups`. The following example uses the annotation configuration:

```
@ParamConverter(
    name="dtoVariable",
    converter="data_transfer_object_converter",
    options={
        "groups": {"group_one", "groupe_two"}
    }
)
```


### Pre-validation brefore data conversion

To validation the raw input before any data conversion, use the assertions with the validation group `dto_raw_input_validation`.


### Disable validation

To disable the validation, you also need to explicitly declare the `ParamConverter` in the controller action and add the option `validate` to `false`. The following example uses the annotation configuration:

```
@ParamConverter(
    name="dtoVariable",
    converter="data_transfer_object_converter",
    options={"validate": false}
)
```

### Handles violations

You can also choose to recover the violations list to process it from the controller without throwing an error. To do that, you need to declare an additional argument in your controller and add the option `validations` to the name of the variable which will host the violation list.

The following example handles the violations list into the variable `$violationsList`:

```php
/**
 * @ParamConverter(
 *    name="dtoVariable",
 *    converter="data_transfer_object_converter",
 *    options={"violations": "violationsList"}
 * )
 */
public function postAction(
    DummyDataTransferObject $dtoVariable,
    ConstraintViolationListInterface $violationsList
): Response {
    // ...
}
```

### Bypass `ParamConverter` exception for specific classes

Some `ParamConverter` will throw an exception in case of a bad input. This is the case of the `DateTimeParamConverter` which will throw a 404 Not Found error if it fails to transform the input in date, when you give no value for instance. A 404 is not appropriated in most cases, especially if you have the `NotBlank` or `NotNull` assertion which will better handle the error.

To bypass it, you can set the following options. This is the default value.

```yaml
chaplean_dto_handler:
    bypass_param_converter_exception:
        - 'DateTime'
```

