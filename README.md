# BuildFlow

**BuildFlow** is a modern construction project management SaaS built with Laravel 12, Inertia.js, and Vue 3. It helps construction teams track projects, manage tasks, log daily activities, and control costs—all in one place.

## Features

- **Project Management**: Create and manage multiple construction projects with detailed tracking
- **Team Collaboration**: Invite team members with role-based access (Owner, PM, Clerk, Accountant, Viewer)
- **Task Management**: Create, assign, and track tasks with priorities and due dates
- **Issue Tracking**: Log and resolve project issues with status tracking
- **Cost Management**: Track project costs with approval workflows
- **Daily Logs**: Record daily site activities and weather conditions
- **Media & Documents**: Upload photos, videos, and documents to projects
- **Reports**: Generate project reports with PDF export and sharing capabilities
- **Unit Tracking**: Track work by individual units/zones within a project
- **Multi-Organization**: Support for multiple organizations with subscription billing
- **Paystack Integration**: Built-in payment processing for Nigerian businesses

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Vue 3, Inertia.js 2, TypeScript
- **Styling**: Tailwind CSS 4
- **Database**: MySQL 8+
- **Queue**: Redis (recommended) or Database
- **PDF Generation**: DomPDF
- **Payments**: Paystack

## Requirements

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8.0+
- Redis (optional, for queue/caching)

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/your-org/buildflow.git
cd buildflow
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure:

- Database credentials (`DB_*`)
- Mail settings (`MAIL_*`)
- Paystack keys (`PAYSTACK_PUBLIC_KEY`, `PAYSTACK_SECRET_KEY`)
- Application URL (`APP_URL`)

### 4. Database setup

```bash
php artisan migrate --seed
```

### 5. Build assets

```bash
npm run build       # Production build
npm run dev         # Development with hot reload
```

### 6. Start the development server

```bash
composer dev
```

This starts:

- Laravel development server at `http://localhost:8000`
- Queue worker for background jobs
- Vite for hot module replacement
- Laravel Pail for log tailing

## Running Tests

```bash
composer test
```

Or run individual test suites:

```bash
php artisan test --filter=ProjectRbacTest
php artisan test --testsuite=Feature
```

### E2E Tests (Playwright)

```bash
npx playwright install   # First time only
npx playwright test
```

## Configuration

### Queue Configuration

For production, configure Redis for queues in `.env`:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Run the queue worker:

```bash
php artisan queue:work --tries=3 --timeout=90
```

### Cache Configuration

For production, use Redis caching:

```env
CACHE_STORE=redis
```

### Rate Limiting

The application includes rate limiters for:

- **API**: 60 requests/minute
- **Auth**: 5 attempts/minute
- **Webhooks**: 100/minute
- **Heavy operations** (PDF): 10/minute
- **Uploads**: 30/minute

## Project Structure

```
app/
├── Http/
│   ├── Controllers/     # Request handlers
│   │   ├── App/         # Main application controllers
│   │   ├── Auth/        # Authentication controllers
│   │   └── Owner/       # Admin panel controllers
│   ├── Middleware/      # Request middleware
│   ├── Requests/        # Form Request validation
│   └── ...
├── Jobs/                # Background jobs
├── Models/              # Eloquent models
├── Notifications/       # Notification classes
├── Policies/            # Authorization policies
├── Services/            # Business logic services
└── Support/             # Helper classes

resources/
├── js/
│   ├── Components/      # Vue components
│   ├── Layouts/         # Page layouts
│   └── Pages/           # Inertia pages
└── views/               # Blade templates (emails, PDFs)

database/
├── migrations/          # Database migrations
├── seeders/             # Database seeders
└── factories/           # Model factories
```

## Role-Based Access Control

### Organization Roles

- **Owner**: Full access, billing management
- **Admin**: Manage members and projects
- **Member**: Access assigned projects
- **Viewer**: Read-only access

### Project Roles

- **Owner**: Full project control
- **PM (Project Manager)**: Manage team and content
- **Clerk**: Create and edit content
- **Accountant**: Manage costs
- **Viewer**: Read-only access

## Security Features

- CSRF protection on all forms
- Paystack webhook signature verification
- Content Security Policy headers
- Rate limiting on sensitive endpoints
- Role-based authorization policies
- Secure file upload validation

## Deployment

### Production Checklist

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Run `php artisan view:cache`
5. Set up Redis for queues and cache
6. Configure a supervisor for queue workers
7. Set up SSL/HTTPS
8. Configure proper file permissions

### Queue Worker (Supervisor)

Example supervisor configuration:

```ini
[program:buildflow-worker]
command=php /path/to/buildflow/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/buildflow-worker.log
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is proprietary software. All rights reserved.

## Support

For support, email <support@buildflow.app> or open an issue in the repository.
