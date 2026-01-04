<?php

require __DIR__ . '/vendor/autoload.php';

$router = new \Bramus\Router\Router();

// Custom 404 Handler
$router->set404(function () {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo '404, route not found!';
});

// Static route: / (Homepage)
$router->get('/', function () {
    require 'Home.php';
});
$router->get('/pocetna', function () {
    require 'Home.php';
});

// Services
$router->get('/usluge', function () {
    require 'Services.php';
});

// About
$router->get('/o-meni', function () {
    require 'About.php';
});

// Blog
$router->get('/blog', function () {
    require 'Blog.php';
});

// Contact
$router->get('/kontakt', function () {
    require 'Contact.php';
});

// Booking
$router->get('/zakazivanje', function () {
    require 'Booking.php';
});

// Login
$router->get('/prijava', function () {
    require 'Login.php';
});
$router->post('/prijava', function () {
    require 'Login.php';
});

// Logout
$router->get('/odjava', function () {
    require 'backend/logout.php';
});

// Register
$router->get('/registracija', function () {
    require 'Register.php';
});
$router->post('/registracija', function () {
    require 'Register.php';
});

// User Dashboard
$router->get('/korisnicki-panel', function () {
    require 'UserDashboard.php';
});

// Worker Dashboard
$router->get('/radni-panel', function () {
    require 'WorkerDashboard.php';
});

// Admin Dashboard
$router->get('/admin-panel', function () {
    require 'AdminDashboard.php';
});

// Admin Subpages
$router->mount('/admin', function () use ($router) {
    $router->get('/termini', function () {
        require 'AdminAppointments.php';
    });
    $router->get('/blog', function () {
        require 'AdminBlog.php';
    });
    $router->get('/usluge', function () {
        require 'AdminServices.php';
    });
    $router->get('/korisnici', function () {
        require 'AdminUsers.php';
    });
    $router->get('/izvoz/excel', function () {
        require 'backend/admin_export_excel.php';
    });
    $router->get('/izvoz/pdf', function () {
        require 'backend/admin_export_pdf.php';
    });
    $router->get('/odjava', function () {
        require 'backend/admin_logout.php';
    });
});

// Edit Profile
$router->get('/uredi-profil', function () {
    require 'EditProfile.php';
});

// Worker Booking
$router->get('/radnik-zakazivanje', function () {
    require 'WorkerBooking.php';
});

// Backend API Passthrough (Fix for 404 on direct file access)
$router->mount('/backend', function () use ($router) {
    $router->match('GET|POST', '/(.*)', function ($filename) {
        $file = __DIR__ . '/backend/' . $filename;
        if (file_exists($file)) {
            require $file;
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            echo 'File not found: ' . htmlspecialchars($filename);
        }
    });
});

// Run the router
$router->run();
