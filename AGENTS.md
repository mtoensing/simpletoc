# Repository Instructions

Follow these rules for all changes in this WordPress plugin repository.

## Core Rules

- Stay close to WordPress core and Gutenberg standards.
- Keep it simple.
- Prefer existing Gutenberg elements, WordPress functions, official APIs, block metadata, block-editor components, and the WordPress Settings API.
- Write as little custom code as possible. Less is more.
- Avoid hacks, CSS tricks, hidden coupling, and one-off workarounds.
- Avoid inline styles unless a user-selected value must be output dynamically and there is no cleaner standard path.
- Keep layout and reusable presentation rules in SCSS/CSS, not in PHP strings.
- Sanitize all dynamic PHP output with the appropriate WordPress escaping helpers.

## Documentation Workflow

- Update `readme.txt` only when changing plugin documentation.
- Never edit or regenerate `README.md` locally. It is generated automatically from `readme.txt` by the GitHub Actions workflow.
- Keep `readme.txt` valid for WordPress.org first; the generated Markdown README is secondary.
- Follow the official WordPress.org Plugin Readmes guidance when editing `readme.txt`: https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/
- Keep the `readme.txt` header in WordPress.org format. `Stable tag` is the plugin release version and must match the versioned tag that WordPress.org should serve; the plugin version itself is read from the main plugin PHP header.
- Keep `Stable tag`, `plugin.php` `Version`, `src/block.json` `version`, `build/block.json` `version`, and `package.json` / `package-lock.json` versions aligned for releases.
- Keep the short description line under the readme header concise and plain text.
- For changelogs, keep only the current release entry in `readme.txt` when practical, and move older release history to `changelog.txt` to keep the WordPress.org readme small.
- When adding a new release entry, add it to both `readme.txt` and `changelog.txt` before release. If pruning older entries from `readme.txt`, keep the full older history in `changelog.txt`.
- Do not use `Stable tag: trunk`; use the numeric plugin version tag.

## Deployment Workflow

- Read and understand the repository's GitHub Actions workflows before changing deployment behavior.
- Check `.github/workflows/` to see which branch, tag, path, or manual triggers deploy plugin code, WordPress.org assets, generated documentation, or release artifacts.
- Check `.distignore` before adding project-only files. Claude skills, local tooling, generated GitHub-only files, and development metadata must not be shipped to WordPress.org.
- Keep deployment changes conservative and explicit. Do not add a new deployment path when an existing workflow already covers the job.

## Block Workflow

- Treat `src/block.json` as the source of truth for block attributes and asset registration.
- Treat `src/edit.js` as editor configuration only.
- For dynamic blocks, render frontend markup in the PHP render path, not in `save.js`.
- When adding a new editor option, wire it through block attributes first, then implement the frontend effect in the existing PHP render path or registered frontend styles.
- Rebuild generated files after source changes so `build/` stays in sync.

## Styling Workflow

- Prefer wrapper classes and registered block stylesheets over hard-coded CSS in PHP.
- Reuse Gutenberg conventions such as palette-driven colors, `has-background`, and block wrapper attributes when they fit.
- For optional styling features, expose explicit settings instead of baking in fixed presets unless the requirement is truly global.
- For global styling behavior, add the option to the plugin settings page and store it via the existing options flow.

## Settings Workflow

- Add global options in the plugin's existing settings module.
- Register options with `register_setting()`, render them with `add_settings_field()`, and keep labels/help text concise.
- Respect existing filter overrides when the settings page already follows that pattern.
- If a global option forces behavior, keep block-level UI simple and make the server-side precedence explicit.

## Project Preferences

- Prefer maintainable, standard-compliant solutions over fast shortcuts.
- If a request appears to require a workaround, stop and look for the clean WordPress-native approach first.
- If a compromise is unavoidable, state the tradeoff explicitly before implementing it.
