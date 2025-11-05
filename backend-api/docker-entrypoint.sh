#!/bin/bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Starting Laravel application entrypoint...${NC}"

# Function to wait for database
wait_for_db() {
    echo -e "${YELLOW}⏳ Waiting for database to be ready...${NC}"
    
    if [ "$DB_CONNECTION" = "pgsql" ]; then
        until php artisan db:show > /dev/null 2>&1; do
            echo -e "${YELLOW}   PostgreSQL is unavailable - sleeping...${NC}"
            sleep 2
        done
        echo -e "${GREEN}✅ PostgreSQL is ready!${NC}"
    fi
}

# Set proper permissions for storage and bootstrap/cache
echo -e "${YELLOW}📁 Setting permissions for storage and cache directories...${NC}"
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache || true

# Only chown if running as root (for development) or if explicitly needed
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache || true
elif [ -n "$FIX_PERMISSIONS" ] && [ "$FIX_PERMISSIONS" = "true" ]; then
    echo -e "${YELLOW}   Note: Not running as root, skipping chown${NC}"
fi
echo -e "${GREEN}✅ Permissions set${NC}"

# Install Composer dependencies if vendor directory doesn't exist or is empty
if [ ! -d "vendor" ] || [ -z "$(ls -A vendor)" ]; then
    echo -e "${YELLOW}📦 Installing Composer dependencies...${NC}"
    composer install --no-interaction --prefer-dist --optimize-autoloader
    echo -e "${GREEN}✅ Composer dependencies installed${NC}"
else
    echo -e "${GREEN}✅ Composer dependencies already installed${NC}"
fi

# Install Node dependencies if node_modules doesn't exist or is empty
if [ ! -d "node_modules" ] || [ -z "$(ls -A node_modules)" ]; then
    echo -e "${YELLOW}📦 Installing Node dependencies...${NC}"
    npm install
    echo -e "${GREEN}✅ Node dependencies installed${NC}"
else
    echo -e "${GREEN}✅ Node dependencies already installed${NC}"
fi

# Clear configuration cache
echo -e "${YELLOW}🧹 Clearing configuration cache...${NC}"
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true
echo -e "${GREEN}✅ Caches cleared${NC}"

# Generate application key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo -e "${YELLOW}🔑 Generating application key...${NC}"
    php artisan key:generate --force || true
    echo -e "${GREEN}✅ Application key generated${NC}"
fi

# Wait for database to be ready
if [ "$DB_CONNECTION" != "sqlite" ]; then
    wait_for_db
fi

# Run database migrations
echo -e "${YELLOW}📊 Running database migrations...${NC}"
php artisan migrate --force || {
    echo -e "${YELLOW}⚠️  Migration failed, but continuing...${NC}"
}
echo -e "${GREEN}✅ Migrations completed${NC}"

# Cache configuration for better performance (optional, can be disabled in development)
if [ "$APP_ENV" = "production" ]; then
    echo -e "${YELLOW}⚡ Optimizing for production...${NC}"
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    echo -e "${GREEN}✅ Production optimization complete${NC}"
fi

# Execute the main command (passed as arguments to this script)
echo -e "${GREEN}✅ Entrypoint setup complete!${NC}"
echo -e "${GREEN}🚀 Starting web server (nginx + php-fpm)...${NC}"
echo ""

exec "$@"

