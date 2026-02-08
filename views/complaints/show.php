<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                    <h1 class="text-3xl font-bold text-steward-dark">
                        STW-<?php echo str_pad($complaint['id'], 6, '0', STR_PAD_LEFT); ?>
                    </h1>
                    <?php
                    $statusColors = [
                        'new' => 'bg-blue-100 text-blue-800',
                        'investigating' => 'bg-yellow-100 text-yellow-800',
                        'resolved' => 'bg-green-100 text-green-800',
                        'deadlock' => 'bg-red-100 text-red-800',
                    ];
                    $statusColor = $statusColors[$complaint['status']] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full <?php echo $statusColor; ?>">
                        <?php echo strtoupper($complaint['status']); ?>
                    </span>
                    <?php if ($isBreached): ?>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-600 text-white breach-pulse">
                            🚨 BREACH - <?php echo $daysOverdue; ?> DAYS OVERDUE
                        </span>
                    <?php endif; ?>
                </div>
                <h2 class="text-xl text-gray-700 mb-4"><?php echo htmlspecialchars($complaint['subject']); ?></h2>
                <div class="text-sm text-gray-500">
                    Created by <?php echo htmlspecialchars($complaint['user_name']); ?> on <?php echo date('d F Y \a\t H:i', strtotime($complaint['created_at'])); ?>
                </div>
            </div>
            <div>
                <a href="/complaints" class="text-steward-primary hover:text-steward-accent">
                    ← Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-steward-dark mb-4">Description</h3>
                <div class="prose max-w-none text-gray-700 whitespace-pre-wrap">
                    <?php echo htmlspecialchars($complaint['body']); ?>
                </div>
            </div>

            <!-- Messages -->
            <?php if (!empty($messages)): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-steward-dark mb-4">Messages & Updates</h3>
                    <div class="space-y-4">
                        <?php foreach ($messages as $message): ?>
                            <div class="border-l-4 <?php 
                                echo match($message['sender_type']) {
                                    'staff' => 'border-blue-500 bg-blue-50',
                                    'system' => 'border-gray-500 bg-gray-50',
                                    default => 'border-green-500 bg-green-50',
                                };
                            ?> p-4 rounded">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-sm">
                                        <?php echo ucfirst($message['sender_type']); ?>
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <?php echo date('d M Y H:i', strtotime($message['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="text-gray-700 whitespace-pre-wrap">
                                    <?php echo htmlspecialchars($message['body']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Audit Trail -->
            <?php if (!empty($auditLogs)): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-steward-dark mb-4">Audit Trail</h3>
                    <div class="space-y-2">
                        <?php foreach ($auditLogs as $log): ?>
                            <div class="flex items-start text-sm border-l-2 border-gray-300 pl-4 py-2">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($log['action']); ?></div>
                                    <?php if ($log['user_name']): ?>
                                        <div class="text-gray-600">by <?php echo htmlspecialchars($log['user_name']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($log['previous_state'] || $log['new_state']): ?>
                                        <div class="text-gray-500 text-xs">
                                            <?php echo htmlspecialchars($log['previous_state'] ?? 'none'); ?> 
                                            → 
                                            <?php echo htmlspecialchars($log['new_state'] ?? 'none'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-xs text-gray-400">
                                    <?php echo date('d M H:i', strtotime($log['timestamp'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Metadata -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-steward-dark mb-4">Details</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($complaint['category']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Stadium Block</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo strtoupper($complaint['stadium_block']); ?></dd>
                    </div>
                    <?php if ($complaint['toxicity_score'] !== null): 
                        $score = (float)$complaint['toxicity_score'];
                        $percentage = round($score * 100);
                        $barColor = $score >= 0.6 ? 'bg-red-600' : ($score >= 0.3 ? 'bg-yellow-500' : 'bg-green-500');
                        $textColor = $score >= 0.6 ? 'text-red-600' : ($score >= 0.3 ? 'text-yellow-600' : 'text-green-600');
                    ?>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Toxicity Score</dt>
                            <dd class="mt-2">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-semibold <?php echo $textColor; ?>"><?php echo $percentage; ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="<?php echo $barColor; ?> h-3 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </dd>
                        </div>
                    <?php endif; ?>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">IFO Deadline</dt>
                        <dd class="mt-1 text-sm font-semibold <?php echo $isBreached ? 'text-red-600' : 'text-gray-900'; ?>">
                            <?php echo date('d F Y', strtotime($complaint['deadlock_deadline'])); ?>
                            <?php if ($isBreached): ?>
                                <br><span class="text-xs">(Breached)</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo date('d M Y H:i', strtotime($complaint['updated_at'])); ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Actions -->
            <?php if (!in_array($complaint['status'], ['resolved', 'deadlock'])): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-steward-dark mb-4">Actions</h3>
                    <form method="POST" action="/complaints/<?php echo $complaint['id']; ?>/escalate" onsubmit="return confirm('Are you sure you want to escalate this to IFO deadlock? A formal letter will be generated.');">
                        <button 
                            type="submit" 
                            class="w-full bg-red-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-red-700"
                        >
                            🚨 Escalate to IFO Deadlock
                        </button>
                    </form>
                    <p class="text-xs text-gray-500 mt-3">
                        This will update the status to "deadlock" and generate a formal letter for the Independent Football Ombudsman.
                    </p>
                </div>
            <?php endif; ?>

            <!-- IFO Information -->
            <?php if ($complaint['status'] === 'deadlock'): ?>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">📄 IFO Deadlock</h3>
                    <p class="text-sm text-gray-700 mb-4">
                        This complaint has been escalated to the Independent Football Ombudsman.
                    </p>
                    <div class="text-xs text-gray-600 space-y-1">
                        <p><strong>Independent Football Ombudsman</strong></p>
                        <p>Premier House</p>
                        <p>1-5 Argyle Way</p>
                        <p>Stevenage, Hertfordshire</p>
                        <p>SG1 2AD</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
