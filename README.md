# Symfony Bundle Cursor Kit

Cursor AI rules and optional skills for building Symfony bundles with best practices: SOLID, AbstractBundle, Contract pattern, and quality tooling (PHPStan 9, GrumPHP, Infection, Deptrac, CI).

**By default** the Composer plugin installs **all 7 rules** into your project’s `.cursor/rules/` and **no skills**. You can enable specific skills via `composer.json` (see [Configuration](#configuration)). User-added files in `.cursor/` are never removed; the plugin only merges or overwrites its own files.

## Requirements

- [Cursor IDE](https://cursor.com/)
- PHP 8.2+
- Composer 2.x or 3.x

## Installation

Add the package as a dev dependency and allow the plugin.

**Using a path repository** (e.g. local clone next to your bundle):

```json
{
    "repositories": [
        { "type": "path", "url": "./bundles-rules" }
    ],
    "require-dev": {
        "seb/symfony-bundle-cursor-kit": "*"
    },
    "config": {
        "allow-plugins": {
            "seb/symfony-bundle-cursor-kit": true
        }
    }
}
```

**Using a VCS repository**:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/your-org/bundles-rules" }
    ],
    "require-dev": {
        "seb/symfony-bundle-cursor-kit": "^1.0"
    },
    "config": {
        "allow-plugins": {
            "seb/symfony-bundle-cursor-kit": true
        }
    }
}
```

Then run:

```bash
composer update seb/symfony-bundle-cursor-kit
```

On every `composer install` or `composer update`, the plugin syncs the kit’s rules (and any enabled skills) into your project’s `.cursor/` directory, so rules stay up to date without manual copy/paste.

## Configuration

Configuration is optional and lives under `extra.symfony-bundle-cursor-kit` in your bundle’s `composer.json`.

### Enabling skills

By default **no skills** are installed. To enable specific skills, set the `skills` array to the skill directory names you want.

**Enable all skills** (most common):

```json
{
    "extra": {
        "symfony-bundle-cursor-kit": {
            "skills": [
                "symfony-bundle-core",
                "symfony-bundle-ux",
                "symfony-bundle-doctrine",
                "symfony-bundle-flex",
                "symfony-bundle-quality"
            ]
        }
    }
}
```

To enable only some skills, use a subset of the list above (e.g. `["symfony-bundle-core", "symfony-bundle-ux"]`).

Available skills:

| Skill | Description |
|-------|-------------|
| `symfony-bundle-core` | Architecture, scaffolding, SOLID, Contract pattern, compiler passes, events |
| `symfony-bundle-ux` | AssetMapper, Stimulus, Twig/Live Components |
| `symfony-bundle-doctrine` | Entities, XML mapping, UUID v7, typed collections |
| `symfony-bundle-flex` | Flex recipes, `manifest.json` |
| `symfony-bundle-quality` | Makefile, PHPStan 9, GrumPHP, Infection, Deptrac, CI |

Only the skills you list are copied into `.cursor/skills/`. Omit `skills` or use an empty array to install rules only.

## What you get

### Rules (always installed)

All 7 rules are merged into your project’s `.cursor/rules/`. The plugin never deletes files you added yourself.

| Rule | Scope | When it applies |
|------|--------|------------------|
| `symfony-bundle.mdc` | `alwaysApply` | Every interaction — SOLID, AbstractBundle, Contract pattern |
| `php-bundle-code.mdc` | `src/**/*.php` | Editing PHP source |
| `xml-config.mdc` | `config/**/*.xml` | Editing XML (services, Doctrine) |
| `bundle-tests.mdc` | `tests/**/*.php` | Editing tests |
| `bundle-ux.mdc` | `assets/**/*.js`, `*.ts` | Editing JS/TS assets |
| `twig-bundle.mdc` | `templates/**/*.twig` | Editing Twig templates |
| `bundle-quality.mdc` | Makefile, CI configs… | Editing quality/CI files |

### Skills (opt-in)

Skills are only installed when listed in `extra.symfony-bundle-cursor-kit.skills`. They provide deeper guidance for specific tasks (e.g. “create a bundle”, “add a Stimulus controller”, “set up Flex recipe”).

### Merge-only behaviour

The plugin only copies or overwrites files that exist in the kit. It never removes or replaces files or directories you added in `.cursor/` (e.g. custom rules or skills).

## Scaffolding a new bundle (optional)

The repo includes a standalone script to generate a full bundle skeleton with quality tooling, Makefile, and CI:

```bash
php scripts/scaffold_bundles.php Acme Blog --with-doctrine --with-ux
```

Options:

- `--with-doctrine` — entities, XML mapping, repositories
- `--with-ux` — AssetMapper, Stimulus controllers, Twig Components
- `--with-flex` — Flex recipe skeleton

The generated bundle includes Makefile, PHPStan 9, PHP-CS-Fixer, GrumPHP, Infection, Deptrac, `.editorconfig`, and GitHub Actions CI. You can then add this kit as a dev dependency and get Cursor rules in sync via Composer.

## .gitignore in your bundle

Either:

- **Ignore** `.cursor/` in your bundle so each dev (and CI) gets rules via `composer install`, or  
- **Commit** `.cursor/` after the first install so everyone has rules without running the plugin every time; run `composer update seb/symfony-bundle-cursor-kit` when you want to refresh.

Document this choice in your own README if relevant.

## Contributing

Issues and pull requests are welcome. Open an issue to discuss changes before sending a PR.

## License

MIT.
