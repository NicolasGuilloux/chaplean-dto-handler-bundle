# Utilities

All utilities are accessible from the `DtoUtility`. Some may require to inject the class as a service, others are accessible whatever the context via static functions.

## Update an ArrayCollection of entities

Doctrine supports badly the changes in an `ArrayCollection` of entities in a `OneToMany` or in `ManyToMany` relationship. In facts, if a different instance of an `ArrayCollection` replaces an existing one, Doctrine will remove all links and recreate the new, so it doesn't take into account the unchanged relations.

The function `updateEntityList` updates an existing `ArrayCollection` from an traversable object without replacing the instance of the input `ArrayCollection` to avoid this bug.

```php
/**
 * @param DummyEntity $dto
 *
 * @return self
 */
public function updateRelationship(DummyEntity $dto): self
    DtoUtility::updateEntityList($this->relationship, $dto->newValues);

    return $this;
}
```

## Load an array into a DTO using the magic of the ParamConverter

It can be useful to instanciate a DTO from an array out of the default controller argument conversion. For instance, you have an API call somewhere and you want to manipulate an object and validate the input data. Exactly what does the `DataTransferObjectParamConverter`. Options can be passed to the ParamConverter aswell.

```php
/**
 * @return DummyDataTransferObject
 */
public makeAnApiCall(): DummyDataTransferObject
{
    $response = $this->api->callSomething();

    return $this->dtoUtility->loadArrayToDto(
        $response->getContent(),
        DummyDataTransferObject::class
    );
}
```