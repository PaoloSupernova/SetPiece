#!/bin/bash
set -e

echo "🏟️  Initializing Steward Complaint Management Portal..."

# Create directory structure
echo "📁 Creating directory structure..."
mkdir -p app/Core
mkdir -p app/Controllers
mkdir -p app/Models
mkdir -p app/Services
mkdir -p bin
mkdir -p public/assets
mkdir -p views/layouts
mkdir -p views/complaints
mkdir -p views/pdf
mkdir -p views/partials
mkdir -p views/auth
mkdir -p views/audit
mkdir -p storage/logs
mkdir -p storage/inbox/processed
mkdir -p storage/inbox/failed
mkdir -p storage/attachments
mkdir -p storage/ocr
mkdir -p database
mkdir -p resources/css

echo "✅ Directory structure created"

# Create .env template
echo "📝 Creating .env template..."
cat > .env.example << 'EOF'
# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=steward
DB_USERNAME=steward
DB_PASSWORD=steward

# Application
APP_ENV=development
APP_URL=http://localhost:8080

# Mail Configuration (IMAP for ingestion)
MAIL_HOST=imap.example.com
MAIL_PORT=993
MAIL_USERNAME=complaints@example.com
MAIL_PASSWORD=
MAIL_ENCRYPTION=ssl

# Tesseract OCR
TESSERACT_PATH=/usr/bin/tesseract

# Toxicity Analysis
TOXICITY_THRESHOLD=0.6

# Session
SESSION_LIFETIME=7200
SESSION_SECURE=false
SESSION_DOMAIN=localhost

# IFO Deadlock Rule
DEADLOCK_DAYS=42
DEADLOCK_WARNING_DAYS=7
EOF

echo "✅ .env.example created"

# Create .gitignore
echo "📝 Creating .gitignore..."
cat > .gitignore << 'EOF'
# Dependencies
/vendor/
/node_modules/

# Build artifacts
/public/build/
.vite/

# Environment
.env

# Storage
/storage/logs/*.log
/storage/inbox/*.eml
/storage/inbox/processed/*
/storage/inbox/failed/*
/storage/attachments/*
/storage/ocr/*

# IDE
.idea/
.vscode/
*.swp
*.swo
*~

# OS
.DS_Store
Thumbs.db

# Composer
composer.phar
composer.lock

# NPM
package-lock.json
yarn.lock
EOF

echo "✅ .gitignore created"

# Create composer.json
echo "📝 Creating composer.json..."
cat > composer.json << 'EOF'
{
  "name": "paolsupernova/steward",
  "description": "Complaint Management Portal for UK Football Clubs",
  "type": "project",
  "require": {
    "php": "^8.4",
    "vlucas/phpdotenv": "^5.6",
    "php-mime-mail-parser/php-mime-mail-parser": "^8.0",
    "thiagoalessio/tesseract_ocr": "^2.14",
    "dompdf/dompdf": "^3.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "app/"
    }
  },
  "config": {
    "optimize-autoloader": true,
    "sort-packages": true
  },
  "minimum-stability": "stable",
  "prefer-stable": true
}
EOF

echo "✅ composer.json created"

# Create package.json
echo "📝 Creating package.json..."
cat > package.json << 'EOF'
{
  "name": "steward",
  "version": "1.0.0",
  "description": "Complaint Management Portal for UK Football Clubs",
  "private": true,
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  },
  "devDependencies": {
    "vite": "^6.0.0",
    "tailwindcss": "^4.0.0",
    "@tailwindcss/vite": "^4.0.0"
  }
}
EOF

echo "✅ package.json created"

# Create vite.config.js
echo "📝 Creating vite.config.js..."
cat > vite.config.js << 'EOF'
import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [tailwindcss()],
  build: {
    outDir: 'public/build',
    manifest: true,
    rollupOptions: {
      input: 'resources/css/app.css',
    },
  },
  server: {
    port: 5173,
    strictPort: true,
    hmr: {
      host: 'localhost',
    },
  },
});
EOF

echo "✅ vite.config.js created"

# Create resources/css/app.css
echo "📝 Creating resources/css/app.css..."
cat > resources/css/app.css << 'EOF'
@import "tailwindcss";

@theme {
  --color-steward-primary: #1e3a8a;
  --color-steward-accent: #3b82f6;
  --color-steward-danger: #dc2626;
  --color-steward-success: #16a34a;
  --color-steward-warning: #ea580c;
  --color-steward-dark: #1f2937;
  --color-steward-muted: #6b7280;

  --font-family-sans: ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

/* Custom utilities */
.breach-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
EOF

echo "✅ resources/css/app.css created"

echo ""
echo "🎉 Project initialization complete!"
echo ""
echo "Next steps:"
echo "1. Copy .env.example to .env and configure your settings"
echo "2. Run 'composer install' to install PHP dependencies"
echo "3. Run 'npm install' to install Node.js dependencies"
echo "4. Import database/schema.sql into MySQL"
echo "5. Run 'npm run dev' to start Vite dev server"
echo "6. Run 'php -S localhost:8080 -t public' to start PHP dev server"
echo ""
echo "See QUICKSTART.sh for automated setup instructions."
