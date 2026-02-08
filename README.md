# SetPiece — Steward

⚽ **Complaint Management Portal for UK Football Clubs**

Built with PHP 8.3+, SQLite/MySQL 8.0, and Tailwind CSS v4 (via Vite).

## 🎯 Key Features

### Domain Requirements (Fully Implemented)

1. **IFO Deadlock Rule** - Automatic 42-day deadline enforcement with breach alerts
2. **Safeguarding Silo** - GDPR-compliant role-based access control (SLO cannot view safeguarding complaints)
3. **Immutable Audit Trail** - Database triggers prevent modification of audit logs
4. **Toxicity Triage** - Football-specific abuse detection with weighted lexicon

### Core Functionality

- ✅ **Authentication System** - Secure login with bcrypt password hashing
- ✅ **Role-Based Access Control** - Admin, SLO, DSO, and Steward roles
- ✅ **Complaint Management** - Full CRUD with toxicity analysis
- ✅ **Dashboard** - Stadium heatmap, breach alerts, and deadline watchlist
- ✅ **IFO Escalation** - Automated PDF letter generation for deadlock cases
- ✅ **Audit Trail** - Complete immutable history of all state changes
- ✅ **Email Ingestion** - Background processing with OCR support (optional)

## 🚀 Quick Start

### Prerequisites

- PHP 8.3 or higher
- Composer
- Node.js & NPM
- SQLite3 (or MySQL 8.0)

### Installation

1. **Run the initialization script:**
   ```bash
   ./init_project.sh
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment:**
   ```bash
   cp .env.example .env
   # Edit .env with your settings
   ```

4. **Set up database:**
   ```bash
   # For SQLite (default):
   sqlite3 database/steward.db < database/schema_sqlite.sql
   
   # For MySQL:
   mysql -u root -p
   CREATE DATABASE steward CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'steward'@'localhost' IDENTIFIED BY 'steward';
   GRANT ALL PRIVILEGES ON steward.* TO 'steward'@'localhost';
   FLUSH PRIVILEGES;
   exit
   mysql -u steward -psteward steward < database/schema.sql
   ```

5. **Start development servers:**
   ```bash
   # Terminal 1: Vite dev server (optional - CDN fallback available)
   npm run dev
   
   # Terminal 2: PHP dev server
   php -S localhost:8080 -t public
   ```

6. **Access the application:**
   Open http://localhost:8080 in your browser

### Default Login Credentials

**Password for all accounts:** `steward2026`

- **admin@steward.local** - Full administrative access
- **slo@steward.local** - Supporter Liaison Officer (no safeguarding access)
- **dso@steward.local** - Designated Safeguarding Officer (full access)
- **steward@steward.local** - Basic steward access

## 📁 Project Structure

```
SetPiece/
├── app/
│   ├── Controllers/      # Request handlers
│   ├── Core/            # Framework components (Router, Database, Controller)
│   ├── Models/          # Data models with business logic
│   └── Services/        # Business services (Toxicity, Audit, Deadlock)
├── bin/                 # CLI scripts (email ingestion)
├── database/            # SQL schema files
├── public/              # Web root (index.php, assets)
├── resources/css/       # Tailwind CSS source
├── storage/             # Logs, uploads, inbox, attachments
├── views/               # PHP templates
│   ├── layouts/        # Layout templates
│   ├── auth/           # Authentication views
│   ├── complaints/     # Complaint views
│   ├── audit/          # Audit trail views
│   └── pdf/            # PDF templates
├── vendor/              # Composer dependencies
└── node_modules/        # NPM dependencies
```

## 🔒 Security Features

- **Strict Types** - PHP 8.3+ strict type declarations throughout
- **Prepared Statements** - SQL injection prevention
- **Password Hashing** - BCrypt with appropriate cost factor
- **Session Security** - HTTPOnly, SameSite=Lax, strict mode
- **CSRF Protection** - Built into forms
- **Input Validation** - Server-side validation on all inputs
- **Role-Based Authorization** - Enforced at SQL query level

## 📊 Key Domain Constraints

### 1. IFO Deadlock Rule
Every complaint has a 42-day deadline from creation. Complaints past this deadline show as "BREACH" with days overdue. The dashboard displays breach alerts prominently.

### 2. Safeguarding Silo (GDPR)
The safeguarding category is filtered at the **SQL query builder level** for SLO users:
```php
if ($role === 'slo') {
    $sql .= " WHERE c.category != 'safeguarding'";
}
```
This ensures SLO users cannot access safeguarding complaints through any query path.

### 3. Immutable Audit Trail
Database triggers prevent any modification or deletion of audit log entries:
```sql
CREATE TRIGGER prevent_audit_update
BEFORE UPDATE ON audit_logs
BEGIN
  SELECT RAISE(ABORT, 'audit_logs is append-only');
END;
```

### 4. Toxicity Triage
Football-specific weighted lexicon with 30+ terms analyzes all complaint content:
- Scores range from 0.0 to 1.0
- Default threshold: 0.6 (configurable via .env)
- High scores trigger UI warnings

## 🎨 Stadium Heatmap

The dashboard features a visual heatmap showing complaint distribution:
- **Green** - 0 complaints
- **Amber** - 1-5 complaints  
- **Red** - >5 complaints

Layout represents actual stadium structure:
```
┌─────────────┐
│    NORTH    │
├─────┬───┬───┤
│WEST │ ⚽│EAST│
├─────┴───┴───┤
│    SOUTH    │
└─────────────┘
```

## 📧 Email Ingestion (Optional)

Process complaints from email:
```bash
php bin/ingest_emails.php
```

Features:
- Parses .eml files from `storage/inbox/`
- Extracts sender, subject, body
- Runs toxicity analysis
- OCR processing for image attachments
- Archives processed emails

Set up cron job for automation:
```bash
*/5 * * * * cd /path/to/steward && php bin/ingest_emails.php >> storage/logs/ingestion.log 2>&1
```

## 🏗️ Tech Stack

- **Backend:** PHP 8.3+ (strict types)
- **Database:** SQLite 3 / MySQL 8.0 with InnoDB
- **Frontend:** Tailwind CSS v4 via Vite (with CDN fallback)
- **PDF Generation:** dompdf/dompdf ^3.0
- **Email Parsing:** zbateson/mail-mime-parser ^2.4
- **OCR:** thiagoalessio/tesseract_ocr ^2.13
- **Environment:** vlucas/phpdotenv ^5.6

## 📝 Development Notes

- **PHP Version:** Requires PHP 8.3+ (adjusted from 8.4 for compatibility)
- **Database:** Supports both SQLite (default) and MySQL
- **Styling:** Uses Tailwind CDN as fallback when Vite dev server unavailable
- **Error Handling:** Custom error handlers with development/production modes
- **Code Style:** Strict types enforced on all PHP files

## 📄 License

Built for UK Football Clubs as part of the SetPiece project.

---

**Built with PHP 8.3, SQLite/MySQL 8.0, and Tailwind CSS v4**