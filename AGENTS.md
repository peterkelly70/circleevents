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
