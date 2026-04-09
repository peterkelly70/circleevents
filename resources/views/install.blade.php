<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Install CircleEvents</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100">
        <div class="relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(251,146,60,0.24),_transparent_32%),radial-gradient(circle_at_80%_20%,_rgba(16,185,129,0.14),_transparent_28%),linear-gradient(180deg,_#1c1917,_#0c0a09)]"></div>
            <div class="relative mx-auto max-w-6xl px-6 py-8 lg:px-8">
                <header class="flex items-center justify-between gap-4">
                    <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight text-amber-300">CircleEvents</a>
                    <nav class="flex items-center gap-3 text-sm">
                        <a href="{{ route('events.index') }}" class="rounded-full border border-white/15 px-4 py-2 text-stone-200 transition hover:border-amber-300 hover:text-amber-200">Browse events</a>
                        <a href="{{ route('home') }}" class="rounded-full border border-white/15 px-4 py-2 text-stone-200 transition hover:border-amber-300 hover:text-amber-200">Back to home</a>
                    </nav>
                </header>

                <section class="grid gap-8 py-16 lg:grid-cols-[1.2fr_.8fr]">
                    <div>
                        <p class="mb-4 text-sm uppercase tracking-[0.35em] text-amber-200/80">Self-hosting</p>
                        <h1 class="max-w-4xl text-5xl font-black leading-tight text-white lg:text-6xl">Install CircleEvents on your own server.</h1>
                        <p class="mt-6 max-w-3xl text-lg leading-8 text-stone-300">
                            This page covers the practical setup for a typical Linux VPS or dedicated host running Apache, PHP, and MariaDB or SQLite. It is aimed at packaging and self-hosted deployments, not managed SaaS installs.
                        </p>
                    </div>

                    <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-200">What you need</p>
                        <div class="mt-6 grid gap-4">
                            <div class="rounded-2xl bg-black/20 p-4">
                                <p class="text-lg font-semibold text-white">Server stack</p>
                                <p class="mt-2 text-sm leading-6 text-stone-300">Apache or Nginx, PHP 8.3+, Composer, Node.js, and a writable app directory.</p>
                            </div>
                            <div class="rounded-2xl bg-black/20 p-4">
                                <p class="text-lg font-semibold text-white">Database</p>
                                <p class="mt-2 text-sm leading-6 text-stone-300">SQLite for simple installs, or MariaDB/MySQL for multi-user deployments.</p>
                            </div>
                            <div class="rounded-2xl bg-black/20 p-4">
                                <p class="text-lg font-semibold text-white">Mail</p>
                                <p class="mt-2 text-sm leading-6 text-stone-300">A real SMTP account if you want invitations, reminders, and announcements to leave the server.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-8 pb-16 lg:grid-cols-[1.1fr_.9fr]">
                    <div class="space-y-8">
                        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8">
                            <p class="text-sm uppercase tracking-[0.3em] text-amber-200/80">Install flow</p>
                            <h2 class="mt-2 text-3xl font-bold text-white">Typical setup</h2>
                            <ol class="mt-6 space-y-5 text-sm leading-7 text-stone-300">
                                <li><strong class="text-white">1. Clone the project</strong><br>Place it in a directory such as <code>/var/www/html/events.example.com</code>.</li>
                                <li><strong class="text-white">2. Install dependencies</strong><br>Run <code>composer install</code> and <code>npm install</code>.</li>
                                <li><strong class="text-white">3. Create environment config</strong><br>Copy <code>.env.example</code> to <code>.env</code>, generate an app key, and fill in app URL, mail, and database settings.</li>
                                <li><strong class="text-white">4. Build assets and run migrations</strong><br>Use <code>npm run build</code> and <code>php artisan migrate --force</code>.</li>
                                <li><strong class="text-white">5. Point your web server at the public directory</strong><br>The document root must be <code>/public</code>, not the repo root.</li>
                                <li><strong class="text-white">6. Lock down writable paths</strong><br>Use the provided permission helper script after deploy.</li>
                                <li><strong class="text-white">7. Enable the scheduler</strong><br>CircleEvents uses Laravel scheduling for reminder and background tasks.</li>
                                <li><strong class="text-white">8. Add TLS and mail testing</strong><br>Finish HTTPS and verify invitation email delivery before opening the site to users.</li>
                            </ol>
                        </div>

                        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8">
                            <p class="text-sm uppercase tracking-[0.3em] text-emerald-200/80">Core commands</p>
                            <h2 class="mt-2 text-3xl font-bold text-white">First run</h2>
                            <pre class="mt-6 overflow-x-auto rounded-2xl border border-white/10 bg-black/30 p-5 text-sm leading-7 text-stone-200"><code>composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm run build
php artisan optimize:clear</code></pre>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8">
                            <p class="text-sm uppercase tracking-[0.3em] text-amber-200/80">Helper scripts</p>
                            <h2 class="mt-2 text-3xl font-bold text-white">Included with the project</h2>
                            <div class="mt-6 space-y-4 text-sm leading-7 text-stone-300">
                                <div class="rounded-2xl bg-black/20 p-4">
                                    <p class="font-semibold text-white"><code>tools/lockdown.sh</code></p>
                                    <p class="mt-2">Tightens permissions on storage, cache, and the SQLite database path.</p>
                                </div>
                                <div class="rounded-2xl bg-black/20 p-4">
                                    <p class="font-semibold text-white"><code>tools/install-scheduler-cron.sh</code></p>
                                    <p class="mt-2">Installs the Laravel scheduler cron entry for reminders and scheduled tasks.</p>
                                </div>
                                <div class="rounded-2xl bg-black/20 p-4">
                                    <p class="font-semibold text-white"><code>tools/fix-apache-and-letsencrypt.sh</code></p>
                                    <p class="mt-2">Helps repair Apache and certificate setup on servers that need a fast bootstrap path.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8">
                            <p class="text-sm uppercase tracking-[0.3em] text-emerald-200/80">Deployment notes</p>
                            <h2 class="mt-2 text-3xl font-bold text-white">Important details</h2>
                            <ul class="mt-6 space-y-3 text-sm leading-7 text-stone-300">
                                <li>Document root should point to <code>public/</code>.</li>
                                <li>Set <code>APP_URL</code> correctly before testing email links and invitations.</li>
                                <li>For production mail, replace the log mailer with real SMTP credentials.</li>
                                <li>If using Google Maps, add a browser-restricted <code>GOOGLE_MAPS_API_KEY</code> to <code>.env</code>.</li>
                                <li>Run queue or scheduler infrastructure if you later move mail delivery off the request thread.</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>
