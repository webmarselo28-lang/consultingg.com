#!/bin/bash

# Install backend dependencies if vendor/ is missing
if [ ! -d "backend/vendor" ]; then
    echo "Installing PHP dependencies..."
    cd backend && composer install
    cd ..
fi

# Install frontend dependencies if node_modules/ is missing  
if [ ! -d "node_modules" ]; then
    echo "Installing Node.js dependencies..."
    npm install
fi

# Start PHP API on port 8080
echo "Starting PHP API server..."
php -S 0.0.0.0:8080 -t . api/index.php > backend.log 2>&1 &

# Start Vite frontend on port 5000
echo "Starting Vite development server..."
npm run dev