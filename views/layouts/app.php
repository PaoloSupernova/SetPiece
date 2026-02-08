<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Steward - Complaint Management Portal</title>
    
    <?php
    // Load Vite assets
    $isDev = ($_ENV['APP_ENV'] ?? 'development') === 'development';
    $viteDevRunning = false;
    
    // Check if Vite dev server is running
    if ($isDev) {
        $context = stream_context_create(['http' => ['timeout' => 1]]);
        $viteDevRunning = @file_get_contents('http://localhost:5173', false, $context) !== false;
    }
    
    if ($viteDevRunning) {
        // Development mode - load from Vite dev server
        echo '<script type="module" src="http://localhost:5173/@vite/client"></script>';
        echo '<link rel="stylesheet" href="http://localhost:5173/resources/css/app.css">';
    } else {
        // Production mode or Vite not running - load from manifest or fallback
        $manifestPath = __DIR__ . '/../../public/build/.vite/manifest.json';
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (isset($manifest['resources/css/app.css']['file'])) {
                echo '<link rel="stylesheet" href="/build/' . $manifest['resources/css/app.css']['file'] . '">';
            }
        } else {
            // Fallback to basic Tailwind CSS via CDN for demo
            echo '<script src="https://cdn.tailwindcss.com"></script>';
            echo '<script>
                tailwind.config = {
                    theme: {
                        extend: {
                            colors: {
                                "steward-primary": "#1e3a8a",
                                "steward-accent": "#3b82f6",
                                "steward-danger": "#dc2626",
                                "steward-success": "#16a34a",
                                "steward-warning": "#ea580c",
                                "steward-dark": "#1f2937",
                                "steward-muted": "#6b7280"
                            }
                        }
                    }
                }
            </script>';
        }
    }
    ?>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-steward-primary text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-6">
                    <a href="/dashboard" class="text-xl font-bold">⚽ Steward</a>
                    <div class="flex space-x-4">
                        <a href="/dashboard" class="hover:text-steward-accent">Dashboard</a>
                        <a href="/complaints" class="hover:text-steward-accent">Complaints</a>
                        <a href="/complaints/create" class="hover:text-steward-accent">New Complaint</a>
                        <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'dso'])): ?>
                            <a href="/audit" class="hover:text-steward-accent">Audit Trail</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-steward-accent">
                        <?php echo strtoupper($_SESSION['role'] ?? 'guest'); ?>
                    </span>
                    <span><?php echo htmlspecialchars($_SESSION['name'] ?? 'Guest'); ?></span>
                    <a href="/logout" class="hover:text-steward-accent">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php
    $flash = null;
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
    }
    
    if ($flash):
        $bgColor = match($flash['type']) {
            'success' => 'bg-steward-success',
            'warning' => 'bg-steward-warning',
            'error' => 'bg-steward-danger',
            default => 'bg-blue-600',
        };
    ?>
        <div class="<?php echo $bgColor; ?> text-white">
            <div class="container mx-auto px-4 py-3">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <?php echo $content; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 mt-12">
        <div class="container mx-auto px-4 py-6 text-center">
            <p>&copy; <?php echo date('Y'); ?> Steward - Complaint Management Portal for UK Football Clubs</p>
            <p class="text-sm mt-2">Built with PHP 8.4, MySQL 8.0, and Tailwind CSS v4</p>
        </div>
    </footer>
</body>
</html>
