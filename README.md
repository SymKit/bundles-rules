# Symfony Bundle AI Kit

[![CI](https://github.com/SymKit/bundles-rules/actions/workflows/ci.yml/badge.svg)](https://github.com/SymKit/bundles-rules/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/bundle-ai-kit.svg)](https://packagist.org/packages/symkit/bundle-ai-kit)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

AI rules, **AGENTS.md**, agent prompts, and **all skills** for **Symfony bundle** packages (Cursor only): AbstractBundle, Contract pattern, SOLID, API/Doctrine/Messenger/UX topics, quality tooling.

On every `composer install` / `composer update`, the plugin copies into your bundle repo:

| Destination | Content |
|-------------|---------|
| `.cursor/rules/*.mdc` | All rules |
| `.cursor/skills/*/` | Every folder under `ai/cursor/skills/` in the package |
| `.cursor/agents/*.md` | pm, architect, qa |
| `AGENTS.md` (project root) | Directives + rules index |

**No `composer.json` configuration required.** User-added files are never deleted; kit files are merged/overwritten only.

## Requirements

- PHP 8.2+
- Composer 2.x or 3.x
- [Cursor](https://cursor.com/)

## Installation

Add the package as a dev dependency and allow the plugin.

**Path repository** (local clone):

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

**VCS repository**:

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

```bash
composer update symkit/bundle-ai-kit
```

## Skills (always installed)

| Skill | Description |
|-------|-------------|
| `feature` | PM → Architect → implementation → QA pipeline |
| `bug-fix` | TDD: regression test first, then fix |
| `refactor` | Safe refactor with coverage first |
| `onboard` | Profile bundle repo, update Project DNA in `AGENTS.md` |
| `learn` | Capture lessons to `docs/lessons-learned.md` |
| `quality-install` | PHPStan, CS-Fixer, Deptrac, Infection, GrumPHP, Makefile templates |
| `create-branch` | Branch naming `type/slug` |
| `commit` | Validate, commit, push (Conventional Commits) |

## What you get

### Rules

24 `.mdc` rules. **`symfony-bundle`** is `alwaysApply`; the others mostly use `globs`.

### AGENTS.md + agents

- **`AGENTS.md`** — behavioural directives and rules index (Project DNA via `/onboard`).
- **`pm`**, **`architect`**, **`qa`** — under `.cursor/agents/` (e.g. `/feature` workflow).

### Merge-only behaviour

The plugin never removes files you added yourself.

## .gitignore in your bundle

```gitignore
.cursor/
# Optional if you regenerate on each clone:
# AGENTS.md
```

Commit `.cursor/` + `AGENTS.md` after first sync, or ignore them and rely on Composer.

## Contributing

Issues and pull requests are welcome.

## License

MIT.
