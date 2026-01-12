# News Aggregator - Copilot Instructions

## Project Overview
A Laravel backend-only News Aggregator system that aggregates articles from multiple external news APIs, stores them locally, and exposes clean REST APIs for querying articles.

## Architecture
- **Strategy Pattern**: Multiple news providers implementing a common interface
- **Adapter/Mapper Pattern**: Normalize different API responses into internal article structure
- **Repository Pattern**: Encapsulate database persistence logic
- **Command + Job Pattern**: Scheduled and asynchronous article fetching

## Key Directories
- `app/Contracts/` - Interfaces and contracts
- `app/Services/` - Business logic services
- `app/Services/NewsProviders/` - News API provider implementations
- `app/Services/Mappers/` - Response mappers/adapters
- `app/Repositories/` - Repository classes
- `app/DTOs/` - Data Transfer Objects
- `app/Jobs/` - Queue jobs
- `app/Console/Commands/` - Artisan commands
- `app/Http/Controllers/Api/` - API controllers
- `app/Http/Resources/` - API resources

## Coding Standards
- Follow PSR-12 coding standards
- Use strict types in all PHP files
- Controllers must be thin (no business logic)
- Use Dependency Injection everywhere
- Follow SOLID, DRY, and KISS principles

## News Providers
- NewsAPI.org
- The Guardian API
- New York Times API

## Database
- `sources` - News source configurations
- `articles` - Aggregated articles with unique constraint on (source_id, external_id)
