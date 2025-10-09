# Filament Resources

This directory contains all Filament resource definitions for the application. Each resource represents a model that can be managed through the Filament admin panel.

## Directory Structure

```
Resources/
├── Assets/
│   ├── AssetResource.php         # Main resource class
│   ├── Pages/                     # Resource pages (List, Create, Edit, View)
│   ├── Schemas/                   # Form and Infolist definitions
│   └── Tables/                    # Table configuration
├── Chains/
│   ├── ChainResource.php
│   ├── Pages/
│   ├── Schemas/
│   └── Tables/
├── Resources/
│   ├── ResourceResource.php
│   ├── Pages/
│   ├── Schemas/
│   └── Tables/
└── Wallets/
    ├── WalletResource.php
    ├── Pages/
    ├── Schemas/
    └── Tables/
```

## Architecture Patterns

### Resource File Structure

Each resource follows a consistent pattern:

1. **Main Resource Class** (`*Resource.php`)
   - Defines the model
   - Sets navigation icon
   - Configures record title attribute
   - References form, table, and page classes

2. **Pages Directory**
   - `List*.php` - List/index page
   - `Create*.php` - Create page
   - `Edit*.php` - Edit page
   - `View*.php` - View page (optional)

3. **Schemas Directory**
   - `*Form.php` - Form field definitions
   - `*Infolist.php` - Read-only display schema (optional)

4. **Tables Directory**
   - `*Table.php` - Table column and action definitions

### Code Conventions

#### 1. Resource Classes

All resource classes must:
- Extend `Filament\Resources\Resource`
- Define the `$model` property
- Set appropriate navigation icons using `Heroicon` class
- Include PHPDoc blocks for all public methods

Example:
```php
class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    /**
     * Configure the wallet form schema.
     *
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return WalletForm::configure($schema);
    }
}
```

#### 2. Form Schemas

Form schemas must:
- Be static classes with a `configure()` method
- Accept and return `Schema` type
- Include PHPDoc documentation
- Use appropriate Filament form components

Example:
```php
class WalletForm
{
    /**
     * Configure the wallet form schema.
     *
     * @param Schema $schema
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
        ]);
    }
}
```

#### 3. Table Configurations

Table configurations must:
- Be static classes with a `configure()` method
- Accept and return `Table` type
- Include PHPDoc documentation
- Define columns, filters, and actions

Example:
```php
class WalletsTable
{
    /**
     * Configure the wallets table.
     *
     * @param Table $table
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([...])
            ->filters([...])
            ->recordActions([...]);
    }
}
```

#### 4. Documentation Standards

All public methods must include PHPDoc blocks with:
- A brief description
- `@param` tags for parameters
- `@return` tag for return type

All comments must be in English.

### Media Collections

Resources that use Spatie Media Library should:
- Use `SpatieMediaLibraryFileUpload` component in forms
- Use `SpatieMediaLibraryImageColumn` component in tables
- Define collection names consistently (e.g., `wallet-icons`, `asset-icons`)
- Configure media collections in the model's `registerMediaCollections()` method

Example:
```php
// In form
SpatieMediaLibraryFileUpload::make('wallet-icon')
    ->collection('wallet-icons')

// In table
SpatieMediaLibraryImageColumn::make('wallet-icon')
    ->collection('wallet-icons')
    ->imageSize(30)
```

### Multi-Tenancy

All resources in this application are scoped to the authenticated user via the `BelongsToUser` trait on models. The Filament resources automatically respect this relationship through Laravel's query scoping.

### Testing

Each resource should have corresponding tests in `tests/Feature/Filament/` directory covering:
- List page access and content
- Create functionality with validation
- Edit functionality
- Delete functionality
- Multi-tenancy enforcement

## Available Resources

### Assets
Manages cryptocurrency and token assets with type classification, chain association, and CoinGecko integration.

**Key Features:**
- Asset type enum (coin, token, etc.)
- Chain relationship
- Contract address tracking
- CoinGecko integration
- Icon upload support
- Updatable flag for price tracking

### Chains
Manages blockchain networks.

**Key Features:**
- Simple name-based identification
- Icon upload support
- Relationship with assets

### Resources
Manages strategy resources and educational materials.

**Key Features:**
- Name-based organization
- Icon upload support
- Relationship with strategies

### Wallets
Manages cryptocurrency wallets.

**Key Features:**
- Name-based identification
- Icon upload support
- Relationship with strategies

## Creating New Resources

To create a new Filament resource:

1. Use Artisan command:
   ```bash
   php artisan make:filament-resource ModelName --generate
   ```

2. Organize the generated files:
   - Move form logic to `Schemas/*Form.php`
   - Move table logic to `Tables/*Table.php`
   - Update page classes as needed

3. Add PHPDoc blocks to all public methods

4. Ensure the model uses the `BelongsToUser` trait

5. Configure media collections if needed

6. Create corresponding tests

7. Run Laravel Pint to format code:
   ```bash
   vendor/bin/pint --dirty
   ```

## Best Practices

1. **Separation of Concerns**: Keep form, table, and page logic separated into their respective classes

2. **Consistency**: Follow existing naming conventions and file structures

3. **Documentation**: Always include PHPDoc blocks for public methods

4. **Validation**: Define validation rules in the form schemas

5. **User Experience**: 
   - Use appropriate field types
   - Add helpful labels and placeholders
   - Configure reasonable column toggles
   - Set sensible default sorting

6. **Performance**:
   - Use eager loading for relationships in tables
   - Limit media collection file sizes
   - Index frequently queried columns

7. **Code Quality**:
   - Run Pint before committing
   - Write tests for new functionality
   - Keep methods focused and concise

## Related Documentation

- [Filament Documentation](https://filamentphp.com/docs)
- [Laravel Documentation](https://laravel.com/docs)
- [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary)
