# Mapping fields in Doctrine ORM using Symfony Serializer

This is a proof of concept for a mapping fields with [Doctrine ORM][doctrine-orm] so they can be serialized with the [Symfony Serializer component][symfony-serializer], without the need to create a mapping type for each possible data type.

Sometimes you just need to store a complex data type (generally a [Value Object][fowler-value-object]) in a JSON field and not worry about database schemas, extra columns or tables, [Doctrine embeddables][doctrine-orm-embeddables], [custom mappings][doctrine-orm-mapping-types].

## Installation

This library requires PHP 8.0 or later.

```
composer require edudobay/doctrine-symfony-serializer
```


## Usage

See the [examples](./examples/main.php) directory for a working code example.

### Abridged example

Add this to your application setup:

```php
use Edudobay\DoctrineSerializable\ReflectionClassMetadataFactory;
use Edudobay\DoctrineSerializable\SerializationHandler;
use Edudobay\DoctrineSerializable\PersistenceEventSubscriber;

$serializer = ...; // Symfony Serializer
$entityManager = ...; // Doctrine ORM EntityManager

$subscriber = new PersistenceEventSubscriber(new SerializationHandler(
    $serializer,
    // You might want to cache this. See Psr6CacheClassMetadataFactory
    new ReflectionClassMetadataFactory()
));

$entityManager->getEventManager()->addEventSubscriber($subscriber);
```

In your entities, have your domain object as you like, and introduce a private backing field that will make it persistent:

```php
use Doctrine\ORM\Mapping as ORM;
use Edudobay\DoctrineSerializable\Attributes\Serializable;

#[ORM\Entity]
class User
{
    // Backing field
    #[ORM\Column('address', type: 'json')]
    private array $_address = [];
    // The actual domain object
    #[Serializable]
    public Address $address;
}
```


[doctrine-orm]: https://www.doctrine-project.org/projects/doctrine-orm/en/2.10/index.html
[doctrine-orm-embeddables]: https://www.doctrine-project.org/projects/doctrine-orm/en/2.10/tutorials/embeddables.html
[doctrine-orm-mapping-types]: https://www.doctrine-project.org/projects/doctrine-orm/en/2.10/cookbook/custom-mapping-types.html
[fowler-value-object]: https://martinfowler.com/bliki/ValueObject.html
[symfony-serializer]: https://symfony.com/doc/current/components/serializer.html
