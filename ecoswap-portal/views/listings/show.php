<?php require_once __DIR__ . '/../layout/main.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><?php echo htmlspecialchars($listing['title']); ?></h4>
                </div>
                <div class="card-body">
                    <span class="badge bg-success mb-2"><?php echo htmlspecialchars($listing['category']); ?></span>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>
                    <p class="card-text"><strong>Price: ₹<?php echo number_format($listing['price'], 2); ?></strong></p>
                    <p class="card-text"><strong>Condition:</strong> <?php echo htmlspecialchars($listing['condition']); ?></p>
                    <p class="card-text"><strong>Posted by:</strong> <?php echo htmlspecialchars($listing['user_name']); ?> (<?php echo htmlspecialchars($listing['college']); ?>)</p>
                    <p class="card-text"><small class="text-muted">Posted on: <?php echo htmlspecialchars($listing['created_at']); ?></small></p>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $listing['user_id']): ?>
                        <a href="/sdg-market/public/listings/<?php echo $listing['id']; ?>/chat" class="btn btn-success mt-2">Chat with Seller</a>
                    <?php endif; ?>
                    <a href="/sdg-market/public/listings" class="btn btn-outline-secondary mt-2 ms-2">Back to Listings</a>
                </div>
            </div>
        </div>
    </div>
</div>
