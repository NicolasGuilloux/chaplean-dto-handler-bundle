# ParamConverter configuration

The `data_transfer_object_converter` maps all public variables from the DTO to the appropriate `ParamConverter`.

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

By default, the converter will validate the output and throw a `400 Bad Request` code if there is any violation. The content of the message will be the list of violations formatted in JSON.

### Validation Groups

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
 *    options={"validations": "violationsList"}
 * )
 */
public function postAction(
    DummyDataTransferObject $dtoVariable,
    ConstraintViolationListInterface $violationsList
): Response {
    // ...
}
```
