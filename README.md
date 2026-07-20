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

If you deploy, run `php artisan filament:assets` as part of your build so the
stylesheet is published to `public/css/mrvls/filament-theme/`.

## Use

Register the plugin on any panel:

```php
use Mrvls\FilamentTheme\MrvlsTheme;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(MrvlsTheme::make());
}
```

That alone gives you the theme with the default deep-green + cream palette and
the DM Sans / Bricolage Grotesque type pair. The plugin also sets the panel's
primary colour, sidebar width (`16rem`) and `maxContentWidth('full')` so the
floating layout lines up — pass `->panelLayout(false)` / `->primaryColor(false)`
to opt out.

## Rebrand

Everything is driven by CSS custom properties; override the ones you want from
the plugin:

```php
MrvlsTheme::make()
    ->accent(color: '#2f5fff', ink: '#ffffff', strong: '#2450d8')
    ->darkAccent(color: '#8ea9ff', ink: '#0b1020')
    ->surfaces(page: '#f4f5fb', card: '#ffffff')
    ->darkSurfaces(page: '#0b1020', card: '#141a2e')
    ->bodyFont('Inter')
    ->headingFont('Space Grotesk', url: 'https://fonts.bunny.net/css?family=space-grotesk:500,600,700&display=swap');
```

`--mrvls-accent-tint` and `--mrvls-accent-ring` derive from `--mrvls-accent` via
`color-mix`, so a single `accent()` call recolours most of the UI. Set `strong`
(the hover shade) and `ink` (text/icon on the accent) when you do a full
rebrand. Anything not passed keeps the theme default.

For deeper edits, publish the stylesheet and edit the tokens directly:

```bash
php artisan vendor:publish --tag=mrvls-filament-theme-styles
```

## Optional chrome

Opt-in pieces that match the theme. Each is off by default.

```php
MrvlsTheme::make()
    // Brand lockup at the top of the sidebar (hides Filament's default logo).
    // Pass one logo, or a light/dark pair.
    ->brandLogos(
        light: asset('images/logo-on-light.svg'),
        dark: asset('images/logo-on-dark.svg'),
        alt: 'Acme',
    )
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
    ])
    // Group-title icons. Key by the `data-group` attribute you set on the nav
    // group; value is a CSS url() painted as a mask (any single-colour SVG).
    ->groupIcons([
        'content' => "url(\"data:image/svg+xml,...\")",
    ]);
```

### Group icons convention

The theme ships default icons for these `data-group` keys: `operator`,
`management`, `content`, `settings`, `help`. Tag a nav group to pick one up and
its items go icon-less so the group title carries the icon:

```php
NavigationGroup::make()
    ->label('Content')
    ->extraSidebarAttributes(['data-group' => 'content']);
```

Groups that don't use these keys keep Filament's default per-item icons.

## Notes

- The stylesheet is registered globally via `FilamentAsset`, so it applies to
  every Filament panel in the app. The plugin's per-panel wiring (colours,
  fonts, chrome) only applies where you register it.
- Requires PHP 8.2+ and Filament v5.

## License

MIT.
