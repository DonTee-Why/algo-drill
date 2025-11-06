#!/bin/bash

# Quick start script for Docker Compose

set -e

echo "🐳 Starting Algo Drill with Docker Compose..."

# Check if .env exists in backend-api
if [ ! -f "backend-api/.env" ]; then
    echo "⚠️  No .env file found in backend-api directory."
    echo "Creating .env from template..."
    
    # Create a basic .env file
    cat > backend-api/.env << EOF
APP_NAME=AlgoDrill
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=algo_drill
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MEMCACHED_HOST=127.0.0.1

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
EOF
    echo "✅ Created .env file"
fi

# Build and start containers
echo "🔨 Building and starting containers..."
docker-compose up -d --build

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 10

# Generate application key if not set
echo "🔑 Checking application key..."
docker-compose exec -T app php artisan key:generate --force || true

# Run migrations
echo "📦 Running database migrations..."
docker-compose exec -T app php artisan migrate --force || true

echo ""
echo "✅ Algo Drill is ready!"
echo ""
echo "🌐 Application: http://localhost"
echo "🗄️  PostgreSQL: localhost:5432"
echo "📦 Redis: localhost:6379"
echo ""
echo "📝 View logs: docker-compose logs -f app"
echo "🛑 Stop services: docker-compose down"
echo ""

