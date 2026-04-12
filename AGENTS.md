# AGENTS.md

## Project
- App name: `CircleEvents`
- Framework: Laravel with Breeze Blade auth
- Purpose: Facebook-events replacement for organizations, public event discovery, and mailing-list subscriptions

## Working Rules
- Keep the stack server-rendered unless there is a clear reason to add heavier frontend tooling.
- Prefer Blade, Eloquent relationships, and simple controller actions over premature abstraction.
- Use slugs for public URLs on organizations, events, and mailing lists.
- Preserve the MVP scope: auth, organizations, events, RSVP states, mailing-list subscriptions, and organizer dashboard flows.
- When adding features, update the design document in `docs/design-document.md`.

## Infrastructure Notes
- Intended document root: `/var/www/html/events.computer-wizard.com.au/public`
- Expected hostname: `events.computer-wizard.com.au`
- Apache and TLS setup may require root-level system access outside this repo.

## Database Handling - CRITICAL RULES

### BEFORE RUNNING TESTS:
- Tests with `RefreshDatabase` can interact with the main database - always backup first
- Run: `cp database/database.sqlite database/backup-$(date +%Y%m%d-%H%M%S).sqlite`

### NEVER DO:
- NEVER run `php artisan migrate:fresh` or `php artisan migrate:fresh --seed` - this DELETES all data
- NEVER run `php artisan db:wipe` or drop tables
- NEVER run `php artisan migrate:rollback` repeatedly (loses all data)
- NEVER assume it's safe to reset a database - always ask first

### ONLY DO WITH EXPLICIT PERMISSION:
- Ask before ANY database destructive operation
- If asked to migrate, use `php artisan migrate` (safe - only runs new migrations)
- If asked to reset, ask for backup first
- Ask before running seeds that add test data

### BACKUP BEFORE ANY RESET:
- If user asks for reset, create backup first:
  ```
  cp database/database.sqlite database/backup-YYYY-MM-DD.sqlite
  ```
- Or export data before reset
- NEVER reset without existing backup

### DEVELOPMENT vs PRODUCTION:
- Ask what environment before database actions
- Never reset production databases
- Ask if user wants backup before any destructive action
