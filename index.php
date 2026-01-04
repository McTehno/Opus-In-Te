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

// Backend API Routes
$router->mount('/backend', function () use ($router) {
    // Public
    $router->match('GET|POST', '/fetch_posts.php', function() { require 'backend/fetch_posts.php'; });
    $router->match('GET|POST', '/increment_view.php', function() { require 'backend/increment_view.php'; });
    $router->match('GET|POST', '/send_contact.php', function() { require 'backend/send_contact.php'; });
    $router->match('GET|POST', '/get_slots.php', function() { require 'backend/get_slots.php'; });
    $router->match('GET|POST', '/book_appointment.php', function() { require 'backend/book_appointment.php'; });

    // Worker
    $router->match('GET|POST', '/worker_update_appointment.php', function() { require 'backend/worker_update_appointment.php'; });
    $router->match('GET|POST', '/worker_get_slots.php', function() { require 'backend/worker_get_slots.php'; });
    $router->match('GET|POST', '/worker_book_appointment.php', function() { require 'backend/worker_book_appointment.php'; });

    // Admin
    $router->match('GET|POST', '/admin_create_worker.php', function() { require 'backend/admin_create_worker.php'; });
    $router->match('GET|POST', '/admin_fetch_users.php', function() { require 'backend/admin_fetch_users.php'; });
    $router->match('GET|POST', '/admin_update_user.php', function() { require 'backend/admin_update_user.php'; });
    $router->match('GET|POST', '/admin_delete_user.php', function() { require 'backend/admin_delete_user.php'; });
    
    $router->match('GET|POST', '/admin_fetch_services.php', function() { require 'backend/admin_fetch_services.php'; });
    $router->match('GET|POST', '/admin_update_service.php', function() { require 'backend/admin_update_service.php'; });
    $router->match('GET|POST', '/admin_add_service.php', function() { require 'backend/admin_add_service.php'; });
    $router->match('GET|POST', '/admin_delete_service.php', function() { require 'backend/admin_delete_service.php'; });

    $router->match('GET|POST', '/admin_fetch_blogs.php', function() { require 'backend/admin_fetch_blogs.php'; });
    $router->match('GET|POST', '/admin_get_blog.php', function() { require 'backend/admin_get_blog.php'; });
    $router->match('GET|POST', '/admin_fetch_blog_authors.php', function() { require 'backend/admin_fetch_blog_authors.php'; });
    $router->match('GET|POST', '/admin_update_blog.php', function() { require 'backend/admin_update_blog.php'; });
    $router->match('GET|POST', '/admin_delete_blog.php', function() { require 'backend/admin_delete_blog.php'; });

    $router->match('GET|POST', '/admin_fetch_appointments.php', function() { require 'backend/admin_fetch_appointments.php'; });
    $router->match('GET|POST', '/admin_create_appointment.php', function() { require 'backend/admin_create_appointment.php'; });
    $router->match('GET|POST', '/admin_delete_appointment.php', function() { require 'backend/admin_delete_appointment.php'; });
    $router->match('GET|POST', '/admin_update_appointment.php', function() { require 'backend/admin_update_appointment.php'; });
    $router->match('GET|POST', '/admin_get_appointment.php', function() { require 'backend/admin_get_appointment.php'; });
});

// Run the router
$router->run();
