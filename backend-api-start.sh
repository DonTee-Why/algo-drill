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
echo "🔨 Building and starting backend-api container..."
cd backend-api && docker compose up -d && cd ..

echo "🔨 Building and starting piston container..."
cd piston && docker compose up -d && cd ..

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 10

# Generate application key if not set
echo "🔑 Checking application key..."
cd backend-api && docker compose exec -T app php artisan key:generate --force || true && cd ..

# Run migrations
echo "📦 Running database migrations..."
cd backend-api && docker compose exec -T app php artisan migrate --force || true && cd ..

echo ""
echo "✅ Algo Drill is ready!"
echo ""
echo "🌐 Application: http://localhost"
echo "🗄️  PostgreSQL: localhost:5432"
echo "📦 Redis: localhost:6379"
echo ""
echo "📝 View logs: cd backend-api && docker compose logs -f app"
echo "🛑 Stop services: cd backend-api && docker compose down"
echo ""

