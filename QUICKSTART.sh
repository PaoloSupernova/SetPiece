#!/bin/bash
set -e

echo "🚀 Steward Quick Start Guide"
echo "============================"
echo ""

# Check if init has been run
if [ ! -f "composer.json" ]; then
    echo "❌ Project not initialized. Running init_project.sh..."
    ./init_project.sh
    echo ""
fi

# Step 1: Environment configuration
echo "📝 Step 1: Configure environment"
if [ ! -f ".env" ]; then
    echo "Creating .env from template..."
    cp .env.example .env
    echo "⚠️  Please edit .env with your database credentials"
    echo "   Default values:"
    echo "   - DB_DATABASE=steward"
    echo "   - DB_USERNAME=steward"
    echo "   - DB_PASSWORD=steward"
    echo ""
    read -p "Press Enter after configuring .env (or continue with defaults)..."
else
    echo "✅ .env already exists"
fi
echo ""

# Step 2: Install PHP dependencies
echo "📦 Step 2: Installing PHP dependencies"
if command -v composer &> /dev/null; then
    composer install
    echo "✅ PHP dependencies installed"
else
    echo "⚠️  Composer not found. Please install Composer and run: composer install"
fi
echo ""

# Step 3: Install Node dependencies
echo "📦 Step 3: Installing Node.js dependencies"
if command -v npm &> /dev/null; then
    npm install
    echo "✅ Node.js dependencies installed"
else
    echo "⚠️  npm not found. Please install Node.js and run: npm install"
fi
echo ""

# Step 4: Database setup
echo "🗄️  Step 4: Database setup"
echo "To import the database schema, run:"
echo "  mysql -u steward -p steward < database/schema.sql"
echo ""
echo "Or create database and user first:"
echo "  mysql -u root -p"
echo "  CREATE DATABASE steward CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "  CREATE USER 'steward'@'localhost' IDENTIFIED BY 'steward';"
echo "  GRANT ALL PRIVILEGES ON steward.* TO 'steward'@'localhost';"
echo "  FLUSH PRIVILEGES;"
echo "  exit"
echo ""
read -p "Press Enter after importing database schema..."
echo ""

# Step 5: Start Vite dev server
echo "🎨 Step 5: Starting Vite dev server"
echo "Run in a separate terminal:"
echo "  npm run dev"
echo ""
echo "This will start the Vite development server on http://localhost:5173"
echo "Keep this terminal open while developing."
echo ""

# Step 6: Start PHP dev server
echo "🚀 Step 6: Starting PHP development server"
echo "Run in another terminal:"
echo "  php -S localhost:8080 -t public"
echo ""
echo "This will start the PHP server on http://localhost:8080"
echo ""

# Step 7: Optional - Email ingestion
echo "📧 Step 7 (Optional): Email ingestion"
echo "To process emails from storage/inbox/, run:"
echo "  php bin/ingest_emails.php"
echo ""
echo "Set up a cron job for automated processing:"
echo "  */5 * * * * cd /path/to/steward && php bin/ingest_emails.php >> storage/logs/ingestion.log 2>&1"
echo ""

# Login information
echo "🔐 Login Information"
echo "===================="
echo "Access the application at: http://localhost:8080"
echo ""
echo "Test accounts (password: steward2026):"
echo "  - admin@steward.local (Admin - full access)"
echo "  - slo@steward.local (SLO - no safeguarding access)"
echo "  - dso@steward.local (DSO - full access including safeguarding)"
echo "  - steward@steward.local (Steward - basic access)"
echo ""
echo "✅ Quick start guide complete!"
echo ""
echo "📚 Remember:"
echo "  - Keep Vite dev server running for CSS hot reload"
echo "  - SLO users CANNOT see 'safeguarding' category complaints (GDPR)"
echo "  - Complaints have 42-day IFO deadline (breach alerts shown)"
echo "  - Audit logs are immutable (append-only with triggers)"
echo "  - Check storage/logs/ for application logs"
