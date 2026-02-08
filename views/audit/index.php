<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-steward-dark">Audit Trail</h1>
        <span class="text-sm text-gray-500">Last 200 entries (append-only, immutable)</span>
    </div>

    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <span class="text-2xl">🔒</span>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Immutable Audit Log</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>This audit trail is append-only and cannot be modified or deleted. 
                    All complaint state changes are permanently recorded for compliance and accountability.</p>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($logs)): ?>
        <div class="bg-white p-12 rounded-lg shadow text-center">
            <div class="text-6xl mb-4">📋</div>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">No audit logs found</h2>
            <p class="text-gray-500">Audit entries will appear here as complaints are created and modified.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">State Change</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('Y-m-d H:i:s', strtotime($log['timestamp'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="/complaints/<?php echo $log['complaint_id']; ?>" class="text-steward-primary hover:text-steward-accent font-medium">
                                    STW-<?php echo str_pad($log['complaint_id'], 6, '0', STR_PAD_LEFT); ?>
                                </a>
                                <?php if ($log['complaint_subject']): ?>
                                    <div class="text-xs text-gray-500">
                                        <?php echo htmlspecialchars(substr($log['complaint_subject'], 0, 40)); ?>
                                        <?php if (strlen($log['complaint_subject']) > 40) echo '...'; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo htmlspecialchars($log['action']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($log['previous_state'] || $log['new_state']): ?>
                                    <span class="text-gray-500"><?php echo htmlspecialchars($log['previous_state'] ?? 'none'); ?></span>
                                    <span class="text-gray-400 mx-1">→</span>
                                    <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($log['new_state'] ?? 'none'); ?></span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($log['user_name']): ?>
                                    <div class="text-gray-900"><?php echo htmlspecialchars($log['user_name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($log['user_email']); ?></div>
                                <?php else: ?>
                                    <span class="text-gray-400 italic">System</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
