<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-steward-dark">Complaints</h1>
        <a href="/complaints/create" class="bg-steward-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90">
            + New Complaint
        </a>
    </div>

    <?php if (empty($complaints)): ?>
        <div class="bg-white p-12 rounded-lg shadow text-center">
            <div class="text-6xl mb-4">📭</div>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">No complaints found</h2>
            <p class="text-gray-500 mb-4">Get started by creating your first complaint.</p>
            <a href="/complaints/create" class="inline-block bg-steward-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90">
                Create Complaint
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toxicity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Block</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($complaints as $complaint): 
                        $isBreached = in_array($complaint['id'], $breachedIds);
                        $rowClass = $isBreached ? 'bg-red-50' : '';
                    ?>
                        <tr class="<?php echo $rowClass; ?> hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="/complaints/<?php echo $complaint['id']; ?>" class="text-steward-primary hover:text-steward-accent font-medium">
                                    STW-<?php echo str_pad($complaint['id'], 6, '0', STR_PAD_LEFT); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars(substr($complaint['subject'], 0, 60)); ?>
                                    <?php if (strlen($complaint['subject']) > 60) echo '...'; ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    by <?php echo htmlspecialchars($complaint['user_name']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'new' => 'bg-blue-100 text-blue-800',
                                    'investigating' => 'bg-yellow-100 text-yellow-800',
                                    'resolved' => 'bg-green-100 text-green-800',
                                    'deadlock' => 'bg-red-100 text-red-800',
                                ];
                                $statusColor = $statusColors[$complaint['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColor; ?>">
                                    <?php echo strtoupper($complaint['status']); ?>
                                </span>
                                <?php if ($isBreached): ?>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-600 text-white breach-pulse ml-1">
                                        BREACH
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($complaint['category']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($complaint['toxicity_score'] !== null): 
                                    $score = (float)$complaint['toxicity_score'];
                                    $percentage = round($score * 100);
                                    $barColor = $score >= 0.6 ? 'bg-red-600' : ($score >= 0.3 ? 'bg-yellow-500' : 'bg-green-500');
                                ?>
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="<?php echo $barColor; ?> h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600"><?php echo $percentage; ?>%</span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo strtoupper($complaint['stadium_block']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('d M Y', strtotime($complaint['deadlock_deadline'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d M Y', strtotime($complaint['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
