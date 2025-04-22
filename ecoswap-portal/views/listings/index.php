<?php require_once __DIR__ . '/../layout/main.php'; ?>

<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-auto">
            <h2 class="mb-0"><?php echo ucfirst($category ?? 'All') ?> Listings</h2>
        </div>
        <div class="col d-flex justify-content-center">
            <form class="flex-grow-1" action="/sdg-market/public/listings" method="GET" style="max-width: 700px; min-width: 350px; width: 100%;">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control" name="search" placeholder="Search listings..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn" style="background-color: #e3eafc; color: #222b45; border: none;" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="col-auto text-end">
            <a href="/sdg-market/public/listings/create" class="btn" style="background-color: #e3eafc; color: #222b45; border: none;">
                <i class="fas fa-plus-circle"></i> Post New Listing
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="row">
                <?php if (empty($listings)): ?>
                <div class="col">
                    <div class="alert alert-info">
                        No listings found in this category.
                    </div>
                </div>
                <?php else: ?>
                <?php foreach ($listings as $listing): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <span class="badge" style="background-color: #e3eafc; color: #222b45;"> <?php echo htmlspecialchars($listing['category']); ?></span>
                            <h5 class="card-title"><?php echo htmlspecialchars($listing['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($listing['description']); ?></p>
                            <p class="card-text">
                                <small class="text-muted">Condition: <?php echo htmlspecialchars($listing['condition']); ?></small><br>
                                <small class="text-muted">Posted by: <?php echo htmlspecialchars($listing['user_name']); ?></small><br>
                                <small class="text-muted">College: <?php echo htmlspecialchars($listing['college']); ?></small>
                            </p>
                            <p class="card-text"><strong>₹<?php echo number_format($listing['price'], 2); ?></strong></p>
                            
                            <a href="/sdg-market/public/listings/<?php echo $listing['id']; ?>" class="btn" style="background-color: #e3eafc; color: #222b45; border: none;">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
