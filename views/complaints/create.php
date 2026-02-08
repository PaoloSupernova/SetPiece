<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-steward-dark mb-6">Create New Complaint</h1>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-6">
            <strong class="font-semibold">Please fix the following errors:</strong>
            <ul class="mt-2 list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="/complaints" class="space-y-6">
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                    Subject <span class="text-red-600">*</span>
                </label>
                <input 
                    type="text" 
                    id="subject" 
                    name="subject" 
                    required
                    maxlength="500"
                    value="<?php echo htmlspecialchars($_SESSION['old_input']['subject'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-steward-accent focus:border-transparent"
                    placeholder="Brief summary of the complaint"
                >
                <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
            </div>

            <div>
                <label for="body" class="block text-sm font-medium text-gray-700 mb-2">
                    Description <span class="text-red-600">*</span>
                </label>
                <textarea 
                    id="body" 
                    name="body" 
                    required
                    rows="8"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-steward-accent focus:border-transparent"
                    placeholder="Detailed description of the complaint..."
                ><?php echo htmlspecialchars($_SESSION['old_input']['body'] ?? ''); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        Category <span class="text-red-600">*</span>
                    </label>
                    <select 
                        id="category" 
                        name="category" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-steward-accent focus:border-transparent"
                    >
                        <?php 
                        $selectedCategory = $_SESSION['old_input']['category'] ?? 'general';
                        $role = $_SESSION['role'] ?? 'steward';
                        ?>
                        <option value="general" <?php echo $selectedCategory === 'general' ? 'selected' : ''; ?>>General</option>
                        <option value="facility" <?php echo $selectedCategory === 'facility' ? 'selected' : ''; ?>>Facility</option>
                        <option value="staff_conduct" <?php echo $selectedCategory === 'staff_conduct' ? 'selected' : ''; ?>>Staff Conduct</option>
                        <option value="supporter_conduct" <?php echo $selectedCategory === 'supporter_conduct' ? 'selected' : ''; ?>>Supporter Conduct</option>
                        <option value="ticketing" <?php echo $selectedCategory === 'ticketing' ? 'selected' : ''; ?>>Ticketing</option>
                        <option value="accessibility" <?php echo $selectedCategory === 'accessibility' ? 'selected' : ''; ?>>Accessibility</option>
                        <?php if ($role !== 'slo'): ?>
                            <option value="safeguarding" <?php echo $selectedCategory === 'safeguarding' ? 'selected' : ''; ?>>Safeguarding (DSO Only)</option>
                        <?php endif; ?>
                    </select>
                    <?php if ($role === 'slo'): ?>
                        <p class="text-xs text-yellow-600 mt-1">⚠️ SLO users cannot create safeguarding complaints</p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="stadium_block" class="block text-sm font-medium text-gray-700 mb-2">
                        Stadium Block <span class="text-red-600">*</span>
                    </label>
                    <select 
                        id="stadium_block" 
                        name="stadium_block" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-steward-accent focus:border-transparent"
                    >
                        <?php $selectedBlock = $_SESSION['old_input']['stadium_block'] ?? 'unknown'; ?>
                        <option value="unknown" <?php echo $selectedBlock === 'unknown' ? 'selected' : ''; ?>>Unknown / Not Applicable</option>
                        <option value="north" <?php echo $selectedBlock === 'north' ? 'selected' : ''; ?>>North Stand</option>
                        <option value="south" <?php echo $selectedBlock === 'south' ? 'selected' : ''; ?>>South Stand</option>
                        <option value="east" <?php echo $selectedBlock === 'east' ? 'selected' : ''; ?>>East Stand</option>
                        <option value="west" <?php echo $selectedBlock === 'west' ? 'selected' : ''; ?>>West Stand</option>
                    </select>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-900 mb-2">📋 Important Information</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• All complaints are automatically analyzed for toxic content</li>
                    <li>• You have 42 days to resolve this complaint (IFO Deadlock Rule)</li>
                    <li>• After 42 days, the complaint can be escalated to the Independent Football Ombudsman</li>
                    <li>• All actions are logged in the immutable audit trail</li>
                </ul>
            </div>

            <div class="flex items-center justify-between">
                <a href="/complaints" class="text-gray-600 hover:text-gray-800">
                    ← Back to Complaints
                </a>
                <button 
                    type="submit" 
                    class="bg-steward-primary text-white px-8 py-3 rounded-lg font-semibold hover:bg-opacity-90"
                >
                    Create Complaint
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Clear old input after displaying
unset($_SESSION['old_input']);
?>
