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

## Multi-Tenancy Requirements
This application implements user-level multi-tenancy where each user only has access to their own data.

### Core Implementation
- **BelongsToUser Trait**: All user-scoped models MUST use `App\Models\Traits\BelongsToUser` trait
- **user_id Column**: All tenant-scoped tables MUST have a `user_id` foreign key column
- **Automatic Assignment**: The trait automatically assigns `user_id` on model creation when user is authenticated
- **Global Scope**: The trait applies a global scope that filters ALL queries to return only current user's records
- **user() Relationship**: All tenant-scoped models MUST define a `user()` BelongsTo relationship

### Models Requiring Multi-Tenancy
Apply BelongsToUser trait to models that store user-specific data:
- Assets, Chains, Resources, Strategies, Snapshots, Transactions, etc.
- Any model that represents user-owned data

### Implementation Pattern
```php
use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YourModel extends Model
{
    use BelongsToUser;
    
    protected $fillable = [
        'user_id',
        // other fields...
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Migration Requirements
- Always include `user_id` foreign key in migrations for tenant-scoped tables
- Add proper foreign key constraints: `$table->foreignId('user_id')->constrained()->cascadeOnDelete();`
- Index the `user_id` column for query performance

### Testing Multi-Tenancy
- **Always test data isolation**: Verify users cannot access other users' data
- **Test automatic assignment**: Ensure `user_id` is set automatically on creation
- **Test global scope**: Verify queries only return current user's records
- Use the pattern from `tests/Feature/Traits/BelongsToUserTest.php` as reference

### Media Library & Storage
- Media files use user-specific paths: `storage/app/private/users/{user_id}/`
- This provides additional data isolation at the file system level
- Configure media collections with `useDisk('local')` for private storage

### Important Notes
- **Never bypass the trait**: Don't use `DB::table()` for tenant-scoped models, always use Eloquent
- **Testing caveat**: When using `DB::table()` in tests to create other users' data, the global scope won't apply
- **Authentication required**: The global scope only applies when a user is authenticated (`Auth::check()`)
- **Existing user_id**: If `user_id` is already set during creation, the trait won't override it

## Model Relationship Requirements
When creating or editing a model, you MUST verify that all necessary relationships with other models are properly defined.

### Relationship Verification Checklist
Before finalizing any model creation or modification:
1. **Identify related entities**: Determine which other models this model should be connected to based on business logic
2. **Define all relationships**: Add relationship methods for each connection (BelongsTo, HasMany, BelongsToMany, etc.)
3. **Use proper type hints**: Always include return type declarations on relationship methods
4. **Check both sides**: Ensure relationships are defined on BOTH models (e.g., if Asset belongsTo Chain, then Chain hasMany Assets)
5. **Review existing models**: Check similar models in the project to ensure consistency in relationship patterns

### Common Relationship Patterns in This Project
- **User relationships**: Most models belong to a User (via BelongsToUser trait + user() method)
- **Chain → Assets**: Chain hasMany Assets; Asset belongsTo Chain
- **Resource → Strategies**: Resource hasMany Strategies; Strategy belongsTo Resource
- **Strategy relationships**: Strategy connects to Resource, Chain, Wallet, Assets, Transactions, Snapshots, etc.
- **Pivot relationships**: Many-to-many relationships use pivot tables (e.g., asset_strategy, asset_snapshot, asset_transaction)

### Example: Complete Model with All Relationships
```php
use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};

class Asset extends Model
{
    use BelongsToUser;
    
    // Multi-tenancy relationship
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    // Parent relationship
    public function chain(): BelongsTo
    {
        return $this->belongsTo(Chain::class);
    }
    
    // Many-to-many relationships
    public function strategies(): BelongsToMany
    {
        return $this->belongsToMany(Strategy::class, 'asset_strategy');
    }
    
    public function snapshots(): BelongsToMany
    {
        return $this->belongsToMany(Snapshot::class, 'asset_snapshot');
    }
    
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'asset_transaction');
    }
}
```

### When to Review Relationships
- **Creating a new model**: Define all relationships from the start
- **Editing an existing model**: Verify no relationships are missing
- **Adding a new feature**: Check if new relationships need to be added to existing models
- **Code review**: Ensure all related models have bidirectional relationships

### Missing Relationships = Data Integrity Issues
Incomplete relationships lead to:
- Inability to query related data efficiently
- N+1 query problems
- Broken application functionality
- Poor code maintainability

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
