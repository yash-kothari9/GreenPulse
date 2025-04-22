<?php require_once __DIR__ . '/../layout/main.php'; ?>

<div class="container mt-4">
    <div class="jumbotron text-center bg-light p-5 rounded-3 mb-4">
        <h1 class="display-4">Campus Exchange</h1>
        <p class="lead">Your Student Marketplace for Books, Electronics & Courses</p>
        <hr class="my-4">
        <p>Buy and sell textbooks, electronics, and online courses with students in your college.</p>

    </div>

        <div class="mt-5">
            <h2 class="text-center mb-4">Recent Listings</h2>
            <div class="row">
                <?php foreach ($listings as $listing): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($listing['category']); ?></span>
                            <h5 class="card-title"><?php echo htmlspecialchars($listing['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($listing['description']); ?></p>
                            <p class="card-text">
                                <small class="text-muted">Condition: <?php echo htmlspecialchars($listing['condition']); ?></small><br>
                                <small class="text-muted">Posted by: <?php echo htmlspecialchars($listing['user_name']); ?></small>
                            </p>
                            <p class="card-text"><strong>₹<?php echo number_format($listing['price'], 2); ?></strong></p>
                            <a href="/sdg-market/public/listings/<?php echo $listing['id']; ?>" class="btn btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

