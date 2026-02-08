<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Steward</title>
    
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
        echo '<script type="module" src="http://localhost:5173/@vite/client"></script>';
        echo '<link rel="stylesheet" href="http://localhost:5173/resources/css/app.css">';
    } else {
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
<body class="bg-gradient-to-br from-steward-primary to-steward-accent min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="text-6xl mb-4">⚽</div>
            <h1 class="text-3xl font-bold text-steward-dark">Steward</h1>
            <p class="text-gray-600 mt-2">Complaint Management Portal</p>
        </div>

        <?php
        $flash = null;
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        
        if ($flash):
            $bgColor = match($flash['type']) {
                'success' => 'bg-green-50 text-green-800 border-green-200',
                'error' => 'bg-red-50 text-red-800 border-red-200',
                default => 'bg-blue-50 text-blue-800 border-blue-200',
            };
        ?>
            <div class="<?php echo $bgColor; ?> border px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Address
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-steward-accent focus:border-transparent"
                    placeholder="you@example.com"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-steward-accent focus:border-transparent"
                    placeholder="••••••••"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-steward-primary text-white py-3 rounded-lg font-semibold hover:bg-opacity-90 transition"
            >
                Sign In
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-600 text-center">
                Test accounts (password: <code class="bg-gray-100 px-2 py-1 rounded">steward2026</code>):
            </p>
            <ul class="text-xs text-gray-500 mt-3 space-y-1">
                <li>• admin@steward.local (Admin)</li>
                <li>• slo@steward.local (SLO - no safeguarding)</li>
                <li>• dso@steward.local (DSO - full access)</li>
                <li>• steward@steward.local (Steward)</li>
            </ul>
        </div>
    </div>
</body>
</html>
