<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Food & Drink Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main-content {
            padding: 20px;
        }
        .navbar {
            padding: 0.8rem 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .nav-link {
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        .nav-link.active {
            background-color: #0d6efd;
            color: white !important;
            border-radius: 4px;
        }
        .nav-link:hover:not(.active) {
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .card-dashboard {
            transition: transform 0.3s;
        }
        .card-dashboard:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>


    <!-- Main Content Area -->
    <div class="container-fluid main-content">
        <!-- Page Content -->
        <?php
        switch($page) {
            case 'dashboard':
                include 'dashboard_content.php';
                break;
            case 'food':
                include 'food_management.php';
                break;
            case 'drinks':
                include 'drinks_management.php';
                break;
            case 'pricing':
                include 'pricing_management.php';
                break;
            case 'reports':
                include 'reports.php';
                break;
            default:
                include 'dashboard_content.php';
        }
        ?>

        <!-- Dashboard Content Example -->
        <?php if($page == 'dashboard'): ?>
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Dashboard</h1>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-primary card-dashboard h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title">Food Items</h5>
                                <p class="card-text display-4"><?php echo $foodCount; ?></p>
                            </div>
                            <div class="bg-white p-2 rounded-circle text-primary">
                                <i class="fas fa-utensils fa-2x"></i>
                            </div>
                        </div>
                        <a href="index.php?page=food" class="text-white d-block mt-2">Manage <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-success card-dashboard h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title">Drink Items</h5>
                                <p class="card-text display-4"><?php echo $drinkCount; ?></p>
                            </div>
                            <div class="bg-white p-2 rounded-circle text-success">
                                <i class="fas fa-glass-martini fa-2x"></i>
                            </div>
                        </div>
                        <a href="index.php?page=drinks" class="text-white d-block mt-2">Manage <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-info card-dashboard h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title">Price List</h5>
                                <p class="card-text display-4"><?php echo $foodCount + $drinkCount; ?></p>
                            </div>
                            <div class="bg-white p-2 rounded-circle text-info">
                                <i class="fas fa-tag fa-2x"></i>
                            </div>
                        </div>
                        <a href="index.php?page=pricing" class="text-white d-block mt-2">Manage <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-warning card-dashboard h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title">Reports</h5>
                                <p class="card-text display-4">4</p>
                            </div>
                            <div class="bg-white p-2 rounded-circle text-warning">
                                <i class="fas fa-chart-bar fa-2x"></i>
                            </div>
                        </div>
                        <a href="index.php?page=reports" class="text-white d-block mt-2">View <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Food Items Quick View -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Food Items</h5>
                        <a href="index.php?page=food" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Example data - in production, this would come from your database
                                    $foods = [
                                        ['id' => 1, 'name' => 'Beef Burger', 'category' => 'Main Course', 'price' => '$8.99', 'status' => 'Available'],
                                        ['id' => 2, 'name' => 'Caesar Salad', 'category' => 'Appetizer', 'price' => '$5.49', 'status' => 'Available'],
                                        ['id' => 3, 'name' => 'Pepperoni Pizza', 'category' => 'Main Course', 'price' => '$12.99', 'status' => 'Out of Stock'],
                                        ['id' => 4, 'name' => 'Chocolate Cake', 'category' => 'Dessert', 'price' => '$4.99', 'status' => 'Available'],
                                    ];
                                    
                                    foreach($foods as $food) {
                                        echo '<tr>';
                                        echo '<td>' . $food['id'] . '</td>';
                                        echo '<td>' . $food['name'] . '</td>';
                                        echo '<td>' . $food['category'] . '</td>';
                                        echo '<td>' . $food['price'] . '</td>';
                                        echo '<td><span class="badge ' . ($food['status'] == 'Available' ? 'bg-success' : 'bg-danger') . '">' . $food['status'] . '</span></td>';
                                        echo '<td>
                                                <a href="edit_food.php?id=' . $food['id'] . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                                <a href="delete_food.php?id=' . $food['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')"><i class="fas fa-trash"></i></a>
                                              </td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>