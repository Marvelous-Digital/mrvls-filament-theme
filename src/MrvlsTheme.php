<?php

namespace Mrvls\FilamentTheme;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsIconAlias;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

/**
 * MRVLS — a Filament v5 admin theme distributed as a plugin.
 *
 * The look lives in resources/css/theme.css (registered as a layered asset by
 * MrvlsThemeServiceProvider). This plugin does the per-panel wiring:
 * brand colours, fonts, the floating-sidebar layout, the sidebar toggle icons,
 * and the opt-in chrome (brand lockup, sign-out button, topbar pill, group
 * icons). Register it on the panels you want; panels without it are untouched.
 */
class MrvlsTheme implements Plugin
{
    /** @var array{color?: string, ink?: string, strong?: string} */
    protected array $accent = [];

    /** @var array{color?: string, ink?: string, strong?: string} */
    protected array $darkAccent = [];

    /** @var array{page?: string, card?: string, sidebar?: string} */
    protected array $surfaces = [];

    /** @var array{page?: string, card?: string, sidebar?: string} */
    protected array $darkSurfaces = [];

    protected bool $setPrimaryColor = true;

    protected bool $applyPanelLayout = true;

    protected ?string $bodyFont = 'DM Sans';

    protected ?string $headingFont = 'Bricolage Grotesque';

    protected ?string $headingFontUrl = 'https://fonts.bunny.net/css?family=bricolage-grotesque:500,600,700&display=swap';

    /** @var array{light?: string, dark?: string}|null */
    protected ?array $brandLogos = null;

    protected string $brandAlt = '';

    protected bool $signout = false;

    protected string $signoutLabel = 'Sign out';

    protected ?Closure $topbarPill = null;

    /** @var array<string, string> data-group value => CSS url(...) for the mask */
    protected array $groupIcons = [];

    protected bool $lucideToggleIcons = true;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'mrvls-filament-theme';
    }

    public function accent(?string $color = null, ?string $ink = null, ?string $strong = null): static
    {
        $this->accent = array_filter([
            'color' => $color,
            'ink' => $ink,
            'strong' => $strong,
        ], fn ($value): bool => $value !== null);

        return $this;
    }

    public function darkAccent(?string $color = null, ?string $ink = null, ?string $strong = null): static
    {
        $this->darkAccent = array_filter([
            'color' => $color,
            'ink' => $ink,
            'strong' => $strong,
        ], fn ($value): bool => $value !== null);

        return $this;
    }

    public function surfaces(?string $page = null, ?string $card = null, ?string $sidebar = null): static
    {
        $this->surfaces = array_filter([
            'page' => $page,
            'card' => $card,
            'sidebar' => $sidebar,
        ], fn ($value): bool => $value !== null);

        return $this;
    }

    public function darkSurfaces(?string $page = null, ?string $card = null, ?string $sidebar = null): static
    {
        $this->darkSurfaces = array_filter([
            'page' => $page,
            'card' => $card,
            'sidebar' => $sidebar,
        ], fn ($value): bool => $value !== null);

        return $this;
    }

    public function primaryColor(bool $condition = true): static
    {
        $this->setPrimaryColor = $condition;

        return $this;
    }

    public function panelLayout(bool $condition = true): static
    {
        $this->applyPanelLayout = $condition;

        return $this;
    }

    public function bodyFont(?string $font): static
    {
        $this->bodyFont = $font;

        return $this;
    }

    public function headingFont(?string $font, ?string $url = null): static
    {
        $this->headingFont = $font;

        if (func_num_args() > 1) {
            $this->headingFontUrl = $url;
        }

        return $this;
    }

    public function brandLogos(?string $light = null, ?string $dark = null, string $alt = ''): static
    {
        $this->brandLogos = array_filter([
            'light' => $light,
            'dark' => $dark,
        ], fn ($value): bool => $value !== null);

        $this->brandAlt = $alt;

        return $this;
    }

    public function signout(bool $enabled = true, string $label = 'Sign out'): static
    {
        $this->signout = $enabled;
        $this->signoutLabel = $label;

        return $this;
    }

    public function topbarPill(?Closure $resolver): static
    {
        $this->topbarPill = $resolver;

        return $this;
    }

    /**
     * @param  array<string, string>  $icons  data-group value => CSS url(...) mask
     */
    public function groupIcons(array $icons): static
    {
        $this->groupIcons = $icons;

        return $this;
    }

    public function lucideToggleIcons(bool $condition = true): static
    {
        $this->lucideToggleIcons = $condition;

        return $this;
    }

    public function register(Panel $panel): void
    {
        if ($this->bodyFont !== null) {
            $panel->font($this->bodyFont);
        }

        if ($this->setPrimaryColor && isset($this->accent['color'])) {
            $panel->colors(['primary' => Color::hex($this->accent['color'])]);
        }

        if ($this->applyPanelLayout) {
            $panel->sidebarWidth('16rem')->maxContentWidth('full');
        }
    }

    public function boot(Panel $panel): void
    {
        if ($this->lucideToggleIcons) {
            $this->registerToggleIcons();
        }

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => $this->headMarkup(),
        );

        if ($this->brandLogos) {
            FilamentView::registerRenderHook(
                PanelsRenderHook::SIDEBAR_START,
                fn (): string => view('mrvls-filament-theme::brand', [
                    'logos' => $this->brandLogos,
                    'alt' => $this->brandAlt,
                ])->render(),
            );
        }

        if ($this->signout) {
            FilamentView::registerRenderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn (): string => view('mrvls-filament-theme::signout', [
                    'label' => $this->signoutLabel,
                ])->render(),
            );
        }

        if ($this->topbarPill !== null) {
            $resolver = $this->topbarPill;

            FilamentView::registerRenderHook(
                PanelsRenderHook::TOPBAR_START,
                function () use ($resolver): string {
                    $pill = $resolver();

                    return $pill
                        ? view('mrvls-filament-theme::topbar-pill', ['pill' => $pill])->render()
                        : '';
                },
            );
        }
    }

    protected function registerToggleIcons(): void
    {
        $close = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/><path d="m16 15-3-3 3-3"/></svg>';
        $open = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/><path d="m14 9 3 3-3 3"/></svg>';

        FilamentIcon::register([
            PanelsIconAlias::TOPBAR_OPEN_SIDEBAR_BUTTON => new HtmlString($open),
            PanelsIconAlias::TOPBAR_CLOSE_SIDEBAR_BUTTON => new HtmlString($close),
            PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON => new HtmlString($close),
            PanelsIconAlias::SIDEBAR_EXPAND_BUTTON => new HtmlString($open),
        ]);
    }

    protected function headMarkup(): string
    {
        $out = '';

        if ($this->headingFontUrl !== null) {
            $out .= '<link href="' . e($this->headingFontUrl) . '" rel="stylesheet" />';
        }

        $rules = [];

        $root = [
            ...$this->tokenLines($this->accent, $this->surfaces),
        ];

        if ($this->headingFont !== null) {
            $root[] = "--mrvls-font-heading:'" . $this->sanitize($this->headingFont) . "', ui-sans-serif, system-ui, sans-serif";
        }

        $dark = $this->tokenLines($this->darkAccent, $this->darkSurfaces);

        if ($root) {
            $rules[] = ':root{' . implode(';', $root) . ';}';
        }

        if ($dark) {
            $rules[] = ':is(.dark){' . implode(';', $dark) . ';}';
        }

        if ($this->brandLogos) {
            $rules[] = '.fi-sidebar-header-ctn,.fi-sidebar-header{display:none !important;}';
        }

        foreach ($this->groupIcons as $group => $icon) {
            $selector = ".fi-sidebar-group[data-group='" . $this->sanitize((string) $group) . "']";
            $mask = $this->sanitizeMask($icon);

            $rules[] = $selector . ' .fi-sidebar-item-icon{display:none !important;}';
            $rules[] = $selector . " .fi-sidebar-group-btn::before{content:'';width:1.5rem;height:1.5rem;flex:none;background-color:var(--mrvls-text-muted);-webkit-mask:" . $mask . ' center/contain no-repeat;mask:' . $mask . ' center/contain no-repeat;}';
            $rules[] = $selector . '.fi-active .fi-sidebar-group-btn::before{background-color:var(--mrvls-text-strong);}';
        }

        if ($rules) {
            $out .= '<style>' . implode('', $rules) . '</style>';
        }

        return $out;
    }

    /**
     * @param  array{color?: string, ink?: string, strong?: string}  $accent
     * @param  array{page?: string, card?: string, sidebar?: string}  $surfaces
     * @return array<int, string>
     */
    protected function tokenLines(array $accent, array $surfaces): array
    {
        $map = [
            '--mrvls-accent' => $accent['color'] ?? null,
            '--mrvls-accent-ink' => $accent['ink'] ?? null,
            '--mrvls-accent-strong' => $accent['strong'] ?? null,
            '--mrvls-page-bg' => $surfaces['page'] ?? null,
            '--mrvls-card-bg' => $surfaces['card'] ?? null,
            '--mrvls-sidebar-bg' => $surfaces['sidebar'] ?? null,
        ];

        $lines = [];

        foreach ($map as $token => $value) {
            if ($value !== null) {
                $lines[] = $token . ':' . $this->sanitize($value);
            }
        }

        return $lines;
    }

    protected function sanitize(string $value): string
    {
        return trim((string) preg_replace('/[^#a-zA-Z0-9(),.%\s_-]/', '', $value));
    }

    protected function sanitizeMask(string $value): string
    {
        return trim((string) preg_replace('/[^#a-zA-Z0-9(),.%\s\'":;\/=+_-]/', '', $value));
    }
}
