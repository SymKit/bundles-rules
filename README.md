# Symfony Bundle AI Kit

[![CI](https://github.com/SymKit/bundles-rules/actions/workflows/ci.yml/badge.svg)](https://github.com/SymKit/bundles-rules/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/bundle-ai-kit.svg)](https://packagist.org/packages/symkit/bundle-ai-kit)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

AI rules, **AGENTS.md**, agent prompts, and optional skills for **Symfony bundle** packages: AbstractBundle, Contract pattern, SOLID, Symfony ecosystem topics (API, Doctrine, Messenger, UX, etc.), and quality tooling (PHPStan 9, GrumPHP, Infection, Deptrac).

Supports **Cursor**, **Claude Code**, **Windsurf**, and **Google Antigravity** from a single source of truth.

**By default** the plugin installs **all rules**, **`AGENTS.md`** at the project root, **agent files** under each editor’s `agents/` directory, for **Cursor** only — and **no skills**. Enable more editors and skills via `composer.json`. User-added files are never removed; the plugin merges and overwrites kit files only.

## Requirements

- PHP 8.2+
- Composer 2.x or 3.x
- At least one supported AI editor:

| Editor | Rules | Skills | Agents |
|--------|-------|--------|--------|
| [Cursor](https://cursor.com/) | `.cursor/rules/*.mdc` | `.cursor/skills/{name}/` | `.cursor/agents/*.md` |
| [Claude Code](https://docs.anthropic.com/en/docs/claude-code) | `.claude/rules/*.md` | `.claude/rules/skills/{name}/` | `.claude/agents/*.md` |
| [Windsurf](https://windsurf.com/) | `.windsurf/rules/*.md` | `.windsurf/rules/skills/{name}/` | `.windsurf/agents/*.md` |
| [Google Antigravity](https://antigravity.google/) | `.agent/rules/*.md` | `.agent/rules/skills/{name}/` | `.agent/agents/*.md` |

**Project root:** `AGENTS.md` (same file for every editor sync).

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

On every `composer install` or `composer update`, the plugin syncs rules, `AGENTS.md`, agents (per editor), and any enabled skills.

## Configuration

Configuration is optional and lives under `extra.bundle-ai-kit` in your bundle’s `composer.json`.

### Choosing editors

By default only **Cursor** is enabled. To add other editors:

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

The plugin reads from:

- `ai/cursor/rules/*.mdc` → rules (converted to `.md` for non-Cursor editors where applicable)
- `ai/AGENTS.md` → project root `AGENTS.md`
- `ai/cursor/agents/*.md` → each editor’s `agents/` directory (copied as-is)
- `ai/cursor/skills/{name}/` → optional skills (`SKILL.mdc` + templates)

### Enabling skills

By default **no skills** are installed. Example — **all skills**:

```json
{
    "extra": {
        "bundle-ai-kit": {
            "editors": ["cursor", "claude", "windsurf", "antigravity"],
            "skills": [
                "feature",
                "bug-fix",
                "refactor",
                "onboard",
                "learn",
                "quality-install",
                "create-branch",
                "commit"
            ]
        }
    }
}
```

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

### Rules (always installed)

24 `.mdc` rules. **`symfony-bundle`** is `alwaysApply`; the others mostly use `globs` (`src/**/*.php`, `templates/**/*.twig`, `config/**/*.xml`, Makefile/CI, etc.).

| Rule | Topic |
|------|--------|
| `symfony-bundle` | AbstractBundle, Contract, config-driven services, translations, semver (always) |
| `architecture` | Bundle layers, Extension, tests, Flex |
| `coding-standards`, `dto`, `testing`, `quality-pipeline` | Core PHP / tests / Makefile |
| `api`, `api-platform`, `security`, `serializer`, `error-handling` | HTTP & API surface when the bundle exposes it |
| `doctrine`, `forms`, `validator`, `messenger`, `workflow` | Optional bundle features |
| `twig`, `frontend`, `i18n` | Templates & assets |
| `caching`, `http-client`, `mailer`, `console-commands`, `observability` | Infra & ops |

### AGENTS.md + agents

- **`AGENTS.md`** — behavioural directives and a rules index (Project DNA filled by `/onboard`).
- **`pm`**, **`architect`**, **`qa`** — Markdown agent definitions under each editor’s `agents/` folder (for subagent workflows, e.g. `/feature`).

### Skills (opt-in)

Installed only when listed in `extra.bundle-ai-kit.skills`.

### Merge-only behaviour

The plugin never deletes user-added files. It copies or overwrites paths that exist in the kit.

## .gitignore in your bundle

```gitignore
.cursor/
.claude/
.windsurf/
.agent/
# Optional: if you regenerate on each machine, ignore synced kit files:
# AGENTS.md
```

Either ignore editor dirs and rely on `composer install`, or commit them after first sync and refresh with `composer update symkit/bundle-ai-kit`.

## Contributing

Issues and pull requests are welcome.

## License

MIT.
