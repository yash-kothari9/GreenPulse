<?php require_once __DIR__ . '/../layout/main.php'; ?>
<div class="container mt-5">
    <h2 class="mb-4">My Messages</h2>
    <?php if (empty($conversations)): ?>
        <div class="alert alert-info">No messages yet.</div>
    <?php else: ?>
        <?php foreach ($conversations as $conv): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <strong>Listing:</strong> <?php echo htmlspecialchars($conv['listing']['title']); ?>
                </div>
                <div class="card-body">
                    <?php if (empty($conv['buyers'])): ?>
                        <div class="text-muted">No buyers have messaged about this listing yet.</div>
                    <?php else: ?>
                        <ul class="list-group">
                        <?php foreach ($conv['buyers'] as $buyer): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?php echo htmlspecialchars($buyer['name']); ?></span>
                                <a href="/sdg-market/public/listings/<?php echo $conv['listing']['id']; ?>/chat?buyer_id=<?php echo $buyer['id']; ?>" class="btn btn-outline-success btn-sm">View Chat</a>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
