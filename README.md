# The Unexplained

A community atlas of ghost stories, folklore, urban legends and conspiracy theories. Readers explore places, unfold an account as a case file, vote on interpretations, discuss stories and save private collections.

Public repository: [Shafeen-Noor/ghost-stories](https://github.com/Shafeen-Noor/ghost-stories).

The original proposal was **Ghost Stories Map**. The repository and local domain retain that name. The core User, Location, Story, Vote, Comment and Tag structure is retained; ReadingCollection and CaseOpinion extend it. This project started from the teacher's [starterpack](https://github.com/ndeblauw/starterpack), with Laravel concepts illustrated by [blaug](https://github.com/ndeblauw/blaug). The atlas, case files, progressive reveals, private collections and investigation desk are application-specific implementations.

## Contents

- [Requirements](#requirements)
- [Fresh installation](#fresh-installation)
- [Running locally](#running-locally)
- [Demo accounts and seeded data](#demo-accounts-and-seeded-data)
- [Updating an existing installation](#updating-an-existing-installation)
- [Assessment checklist](#assessment-checklist)
- [Models and relationships](#models-and-relationships)
- [MVC and important file paths](#mvc-and-important-file-paths)
- [Pages and access](#pages-and-access)
- [Storage, configuration and external services](#storage-configuration-and-external-services)
- [Tests and checks](#tests-and-checks)
- [Troubleshooting](#troubleshooting)
- [Defence walkthrough](#defence-walkthrough)
- [Scope and verification](#scope-and-verification)

All file paths below are relative to the repository root, the folder containing `artisan` and `composer.json`.

## Requirements

| Item | Requirement |
| --- | --- |
| PHP | **8.5.x**; the project requires `^8.5.0`. Locally checked with PHP 8.5.13. |
| Framework | Laravel 13; locked version 13.42.0. Install from `composer.lock`. |
| Composer | Composer 2; platform checks passed with Composer 2.9. |
| Database | SQLite, with PHP PDO SQLite support. A MySQL/Redis service is not needed. |
| PHP extensions | PDO, `pdo_sqlite`, `sqlite3`, Fileinfo, OpenSSL, Tokenizer, XML/DOM and the extensions reported by Composer. Mbstring is recommended; the locked packages also include its polyfill. |
| Web server | Laravel Herd, or PHP's local development server. The document root must be `public/`. |
| Browser | A current browser supporting native HTML dialogs, `fetch`, Pointer Events and CSS Grid. Enable JavaScript for the editor, case reveals and interactive map. |
| Node | **Optional** for the current UI. The retained Vite toolchain uses Node 22.12+; CI uses Node 22. |
| Internet | Needed initially for Git/Composer downloads. Detailed OpenStreetMap tiles need connectivity; the bundled world outline, story pins and the rest of the UI do not depend on a CDN. |

Check the terminal before installing:

```sh
php -v
composer --version
php -m
```

If using Herd's isolated PHP, use `herd php` and `herd composer` instead of `php` and `composer` for the commands below. A system PHP executable can differ from the PHP version serving the site.

## Fresh installation

Run these commands in a terminal. These instructions assume a **new clone**, not a directory containing data you want to retain.

```sh
git clone https://github.com/Shafeen-Noor/ghost-stories.git
cd ghost-stories
composer install --prefer-dist --no-interaction
php -r "file_exists('.env') || copy('.env.example', '.env');"
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan key:generate
php artisan migrate:fresh --seed
```

`migrate:fresh --seed` drops every table in the configured database and builds the complete demo. Use it only for a disposable local assessment/demo database. See the update instructions for an installation with existing content.

Do **not** use `composer install --no-dev` for this assessment setup: the sample factories use Faker, and the test/formatting tools are development dependencies. Do not run `composer update` to install a clone; `composer install` reproduces the locked versions.

The supplied `.env.example` selects SQLite and file-based sessions/cache. Leaving `DB_DATABASE` unset uses `database/database.sqlite`. No database credentials or API keys need to be added. Do not copy someone else's `APP_KEY`; generate a key for your installation.

Verify setup:

```sh
php artisan migrate:status
composer check-platform-reqs
```

Keep `.env`, the SQLite database and private uploaded files out of Git. The repository's ignore rules already exclude them.

## Running locally

### Laravel Herd

From the project root, with PHP 8.5 installed in Herd:

```sh
herd link ghost-stories
herd isolate 8.5
```

Open **http://ghost-stories.test**. The home route redirects to the populated story archive. If Herd has parked your project directory already, linking may be unnecessary.

To use Herd's PHP for installation and checks:

```sh
herd composer install --prefer-dist --no-interaction
herd php artisan key:generate
herd php artisan migrate:fresh --seed
herd php artisan test --compact
```

### Without Herd

From the project root:

```sh
php artisan serve --host=127.0.0.1 --port=8000
```

Open **http://127.0.0.1:8000**. Set `APP_URL=http://127.0.0.1:8000` in `.env` when using this address. Keep the terminal running. If port 8000 is occupied, choose another port and update `APP_URL` to match.

For another web server, serve `public/`, never the repository root. It must route application requests to `public/index.php`.

### Frontend assets

The current UI loads `public/css/app.css` and `public/js/*.js` directly. Authentication pages use the same local stylesheet. **No Node process, Vite server, Tailwind CDN or Alpine CDN is required to use the application.** Asset URLs include file modification versions so changed files are fetched after updates.

The starter pack's optional Vite build remains available:

```sh
npm ci
npm run build
```

Use Node 22.12+ for that toolchain. `public/build/` is generated and ignored by Git. The current page layouts do not require its manifest. `npm run dev` is only useful when working on the optional `resources/css` / `resources/js` entry points; edit `public/` for the current UI.

## Demo accounts and seeded data

| Account | Password | Purpose |
| --- | --- | --- |
| `admin@admin.com` | `password` | Required assessment administrator; owns “The floor above the top floor”. |
| `reader1@example.test` | `password` | Ordinary reader, useful for testing votes and ownership. |
| `reader2@example.test` | `password` | Ordinary reader. |
| `reader3@example.test` | `password` | Ordinary reader. |

“Night Watcher” is a seeded content author, not a demo login; its password is randomly generated. The predictable accounts above are for local demonstration, not a public deployment.

The seeder creates all eight model types, the required admin, sample stories and locations, tags, comments/replies, votes, a saved collection and case interpretations. All named demo places and accounts are fictional. Map coordinates for those places are illustrative city positions, not real reports of the described events.

`php artisan db:seed` can be rerun without duplicating the existing sample data. It also restores the required demo admin's password/admin access. It is not a general-purpose command for preserving a customised production account.

## Updating an existing installation

Back up `database/database.sqlite` and `storage/app/private/` first. Keep the existing `.env` and `APP_KEY`.

```sh
git pull
composer install --prefer-dist --no-interaction
php artisan optimize:clear
php artisan migrate
```

Do not regenerate the key or run `migrate:fresh` during an ordinary update. Existing encrypted cookies depend on the key, and `migrate:fresh` deletes database records. Run `php artisan db:seed` only if you want to add/repair the demo fixtures described above.

For a completely disposable reset:

```sh
php artisan optimize:clear
php artisan migrate:fresh --seed
```

A database reset does not erase old uploaded files from disk. Database rows and uploaded files are separate resources; retain them together in backups. Do not blindly delete a storage directory containing real user uploads.

## Assessment checklist

| Requirement | Implementation / evidence |
| --- | --- |
| Public Git repository; atomic increments | Public repository linked above; functional work and cleanup are recorded in separate commits. |
| Own application based on the submitted idea | Original six-model story/place community remains; map, case files, polls, collections and investigation desk add distinct workflows. |
| At least three models, including User | Eight models, listed below. |
| At least one 1:N relationship | User → stories; Location → stories; Story → comments/votes/opinions; User → collections. |
| Model, migration and factory for every model | `app/Models`, `database/migrations`, `database/factories`. |
| Factories called in DatabaseSeeder | All eight factories are used in `database/seeders/DatabaseSeeder.php`; seeded-install tests check all eight tables. |
| Every application route targets a controller | `routes/web.php` and `routes/auth.php`. Framework/vendor health/debug routes are outside the application route definitions. |
| Full seven-method CRUD | `Userzone/StoryController`: index, show, create, store, edit, update, destroy. |
| Input validation and error feedback | Form requests and controller validation; inline errors, old input, named error bags, and status messages. Ignored/privileged client fields are not mass-assigned. |
| Login/register | Starter authentication controllers and Blade forms in `resources/views/auth`. |
| Current user in a view | Account menu, comment composer, author controls and preview byline. |
| Current user in a controller | New stories/comments belong to the current user; policies protect ownership; collections are scoped to their owner. |
| Shared layout and necessary views | `components/site-layout.blade.php`; `layouts/guest.blade.php` for authentication. |
| Seeded assessment admin | `admin@admin.com` / `password`, enforced in DatabaseSeeder. |
| Relevant welcome page | `/` redirects to `/stories`, with the featured case and seeded archive. |
| Extra course concepts | Unique slugs, soft deletion/restoration, cache invalidation, sessions, eager loading, private media and authorisation policies. |

The proposal's display username is implemented as `User.name`; roles use `is_admin` / `is_banned`; password hashing uses Laravel's password cast. Story URLs use a stable unique slug instead of a numeric ID. The tag pivot is Laravel's conventional `story_tag`. These are implementation choices within the proposed model, not additional external requirements.

## Models and relationships

Every model below has its corresponding `*Factory.php` in `database/factories`.

| Model | Defining migration(s), under `database/migrations/` | Main relationships |
| --- | --- | --- |
| User | `0001_01_01_000000_create_users_table.php`; `2026_11_26_203744_add_moderation_fields_to_users_table.php` | Has many stories, comments, votes and reading collections. |
| Location | `2026_11_24_000001_create_locations_table.php`; geography fields added later | Has many stories. |
| Story | `2026_11_24_000002_create_stories_table.php`; later slug, soft-delete, media and coordinate migrations | Belongs to author and place; has many comments, votes and opinions; belongs to many tags/collections. |
| Vote | `2026_11_25_204029_create_votes_table.php` | Belongs to a user and story; unique `(story_id, user_id)`. |
| Comment | `2026_11_26_202745_create_comments_table.php`; `2026_11_26_214248_add_threads_to_comments_table.php` | Belongs to author/story; parent/replies reference Comment; supporters use `comment_user`. |
| Tag | `2026_11_26_203001_create_tags_table.php`; `2026_11_26_203003_create_story_tag_table.php` | Belongs to many stories. |
| ReadingCollection | `2026_11_26_214534_create_reading_collections_table.php` | Belongs to a user; stories use `reading_collection_story`. |
| CaseOpinion | `2026_11_26_221951_create_case_opinions_table.php` | Belongs to a user/story; one changeable interpretation per user/story, tied to the body revision. |

Votes and case opinions are separate: vouch/doubt is the proposal's community score; the case poll records a reader's interpretation after the sequential account reveal. Neither establishes a factual verdict.

## MVC and important file paths

```text
routes/web.php                         Public, member and admin route groups
routes/auth.php                        Authentication routes
app/Http/Controllers/                  Request handling and responses
app/Http/Controllers/Userzone/          Own-story CRUD, desk, profile and trash
app/Http/Controllers/Admin/             Moderation
app/Http/Requests/                      Validated form/archive input
app/Models/                            Data relationships and shared query scopes
app/Policies/                          Story/comment ownership rules
app/Observers/                         Cache invalidation and file cleanup
resources/views/                       Blade pages and reusable components
public/css/app.css                     Current UI stylesheet
public/js/                             Editor, preview, map and case interactions
public/images/                         Bundled artwork and world outline
resources/geography.json               Bundled country/region selector data
database/migrations/                   Schema history
database/factories/                     Factories for every model
database/seeders/DatabaseSeeder.php     Complete assessment/demo seed
database/database.sqlite              Local database, created during setup
storage/app/private/                   Private uploaded files
storage/framework/                     Sessions, view compilation and file cache
storage/logs/laravel.log                Application errors and local mail output
bootstrap/cache/                       Generated framework cache files
tests/Feature/                         HTTP, authorisation and seeded-install tests
tests/Unit/StoryTextTest.php            Markdown-free excerpt check
.github/workflows/tests.yml            Automated PHP checks and optional asset build
```

The source does not require copying into a fixed absolute directory. `/Users/apple/Herd/ghost-stories` is the author's local checkout; your clone can live elsewhere.

| Functionality to explain | Start here |
| --- | --- |
| Create/edit a story | `Userzone/StoryController.php` → `StoryRequest.php` → Story/Location → writer views |
| Reused story-card queries | `Story::withCardDataFor` and `Story::withVotingFor` |
| Reused visible discussion queries | `Comment::visibleInThread`, `Comment::threadsFor`, `Story::visibleComments` |
| Slugs | `Story::booted`, `slugify`, `getRouteKeyName`; slug migration |
| Safe Markdown | `app/StoryText.php`; `StoryPreviewController.php` |
| Private image handling | `app/StoryMedia.php`; `StoryMediaController.php`; `StoryObserver.php` |
| Country/region selection | `app/Geography.php`; `RegionController.php`; `location-picker` component |
| Map | `MysteryMapController.php`; `views/discovery/map.blade.php`; `public/js/mystery-map.js` |
| Featured case, reveals and poll | `app/CaseFile.php`; `CaseController.php`; `views/cases`; `case-file.js` |
| Private collections | `CollectionController.php`; `ReadingCollection.php`; `views/collections` |
| Saved-case activity | `Userzone/DashboardController.php`; `views/userzone/dashboard.blade.php` |
| Moderation | `Admin/*Controller.php`; `Gate::define('admin')` in `AppServiceProvider.php` |

Controller paths in this table are under `app/Http/Controllers`; view paths are under `resources` unless written in full.

## Pages and access

| URL | Access and purpose |
| --- | --- |
| `/`, `/stories` | Public featured case, archive, search, filters and pagination. |
| `/stories/{slug}` | Public visible story and discussion. Votes/comments require login. |
| `/tonight` | Redirect to the day's featured visible case. |
| `/cases/{slug}`, `/trails/{slug}` | Public sequential account and related stories. Poll voting requires login. |
| `/map`, `/discover` | Public atlas and random visible story. |
| `/locations`, `/locations/{id}` | Public place directory and its visible stories. |
| `/users/{id}` | Public profile and visible stories; no private email address. |
| `/register`, `/login` | Guest registration and sign-in. Logout uses POST, not a URL opened with GET. |
| `/dashboard` | Signed-in investigation desk and own stories. |
| `/user/stories` | Own-story index. |
| `/user/stories/create` | Signed-in writer, with preview before publishing. |
| `/user/stories/{slug}`, `/user/stories/{slug}/edit` | Owner-only show/edit; update/delete use PATCH/DELETE forms. |
| `/user/trash` | Owner's soft-deleted stories; restore uses PATCH. |
| `/collections`, `/collections/{id}` | Private to the collection owner. |
| `/profile` | Own account details, password and account deletion. |
| `/admin` | Administrator moderation. |

Use links and forms for write actions. Visiting a POST/PATCH/DELETE endpoint directly in the address bar gives a method-not-allowed response by design. An administrator moderates other authors through `/admin`; the ordinary writer's edit screen remains owner-only. The seeded admin owns one story so full CRUD can be demonstrated immediately.

## Storage, configuration and external services

- **Soft deletion:** stories use `deleted_at`. Public queries exclude trash. Restoring preserves the slug, tags, comments, votes and files. Permanent account deletion separately removes owned content through observers and foreign keys.
- **Cache:** archive statistics last five minutes and are invalidated by observers. The featured case ID is cached until the end of the day; hidden/trashed selections are replaced. Personal vote controls are not stored in the shared statistics cache.
- **Sessions:** archive filters, last random discovery, case reveal progress and last desk visit. Login regenerates the session ID; logout invalidates it and regenerates the CSRF token.
- **Media:** JPG/PNG/WebP, up to 4 MB each; up to six gallery photos. Files are served through a visibility/ownership-checking controller. No `storage:link` is required for these private files. Never put the private storage directory under a public web root.
- **PHP upload configuration:** in the PHP configuration used by the web server, allow `upload_max_filesize=4M` or higher and `post_max_size=40M` or higher. CLI and web-server PHP can load different INI files; `php --ini` reports the CLI configuration. Restart the relevant PHP service after changing its INI file.
- **Mail:** `MAIL_MAILER=log` records local password-reset/email messages in `storage/logs/laravel.log`; it does not deliver mail to an inbox. Configure a real SMTP service only if inbox delivery is required. Email verification is not required to browse/post in this demo.
- **Queues:** no queue worker is required; `.env.example` uses `QUEUE_CONNECTION=sync`.
- **Map:** street tiles use OpenStreetMap with visible attribution. The local world outline is derived from the public-domain [Natural Earth 110m land dataset](https://github.com/nvkelso/natural-earth-vector/blob/master/geojson/ne_110m_land.geojson). Country/region data is bundled with its licence in `resources/geography-LICENSE.txt`.
- **Debugging:** `.env.example` disables the debug toolbar. Enable `DEBUGBAR_ENABLED=true` only when you want it locally. `APP_DEBUG=true` is intended for the local assessment environment; use false for a public deployment.

## Tests and checks

```sh
composer validate --strict
composer check-platform-reqs
php artisan test --compact
vendor/bin/pint
php artisan view:cache
php artisan route:cache
php artisan route:clear
```

On Windows, use `php vendor/bin/pint` if the direct command is unavailable. With Herd, prefix PHP/Composer commands with `herd` as shown earlier.

Tests use a separate in-memory SQLite database through `phpunit.xml`; they do not use the local demo database. Coverage includes ownership, input validation, cache invalidation, seeding every model, seeded admin login, trash restoration, private images, case progress/results and collection privacy.

To inspect routes or run a focused check:

```sh
php artisan route:list --except-vendor
php artisan test --compact tests/Feature/SeededInstallationTest.php
php artisan test --compact tests/Feature/StoryPostingTest.php
```

GitHub Actions runs Composer installation, PHP tests, formatting and the retained Vite build. The assessment install does not require downloading the optional workflow artifact.

## Troubleshooting

These are the main installation/runtime failures and their recovery steps. This is not a guarantee against every possible machine-specific issue; the first error message and application log identify which remedy applies.

| Symptom | Check / remedy |
| --- | --- |
| `Could not open input file: artisan` | Run commands from the repository root, not `public/` or your home directory. |
| PHP version/platform error | Run `php -v`. Use PHP 8.5, or `herd php` / `herd composer`; check Herd's isolated site version too. |
| Missing `vendor/autoload.php` or class not found | Run `composer install`. If vendor files were manually changed, rerun installation and `composer dump-autoload`. |
| Composer cannot download packages | Check internet access/proxy/certificates and retry. Keep `composer.lock`; do not disable TLS or replace install with update. |
| Missing PHP extension / `could not find driver` | Enable PDO SQLite and the extension named by `composer check-platform-reqs` for the PHP executable actually in use. |
| Missing `.env` | Copy `.env.example` to `.env` using the setup command. Do not overwrite an existing customised environment. |
| `No application encryption key has been specified` | On a fresh install, run `php artisan key:generate`. On an existing install, restore its original key rather than generating a replacement. |
| SQLite database does not exist | Create `database/database.sqlite` using the setup command. If `DB_DATABASE` is set, use a valid absolute path. |
| SQLite readonly / unable to open database | The PHP process needs write access to both the SQLite file and its parent directory. Fix ownership/permissions; do not use blanket world-writable permissions. |
| `no such table` / missing column | Run `php artisan migrate`. For a disposable fresh demo, run `php artisan migrate:fresh --seed`. Check `php artisan migrate:status`. |
| No stories or no admin account | Run the seeder against the intended local database. Verify SQLite configuration; tests use a different database. |
| Herd domain does not resolve | Confirm Herd is running and the folder is linked/parked. Run `herd link ghost-stories` from the root. Keep `APP_URL` consistent. |
| Only the welcome page works / other paths 404 | Ensure the web-server document root is `public/` and requests are rewritten to `public/index.php`. |
| Slug URL gives 404 | Use the link rendered by the app; story routes bind by slug. Hidden/trashed stories are intentionally absent publicly. Place/profile IDs remain numeric. |
| 419 / page expired after submitting | Refresh the form, allow cookies and use one consistent host/scheme. Check `APP_URL`, `SESSION_DOMAIN` and secure-cookie settings. Avoid mixing localhost, 127.0.0.1 and the Herd domain in one login session. |
| Login rejects the demo account | Run the seeder for a disposable demo; use `admin@admin.com` / `password`. A banned reader cannot sign in. |
| 403 on edit, collections or admin | Authorisation is enforced server-side. Use the owning reader or the correct admin moderation screen. Login alone does not grant ownership of other people's records. |
| 429 / too many requests | Preview/reveal/poll routes are rate-limited. Wait briefly and retry rather than repeatedly submitting. |
| Permission error writing cache/log/session | Check `storage/` and `bootstrap/cache/` are writable by the PHP process. |
| Changed `.env` appears ignored | Run `php artisan optimize:clear`; restart the PHP service if necessary. |
| Old CSS/JS or an old page is visible | Reload the page. Assets have versioned URLs. If still stale, run `php artisan view:clear` and check you are opening the correct checkout/domain. |
| Vite manifest error from old custom templates | The current layouts use public assets. Pull current code and clear compiled views; use `npm ci && npm run build` only for templates intentionally using Vite. |
| Optional Vite build fails | Check Node 22.12+, run `npm ci`, then `npm run build`. Do not mix arbitrary dependency upgrades into an assessment install. |
| Photo upload rejected / 413 response | Check file type, 4 MB per-file limit, six-photo total and the web PHP upload/post limits above. A huge upload can be rejected before Laravel sees its form fields. |
| Uploaded photo 404 | Check its story is visible or you are its author/admin; trashed stories are not publicly served. Backups must include private files as well as the database. A symlink does not bypass the media policy. |
| Regions appear disabled | Choose a country first. Some have no region data. If the region request fails, a country-only choice remains valid. Choosing an existing place replaces the country selection. |
| Empty archive after navigation | Filters are remembered in the session. Use “Reset filters” / “All stories”. |
| No pin for a story | An author/admin must supply coordinates. A country-only selection is not silently geocoded. |
| Map background lacks street details | Check external connectivity to OpenStreetMap. The bundled base map and story list/pins continue to work. |
| Case results are sealed | Reveal every section and choose an interpretation while signed in. Editing the account changes its revision and resets that flow. |
| Reset email never arrives | The default mail driver writes to the log. Read the latest local reset message or configure SMTP; an inbox message is not sent by the log driver. |
| Chrome warns about the demo password | `password` is deliberately the required assessment credential. Do not use it for a real account or save it to your personal password manager. |
| An unexplained 500 response | Inspect the latest entry in `storage/logs/laravel.log`, then check key, database, permissions and extensions before changing code. Do not publish the entire log or `.env`. |

## Defence walkthrough

1. Open `/` and explain `WelcomeController` redirecting to `StoryController@index`, the query and the shared Blade layout.
2. Open tonight's case. Reveal the account, choose an interpretation, and explain the session progress, cached featured ID and CaseOpinion relationship.
3. Sign in as the assessment admin. Create/edit the admin-owned story. Follow `StoryRequest` → controller → model/media handling → redirect and flash message. Show the stable slug.
4. Trash and restore the story. Show `deleted_at`, the SoftDeletes trait and the ownership policy. Explain how hidden moderation differs from deletion.
5. Use an ordinary reader to vote/comment/save a collection. Show the unique vote constraint, parent/reply relationship and private collection ownership check.
6. Explain User → Story and Location → Story as 1:N, and Story ↔ Tag / ReadingCollection ↔ Story as N:M pivots.
7. Show shared model scopes (`withCardDataFor`, `threadsFor`, `visibleInThread`), eager loading and the five-minute statistics cache with observer invalidation.
8. Change a small label or category label in its central definition, run a focused test and reload. For validation changes, change a rule and demonstrate the error in the form.

## Scope and verification

The core proposal is implemented. Optional proposal ideas such as abuse reports, full-text indexing, weekly trending, reputation and verified badges are not required by the current subset and are not implemented. Email delivery services and queue workers are not required.

Browser QA covered case reveals/results, login errors, ordinary-reader vote switching, rich text escaping, a real image upload, preview/publication, editing with the original slug/photo, collections, investigation desk, reading mode, map filtering and responsive layouts. Fixes included sidebar overlap, reply counts, unstable daily selection, conflicting collection inputs, clipped map pins and stale assets. Destructive account deletion and physical-device testing were not part of that browser pass; account cleanup has automated tests.

The installation checks use a separate temporary SQLite database to exercise key generation, `migrate:fresh --seed` and repeatable seeding. Do not substitute the working demo database for that temporary test database.
