<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Exchange - Student Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg" style="background-color: #e3eafc; color: #222b45;">
        <div class="container">
            <a class="navbar-brand" href="/sdg-market/public/">Campus Exchange</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a href="/sdg-market/public/listings?category=books" class="btn" style="background-color: #e3eafc; color: #222b45; border: none;">Browse Books</a>
                    </li>
                    <li class="nav-item">
                        <a href="/sdg-market/public/listings?category=electronics" class="btn" style="background-color: #e3eafc; color: #222b45; border: none;">Browse Electronics</a>
                    </li>
                    <li class="nav-item">
                        <a href="/sdg-market/public/listings?category=courses" class="btn" style="background-color: #e3eafc; color: #222b45; border: none;">Browse Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/sdg-market/public/listings/create"><i class="fas fa-plus-circle"></i> Post Listing</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        // Notification badge for unread messages
                        $unread = 0;
                        if (isset($_SESSION['user_id'])) {
                            try {
                                $db = new \App\Core\Database();
                                $stmt = $db->query("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0", [$_SESSION['user_id']]);
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $unread = $row ? (int)$row['cnt'] : 0;
                            } catch (\Throwable $e) { $unread = 0; }
                        }
                        ?>
                        <li class="nav-item position-relative<?php if ($unread > 0) echo ' dropdown'; ?>">
                            <a class="nav-link<?php if ($unread > 0) echo ' dropdown-toggle'; ?>" href="/sdg-market/public/messages" id="notifDropdown"<?php if ($unread > 0) echo ' role="button" data-bs-toggle="dropdown" aria-expanded="false"'; ?>>
                                My Messages
                                <?php if ($unread > 0): ?>
                                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
                                        <?php echo $unread; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <?php if ($unread > 0): ?>
                                <ul class="dropdown-menu" aria-labelledby="notifDropdown" style="min-width:300px;">
                                    <?php
                                    // Show which item/listing has unread messages
                                    $notifDb = new \App\Core\Database();
                                    $notifStmt = $notifDb->query(
                                        "SELECT n.id, n.message_id, m.listing_id, l.title, m.sender_id, u.name as sender_name FROM notifications n
                                         JOIN messages m ON n.message_id = m.id
                                         JOIN listings l ON m.listing_id = l.id
                                         JOIN users u ON m.sender_id = u.id
                                         WHERE n.user_id = ? AND n.is_read = 0
                                         ORDER BY n.created_at DESC LIMIT 10",
                                        [$_SESSION['user_id']]
                                    );
                                    $notifs = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($notifs as $notif):
                                        $chatLink = "/sdg-market/public/listings/{$notif['listing_id']}/chat";
                                        if ($notif['sender_id'] != $_SESSION['user_id']) {
                                            $chatLink .= "?buyer_id={$notif['sender_id']}";
                                        }
                                    ?>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo $chatLink; ?>">
                                            <strong><?php echo htmlspecialchars($notif['title']); ?></strong><br>
                                            <small>New message from <?php echo htmlspecialchars($notif['sender_name']); ?></small>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/sdg-market/public/logout">Logout</a>
                        </li>
                    <?php endif; ?>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/sdg-market/public/login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/sdg-market/public/register">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4">
        <?php if (isset($content)) echo $content; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
