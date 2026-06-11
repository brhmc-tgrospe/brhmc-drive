Write-Host "Starting Monorepo Deployment..." -ForegroundColor Green

# Ensure we are in the repository root
Set-Location -Path "C:\laragon\www"

# Pull latest changes from the main branch
Write-Host "Pulling latest code from GitHub..." -ForegroundColor Cyan
git pull origin main

# Deploy API
Write-Host "Deploying Laravel API..." -ForegroundColor Cyan
Set-Location -Path "C:\laragon\www\drive-api"
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache

# Deploy Frontend
Write-Host "Deploying Vue 3 Frontend..." -ForegroundColor Cyan
Set-Location -Path "C:\laragon\www\drive-frontend"
npm install
npm run build

Write-Host "Deployment Completed Successfully!" -ForegroundColor Green
