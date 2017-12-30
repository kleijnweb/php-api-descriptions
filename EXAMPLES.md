### Custom Processors

Class name resolution and DateTime handling can be tweaked by injecting custom instances into the builder, but pretty much all parts of the hydration process are customizable. You can inject custom processors by injecting factories for them into the "processor factory queue". 
All of the processors and their factories are open for extension.  

Here is a trivial example that preempts the default scalar processor.

```php
$builder->add(
  new class implements Factory
  {
      public function create(Schema $schema, ProcessorBuilder $builder)
      {
          if (!$schema instanceof ScalarSchema) {
              return null;
          }
  
          return new class($schema) extends Processor
          {
              public function hydrate($value)
              {
                  return 42;
              }
  
              public function dehydrate($value)
              {
                  return 'still 42';
              }
          };
      }
  
      public function getPriority(): int
      {
          return ScalarFactory::PRIORITY + 1;
      }
  }
);

$processor = $builder->build(TestHelperFactory::createFullPetSchema());

/** @var Pet $actual */
$actual = $processor->hydrate((object)['id' => 999]);
$this->assertSame(42, $actual->getId());

/** @var \stdClass $actual */
$actual = $processor->dehydrate($actual);
$this->assertSame('still 42', $actual->id);
```

A more typical use case would be to inject custom logic for complex types. The following example uses an identity map to make sure variables with the same ID of a certain type refer to the same object. A typical variant of this would fetch objects from a data store.

```php
$builder->add(
    new class($classNameResolver) extends ComplexTypeFactory
    {
        public function supports(Schema $schema)
        {
            if (!parent::supports($schema)) {
                return false;
            }
            /** @var ObjectSchema $schema */
            return 'Tag' === $schema->getComplexType()->getName();
        }

        public function getPriority(): int
        {
            return ComplexTypeFactory::PRIORITY + 1;
        }

        protected function instantiate(ObjectSchema $schema, ProcessorBuilder $builder): ObjectProcessor
        {
            $className = $this->classNameResolver->resolve($schema->getComplexType()->getName());

            return new class($schema, $className) extends ComplexTypePropertyProcessor
            {
                private $identityMap = [];

                protected function getObjectForHydration(\stdClass $object)
                {
                    if (isset($this->identityMap[$object->id])) {
                        return $this->identityMap[$object->id];
                    }

                    return $this->identityMap[$object->id] = parent::getObjectForHydration($object);
                }
            };
        }
    }
);

$processor = $builder->build(TestHelperFactory::createTagSchema());

/** @var Tag $actual */
$tag1 = $processor->hydrate((object)['id' => 1]);
$tag2 = $processor->hydrate((object)['id' => 1]);
$tag3 = $processor->hydrate((object)['id' => 2]);
$this->assertSame($tag1, $tag2);
$this->assertNotSame($tag2, $tag3);

$processor = $builder->build(TestHelperFactory::createFullPetSchema());

/** @var Pet $actual */
$pet1 = $processor->hydrate((object)['id' => 1]);
$pet2 = $processor->hydrate((object)['id' => 1]);
$pet3 = $processor->hydrate((object)['id' => 2]);
$this->assertNotSame($pet1, $pet2);
$this->assertNotSame($pet2, $pet3);
```
