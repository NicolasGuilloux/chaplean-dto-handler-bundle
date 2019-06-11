# Data Transfer Object configuration

## Mandatory annotations

To recognize the Data Transfer Object, the `ParamConverter` requires the class annotation `@DTO` to be set.

To correctly extract the appropriate values to the variables, the `ParamConverter` requires to explicitly declare the type using the `Type` assertion of the Symfony validation component.

The `DataTransferParamConverter` also allows to map a collection of object as well using the Symfony assertion `All`.

The following example uses the annotation style configuration:

```php
/**
 * @DTO
 */
final class DummyDataTransferObject
{
    /**
     * @Assert\Type("App\Entity\YourEntity")
     */
    public $property;
    
    /**
     * @Assert\Type("\DateTime")
     */
    public $anyDate;
    
    /**
     * @Assert\All(
     *     @Assert\Type("App\Entity\AnotherEntity");
     * )
     */
    public $collectionOfEntity
}
```

## MapTo annotation

The MapTo annotation allows to map the content request to a specific field of an Entity. For instance, if the entity `DummyEntity` has a field `keyname`, you can get it using this field by adding the `MapTo` annotation.

Here is an example of this annotation:

```php
/**
 * @Assert\Type("App\Entity\DummyEntity")
 *
 * @MapTo("keyname")
 */
public $dummyEntity;
```

## Validation

### UniqueEntityData

The UniqueEntityData constraint checks if the selected DTO fields are unique for the target entity.

In the following example, the validator will check if a `DummyEntity` with the same `property1` and `property2` fields exists.

```php
/**
 * @DTO
 *
 * @UniqueEntityData(
 *     fields={"property1", "property2"},
 *     entityClass="App\Entity\DummyEntity"
 * )
 */
final class DummyDataTransferObject
{
    public $property1;
    
    public $property2;
}
```

An entity can be accepted using the `except` parameter which will tolerate to break the unicity restriction if the found entity is the same as the one in the `except`:

```php
/**
 * @DTO
 *
 * @UniqueEntityData(
 *     fields={"property1", "property2"},
 *     entityClass="App\Entity\DummyEntity",
 *     except="targetEntity"
 * )
 */
final class DummyDataTransferObject
{
    /**
     * @Assert\Type("App\Entity\DummyEntity")
     */
    public $targetEntity;
    
    public $property1;
    
    public $property2;
}
```
