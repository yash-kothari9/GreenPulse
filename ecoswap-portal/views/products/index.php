<?php require_once __DIR__ . '/../layout/main.php'; ?>

<div class="row mb-4">
    <div class="col">
        <h1>Sustainable Products</h1>
    </div>
    <div class="col-auto">
        <div class="dropdown">
            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Filter by SDG
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="?sdg=1">No Poverty</a></li>
                <li><a class="dropdown-item" href="?sdg=13">Climate Action</a></li>
                <li><a class="dropdown-item" href="?sdg=12">Responsible Consumption</a></li>
                <!-- Add more SDGs -->
            </ul>
        </div>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-3 g-4">
    <?php foreach ($products as $product): ?>
    <div class="col">
        <div class="card h-100">
            <div class="card-body">
                <span class="badge bg-success mb-2">SDG <?php echo htmlspecialchars($product['sdg_category']); ?></span>
                <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                <p class="card-text"><strong>$<?php echo number_format($product['price'], 2); ?></strong></p>
                <a href="/products/<?php echo $product['id']; ?>" class="btn btn-outline-success">View Details</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
