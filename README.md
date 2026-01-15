# News Aggregator API

A backend-only News Aggregator system built with **Laravel 11** and **PHP 8.3** that aggregates articles from multiple external news APIs, stores them locally, and exposes clean REST APIs for querying articles.

## Tech Stack

- **Framework**: Laravel 11.x
- **PHP**: 8.3
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis
- **Testing**: PHPUnit 11

## Features

- 🔄 **Multi-source aggregation**: Fetch articles from NewsAPI, The Guardian, and NY Times
- 🏗️ **Clean Architecture**: Strategy, Adapter, and Repository patterns
- 🚫 **Duplicate Prevention**: Unique constraint on `(source_id, external_id)` with upsert logic
- 📅 **Scheduled fetching**: Automatic article updates every 30 minutes via Laravel Scheduler
- 🔍 **Advanced filtering**: Search, filter by source, category, author, date range
- ⚡ **Queue support**: Asynchronous article fetching via Laravel Queues
- 🐳 **Dockerized**: Production-ready Docker setup with MySQL & Redis
- 🧪 **Tested**: 47+ unit and feature tests included

## Architecture

### Design Patterns

1. **Strategy Pattern**: Each news provider implements `NewsProviderInterface`, allowing easy addition of new sources without modifying existing code.

2. **Adapter/Mapper Pattern**: Each provider has a dedicated mapper that normalizes different API responses into a consistent internal structure.

3. **Repository Pattern**: `ArticleRepository` encapsulates all database operations, making it easy to swap data storage implementations.

4. **Command + Job Pattern**: Scheduled commands dispatch jobs for article fetching, supporting both synchronous and queued execution.

5. **DTO Pattern**: `ArticleDTO` provides type-safe data transfer between layers.

### How Duplicate Prevention Works

```
Article fetched from API
    ↓
Mapper generates external_id (md5 of URL or API's native ID)
    ↓
Repository calls updateOrCreate(['source_id', 'external_id'], data)
    ↓
If exists → UPDATE | If not → INSERT
    ↓
DB unique constraint as backup protection
```

### Directory Structure

```
app/
├── Console/
│   └── Commands/
│       └── FetchArticlesCommand.php    # Artisan command to trigger fetching
├── Contracts/                          # Interfaces
│   ├── ArticleMapperInterface.php
│   ├── ArticleRepositoryInterface.php
│   └── NewsProviderInterface.php
├── DTOs/
│   └── ArticleDTO.php                  # Data Transfer Object
├── Http/
│   ├── Controllers/Api/
│   │   ├── ArticleController.php
│   │   └── SourceController.php
│   ├── Requests/
│   │   └── ArticleIndexRequest.php
│   └── Resources/                      # API Resources
│       ├── ArticleCollection.php
│       ├── ArticleResource.php
│       ├── SourceCollection.php
│       └── SourceResource.php
├── Jobs/
│   └── FetchArticlesJob.php            # Queue job for fetching
├── Models/
│   ├── Article.php
│   └── Source.php
├── Repositories/
│   └── ArticleRepository.php
└── Services/
    ├── NewsAggregatorService.php       # Orchestrates all providers
    ├── Mappers/                        # Adapter pattern - normalize API responses
    │   ├── BaseArticleMapper.php
    │   ├── GuardianMapper.php
    │   ├── NewsApiMapper.php
    │   └── NyTimesMapper.php
    └── NewsProviders/                  # Strategy pattern - provider implementations
        ├── BaseNewsProvider.php
        ├── GuardianProvider.php
        ├── NewsApiProvider.php
        └── NyTimesProvider.php
```

## Requirements

- PHP 8.2+ (or Docker)
- Composer
- MySQL 8.0
- Redis (for queues/cache)
- News API keys (see below)

## Installation

### Option 1: Docker (Recommended)

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd news-aggregator
   ```

2. **Copy environment file**
   ```bash
   cp .env.docker .env
   ```

3. **Generate application key**
   ```bash
   # Generate a key and add it to .env
   php -r "echo 'APP_KEY=base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
   ```

4. **Add your API keys** to `.env`
   ```env
   NEWSAPI_KEY=your_newsapi_key
   GUARDIAN_API_KEY=your_guardian_key
   NYTIMES_API_KEY=your_nytimes_key
   ```

5. **Build and start containers**
   ```bash
   docker-compose up -d --build
   ```

6. **Run migrations and seed**
   ```bash
   docker-compose exec app php artisan migrate --seed
   ```

7. **Access the API**
   ```
   http://localhost:8000/api/v1/articles
   ```

#### Docker Commands

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# View logs
docker-compose logs -f app

# Run artisan commands
docker-compose exec app php artisan <command>

# Fetch articles manually
docker-compose exec app php artisan news:fetch --sync

# Run tests
docker-compose exec app php artisan test

# Access MySQL
docker-compose exec mysql mysql -u news_user -psecret news_aggregator
```

#### Development with Docker

For development with hot-reload:

```bash
docker-compose -f docker-compose.dev.yml up -d --build
```

### Option 2: Local Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd news-aggregator
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Configure database** in `.env`
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=news_aggregator
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Add your API keys** in `.env`
   ```env
   # Get keys from:
   # - NewsAPI: https://newsapi.org/register
   # - The Guardian: https://open-platform.theguardian.com/access/
   # - NY Times: https://developer.nytimes.com/get-started
   
   NEWSAPI_KEY=your_newsapi_key
   GUARDIAN_API_KEY=your_guardian_key
   NYTIMES_API_KEY=your_nytimes_key
   ```

7. **Run migrations and seed**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

## Fetching Articles

### Manual Fetch (Command Line)

```bash
# Fetch from all providers (queued)
php artisan news:fetch

# Fetch from all providers (synchronous)
php artisan news:fetch --sync

# Fetch from specific provider
php artisan news:fetch --provider=newsapi --sync
php artisan news:fetch --provider=guardian --sync
php artisan news:fetch --provider=nytimes --sync
```

### Scheduled Fetching

The system is configured to fetch articles every 30 minutes. To enable scheduling:

```bash
# Add this cron entry to your server
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Queue Worker (for async fetching)

```bash
php artisan queue:work
```

## API Endpoints

### Articles

#### List Articles
```
GET /api/v1/articles
```

**Query Parameters:**

| Parameter | Type   | Description                          |
|-----------|--------|--------------------------------------|
| q         | string | Keyword search (title, description)  |
| source    | string | Filter by source slug                |
| category  | string | Filter by category                   |
| author    | string | Filter by author name                |
| from      | date   | Start date (YYYY-MM-DD)              |
| to        | date   | End date (YYYY-MM-DD)                |
| per_page  | int    | Items per page (1-100, default: 15)  |
| page      | int    | Page number                          |

**Example Request:**
```bash
curl "http://localhost:8000/api/v1/articles?q=technology&category=Business&from=2026-01-01&per_page=10"
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Tech Industry Update",
      "description": "Latest news from the tech world...",
      "content": "Full article content...",
      "author": "John Doe",
      "url": "https://example.com/article",
      "image_url": "https://example.com/image.jpg",
      "category": "Technology",
      "published_at": "2026-01-12T10:00:00+00:00",
      "source": {
        "id": 1,
        "name": "NewsAPI",
        "slug": "newsapi",
        "is_active": true
      },
      "created_at": "2026-01-12T10:30:00+00:00",
      "updated_at": "2026-01-12T10:30:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 75
  },
  "links": {
    "first": "http://localhost:8000/api/v1/articles?page=1",
    "last": "http://localhost:8000/api/v1/articles?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/v1/articles?page=2"
  }
}
```

#### Get Single Article
```
GET /api/v1/articles/{id}
```

### Sources

#### List Sources
```
GET /api/v1/sources
```

#### Get Single Source
```
GET /api/v1/sources/{id}
```

## Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test files
php artisan test tests/Unit/Services/Mappers/
php artisan test tests/Feature/Api/ArticleApiTest.php
```

## Adding a New News Provider

1. **Create the Mapper** in `app/Services/Mappers/`:
   ```php
   class NewProviderMapper extends BaseArticleMapper
   {
       protected function extractArticles(array $rawData): array
       {
           return $rawData['articles'] ?? [];
       }

       public function mapSingle(array $rawArticle): ?array
       {
           // Map fields to internal structure
       }
   }
   ```

2. **Create the Provider** in `app/Services/NewsProviders/`:
   ```php
   class NewProvider extends BaseNewsProvider
   {
       public function getProviderName(): string
       {
           return 'newprovider';
       }

       protected function makeRequest(array $filters = []): Response
       {
           // Make API request
       }

       protected function buildQueryParams(array $filters = []): array
       {
           // Build query parameters
       }
   }
   ```

3. **Register the Provider** in `AppServiceProvider`:
   ```php
   $this->app->singleton(NewsAggregatorService::class, function ($app) {
       return new NewsAggregatorService(
           $app->make(ArticleRepositoryInterface::class),
           // ... existing providers
           $app->make(NewProvider::class)
       );
   });
   ```

4. **Add API Key** configuration in `config/services.php` and `.env`

5. **Add Source Record** in database seeder or manually

## Error Handling

- External API failures are logged and don't affect other providers
- Jobs are automatically retried (3 attempts with 60-second backoff)
- Invalid articles are skipped without stopping the import process
- All errors are logged for debugging

## Security Notes

- Never commit `.env` file with API keys
- Use `.env.example` as a template
- API keys should be stored securely in production

## License

This project is open-sourced software licensed under the MIT license.

## API Quick Reference

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/articles` | GET | List/search articles with filters |
| `/api/v1/articles/{id}` | GET | Get single article |
| `/api/v1/sources` | GET | List all active sources |
| `/api/v1/sources/{id}` | GET | Get single source |

### Filter Parameters for `/api/v1/articles`

```bash
# Search by keyword
GET /api/v1/articles?q=technology

# Filter by source
GET /api/v1/articles?source=newsapi

# Filter by category
GET /api/v1/articles?category=business

# Filter by author
GET /api/v1/articles?author=john

# Filter by date range
GET /api/v1/articles?from=2026-01-01&to=2026-01-15

# Pagination
GET /api/v1/articles?per_page=20&page=2

# Combined filters
GET /api/v1/articles?q=tech&source=guardian&category=technology&from=2026-01-01&per_page=10
```
