<?php require_once __DIR__ . '/../layout/main.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background-color: #e3eafc; color: #222b45;">
                    <h4 class="mb-0">Register</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    <form action="/sdg-market/public/auth/register" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" style="color: #222b45;" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" style="color: #222b45;" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="registration_number" class="form-label">Registration Number</label>
                            <input type="text" style="color: #222b45;" class="form-control" id="registration_number" name="registration_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label for="confirm" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm" name="confirm" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label for="college" class="form-label">College/University</label>
                            <input type="text" style="color: #222b45;" class="form-control" id="college" name="college" required>
                        </div>
                        <button type="submit" class="btn" style="background-color: #e3eafc; color: #222b45; border: none;">Register</button>
                        <a href="/sdg-market/public/login" class="btn btn-link">Already have an account? Login</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
