# Symfony Bundle AI Kit

[![CI](https://github.com/symkit/bundle-ai-kit/actions/workflows/ci.yml/badge.svg)](https://github.com/symkit/bundle-ai-kit/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/bundle-ai-kit.svg)](https://packagist.org/packages/symkit/bundle-ai-kit)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

AI rules and optional skills for building Symfony bundles with best practices: SOLID, AbstractBundle, Contract pattern, and quality tooling (PHPStan 9, GrumPHP, Infection, Deptrac, CI).

Supports **Cursor**, **Claude Code**, **Windsurf**, and **Google Antigravity** from a single source of truth.

**By default** the Composer plugin installs **all 7 rules** for **Cursor** into your project and **no skills**. You can enable additional editors and specific skills via `composer.json` (see [Configuration](#configuration)). User-added files are never removed; the plugin only merges or overwrites its own files.

## Requirements

- PHP 8.2+
- Composer 2.x or 3.x
- At least one supported AI editor:

| Editor | Rules location | Skills location |
|--------|---------------|-----------------|
| [Cursor](https://cursor.com/) | `.cursor/rules/*.mdc` | `.cursor/skills/` |
| [Claude Code](https://docs.anthropic.com/en/docs/claude-code) | `.claude/rules/*.md` | `.claude/rules/skills/` |
| [Windsurf](https://windsurf.com/) | `.windsurf/rules/*.md` | `.windsurf/rules/skills/` |
| [Google Antigravity](https://antigravity.google/) | `.agent/rules/*.md` | `.agent/rules/skills/` |

## Installation

Add the package as a dev dependency and allow the plugin.

**Using a path repository** (e.g. local clone next to your bundle):

```json
{
    "repositories": [
        { "type": "path", "url": "./bundles-rules" }
    ],
    "require-dev": {
        "symkit/bundle-ai-kit": "*"
    },
    "config": {
        "allow-plugins": {
            "symkit/bundle-ai-kit": true
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
        "symkit/bundle-ai-kit": "^1.0"
    },
    "config": {
        "allow-plugins": {
            "symkit/bundle-ai-kit": true
        }
    }
}
```

Then run:

```bash
composer update symkit/bundle-ai-kit
```

On every `composer install` or `composer update`, the plugin syncs the kit's rules (and any enabled skills) into the appropriate directories for each configured editor.

## Configuration

Configuration is optional and lives under `extra.bundle-ai-kit` in your bundle's `composer.json`.

### Choosing editors

By default only **Cursor** is enabled. To add other editors, set the `editors` array:

```json
{
    "extra": {
        "bundle-ai-kit": {
            "editors": ["cursor", "claude", "windsurf", "antigravity"]
        }
    }
}
```

Valid values: `"cursor"`, `"claude"`, `"windsurf"`, `"antigravity"`.

The plugin reads rules from a single source (`ai/cursor/`) and converts the frontmatter format automatically for each target editor:

- **Cursor** — copies `.mdc` files as-is
- **Claude Code** — converts to `.md` with `globs:` frontmatter for path-scoped rules
- **Windsurf** — converts to `.md` with no frontmatter (plain markdown)
- **Google Antigravity** — converts to `.md` with no frontmatter (activation modes are configured via the IDE UI)

### Enabling skills

By default **no skills** are installed. To enable specific skills, set the `skills` array:

**Enable all skills** (most common):

```json
{
    "extra": {
        "bundle-ai-kit": {
            "editors": ["cursor", "claude", "windsurf", "antigravity"],
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

To enable only some skills, use a subset (e.g. `["symfony-bundle-core", "symfony-bundle-ux"]`).

Available skills:

| Skill | Description |
|-------|-------------|
| `symfony-bundle-core` | Architecture, scaffolding, SOLID, Contract pattern, compiler passes, events |
| `symfony-bundle-ux` | AssetMapper, Stimulus, Twig/Live Components |
| `symfony-bundle-doctrine` | Entities, XML mapping, UUID v7, typed collections |
| `symfony-bundle-flex` | Flex recipes, `manifest.json` |
| `symfony-bundle-quality` | Makefile, PHPStan 9, GrumPHP, Infection, Deptrac, CI |

Skills are installed into the editor-specific skills directory (e.g. `.cursor/skills/`, `.claude/rules/skills/`, `.windsurf/rules/skills/`, `.agent/rules/skills/`).

## What you get

### Rules (always installed)

All 7 rules are merged into the rules directory of each configured editor. The plugin never deletes files you added yourself.

| Rule | Scope | When it applies |
|------|--------|------------------|
| `symfony-bundle` | Always | Every interaction — SOLID, AbstractBundle, Contract pattern |
| `php-bundle-code` | `src/**/*.php` | Editing PHP source |
| `xml-config` | `config/**/*.xml` | Editing XML (services, Doctrine) |
| `bundle-tests` | `tests/**/*.php` | Editing tests |
| `bundle-ux` | `assets/**/*.js`, `*.ts` | Editing JS/TS assets |
| `twig-bundle` | `templates/**/*.twig` | Editing Twig templates |
| `bundle-quality` | Makefile, CI configs | Editing quality/CI files |

### Skills (opt-in)

Skills are only installed when listed in `extra.bundle-ai-kit.skills`. They provide deeper guidance for specific tasks (e.g. "create a bundle", "add a Stimulus controller", "set up Flex recipe").

### Merge-only behaviour

The plugin only copies or overwrites files that exist in the kit. It never removes or replaces files or directories you added yourself (e.g. custom rules or skills).

## Scaffolding a new bundle (optional)

The `symfony-bundle-core` skill includes a standalone script to generate a full bundle skeleton with quality tooling, Makefile, and CI:

```bash
php vendor/symkit/bundle-ai-kit/ai/cursor/skills/symfony-bundle-core/scaffold_bundle.php Acme Blog --with-doctrine --with-ux
```

Options:

- `--with-doctrine` — entities, XML mapping, repositories
- `--with-ux` — AssetMapper, Stimulus controllers, Twig Components
- `--with-flex` — Flex recipe skeleton

## .gitignore in your bundle

Choose which AI editor directories to ignore. Add any combination of:

```gitignore
.cursor/
.claude/
.windsurf/
.agent/
```

Either:

- **Ignore** the directories so each dev gets rules via `composer install`, or
- **Commit** them after the first install so everyone has rules immediately; run `composer update symkit/bundle-ai-kit` to refresh.

## Contributing

Issues and pull requests are welcome. Open an issue to discuss changes before sending a PR.

## License

MIT.
