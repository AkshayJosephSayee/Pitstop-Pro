<?php
// Include configuration
require_once 'config.php';

// Initialize variables
$first_name = $email = $mobile = $service_type = $appointment_date = $appointment_time = $special_request = '';
$success_message = $error_message = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $first_name = trim($_POST['first_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $service_type = trim($_POST['service_type']);
    $appointment_date = trim($_POST['appointment_date']);
    $appointment_time = trim($_POST['appointment_time']);
    $special_request = trim($_POST['special_request']);
    
    // Basic validation
    if (empty($first_name) || empty($email) || empty($mobile) || empty($service_type) || empty($appointment_date) || empty($appointment_time)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO tbl_bookings (first_name, email, mobile, service_type, appointment_date, appointment_time, special_request) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $first_name, $email, $mobile, $service_type, $appointment_date, $appointment_time, $special_request);
        
        if ($stmt->execute()) {
            $success_message = "Booking submitted successfully! We'll contact you soon.";
            // Clear form fields
            $first_name = $email = $mobile = $service_type = $appointment_date = $appointment_time = $special_request = '';
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Pitstop-Pro - Booking</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta content="" name="keywords">
        <meta content="" name="description">

        <!-- Favicons -->
        <link href="img/favicon.ico" rel="icon">
        <link href="img/apple-touch-icon.png" rel="apple-touch-icon">

        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600|Nunito:600,700,800,900" rel="stylesheet"> 

        <!-- Bootstrap CSS File -->
        <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <!-- Libraries CSS Files -->
        <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <link href="vendor/animate/animate.min.css" rel="stylesheet">
        <link href="vendor/ionicons/css/ionicons.min.css" rel="stylesheet">
        <link href="vendor/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
        <link href="vendor/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

        <!-- Main Stylesheet File -->
        <link href="css/hover-style.css" rel="stylesheet">
        <link href="css/style.css">
        
        <style>
            .alert {
                padding: 15px;
                margin-bottom: 20px;
                border: 1px solid transparent;
                border-radius: 4px;
            }
            .alert-success {
                color: #3c763d;
                background-color: #dff0d8;
                border-color: #d6e9c6;
            }
            .alert-danger {
                color: #a94442;
                background-color: #f2dede;
                border-color: #ebccd1;
            }
        </style>
    </head>

    <body>
        <!-- Top Header Start -->
        <section class="banner-header">
            <video autoplay muted loop>
                <source src="c:\Users\ACER\Downloads\BMW M3 Competition - 4K Cinematic Short Video.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <header class="relative py-24 lg:py-32 hero-bg-pattern overflow-hidden">
                <div class="relative z-10 max-w-4xl mx-auto text-center px-4">
                    <div class="glitch-container">
                        <h1 id="glitch-heading" class="glitch-text text-4xl md:text-5xl lg:text-6xl font-black mb-6" data-text="Pitstop Pro">
                            Pitstop <span class="gradient-text">Pro</span>
                        </h1>
                    </div>
                    <h2>Your Car Doctor</h2>
                </div>
            </header>
        </section>
        <!-- Top Header End -->

        <!-- Header Start -->
        <header id="header">
            <div class="container">
                <nav id="nav-menu-container">
                    <ul class="nav-menu">
                        <li><a href="index.html">Home</a></li>
                        <li><a href="about.html">About</a></li>
                        <li><a href="service.html">Services</a></li>
                        <li class="menu-active"><a href="booking.php">Booking</a></li>
                        <li><a href="login.html">Login</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </header>
        <!-- Header End -->

        <main id="main">

            <!-- Booking Section Start -->
            <section id="booking">
                <div class="container">
                    <div class="section-header">
                        <h3>Book for Getting Services</h3>
                    </div>
                    
                    <!-- Display success/error messages -->
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="booking-form">
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <div class="form-row">
                                        <div class="control-group col-sm-6">
                                            <label>First Name *</label>
                                            <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required="required" />
                                        </div>
                                        <div class="control-group col-sm-6">
                                            <label>Email *</label>
                                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" required="required" />
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="control-group col-sm-6">
                                            <label>Mobile *</label>
                                            <input type="text" class="form-control" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" required="required" />
                                        </div>
                                        <div class="control-group col-sm-6">
                                            <label>Select a Service *</label>
                                            <select class="custom-select" name="service_type" required>
                                                <option value="">Choose a service...</option>
                                                <option value="Engine Tuning" <?php echo ($service_type == 'Engine Tuning') ? 'selected' : ''; ?>>Engine Tuning</option>
                                                <option value="Paint work" <?php echo ($service_type == 'Paint work') ? 'selected' : ''; ?>>Paint work</option>
                                                <option value="Break check" <?php echo ($service_type == 'Break check') ? 'selected' : ''; ?>>Break check</option>
                                                <option value="Service" <?php echo ($service_type == 'Service') ? 'selected' : ''; ?>>Service</option>
                                                <option value="Wheel Alignment" <?php echo ($service_type == 'Wheel Alignment') ? 'selected' : ''; ?>>Wheel Alignment</option>
                                                <option value="Body Work" <?php echo ($service_type == 'Body Work') ? 'selected' : ''; ?>>Body Work</option>
                                                <option value="Accessories" <?php echo ($service_type == 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
                                                <option value="Washing" <?php echo ($service_type == 'Washing') ? 'selected' : ''; ?>>Washing</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="control-group col-sm-6">
                                            <label>Appointment Date *</label>
                                            <input type="date" class="form-control" name="appointment_date" value="<?php echo htmlspecialchars($appointment_date); ?>" required="required" />
                                        </div>
                                        <div class="control-group col-sm-6">
                                            <label>Appointment Time *</label>
                                            <input type="time" class="form-control" name="appointment_time" value="<?php echo htmlspecialchars($appointment_time); ?>" required="required" />
                                        </div>
                                    </div>
                                    <div class="control-group">
                                        <label>Special Request</label>
                                        <textarea class="form-control" name="special_request" rows="3"><?php echo htmlspecialchars($special_request); ?></textarea>
                                    </div>
                                    <div class="button">
                                        <button type="submit">Book Now</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Booking Section End -->
            
            <!-- Support Section Start -->
            <section id="support" class="wow fadeInUp">
                <div class="container">
                    <h1>
                        Need help? Call me 24/7 at +91 9567884807
                    </h1>
                </div>
            </section>
            <!-- Support Section end -->

        </main>

        <!-- Footer Start -->
        <footer id="footer">
            <div class="container">
                <div class="copyright">
                    <p>&copy; Copyright <a href="#">Pitstop-Pro</a>. All Rights Reserved</p>
                </div>
            </div>
        </footer>
        <!-- Footer end -->

        <a href="#" class="back-to-top"><i class="fa fa-chevron-up"></i></a>

        <!-- JavaScript Libraries -->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/jquery/jquery-migrate.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="vendor/easing/easing.min.js"></script>
        <script src="vendor/stickyjs/sticky.js"></script>
        <script src="vendor/superfish/hoverIntent.js"></script>
        <script src="vendor/superfish/superfish.min.js"></script>
        <script src="vendor/owlcarousel/owl.carousel.min.js"></script>
        <script src="vendor/touchSwipe/jquery.touchSwipe.min.js"></script>
        <script src="vendor/tempusdominus/js/moment.min.js"></script>
        <script src="vendor/tempusdominus/js/moment-timezone.min.js"></script>
        <script src="vendor/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

        <!-- Main Javascript File -->
        <script src="js/main.js"></script>

    </body>
</html>

<?php
// Close database connection
$conn->close();
?>