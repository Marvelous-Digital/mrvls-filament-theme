# MRVLS — a Filament v5 theme

A drop-in admin theme for Filament v5: a floating detached sidebar with a tree
nav, a transparent topbar, big rounded cards on a cream-tinted canvas, a
configurable accent, and a fully themed dark mode. Distributed as a plugin, so
installing it in a new project is `composer require` plus one line.

It ships as a **layered CSS asset** on top of Filament's own styles — no Vite
step, no Tailwind build in your app. Every rule targets `.fi-*` / `.mrvls-*`
classes, so it applies cleanly to a stock panel.

## Install

```bash
composer require mrvls/filament-theme
php artisan filament:assets
```

Then register the plugin on any panel:

```php
use Mrvls\FilamentTheme\MrvlsTheme;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(MrvlsTheme::make());
}
```

That alone gives you the theme with its default deep-green + cream palette and
the DM Sans / Bricolage Grotesque type pair. If you deploy, run
`php artisan filament:assets` as part of your build so the stylesheet is
published to `public/css/mrvls/filament-theme/`.

## Let Claude brand it

Getting from "installed" to "looks like our product" is mostly judgement calls:
pulling a palette out of the existing brand, checking contrast in both colour
schemes, matching nav groups to icons. That is exactly the kind of work to hand
to Claude Code (or any coding agent) — including the install itself. Paste this
prompt at the root of your project and it does the whole thing:

```text
Install and brand the mrvls/filament-theme package in this project.

1. Install it: run `composer require mrvls/filament-theme`, then
   `php artisan filament:assets`, then register
   `->plugin(\Mrvls\FilamentTheme\MrvlsTheme::make())` on every Filament panel
   provider that should get the theme. If the repo has a deploy or build
   script, add `php artisan filament:assets` to it.

2. Before writing any colour, work out the brand. Look at logo and favicon
   files in public/ and resources/, tailwind.config.js, the app's front-end
   CSS, the panel's existing ->colors([...]), and any brand or design docs in
   the repo. Tell me the palette you derived and where each value came from.
   If the project has no brand of its own, say so and keep the theme defaults
   rather than inventing one.

3. Apply the palette in a single MrvlsTheme::make() chain:
   - ->accent(color:, ink:, strong:) — `color` is the brand accent, `ink` is
     the text/icon colour that sits ON the accent, `strong` is the hover shade
     (roughly 8–12% darker than `color`).
   - ->surfaces(page:, card:, sidebar:) — `page` is the canvas behind the
     floating panels (slightly tinted), `card` and `sidebar` sit on top of it
     and should be a shade lighter.
   - ->darkAccent(...) and ->darkSurfaces(...) — set these EXPLICITLY. Dark
     tokens are not derived from the light ones, so skipping them leaves the
     default green/cream theme in dark mode. Dark surfaces should be dark and
     desaturated; the dark accent usually needs to be lighter than the light
     accent to stay legible.
   Use hex values. Then check contrast and fix what fails, telling me what you
   adjusted: accent ink vs accent >= 4.5:1, body text vs page and vs card
   >= 4.5:1, in BOTH colour schemes.

4. Fonts: ->bodyFont('Family') (Filament loads it from Bunny Fonts) and
   ->headingFont('Family', url: 'https://fonts.bunny.net/css?family=...').
   Always pass `url:` — omitting it keeps the default Bricolage Grotesque
   stylesheet, so the heading font silently never loads. Pick a pair that
   matches the brand; if the project already uses webfonts on its front end,
   reuse those.

5. Brand lockup: if the project has a logo, wire it with
   ->brandLogos(light: asset('...'), dark: asset('...'), alt: '<product name>')
   — `light` is the logo used ON the light theme. If only one logo exists and
   it is an SVG, derive the second variant by recolouring it; if it is a raster
   file, pass the single logo and tell me a dark variant is missing.

6. Nav groups: for every NavigationGroup in the panel, add
   ->extraSidebarAttributes(['data-group' => '<key>']) using one of the icons
   the theme already ships — operator, management, content, settings, help —
   whenever one honestly fits. For a group that fits none, draw a 24x24
   single-colour stroke icon (viewBox="0 0 24 24", fill="none",
   stroke="black", stroke-width="1.6", no text), URL-encode it as a data: URI
   and register it under its own key via
   ->groupIcons(['<key>' => 'url("data:image/svg+xml,...")']).
   Note that tagging a group hides its per-item icons by design — the group
   title carries the icon instead — so only tag groups where that reads well.

7. Optional chrome, only where it earns its place: ->signout() for a sign-out
   button in the sidebar footer, and ->topbarPill(fn (): ?array => ...) if the
   app has an obvious "record I am currently working on" context worth pinning
   in the topbar. Skip them otherwise.

8. Never edit anything under vendor/. If the plugin's tokens can't express what
   is needed, run `php artisan vendor:publish --tag=mrvls-filament-theme-styles`
   and edit the published copy instead.

9. Boot the app, open the panel in both light and dark mode, and report back:
   the palette and fonts chosen, which nav groups got which icon, which chrome
   you enabled, and anything you couldn't determine from the repo.
```

Review what comes back — the palette and the type pair are brand decisions, and
they are what everyone sees first.

## Manual setup

Prefer to do it yourself? Everything the prompt above automates.

### 1. Register the plugin

```php
->plugin(MrvlsTheme::make())
```

Beyond the look, the plugin sets the panel's primary colour (from your accent),
its sidebar width (`16rem`) and `maxContentWidth('full')` so the floating layout
lines up, and swaps the sidebar toggle icons for Lucide ones. Opt out of any of
those individually:

```php
MrvlsTheme::make()
    ->primaryColor(false)       // leave Filament's primary colour alone
    ->panelLayout(false)        // leave sidebar width / content width alone
    ->lucideToggleIcons(false); // keep Filament's default toggle icons
```

### 2. Set your palette

Everything is driven by CSS custom properties, written into the page head from
the plugin — you never touch a stylesheet:

```php
MrvlsTheme::make()
    ->accent(color: '#2f5fff', ink: '#ffffff', strong: '#2450d8')
    ->surfaces(page: '#f4f5fb', card: '#ffffff', sidebar: '#ffffff')
    ->darkAccent(color: '#8ea9ff', ink: '#0b1020', strong: '#7b98f5')
    ->darkSurfaces(page: '#0b1020', card: '#141a2e', sidebar: '#141a2e');
```

| Argument | What it colours |
| --- | --- |
| `color` | the accent itself — active nav item, buttons, focus rings |
| `ink` | text and icons drawn *on* the accent |
| `strong` | the accent's hover shade |
| `page` | the canvas behind the floating sidebar and cards |
| `card` | panels, tables, modals |
| `sidebar` | the floating sidebar (defaults to the same as `card` in the theme) |

Two things to know:

- **Dark mode is separate.** `darkAccent()` / `darkSurfaces()` do not derive
  from the light ones — set both pairs or dark mode keeps the theme default.
- `--mrvls-accent-tint` and `--mrvls-accent-ring` are derived from
  `--mrvls-accent` with `color-mix`, so a single `accent()` call recolours most
  of the UI. Anything you don't pass keeps the theme default.

### 3. Fonts

```php
MrvlsTheme::make()
    ->bodyFont('Inter')
    ->headingFont('Space Grotesk', url: 'https://fonts.bunny.net/css?family=space-grotesk:500,600,700&display=swap');
```

`bodyFont()` goes through Filament's own `$panel->font()`, which loads the
family from Bunny Fonts. `headingFont()` needs the stylesheet `url:` passed
alongside it — call it with just a family name and the default Bricolage
Grotesque stylesheet stays loaded, so your font never arrives. Pass `null` to
either to drop it.

### 4. Brand lockup

```php
MrvlsTheme::make()
    ->brandLogos(
        light: asset('images/logo-on-light.svg'),
        dark: asset('images/logo-on-dark.svg'),
        alt: 'Acme',
    );
```

Pins your logo to the top of the sidebar (linked to the panel home) and hides
Filament's default logo/name header. `light` is the file shown *on* the light
theme. Pass only one of the two and it is used in both modes.

### 5. Nav group icons

The theme ships icons for five `data-group` keys — `operator`, `management`,
`content`, `settings`, `help`. Tag a navigation group to pick one up:

```php
NavigationGroup::make()
    ->label('Content')
    ->extraSidebarAttributes(['data-group' => 'content']);
```

A tagged group's items go icon-less on purpose, so the group title carries the
icon and the tree stays quiet. Untagged groups keep Filament's default per-item
icons.

For keys of your own, register a mask — any single-colour SVG as a CSS `url()`.
It is painted with the theme's text colour, so the SVG's own colours are
ignored:

```php
MrvlsTheme::make()
    ->groupIcons([
        'billing' => "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' ...%3E%3C/svg%3E\")",
    ]);
```

### 6. Optional chrome

Both off by default.

```php
MrvlsTheme::make()
    // A sign-out button pinned to the sidebar footer.
    ->signout(label: 'Sign out')
    // A context pill in the topbar. The resolver runs per request and returns
    // an array, or null to render nothing.
    ->topbarPill(fn (): ?array => [
        'title' => 'Q4 report',
        'href' => '/admin/reports/1',
        'status' => 'published',      // published | draft | accent (status dot)
        'badge' => 'Live',            // optional pill badge
        'done' => 6, 'total' => 10,   // optional progress ring
        'tooltip' => 'Editing Q4 report',
        'external' => false,
    ]);
```

Only `title` is required on the pill; every other key is optional and its
element is skipped when absent.

### 7. Deeper edits

When the tokens above aren't enough, publish and edit your own copy:

```bash
php artisan vendor:publish --tag=mrvls-filament-theme-styles  # the stylesheet
php artisan vendor:publish --tag=mrvls-filament-theme-views   # brand / signout / pill Blade
```

The stylesheet's `:root` block holds the rest of the design tokens — radii
(`--mrvls-radius-card`, `--mrvls-radius-input`), shadows (`--mrvls-shadow-card`,
`--mrvls-shadow-float`, `--mrvls-shadow-pop`), layout metrics (`--mrvls-gap`,
`--mrvls-sidebar-float-w`), hairlines and text colours (`--mrvls-hairline`,
`--mrvls-text-strong`, `--mrvls-text-muted`), and the motion curves.

Re-run `php artisan filament:assets` after upgrading the package.

## Notes

- The stylesheet is registered globally via `FilamentAsset`, so it applies to
  every Filament panel in the app. The plugin's per-panel wiring (colours,
  fonts, chrome) only applies where you register it.
- Filament's default (colourless) buttons — `Cancel`, and the builder's
  `Add to ...` / `Insert between blocks` — ship pure white with a violet-grey
  ink and ring. The theme retints them onto the card tokens; outlined and link
  variants keep their transparent faces.
- If [`mrvls/filament-block-picker`](https://github.com/Marvelous-Digital/mrvls-filament-block-picker)
  is installed, its `--mrvls-block-*` tokens are aliased to this theme's, so the
  picker's search field and hover preview follow your palette instead of falling
  back to Filament's grey ramp. Nothing to configure; inert without the picker.
- Requires PHP 8.2+ and Filament v5.
