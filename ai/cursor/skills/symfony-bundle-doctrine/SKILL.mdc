---
name: symfony-bundle-doctrine
description: "Use this skill when creating or managing Doctrine ORM entities within a Symfony bundle. Triggers include: 'doctrine entities in bundle', 'bundle entity mapping', 'XML mapping doctrine', 'orm.xml bundle', 'doctrine bundle integration', 'entity in reusable bundle', 'bundle database model'. Also trigger when the user asks about mapping strategies for distributable Symfony packages, overriding entity metadata, or why PHP attributes shouldn't be used for bundle entities. Do NOT trigger for application-level Doctrine usage."
---

# Symfony Bundle Doctrine — Entity Modeling with XML Mapping

Prerequisite: Read `symfony-bundle-core` for the base bundle structure.

Key insight: **bundles must use XML mapping, not PHP attributes**, for extensibility.

---

## 1. Why XML Mapping, Not PHP Attributes

For apps, PHP attributes are standard. For distributable bundles, XML mapping is mandatory because:
- Host app can override table names, column specs, relationships
- No need for complex compiler passes to modify Doctrine metadata
- Standard bundle override mechanism works

---

## 2. Entity Class (Clean POPO)

```php
<?php

declare(strict_types=1);

namespace Acme\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Article
{
    private ?int $id = null;
    private string $title;
    private string $slug;
    private ?string $content = null;
    private \DateTimeImmutable $createdAt;
    private bool $published = false;

    /** @var Collection<int, Comment> */
    private Collection $comments;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    /** @return Collection<int, Comment> */
    public function getComments(): Collection { return $this->comments; }
}
```

No Doctrine imports for mapping. Clean POPO.

---

## 3. XML Mapping

```xml
<!-- config/doctrine/Article.orm.xml -->
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping">
    <entity name="Acme\BlogBundle\Entity\Article"
            table="acme_blog_article"
            repository-class="Acme\BlogBundle\Repository\ArticleRepository">

        <id name="id" type="integer" column="id">
            <generator strategy="AUTO"/>
        </id>

        <field name="title" type="string" length="255" nullable="false"/>
        <field name="slug" type="string" length="255" nullable="false" unique="true"/>
        <field name="content" type="text" nullable="true"/>
        <field name="createdAt" type="datetime_immutable" column="created_at"/>
        <field name="published" type="boolean"/>

        <indexes>
            <index name="idx_acme_blog_article_slug" columns="slug"/>
        </indexes>
    </entity>
</doctrine-mapping>
```

Conventions: `{EntityName}.orm.xml`, table prefix `acme_blog_`, index prefix `idx_acme_blog_`.

---

## 4. Registering Mappings

Via `prependExtension()`:
```php
$builder->prependExtensionConfig('doctrine', [
    'orm' => [
        'mappings' => [
            'AcmeBlogBundle' => [
                'type' => 'xml',
                'dir' => __DIR__ . '/../config/doctrine',
                'prefix' => 'Acme\\BlogBundle\\Entity',
                'alias' => 'AcmeBlog',
                'is_bundle' => false,
            ],
        ],
    ],
]);
```

---

## 5. Repository Classes

```php
<?php

declare(strict_types=1);

namespace Acme\BlogBundle\Repository;

use Acme\BlogBundle\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /** @return Article[] */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.published = :published')
            ->setParameter('published', true)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
```

---

## 6. Public API Identifiers

If the bundle exposes entities via an API:
- Use **UUID v7** or **Ulid** as public identifiers. Never expose auto-increment IDs.
- Add a `uuid` field in the XML mapping with `type="guid"` or use `symfony/uid`.
- The internal `id` (auto-increment) stays for DB joins; the `uuid` is the public API field.

---

## 7. PHPStan Level 9 Requirements

- Collections: `/** @var Collection<int, Entity> */` on every collection property
- Repository methods: `@return Entity[]` or exact union type, never `mixed[]`
- Entity getters: fully typed return values, no implicit `void`

---

## 8. Migrations Strategy

Bundles must NOT ship migrations. Ship XML mapping files. Document that users should run `doctrine:migrations:diff`.

---

## 9. Anti-Patterns

| Anti-Pattern | Impact |
|---|---|
| `#[ORM\Entity]` attributes on distributed entities | Host can't override |
| Unprefixed table names | Collisions |
| Shipping migrations | App-specific, break on different DB states |
| `auto_mapping` without fallback | Fragile |
| Doctrine optional but used unconditionally | Runtime crash |

---

## 9.5. Critical Rules (Before Checklist)

- **UUID v7 or Ulid** as public identifiers if bundle exposes entities via API. Never expose auto-increment IDs.
- **Typed collections**: `/** @var Collection<int, Entity> */` mandatory (PHPStan level 9)
- **Repository return types**: `@return Entity[]` or exact union type, never `mixed[]`

---

## 10. Generation Checklist

1. Clean POPO entities in `src/Entity/` with typed collections
2. XML mapping in `config/doctrine/{Entity}.orm.xml`
3. Prefix all tables and indexes
4. Repositories extending `ServiceEntityRepository` with typed returns
5. Register mappings via `prependExtension()`
6. Doctrine as explicit `require`
7. UUID/Ulid for public API identifiers
8. Document migration generation
9. Integration tests with in-memory SQLite
