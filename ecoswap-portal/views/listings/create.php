<?php require_once __DIR__ . '/../layout/main.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header " style="background-color: #e3eafc; color: #222b45; text-white">
                    <h4 class="mb-0">Create New Listing</h4>
                </div>
                <div class="card-body">
                    <form id="createListingForm" method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" style="color: #222b45;" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (₹)</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="registration_number" class="form-label">Registration Number</label>
                            <input type="text" style="color: #222b45;" class="form-control" id="registration_number" name="registration_number" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select a category</option>
                                <option value="books">Books</option>
                                <option value="electronics">Electronics</option>
                                <option value="courses">Online Courses</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="condition" class="form-label">Condition</label>
                            <select class="form-select" id="condition" name="condition" required>
                                <option value="">Select condition</option>
                                <option value="new">New</option>
                                <option value="like_new">Like New</option>
                                <option value="good">Good</option>
                                <option value="fair">Fair</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn" style="background-color: #e3eafc; color: #222b45; border-color: #e3eafc;">
                            <i class="fas fa-plus-circle"></i> Create Listing
                        </button>
                        <a href="/sdg-market/public/listings" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
