# CircleEvents Design Document

## Goal
Build a Laravel application that replaces the practical parts of Facebook Events for clubs, communities, and event organizers:
- user accounts and organizer login
- organization profiles
- public event pages
- RSVP tracking
- mailing-list subscriptions

## MVP Scope
- Authentication with registration, login, password reset, and profile management
- Public homepage highlighting featured events, organizations, and mailing lists
- Public events index and event detail pages
- Public organization pages with follower posts and member announcements
- Invite-by-email flow for organizations with account creation/login handoff
- Manager assignment for organizations through promotion or manager-role invites
- Organizer dashboard for:
  - creating organizations
  - creating events
  - creating mailing lists
- Subscriber flow for joining mailing lists
- Follower flow for joining organizations directly
- RSVP flow for marking interest, going, or waitlist

## Data Model
- `users`
  - base Laravel auth fields
  - `city`
  - `bio`
- `organizations`
  - owner, name, slug, summary, description, city, website, avatar, banner, visibility (`public`, `private`, `unlisted`)
- `organization_user`
  - membership pivot with role including owner, manager, and follower
- `organization_posts`
  - organization, user, body
- `organization_messages`
  - organization, sender, subject, body, emailed timestamp
- `organization_invitations`
  - organization, inviter, recipient email, role, token, accepted timestamp
- `reports`
  - reporter, polymorphic target, reason, details, status
- `blocks`
  - blocker, polymorphic target
- `events`
  - organization, creator, title, slug, summary, description, venue, dates, timezone, capacity, visibility (`public`, `private`, `unlisted`), published flag
- `event_rsvps`
  - event, user, status, notes
- `mailing_lists`
  - organization, name, slug, description, audience
- `mailing_list_user`
  - subscriber state and subscribed timestamp

## Route Shape
- `/`
- `/events`
- `/events/{slug}`
- `/organizations/{slug}`
- `/mailing-lists/{slug}`
- `/dashboard`
- auth/profile routes from Breeze

## UI Direction
- Warm amber and stone palette rather than default Laravel styling
- Dark mode is the default visual theme across public, auth, and dashboard surfaces
- Public-facing landing page with strong hierarchy and clear organizer CTA
- Dashboard oriented around organizer actions instead of generic “you are logged in”

## Next Steps
1. Add authorization policies so organizer ownership and manager roles are enforced centrally.
2. Add seeding/demo fixtures for quick preview environments.
3. Add email notifications for event reminders and mailing-list sends.
4. Add event filtering by city, date, and organization.
5. Add tests for organizer creation, event publishing, RSVP updates, and subscriptions.
