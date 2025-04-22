<?php require_once __DIR__ . '/../layout/main.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5>Chat about: <?php echo htmlspecialchars($listing['title']); ?></h5>
                </div>
                <div class="card-body" style="min-height:300px; max-height:400px; overflow-y:auto;">
                    <?php if (empty($messages)): ?>
                        <div class="text-center text-muted">No messages yet. Start the conversation!</div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="mb-2">
                                <strong>
                                    <?php
                                    if ($msg['sender_id'] == $_SESSION['user_id']) {
                                        echo 'You';
                                    } else {
                                        // Fetch sender name from database (cache for efficiency)
                                        static $userCache = [];
                                        $sid = $msg['sender_id'];
                                        if (!isset($userCache[$sid])) {
                                            $stmt = (new \App\Core\Database())->query('SELECT name FROM users WHERE id = ?', [$sid]);
                                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                            $userCache[$sid] = $row ? $row['name'] : 'Unknown User';
                                        }
                                        echo htmlspecialchars($userCache[$sid]);
                                    }
                                    ?>:
                                </strong>
                                <span><?php echo nl2br(htmlspecialchars($msg['message'])); ?></span>
                                <div class="small text-muted ms-2"><?php echo htmlspecialchars($msg['created_at']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <form method="POST" action="/sdg-market/public/listings/<?php echo $listing['id']; ?>/chat/send<?php echo isset($_GET['buyer_id']) ? '?buyer_id=' . (int)$_GET['buyer_id'] : ''; ?>" class="d-flex">
                        <input type="text" name="message" class="form-control me-2" placeholder="Type a message..." required>
                        <button type="submit" class="btn btn-success">Send</button>
                    </form>
                </div>
            </div>
            <a href="/sdg-market/public/listings/<?php echo $listing['id']; ?>" class="btn btn-link mt-3">Back to Listing</a>
        </div>
    </div>
</div>
