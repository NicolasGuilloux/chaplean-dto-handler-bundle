# Utilities

All utilities are accessible from the `DtoUtility`. Some may require to inject the class as a service, others are accessible whatever the context via static functions.

## Update an ArrayCollection of entities

Doctrine supports badly the changes in an `ArrayCollection` of entities in a `OneToMany` or in `ManyToMany` relationship. In facts, if a different instance of an `ArrayCollection` replaces an existing one, Doctrine will remove all links and recreate the new, so it doesn't take into account the unchanged relations.

The function `updateCollection` updates an existing `ArrayCollection` from a traversable object without replacing the instance of the input `ArrayCollection` to avoid this bug.

TODO Expand and show examples.

## Load an array into a DTO using the magic of the ParamConverter

TODO Explain Normalizer/Denormalizer magic.
