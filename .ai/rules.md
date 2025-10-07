# MyStrategies AI Development Rules

## Project Overview
This is a Laravel 12 application for managing trading strategies with Livewire 3 + Volt for interactivity, Flux UI for components, and Pest 4 for testing.

## Git Workflow (CRITICAL)
- **Development branch**: `dev` - all development happens here
- **Production branch**: `main` - stable, tested code only
- **Process**:
    1. Make all changes in `dev` branch
    2. Test thoroughly in `dev`
    3. Merge to `main` only after successful testing
    4. Use feature branches for large tasks: `feature/feature-name`

## Pre-Merge Checklist (dev → main)
Before merging `dev` to `main`, ensure:
- [ ] All tests pass: `php artisan test`
- [ ] Code is formatted: `vendor/bin/pint --dirty`
- [ ] Functionality verified locally
- [ ] No merge conflicts with `main`
- [ ] Database migrations tested (if any)
- [ ] Browser tests pass (if applicable)

## Tech Stack
- **Backend**: Laravel 12 (PHP 8.3.26)
- **Frontend**: Livewire 3 + Volt (single-file components)
- **UI Components**: Flux UI Free (no Pro components available)
- **Styling**: Tailwind CSS v4
- **Testing**: Pest 4 (with browser testing support)
- **Authentication**: Laravel Fortify
- **Code Formatting**: Laravel Pint

## Development Standards

### Code Quality
- Always run `vendor/bin/pint --dirty` before finalizing changes
- Write tests for all new features (feature tests preferred)
- Use factories for test data, never create models directly in tests
- Follow existing project conventions - check sibling files first

### Livewire + Volt
- Use Volt for all interactive components (class-based or functional)
- Single root element required in Volt components
- Use `wire:model.live` for real-time updates
- Add `wire:key` in loops for proper reactivity
- Create new components: `php artisan make:volt [name] --test --pest`

### Testing Requirements
- **Write tests for every change** - this is mandatory
- Use Pest 4 syntax exclusively
- Run minimal tests with filters for speed: `php artisan test --filter=testName`
- Browser tests for UI interactions (use Pest 4 browser testing)
- Test directory: `tests/Feature/` for feature tests, `tests/Browser/` for browser tests
- Never remove tests without explicit approval

### UI Components
- **First choice**: Use Flux UI components when available
- **Available Flux components**: avatar, badge, brand, breadcrumbs, button, callout, checkbox, dropdown, field, heading, icon, input, modal, navbar, profile, radio, select, separator, switch, text, textarea, tooltip
- **Fallback**: Standard Blade components if Flux unavailable
- **Styling**: Tailwind CSS v4 classes (no deprecated utilities)
- Support dark mode if existing components do: use `dark:` prefix

### Database & Models
- Use Eloquent relationships with proper type hints
- Prevent N+1 queries with eager loading
- Create factories and seeders for new models
- Use `Model::query()` instead of `DB::`
- Migrations: Include all column attributes when modifying columns

### Validation
- Always use Form Request classes (not inline validation)
- Check sibling Form Requests for array vs string rule conventions
- Include custom error messages

### Artisan Commands
- Use `php artisan make:` for all new files
- Always pass `--no-interaction` to Artisan commands
- Pass appropriate `--options` for correct behavior

### Configuration
- Never use `env()` outside config files
- Always use `config('key.name')` in application code

## File Creation Guidelines
- **Controllers**: `php artisan make:controller`
- **Models**: `php artisan make:model -mfs` (with migration, factory, seeder)
- **Tests**: `php artisan make:test --pest` (feature), add `--unit` for unit tests
- **Volt Components**: `php artisan make:volt [name] --test --pest`
- **Migrations**: `php artisan make:migration`
- **Form Requests**: `php artisan make:request`

## Testing Workflow
1. Write or update tests for your changes
2. Run affected tests: `php artisan test --filter=relevantTest`
3. Ensure tests pass before proceeding
4. Run full test suite before merge to `main`: `php artisan test`
5. Fix any failures before finalizing

## Communication Preferences
- Be concise - focus on important details, not obvious explanations
- Always run tests before claiming work is complete
- Ask before removing tests or making major architectural changes
- Confirm before changing dependencies or directory structure
- Provide code snippets with proper syntax highlighting

## Project-Specific Notes
- Server: Laravel Herd (automatically available at `https://mystrategies.test`)
- Don't run commands to start the server - Herd handles it
- Use `get-absolute-url` tool for generating project URLs
- Check `docs/` directory for additional project documentation

## What NOT to Do
- ❌ Don't create verification scripts when tests cover functionality
- ❌ Don't use deprecated Tailwind utilities (check v4 docs)
- ❌ Don't create documentation files unless explicitly requested
- ❌ Don't modify dependencies without approval
- ❌ Don't create new base folders without approval
- ❌ Don't remove or modify existing tests without approval
- ❌ Don't commit to `main` directly - always use `dev`

## Laravel 12 Specific
- No `app/Http/Middleware/` files - use `bootstrap/app.php`
- No `app/Console/Kernel.php` - commands auto-register
- Service providers: `bootstrap/providers.php`
- Model casts: prefer `casts()` method over `$casts` property

## When Stuck
- Use `search-docs` tool for version-specific Laravel ecosystem docs
- Use `tinker` tool for debugging PHP/Eloquent issues
- Use `database-query` tool for read-only database queries
- Check `list-artisan-commands` for available Artisan options
- Use `browser-logs` tool for frontend debugging

## Success Criteria
Every change should:
1. ✅ Have passing tests
2. ✅ Follow existing conventions
3. ✅ Be formatted with Pint
4. ✅ Work in the `dev` branch
5. ✅ Be ready for merge to `main` after verification
