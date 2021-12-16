# Mapping fields in Doctrine ORM using Symfony Serializer

This is a proof of concept for a mapping fields with [Doctrine ORM][doctrine-orm] so they can be serialized with the [Symfony Serializer component][symfony-serializer], without the need to create a mapping type for each possible data type.

Sometimes you just need to store a data type in a JSON field and not worry about database schemas, extra columns or tables, [Doctrine embeddables][doctrine-orm-embeddables], [custom mappings][doctrine-orm-mapping-types].


[doctrine-orm]: https://www.doctrine-project.org/projects/doctrine-orm/en/2.10/index.html
[doctrine-orm-embeddables]: https://www.doctrine-project.org/projects/doctrine-orm/en/2.10/tutorials/embeddables.html
[doctrine-orm-mapping-types]: https://www.doctrine-project.org/projects/doctrine-orm/en/2.10/cookbook/custom-mapping-types.html
[symfony-serializer]: https://symfony.com/doc/current/components/serializer.html
