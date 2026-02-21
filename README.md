# Symfony Bundle Cursor Kit

Configuration Cursor AI complete pour developper des bundles Symfony communautaires avec les meilleures pratiques.

Fournit **7 rules projet** (portables, a copier dans chaque bundle) et **5 skills personnels** (globaux, installes une seule fois) qui guident l'AI dans la creation de bundles Symfony de qualite professionnelle.

## Ce que l'AI appliquera automatiquement

- `final readonly class` pour tous les services, handlers, processors, voters, listeners, DTOs
- SOLID strict : Contract pattern (`src/Contract/`), composition over inheritance
- `declare(strict_types=1)` partout, fonctions natives prefixees (`\count()`, `\sprintf()`)
- PHPStan level 9, PHP-CS-Fixer `@Symfony`, Deptrac pour l'architecture
- Compatibilite Symfony 7 + 8 (`^7.0 || ^8.0`)
- GrumPHP (pre-commit hooks), Infection (mutation testing), `composer audit`
- GitHub Actions CI : matrice PHP 8.2-8.4 x Symfony 7+8, 6 jobs paralleles
- AssetMapper + Stimulus : `stimulusFetch: 'lazy'`, Twig helpers obligatoires, pas de jQuery/npm
- Live Components : securite `LiveProp`, `debounce`, stable `id` dans les boucles
- Doctrine : XML mapping, UUID v7 pour les IDs publics, collections typees

## Prerequis

- [Cursor IDE](https://cursor.com/)
- PHP 8.2+

## Installation

### 1. Skills personnels (une seule fois)

Les skills sont globaux et disponibles dans tous vos projets Cursor.

```bash
cp -r skills/symfony-bundle-core     ~/.cursor/skills/symfony-bundle-core
cp -r skills/symfony-bundle-ux       ~/.cursor/skills/symfony-bundle-ux
cp -r skills/symfony-bundle-doctrine ~/.cursor/skills/symfony-bundle-doctrine
cp -r skills/symfony-bundle-flex     ~/.cursor/skills/symfony-bundle-flex
cp -r skills/symfony-bundle-quality  ~/.cursor/skills/symfony-bundle-quality
```

Ou en une commande :

```bash
cp -r skills/symfony-bundle-* ~/.cursor/skills/
```

### 2. Rules projet (pour chaque bundle)

Copiez le dossier `.cursor/rules/` a la racine de votre bundle :

```bash
cp -r .cursor/rules/ /chemin/vers/mon-bundle/.cursor/rules/
```

### 3. Scaffolder un nouveau bundle (optionnel)

Le script genere un bundle complet avec toute la structure, les fichiers de qualite, le Makefile et la CI :

```bash
php scripts/scaffold_bundles.php Acme Blog --with-doctrine --with-ux
```

Options :
- `--with-doctrine` : ajoute les entites, le mapping XML, les repositories
- `--with-ux` : ajoute AssetMapper, Stimulus controllers, Twig Components
- `--with-flex` : genere un squelette de recipe Flex

Le bundle scaffolde inclut : Makefile, PHPStan 9, PHP-CS-Fixer, GrumPHP, Infection, Deptrac, `.editorconfig`, LICENSE MIT, CHANGELOG, CI GitHub Actions.

## Structure du repo

```
.cursor/rules/                    # Rules portables (a copier par bundle)
  symfony-bundle.mdc              # Conventions generales, SOLID, Contract pattern (always active)
  php-bundle-code.mdc             # Code style PHP strict (src/**/*.php)
  xml-config.mdc                  # Services XML, Doctrine mapping (config/**/*.xml)
  bundle-tests.mdc                # Patterns de test (tests/**/*.php)
  bundle-ux.mdc                   # Stimulus, AssetMapper (assets/**/*.js, *.ts)
  twig-bundle.mdc                 # Twig/Live Components (templates/**/*.twig)
  bundle-quality.mdc              # Tooling qualite, CI (Makefile, configs...)

skills/                           # Skills personnels (a copier dans ~/.cursor/skills/)
  symfony-bundle-core/            # Architecture, scaffolding, SOLID, Contract pattern
    SKILL.md
    advanced-patterns.md           # Compiler passes, events, voters, cache warmers
    scaffold_bundle.php            # Version enrichie du scaffolder
  symfony-bundle-ux/              # AssetMapper, Stimulus, Twig/Live Components
    SKILL.md
  symfony-bundle-doctrine/        # Entites, XML mapping, UUID v7, collections typees
    SKILL.md
  symfony-bundle-flex/            # Recipes Flex, manifest.json
    SKILL.md
  symfony-bundle-quality/         # Makefile, PHPStan 9, GrumPHP, Infection, Deptrac, CI
    SKILL.md

scripts/
  scaffold_bundles.php            # Scaffolder de bundle standalone
```

## Rules : quand s'activent-elles ?

| Rule | Scope | Activation |
|---|---|---|
| `symfony-bundle.mdc` | `alwaysApply` | Toujours active — socle SOLID/architecture |
| `php-bundle-code.mdc` | `src/**/*.php` | Edition de code source PHP |
| `xml-config.mdc` | `config/**/*.xml` | Edition de config XML (services, Doctrine) |
| `bundle-tests.mdc` | `tests/**/*.php` | Edition de tests |
| `bundle-ux.mdc` | `assets/**/*.js, *.ts` | Edition d'assets JS/TS |
| `twig-bundle.mdc` | `templates/**/*.twig` | Edition de templates Twig |
| `bundle-quality.mdc` | Makefile, configs CI... | Edition de fichiers de qualite |

## Skills : quand se declenchent-ils ?

Les skills se declenchent automatiquement quand vous demandez a Cursor des actions correspondantes :

| Skill | Exemples de declencheurs |
|---|---|
| `symfony-bundle-core` | "cree un bundle", "AbstractBundle", "scaffold", "bundle structure" |
| `symfony-bundle-ux` | "stimulus controller", "assetmapper", "live component", "twig component" |
| `symfony-bundle-doctrine` | "doctrine entity bundle", "XML mapping", "orm.xml" |
| `symfony-bundle-flex` | "flex recipe", "manifest.json", "distribuer le bundle" |
| `symfony-bundle-quality` | "quality", "CI", "makefile", "phpstan", "grumphp", "infection" |

## Makefile genere

Chaque bundle scaffolde dispose de ces commandes :

```bash
make install          # composer install
make test             # PHPUnit
make phpstan          # Analyse statique (level 9)
make cs-fix           # Corrige le code style
make cs-check         # Verifie le code style
make infection        # Tests de mutation (MSI >= 70%)
make deptrac          # Verification d'architecture
make security-check   # composer audit
make lint             # Lint des fichiers XML
make quality          # Pipeline complete (cs + phpstan + deptrac + lint + test + infection)
make ci               # Pipeline CI (security + quality)
```

## Principes imposes par le kit

### Architecture

- **Contract pattern** : `src/Contract/` contient les interfaces publiques (API BC-safe)
- **SOLID** : `final readonly class` partout, extension par composition (events, decorateurs, interfaces)
- **Separation** : pas de logique metier dans controllers/entites, injection constructeur uniquement

### Qualite

- **PHPStan 9** : tout type, pas de `mixed` injustifie, collections Doctrine typees
- **PHP-CS-Fixer** : `@Symfony`, `declare_strict_types`, native function prefix, trailing comma
- **Deptrac** : Entity ne depend de rien, Service pas de Controller, Contract pas d'interne
- **Infection** : MSI >= 70%, `--only-covered`
- **GrumPHP** : pre-commit hooks (phpstan, cs-fixer, phpunit, composer audit)

### Compatibilite

- PHP 8.2+
- Symfony 7 et 8 (`^7.0 || ^8.0`)
- CI : matrice PHP 8.2/8.3/8.4 x Symfony 7/8

## Contribuer

Les suggestions et PR sont les bienvenues. Ouvrez une issue pour discuter des ajouts ou modifications avant de soumettre une PR.

## Licence

MIT
