# Laravel Loadout Product Plan

## Positioning

Laravel Loadout helps developers assemble a Laravel starter stack, save it for later, and share it with teammates or the community.

Tagline: Build, save and share your Laravel starter stack.

## Core Users

- Solo Laravel developers who want a repeatable starting point for new apps.
- Freelancers and agencies who maintain preferred client project stacks.
- Teams that want an easy way to discuss and standardize package choices.
- Learners who want curated starter stack examples.

## Product Shape

Laravel Loadout is a step-by-step Laravel project builder. Each step asks one group of Laravel installer-style questions, stores the answer as structured loadout data, and contributes to generated starter files such as `composer.json`, `package.json`, `compose.yaml`, Dockerfiles, environment examples, scripts, and README instructions.

Users can download the generated starter template or share a saved loadout through a unique public slug.

The second product phase turns Laravel Loadout into an account-based workspace where users can curate packages and loadouts, clone and fine-tune existing templates, collaborate with others, star useful loadouts, and build reputation from public contributions.

## MVP Functionality

- Public landing page that routes users into the builder.
- Step-by-step builder with resumable state.
- Laravel installer-style project questions.
- Curated package catalog with search, filters, sorting, and package details.
- Custom package URL entry for unlisted packages directly inside the package catalog step.
- Generated starter artifact preview.
- Downloadable project template archive.
- Authenticated save flow with unique share slugs.
- Public shared loadout pages.
- Admin moderation flow for custom package suggestions.
- Email notifications when users suggest unknown packages.
- Duplicate or fork an existing public loadout into the current user account.

## Builder Flow

### Step 1: Project Basics

- Project name.
- App name.
- Description.
- PHP version target.
- Laravel version target.
- Test framework preference: PHPUnit first, Pest later if we decide to support it.
- Package manager: Yarn by default, with npm, pnpm, and bun as later options.

### Step 2: Starter Kit

- Frontend stack: React/Inertia, Vue/Inertia, Livewire, API-only, or Blade-only.
- Authentication: Laravel starter auth, none, WorkOS/AuthKit later.
- Team support: yes/no.
- Dark mode/layout preferences when the chosen starter kit supports them.

### Step 3: Database And Runtime

- Database: SQLite, MySQL, MariaDB, PostgreSQL.
- Cache: database, Redis.
- Queue: database, Redis.
- Mail: log, Mailpit, SMTP placeholder.
- Search: none initially, Scout drivers later.
- Local runtime: Sail, Valet/Herd instructions, Docker-only later.

### Step 4: Official Laravel Packages

- Sail.
- Sanctum or Passport for API needs.
- Horizon when Redis queues are selected.
- Scout when search is selected.
- Cashier Stripe or Paddle.
- Telescope for local debugging.
- Pulse or Nightwatch later for monitoring.

### Step 5: Packages

- Searchable/filterable catalog of high-value community packages.
- Package cards show name, vendor/package, category, description, stars, usage/download count, last update, supported Laravel/PHP versions, and maintenance status.
- Users select packages and optionally configure package-specific install notes.
- Compatibility warnings appear when selected packages conflict with earlier choices.
- Users can add one or more custom package URLs directly on this page.
- System extracts package metadata when possible.
- Custom packages are added only to the current loadout unless approved by an admin.
- A moderation email is sent to admins with approve and ignore links.
- Approved packages become curated catalog entries.
- Ignored packages are stored so repeated suggestions do not keep notifying admins.

### Step 6: Review And Generate

- Summary of all chosen options.
- Generated install commands.
- Generated file previews for `composer.json`, `package.json`, `compose.yaml`, `.env.loadout`, Docker-related files, and README.
- Warnings for incomplete or incompatible choices.
- Save, share, duplicate, and download actions.

## Curated Package Catalog

This is the core value of the product and should be treated as a first-class search and discovery surface.

### Catalog Data

- Package name and Composer package name.
- Optional npm package name for frontend packages.
- Source URL.
- Packagist URL.
- GitHub URL.
- Short description.
- Long notes.
- Category.
- Tags.
- Install command.
- Post-install commands.
- Config publish commands.
- Migration commands.
- Required services.
- Laravel version constraints.
- PHP version constraints.
- Stars from stored snapshots.
- Downloads or usage count from stored snapshots.
- Last release date from stored snapshots.
- Last source update date from stored snapshots.
- Maintainer name.
- Approval status.
- Ignore status for rejected suggestions.
- Last stats refresh timestamp.

### Initial Categories

- Authentication and authorization.
- Admin panels.
- Billing and payments.
- Media and uploads.
- Search.
- Permissions and roles.
- Settings and feature flags.
- API tooling.
- Testing and quality.
- Developer experience.
- Observability and debugging.
- Queues and async.
- Content and SEO.
- AI and automation.

### Search And Filters

- Search by name, vendor/package, description, and tags.
- Filter by category.
- Filter by official/community/custom.
- Filter by Laravel version compatibility.
- Filter by requires Sail service: Redis, Meilisearch, Mailpit, database.
- Sort by relevance, stars, downloads/usage, last update, name, and recently added.
- Show stale or risky packages with visible maintenance warnings.

### Package Stats

- Package stats come from stored snapshots, not live API calls during browsing.
- A dedicated background job refreshes package stats every hour.
- Snapshot sources can include Packagist and GitHub, depending on what URLs are available for the package.
- The package catalog reads only local database values for filtering and sorting.
- Failed refresh attempts should be recorded without hiding the package from the catalog.

## Custom Package Moderation

### User Flow

- User pastes one or more package URLs into the custom package input on the package catalog page.
- Supported inputs: Packagist package URL, GitHub repository URL, and raw `vendor/package`.
- App validates whether it can identify a package.
- The package is attached only to the user's current loadout as a custom unapproved package.
- Custom packages are not globally searchable or reusable unless an admin promotes them to curated.
- User sees a clear note that custom packages are private to this loadout and not part of the curated catalog.

### Admin Email Flow

- Email subject: `Laravel Loadout package suggestion: vendor/package`.
- Email includes package metadata, suggested by user, source URL, current loadout slug, and generated moderation links.
- Approve link marks suggestion approved and creates or updates the curated package entry.
- Ignore link marks the suggestion ignored and suppresses future duplicate emails.
- Links should be signed URLs with expiration.

### Moderation States

- `pending`: suggested and awaiting review.
- `approved`: promoted to curated catalog.
- `ignored`: reviewed and intentionally excluded.
- `duplicate`: recognized as already curated or already pending.
- `failed_lookup`: URL could not be reliably resolved.

## Generated Output

### Files To Generate

- `composer.json`.
- `package.json`.
- `compose.yaml`.
- `.env.loadout`.
- `README.md`.
- Optional Dockerfile or Sail runtime notes.
- Optional install script after we trust the generated commands.

### Download Archive

- Generated project download is a zip archive.
- The zip contains only essential starter files and generated documentation.
- The zip must not contain `vendor`, `node_modules`, build artifacts, local caches, or user-specific secrets.
- `.env.loadout` is included so the user can rename it to `.env` and start from the generated configuration.
- The README includes project summary, Laravel/framework version, selected starter stack, selected packages, setup commands, and the shareable loadout URL.

### Generation Rules

- Generate from structured choices, not string concatenation scattered through controllers.
- Keep artifact generation deterministic so the same loadout produces the same output.
- Preserve selected package versions and install commands.
- Show warnings when a selected package requires manual setup.
- Store a generation snapshot with each saved loadout so shared links remain stable even if catalog metadata changes.
- Dependencies are not installed into the archive. Users install Composer and frontend dependencies themselves after download.

## Data Model Draft

- `loadouts`: user-owned saved stack, title, slug, description, visibility, current step, metadata, generation snapshot.
- `loadout_steps`: normalized step answers when we need step-level persistence and validation.
- `package_categories`: catalog grouping such as auth, admin panels, testing, deployment, observability, and AI tooling.
- `packages`: curated or suggested package records with metadata, status, source URLs, snapshot stats, and compatibility fields.
- `package_stat_snapshots`: hourly package stats from Packagist/GitHub, including stars, downloads, release/update timestamps, and refresh errors.
- `package_suggestions`: custom package submissions, lookup result, moderation status, suggested by user, approved or ignored by admin.
- `loadout_packages`: selected package records for each loadout, including custom package metadata snapshots.
- `generated_artifacts`: generated files for a saved loadout, with filename, content hash, and content or storage path.
- `presets`: curated starter loadouts that users can duplicate.
- `loadout_collaborators`: user roles on shared loadouts.
- `loadout_stars`: user stars for public loadouts.
- `loadout_clones`: clone relationships and attribution from source loadout to cloned loadout.
- `package_collections`: user-curated package lists.
- `package_collection_items`: packages inside user-curated collections.

## Core Services

- `LoadoutBuilder`: validates and stores step answers.
- `PackageCatalog`: queries curated packages with filters and sorting.
- `PackageLookup`: resolves Packagist/GitHub/custom package URLs into metadata.
- `PackageStatsRefresher`: updates stored package stat snapshots on a scheduled hourly job.
- `PackageSuggestionModerator`: creates suggestions, sends emails, approves, ignores, and deduplicates.
- `ArtifactGenerator`: builds generated files and commands from a loadout snapshot.
- `TemplateArchiveBuilder`: creates lean zip downloads without dependencies or generated local artifacts.
- `SlugGenerator`: creates stable unique public slugs.
- `LoadoutCloner`: duplicates public or permitted loadouts into a user's account while preserving source attribution.
- `LoadoutCollaboratorManager`: manages collaborator invitations and roles.
- `LoadoutStarService`: toggles stars and keeps counts consistent.

## First Technical Decisions

- Use Laravel policies so users can only edit their own loadouts.
- Use signed routes for package suggestion approve/ignore links.
- Use queued mail for moderation notifications once queues are configured; use sync or database queue for MVP.
- Use database JSON columns for flexible builder answers, then normalize only the package/catalog parts early.
- Use generated artifacts stored in the database for MVP; move large archives to storage later if needed.
- Use server-side filtering and pagination for the package catalog from the start.
- Keep download generation synchronous at first, then queue archive generation if it becomes slow.
- Refresh package stats hourly through Laravel's scheduler and queued jobs.
- Include `.env.loadout` in archives instead of `.env` to avoid accidentally overwriting user environments.
- Never include `vendor`, `node_modules`, `public/build`, or local cache files in generated template zips.
- Treat account/community features as Phase 2 after the core builder, catalog, artifact generation, and sharing flow are stable.
- Use policies for loadout ownership, collaboration roles, starring, cloning, and visibility.
- Denormalize star and clone counts on loadouts for fast gallery sorting, while keeping source event tables.

## Nice-To-Have After MVP

- Official presets such as SaaS, API backend, admin dashboard, content site, AI app, and ecommerce.
- Community loadout gallery with search, filters, likes, and copy counts.
- Version-aware package recommendations.
- Export options for README snippets, shell scripts, and GitHub issue templates.
- Team workspaces for shared internal standards.
- Admin moderation for public loadouts and catalog entries.

## Phase 2: Accounts And Community

### User Accounts

- Users have a profile page with public name, username, bio, avatar, website, GitHub link, and public contribution stats.
- Users can manage their own saved loadouts.
- Users can choose whether each loadout is private, unlisted, or public.
- Users can see received stars across their public loadouts.
- Users can see their own starred loadouts as bookmarks.

### Loadout Ownership

- Loadouts belong to a user by default.
- Public loadouts have shareable slugs and public detail pages.
- Private loadouts are visible only to owners and collaborators.
- Unlisted loadouts are accessible by URL but do not appear in public discovery.
- Each loadout keeps generation snapshots so older public templates stay reproducible.

### Clone And Fine-Tune

- Users can clone a public loadout into their account.
- Cloned loadouts preserve a reference to the source loadout.
- Users can fine-tune cloned loadouts without affecting the original.
- Public pages should show attribution such as "Based on ..." when a loadout was cloned.
- Clone counts become part of loadout discovery signals.

### Collaboration

- Owners can invite collaborators to a loadout.
- Collaborator roles start simple: viewer, editor, owner.
- Editors can change builder answers, package selections, and generated artifacts.
- Owners can manage visibility, delete loadouts, and manage collaborators.
- Collaboration activity should be auditable later, but MVP can store only `updated_by`.

### User Package Curation

- Users can suggest custom packages from a loadout, as planned in MVP.
- Phase 2 adds user-curated package lists or collections.
- Curated package collections can be public or private.
- Admin-approved packages can show contributor attribution.
- Repeated high-quality suggestions can feed into user reputation later.

### Stars And Reputation

- Authenticated users can star public loadouts.
- Users cannot star their own loadouts.
- Star counts appear on public loadout cards and detail pages.
- Received stars roll up to the owner's profile.
- Stars are used as one ranking signal, not the only discovery signal.

### Discovery

- Public gallery of loadouts with search, category filters, tags, stars, clone counts, and recent updates.
- Profile pages list public loadouts and curated package collections.
- Loadout cards show title, description, owner, starter stack, package count, stars, clones, and last update.
- Sorting options include relevance, most starred, most cloned, newest, recently updated.

## Initial Milestones

1. Confirm the builder steps, catalog fields, and moderation rules.
2. Build migrations, models, factories, policies, and seeders for loadouts, package categories, packages, suggestions, selections, and artifacts.
3. Seed the first curated package categories and a small high-quality package list.
4. Replace the default welcome page with the Laravel Loadout builder entry point.
5. Implement step-by-step builder state and validation.
6. Implement package catalog search, filters, sorting, selection, and custom package URL entry on the same page.
7. Implement package URL lookup, loadout-only custom package selection, and suggestion creation.
8. Implement hourly package stat snapshot refresh jobs.
9. Implement signed approve/ignore moderation links and email notifications.
10. Implement generated artifact preview for `composer.json`, `package.json`, `compose.yaml`, `.env.loadout`, and README.
11. Implement save, public share slug pages, duplicate/fork, and lean zip archive download.
12. Add tests for step validation, package filtering, custom suggestions, stat snapshots, moderation links, slug uniqueness, artifact generation, archive contents, and visibility.
13. Phase 2: add user profiles, private/unlisted/public visibility controls, cloned loadout attribution, collaboration roles, stars, public gallery ranking, and user package collections.

## Implementation Status

- Completed: project scaffold with Laravel 13, Inertia, React, shadcn/ui, Fortify auth, Sail, Yarn, and Laravel Boost.
- Completed: product plan for the builder, catalog, custom package moderation, generated zip output, and Phase 2 account/community features.
- Completed: domain foundation for loadouts, package categories, curated packages, stat snapshots, package suggestions, selected loadout packages, step answers, generated artifacts, factories, and initial package catalog seeding.
- Completed: Sail/MySQL migrations and `PackageCatalogSeeder` applied locally.
- Completed: package catalog query layer for server-side search, filters, sorting, and pagination.
- Completed: public builder package-selection route and Inertia page backed by the package catalog query layer.
- Completed: session-backed draft loadout creation, curated package selection, loadout-only custom package URL entry, package suggestion persistence, and selected/custom package display on the package-selection page.
- Completed: generated Wayfinder route/action helpers for the builder package endpoints.
- Completed: package suggestion moderation emails with signed approve/ignore links, admin email configuration, and package promotion into the curated catalog.
- Completed: hourly package stat refresh job, scheduler entry, Packagist/GitHub snapshots, denormalized package stat updates, and failure snapshot recording.
- Completed: generated artifact preview service and builder review page for `composer.json`, `package.json`, `compose.yaml`, `.env.loadout`, and README.
- Completed: lean zip archive generation and builder download route using generated artifact previews only.
- Completed: unlisted share publishing, stable slug-based public loadout pages, public zip downloads, and private loadout visibility protection.
- Completed: editable Project Basics builder step with persisted project metadata feeding generated artifacts.
- Completed: editable Starter Kit and Runtime builder steps with persisted stack/runtime choices feeding generated artifacts.
- Completed: official Laravel package recommendations, seeded first-party package catalog entries, and compatibility warnings for packages requiring disabled services.
- Completed: backend and frontend verification with `vendor/bin/sail composer test` and `vendor/bin/sail yarn run build`.
- Next: implement package-specific install notes and selected-package configuration.
