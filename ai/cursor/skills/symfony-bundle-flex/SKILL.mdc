---
name: symfony-bundle-flex
description: "Use this skill when creating Symfony Flex recipes for bundle distribution, writing manifest.json files, configuring automatic bundle registration, importmap injection, environment variable scaffolding, or preparing a bundle for publication on Packagist with Flex integration. Trigger when: 'flex recipe', 'manifest.json symfony', 'bundle distribution', 'publish symfony bundle', 'packagist bundle', 'recipes-contrib', 'copy-from-recipe', 'auto-configure bundle install', 'plug and play bundle'. Also trigger when the user says 'I want users to install my bundle with zero config' or 'automate the bundle setup'. Do NOT trigger for general Composer package publishing unrelated to Symfony bundles."
---

# Symfony Bundle Flex — Recipes & Distribution

This skill covers creating Symfony Flex recipes that automate bundle installation, and preparing bundles for publication.

**Prerequisite**: Read `symfony-bundle-core` for the base bundle structure.

---

## 1. What Flex Recipes Do

When a user runs `composer require acme/blog-bundle`, Flex checks for a matching recipe and automatically:
- Registers the bundle in `config/bundles.php`
- Copies default config files to `config/packages/`
- Adds environment variables to `.env`
- Updates `importmap.php` with JS dependencies
- Copies template stubs or other files

Without a recipe, the user must do all of this manually. A well-crafted recipe transforms installation from a multi-step documented process into a single command.

---

## 2. Recipe Repository Structure

Recipes live in one of two repositories:
- **`symfony/recipes`** — Official recipes (Symfony core and blessed packages). Requires review.
- **`symfony/recipes-contrib`** — Community recipes. More accessible, still requires a PR.

Your recipe directory follows this structure in the repository:

```
acme/blog-bundle/
├── 1.0/
│   ├── manifest.json
│   ├── config/
│   │   └── packages/
│   │       └── acme_blog.yaml
│   └── templates/
│       └── bundles/
│           └── AcmeBlogBundle/
│               └── ... (optional template stubs)
```

The `1.0/` directory corresponds to the minimum version of your bundle this recipe applies to. Flex picks the highest matching recipe version.

---

## 3. manifest.json Reference

```json
{
    "bundles": {
        "Acme\\BlogBundle\\AcmeBlogBundle": ["all"]
    },
    "copy-from-recipe": {
        "config/": "%CONFIG_DIR%/"
    },
    "env": {
        "ACME_BLOG_API_KEY": "your-api-key-here",
        "ACME_BLOG_DSN": "https://api.example.com"
    },
    "aliases": ["acme-blog", "blog"]
}
```

### Directive Reference

| Directive | Purpose | Example |
|---|---|---|
| `bundles` | Register bundle class in `config/bundles.php` | `{"FQCN": ["all"]}` or `{"FQCN": ["dev", "test"]}` |
| `copy-from-recipe` | Copy files from recipe archive to host project | `{"config/": "%CONFIG_DIR%/"}` |
| `env` | Append variables to `.env` | `{"API_KEY": "default-value"}` |
| `composer-scripts` | Run Composer scripts post-install | `{"auto-scripts": {"cache:clear": "symfony-cmd"}}` |
| `aliases` | Short names for `composer require` | `["acme-blog"]` → `composer require acme-blog` works |

### Environment Specifiers for `bundles`

```json
{
    "bundles": {
        "Acme\\BlogBundle\\AcmeBlogBundle": ["all"],
        "Acme\\BlogBundle\\Debug\\AcmeDebugBundle": ["dev", "test"]
    }
}
```

- `"all"` — enabled in every environment
- `["dev", "test"]` — only in development and testing
- `["prod"]` — only in production (rare)

---

## 4. Default Config File

The recipe typically ships a default YAML config:

```yaml
# config/packages/acme_blog.yaml
acme_blog:
    api_key: '%env(ACME_BLOG_API_KEY)%'
    cache_ttl: 3600
    enable_tracking: false
    # allowed_formats:
    #     - html
    #     - json
```

**Best practices for default config:**
- Use `%env()%` for sensitive values, not hardcoded secrets
- Comment out optional settings with sensible defaults shown
- Add brief inline comments explaining each option
- Only include settings the user is likely to customize

---

## 5. Importmap Integration (for UX Bundles)

If your bundle ships Stimulus controllers or JS dependencies, the Flex recipe can update the host's `importmap.php`:

This is handled automatically by `symfony/stimulus-bundle` when it detects your `assets/package.json`. No manual importmap directive is usually needed in the recipe.

However, if your bundle requires standalone JS packages (not tied to Stimulus controllers), you can document them:

```json
{
    "copy-from-recipe": {
        "config/": "%CONFIG_DIR%/"
    },
    "bundles": {
        "Acme\\BlogBundle\\AcmeBlogBundle": ["all"]
    }
}
```

And in your docs, instruct users to run:
```bash
php bin/console importmap:require chart.js
```

---

## 6. Publishing Workflow

### Step 1: Prepare the Package

1. Ensure `composer.json` has `"type": "symfony-bundle"`
2. Tag a release: `git tag v1.0.0 && git push --tags`
3. Register on [Packagist](https://packagist.org)

### Step 2: Submit the Flex Recipe

1. Fork `symfony/recipes-contrib`
2. Create directory: `your-vendor/your-bundle/1.0/`
3. Add `manifest.json` and any config files
4. Open a PR with:
   - Link to your Packagist package
   - Brief description of what the recipe does
   - Confirmation that `composer require` + recipe produces a working setup

### Step 3: Testing Before Submission

Test your recipe locally:

```bash
# In a fresh Symfony project
composer config --json extra.symfony.endpoint '["https://raw.githubusercontent.com/YOUR-USER/recipes-contrib/YOUR-BRANCH/index.json", "flex://defaults"]'
composer require acme/blog-bundle
```

Verify:
- Bundle appears in `config/bundles.php`
- Config file created in `config/packages/`
- `.env` variables added
- `bin/console debug:config acme_blog` shows merged config

---

## 7. Recipe File Templates

### Minimal Recipe (Config Only)

```
acme/blog-bundle/1.0/
├── manifest.json
└── config/
    └── packages/
        └── acme_blog.yaml
```

```json
// manifest.json
{
    "bundles": {
        "Acme\\BlogBundle\\AcmeBlogBundle": ["all"]
    },
    "copy-from-recipe": {
        "config/": "%CONFIG_DIR%/"
    }
}
```

### Full Recipe (Config + Env + Routes)

```
acme/blog-bundle/1.0/
├── manifest.json
├── config/
│   ├── packages/
│   │   └── acme_blog.yaml
│   └── routes/
│       └── acme_blog.yaml
```

```json
// manifest.json
{
    "bundles": {
        "Acme\\BlogBundle\\AcmeBlogBundle": ["all"]
    },
    "copy-from-recipe": {
        "config/": "%CONFIG_DIR%/"
    },
    "env": {
        "ACME_BLOG_DSN": "https://api.example.com/v1"
    },
    "aliases": ["acme-blog"]
}
```

Route file copied to host:
```yaml
# config/routes/acme_blog.yaml
acme_blog:
    resource: '@AcmeBlogBundle/config/routes.yaml'
    prefix: /blog
```

---

## 8. Anti-Patterns for Recipes

| Anti-Pattern | Impact |
|---|---|
| Hardcoded secrets in `env` | Exposed in `.env` which is often committed to VCS |
| Overwriting existing config files without merge | Destroys user customizations on `composer update` |
| Registering dev-only bundles in `["all"]` | Debug tools active in production |
| Missing `copy-from-recipe` for config files | User gets no default config, bundle may crash with missing params |
| Overly complex recipes that create dozens of files | Intimidates users; keep it minimal |
| Not testing recipe in a clean Symfony install | Recipe may conflict with default Symfony config |

---

## 9. Uninstallation

Flex also handles uninstallation (`composer remove acme/blog-bundle`):
- Removes the bundle from `config/bundles.php`
- Deletes files that were copied by `copy-from-recipe` (if unchanged)
- Removes `.env` entries

Design your recipe with clean uninstall in mind — don't create files outside the standard locations (`config/`, `.env`).

---

## 10. Generation Checklist

When preparing a bundle for distribution:

1. Create `manifest.json` with bundle registration and config copy
2. Create default `config/packages/{alias}.yaml` with env references
3. Add `.env` entries for any required secrets or DSNs
4. Create route import file if bundle exposes controllers
5. Test in a clean `symfony new` project
6. Submit PR to `symfony/recipes-contrib`
7. Document post-install steps (migrations, importmap, etc.) in README
