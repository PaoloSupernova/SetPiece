<div class="space-y-6">
    <h1 class="text-3xl font-bold text-steward-dark">Dashboard</h1>

    <!-- Breach Alert Panel -->
    <?php if (!empty($breached)): ?>
        <div class="bg-red-50 border-l-4 border-red-600 p-6 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <span class="text-3xl">🚨</span>
                </div>
                <div class="ml-4 flex-1">
                    <h2 class="text-xl font-bold text-red-900 mb-2">IFO Deadline Breached</h2>
                    <p class="text-red-800 mb-4">
                        <?php echo count($breached); ?> complaint(s) have exceeded the 42-day IFO deadline.
                    </p>
                    <div class="space-y-2">
                        <?php foreach ($breached as $complaint): ?>
                            <div class="bg-white p-3 rounded border border-red-200">
                                <a href="/complaints/<?php echo $complaint['id']; ?>" class="font-semibold text-red-900 hover:text-red-700">
                                    STW-<?php echo str_pad($complaint['id'], 6, '0', STR_PAD_LEFT); ?> - <?php echo htmlspecialchars($complaint['subject']); ?>
                                </a>
                                <span class="ml-2 text-sm text-red-600">
                                    (<?php echo (int)$complaint['days_overdue']; ?> days overdue)
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Deadlock Watchlist Panel -->
    <?php if (!empty($watchlist)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-600 p-6 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <span class="text-3xl">⏰</span>
                </div>
                <div class="ml-4 flex-1">
                    <h2 class="text-xl font-bold text-yellow-900 mb-2">Deadlock Watchlist</h2>
                    <p class="text-yellow-800 mb-4">
                        <?php echo count($watchlist); ?> complaint(s) expiring within <?php echo $warningDays; ?> days.
                    </p>
                    <div class="space-y-2">
                        <?php foreach ($watchlist as $complaint): ?>
                            <div class="bg-white p-3 rounded border border-yellow-200">
                                <a href="/complaints/<?php echo $complaint['id']; ?>" class="font-semibold text-yellow-900 hover:text-yellow-700">
                                    STW-<?php echo str_pad($complaint['id'], 6, '0', STR_PAD_LEFT); ?> - <?php echo htmlspecialchars($complaint['subject']); ?>
                                </a>
                                <span class="ml-2 text-sm text-yellow-600">
                                    (<?php echo (int)$complaint['days_remaining']; ?> days remaining)
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Stadium Heatmap -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold text-steward-dark mb-4">Stadium Heatmap - Open Complaints by Block</h2>
        
        <div class="grid grid-cols-3 gap-2 max-w-2xl mx-auto mb-6">
            <!-- Row 1: NORTH (full width) -->
            <div class="col-span-3 p-8 rounded-lg text-white font-bold text-center text-lg <?php
                $count = $blockCounts['north'];
                echo $count > 5 ? 'bg-red-600' : ($count >= 1 ? 'bg-yellow-500' : 'bg-green-500');
            ?>">
                NORTH STAND<br>
                <span class="text-3xl"><?php echo $count; ?></span>
            </div>

            <!-- Row 2: WEST | PITCH | EAST -->
            <div class="p-8 rounded-lg text-white font-bold text-center text-lg <?php
                $count = $blockCounts['west'];
                echo $count > 5 ? 'bg-red-600' : ($count >= 1 ? 'bg-yellow-500' : 'bg-green-500');
            ?>">
                WEST<br>
                <span class="text-3xl"><?php echo $count; ?></span>
            </div>
            
            <div class="p-8 rounded-lg bg-green-800 text-white font-bold text-center flex items-center justify-center">
                <span class="text-5xl">⚽</span>
            </div>
            
            <div class="p-8 rounded-lg text-white font-bold text-center text-lg <?php
                $count = $blockCounts['east'];
                echo $count > 5 ? 'bg-red-600' : ($count >= 1 ? 'bg-yellow-500' : 'bg-green-500');
            ?>">
                EAST<br>
                <span class="text-3xl"><?php echo $count; ?></span>
            </div>

            <!-- Row 3: SOUTH (full width) -->
            <div class="col-span-3 p-8 rounded-lg text-white font-bold text-center text-lg <?php
                $count = $blockCounts['south'];
                echo $count > 5 ? 'bg-red-600' : ($count >= 1 ? 'bg-yellow-500' : 'bg-green-500');
            ?>">
                SOUTH STAND<br>
                <span class="text-3xl"><?php echo $count; ?></span>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex justify-center space-x-6 text-sm">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                <span>0 complaints</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-yellow-500 rounded mr-2"></div>
                <span>1-5 complaints</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-red-600 rounded mr-2"></div>
                <span>&gt;5 complaints</span>
            </div>
        </div>

        <?php if ($blockCounts['unknown'] > 0): ?>
            <div class="mt-4 text-center text-sm text-gray-600">
                Note: <?php echo $blockCounts['unknown']; ?> complaint(s) with unknown location not shown on heatmap
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-3xl mb-2">📋</div>
            <div class="text-2xl font-bold text-steward-dark">
                <?php echo array_sum($blockCounts); ?>
            </div>
            <div class="text-gray-600">Open Complaints</div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-3xl mb-2">⚠️</div>
            <div class="text-2xl font-bold text-yellow-600">
                <?php echo count($watchlist); ?>
            </div>
            <div class="text-gray-600">Approaching Deadline</div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-3xl mb-2">🚨</div>
            <div class="text-2xl font-bold text-red-600">
                <?php echo count($breached); ?>
            </div>
            <div class="text-gray-600">Deadline Breached</div>
        </div>
    </div>
</div>
