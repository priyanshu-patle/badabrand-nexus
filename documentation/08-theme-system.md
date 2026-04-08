# Theme System

Badabrand Technologies includes a production-safe theme layer for both the admin workspace and the public website.

## Available Themes

- `dark` - Dark Pro
- `light` - Light Classic
- `midnight` - Midnight Glass

## Where Themes Are Managed

- Admin route: `/admin/appearance/themes`
- Backup controls: `/admin/settings/general`

## Theme Settings Keys

- `theme_default`
- `theme_admin`
- `theme_public`

## How It Works

1. Layouts resolve the theme through `resolve_theme_name()`
2. The selected value is written into the `<html data-theme="...">` attribute
3. CSS variables in `public/assets/css/app.css` drive the visual system
4. Users can temporarily switch themes from the topbar/site toggle, while admin settings control defaults

## Extending Themes

To add a new theme:

1. Add a new theme key to `theme_presets()` in `app/Helpers/functions.php`
2. Add CSS variable overrides for `[data-theme="your-theme"]` in `public/assets/css/app.css`
3. Add or update any preview card copy in `workspace-appearance-themes.php`

