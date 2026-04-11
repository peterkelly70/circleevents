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
- User profiles support 256x256 normalized avatar uploads
- Registration requires accepting a lightweight usage-conditions notice, viewable in a popup from the sign-up form
- Site admins can switch both user registration and organization creation between open and moderated approval modes
- Uploaded images are re-encoded, MIME-checked, size-checked, and can optionally be scanned with ClamAV when configured
- Admins have a report-review queue plus suspend/restore controls for users and organizations
- Public homepage acting as a real landing page with registration CTA, public events, and public organizations
- Public install page for self-hosted packaging and server setup guidance
- Helper scripts include scheduler, permission lockdown, certificate repair, and optional ClamAV install support for safer uploads
- Public events index and event detail pages
- Public organization pages with follower posts and member announcements
- Organization pages lead with published events and recent member messages, and published events can expand inline to show event discussion previews
- Organizations can choose from twenty-two full profile themes, including fantasy/medieval, light serif editorial, dark sci-fi, royal-blue, cosmic, woodland, marine, bright iridescent, and fantasy-palette treatments with distinct typography
- Invite-by-email flow for organizations with account creation/login handoff
- Manager assignment for organizations through promotion or manager-role invites
- Organizer dashboard for:
  - creating organizations
  - creating events
  - creating mailing lists
- Subscriber flow for joining mailing lists
- Follower flow for joining organizations directly
- RSVP flow for marking interest, going, or waitlist
- Mailing-list subscribers receive email notices when an organization publishes a public or unlisted event
- Each newly published event gets an automatic linked update mailing list that users can subscribe to from the event page
- Organizers can create repeating daily, weekly, or monthly event series from the dashboard
- Event editors can optionally re-announce updates by email and connected outbound channels, and managers can manually re-announce an event from the event page
- Organizers can choose follower reminder timings per event for one week, one day, and one hour before the start time
- Follower reminder sends exclude owners/managers and avoid double-emailing people who are also marked as going
- Attendees with RSVP status `going` can set their own one-week, one-day, and one-hour reminder preferences on each event page
- Event discussions, organization posts, and organization member messages support BBCode-style formatting and optional image attachments
- Event creation uses separate date and time controls and supports Google Maps Places-powered location lookup when `GOOGLE_MAPS_API_KEY` is configured
- Events can be marked as online, with an optional meeting URL instead of a physical venue
- Event detail pages render a live venue map when coordinates are present, with a direct Google Maps directions link
- Event pages support Google Calendar, Outlook, and `.ics` save/export actions
- Events and organizations support both targeted email invites and reusable share invite codes with optional expiry
- Managers can cancel pending event and organization email invites with a required cancellation reason that is shown if someone later tries to use the revoked invite
- Organization profiles can include website, Discord, X/Twitter, and Facebook links
- Organizations can connect a Discord webhook to auto-post newly published non-private events
- Organization announcements can also be posted to Discord, with a per-message checkbox and an organization-level default
- Organizations can connect Facebook Page credentials to post events and announcements to a Facebook Page
- Event pages and organization announcements include lightweight copy/share actions as a low-friction alternative to deep social integrations

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
- Dashboard includes an in-app help popup for first-time organizers
- Organization uploads are normalized on save to a 512x512 avatar and a 1600x480 banner
- Organization uploads accept larger originals before normalization so high-resolution source images can be converted server-side
- Each organization can switch between twenty-two distinct public themes rather than a single shared presentation
- Event detail pages inherit the owning organization theme, unless the signed-in user has selected a personal organization-theme override
- Event image uploads are normalized on save to 1600x900

## Next Steps
1. Add authorization policies so organizer ownership and manager roles are enforced centrally.
2. Add seeding/demo fixtures for quick preview environments.
3. Add email notifications for event reminders and mailing-list sends.
4. Add event filtering by city, date, and organization.
5. Add tests for organizer creation, event publishing, RSVP updates, and subscriptions.
