<div class="max-w-2xl mx-auto text-center py-12">
    <div class="text-8xl mb-6">🔍</div>
    <h1 class="text-4xl font-bold text-gray-900 mb-4">Complaint Not Found</h1>
    <p class="text-lg text-gray-600 mb-8">
        The complaint you're looking for doesn't exist or you don't have permission to view it.
    </p>
    <div class="space-x-4">
        <a href="/complaints" class="inline-block bg-steward-primary text-white px-6 py-3 rounded-lg hover:bg-opacity-90">
            View All Complaints
        </a>
        <a href="/dashboard" class="inline-block bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300">
            Go to Dashboard
        </a>
    </div>
    
    <?php if (($_SESSION['role'] ?? '') === 'slo'): ?>
        <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-800">
                ℹ️ <strong>Note for SLO users:</strong> You cannot view safeguarding complaints due to GDPR restrictions. 
                Only DSO officers have access to these cases.
            </p>
        </div>
    <?php endif; ?>
</div>
