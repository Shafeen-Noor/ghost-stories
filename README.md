# Unexplained

A community atlas for folklore, conspiracy theories, ghost stories and unexplained experiences. Readers follow mysteries on a map, compare explanations, and add their own encounters. The repository and local Herd domain retain the original ghost-stories name. Built with PHP 8.5, Laravel 13, Blade and SQLite, using the teacher's [starter pack](https://github.com/ndeblauw/starterpack) and translating the concepts from [blaug](https://github.com/ndeblauw/blaug) into a story archive.

## Fresh installation

Requirements: PHP 8.5 with SQLite and mbstring, Composer. Node 22.12+ is needed only to rebuild the optional Vite assets.

```sh
git clone https://github.com/Shafeen-Noor/ghost-stories.git
cd ghost-stories
composer install
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
php artisan migrate:fresh --seed
```

With Laravel Herd:

```sh
herd link ghost-stories
herd isolate 8.5
```

Open http://ghost-stories.test. Without Herd, run `php artisan serve` and use the address it prints. The main site uses the committed CSS and JavaScript in `public/`. Authentication templates also support the starter pack's CDN fallback. To build the Vite assets locally, run `npm ci && npm run build`.

The assessment account is **admin@admin.com**, password **password**. This is the required local demo account. Three ordinary readers are seeded as `reader1@example.test`, `reader2@example.test` and `reader3@example.test`, also with password `password`.

`php artisan migrate:fresh --seed` creates every table and all demo data. `php artisan db:seed` can also be rerun without duplicating the sample records. The sample stories and named places are fictional.

## Features and access

- Visitors can browse, search, filter and paginate stories; read discussions; and visit place and storyteller pages.
- Registered users can publish, edit and trash their own stories, restore them, select countries and regions, choose up to five tags, comment, and vote on other people's stories.
- The editorial interface includes a visual text editor with headings, lists, quotes, links, source mode and a safe server-rendered preview.
- The compact writer keeps essential details beside the editor. Authors can attach up to six photographs and optionally choose an approximate map pin. Cover and atmosphere controls have been removed from the writing form. Existing image data remains intact when editing.
- Preview & publish opens a full review with the title, author, location, formatted text and photos. Nothing is written to the database until Publish story is confirmed.
- The mystery map supports panning, zooming, clickable stories and category/country filters. Country-only stories are not assigned invented coordinates. The four fictional demo places use explicitly illustrative city positions.
- Surprise me opens a random visible mystery and remembers the last discovery in the session to avoid immediate repeats when possible.
- Discussions support possible explanations, related encounters and replies. Readers can give one removable convincing vote to another reader's explanation. Moderated threads do not expose their replies.
- Private saved collections let readers group, rename and remove bookmarks. Removing a collection never deletes its stories.
- A story has one vote per reader. A reader can change or remove their vote. Its truth score is the sum of the votes.
- Story slugs are unique and remain stable after editing. Trashed stories reserve their slugs for restoration.
- Administrators can hide or restore stories, moderate comments, suspend readers, edit place descriptions and map coordinates, remove unused places, and manage tags.
- A suspended reader cannot sign in or continue an existing authenticated session.
- Users can update their account details or password. Deleting an account permanently removes its stories, comments and votes, while retaining other users' independent content.

## MVC and database structure

Every application route points to a controller method. Public, authenticated and admin routes are grouped in `routes/web.php`; authentication routes are in `routes/auth.php`.

| Responsibility | Where to find it |
| --- | --- |
| Full seven-method story CRUD | `app/Http/Controllers/Userzone/StoryController.php` |
| Public archive queries and session filters | `StoryController`, `ArchiveRequest`, and the `Story::filter` scope |
| Ownership rules | `app/Policies/StoryPolicy.php` and `CommentPolicy.php` |
| Validation and error messages | Form requests, controller validation, and Blade form components |
| Shared page frame and presentation | `resources/views/components/site-layout.blade.php` and page views |
| Database definitions and sample data | `database/migrations`, `database/factories`, `DatabaseSeeder` |
| Country and region selection | `Geography`, `Location`, `RegionController`, `location-picker` component |
| Formatted writing and private images | `StoryText`, `StoryMedia`, `StoryPreviewController`, `StoryMediaController` |
| Shared statistics cache | `ArchiveStatistics`, `StoryObserver`, `VoteObserver` |
| Account deletion and related records | `ProfileController`, `UserObserver`, and database foreign keys |

The seven models are User, Location, Story, Vote, Comment, Tag and ReadingCollection. Every model has a migration and factory, and every factory is used in the seeder. Users have many stories and comments; places have many stories; stories have many votes and comments. Story and Tag use the `story_tag` pivot for a many-to-many relationship. The unique `(story_id, user_id)` constraint prevents duplicate votes.

## Cache, soft deletion and session state

Archive statistics are cached for five minutes. Story and vote observers clear the cache when relevant records change. Cached data is shared aggregate data; a reader's vote controls are loaded separately for each request.

Story deletion uses Laravel SoftDeletes and a `deleted_at` column. Public queries exclude the trash. An author can restore only their own stories, with their original slug, votes, comments and tags intact. Moderators can also restore stories. Account deletion is a separate, password-confirmed permanent operation.

The session stores validated archive filters. The reset link clears them. Login regenerates the session ID, and logout invalidates the session and regenerates its CSRF token. Flash messages report successful writes, while validation returns errors and old input to forms.

## Useful pages

| Page | Access |
| --- | --- |
| `/` → `/stories` | Public home and archive |
| `/stories/{slug}` | Public visible story, votes and comments |
| `/map`, `/discover` | Public interactive atlas and random discovery |
| `/collections`, `/collections/{id}` | Private saved lists for their owner |
| `/locations`, `/locations/{id}` | Public place directory and stories |
| `/users/{id}` | Public storyteller profile, without email or private data |
| `/dashboard` | Personal dashboard and account links |
| `/user/stories` | Own stories; full resource CRUD under this prefix |
| `/user/trash` | Own trashed stories and restoration |
| `/profile` | Account details, password and account deletion |
| `/admin` | Administrator moderation |

The atlas uses a small first-party pan/zoom script rather than an additional JavaScript dependency. Detailed street tiles come from [OpenStreetMap](https://www.openstreetmap.org/copyright), with visible attribution. A bundled Mercator land outline derived from the public-domain [Natural Earth 110m land dataset](https://github.com/nvkelso/natural-earth-vector/blob/master/geojson/ne_110m_land.geojson) keeps the background and pins usable when tiles are slow or unavailable.

Place pages embed an OpenStreetMap map when an administrator has supplied both coordinates. The geography selector itself works from bundled data and needs no external API key. Email notifications and background queue workers are outside the implemented subset of the original proposal; they are not needed to run the project.

Uploads accept JPG, PNG or WebP images up to 4 MB each. They are stored on the private local disk and served through a controller that checks story visibility and ownership; no storage symlink is needed. Trashing a story retains its images for restoration. Replacing or removing an image cleans up its file, and permanent account deletion removes the associated files. The default photographs are bundled in `public/images`. Set PHP upload limits above 4 MB per file and allow a request large enough for the selected images (for example, `post_max_size=40M`).

Formatted text uses the existing CommonMark dependency. Raw HTML is escaped and unsafe links are blocked. The visual editor submits Markdown; Source mode allows direct editing, and Preview uses the same renderer as the public article.

## Tests and build

```sh
php artisan test --compact
vendor/bin/pint
npm ci
npm run build
```

GitHub Actions runs the PHP tests, formatting and frontend build after each push. It also publishes a downloadable source/build artifact. Tests use a separate in-memory SQLite database.

## A short defence walkthrough

1. Start at the archive; search or filter, open a story, and explain `route → controller → model/query → Blade view`.
2. Sign in as the seeded admin. Open Dashboard and edit the admin-owned story. Follow `StoryRequest`, the ownership policy, `update`, and the redirect with its flash message.
3. Trash and restore the story. Show `deleted_at`, the SoftDeletes trait, and the author-only restore route.
4. Vote using an ordinary reader. Show the unique vote constraint and the truth-score calculation.
5. Choose tags and explain the many-to-many pivot; contrast it with the story-to-author and story-to-place foreign keys.
6. Show `ArchiveStatistics` and its observers. Explain why vote controls are not shared in the cache.
7. Show the remembered search filters in `ArchiveRequest`, then reset them.
8. Open moderation and run a targeted feature test. The tests also cover seeding and the required admin login.

For the investigation workflow, open the map, select a story, compare explanations, add an encounter or reply, and save it to a private collection. Explain the self-referencing Comment relationship, the comment_user endorsement pivot, and the ReadingCollection-to-Story pivot. The preview endpoint renders text without saving any story.
