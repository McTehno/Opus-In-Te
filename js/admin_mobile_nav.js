/**
 * Admin Mobile Navigation Handler
 * Opus in te - Admin Panel
 * Handles hamburger menu toggle for admin navigation
 */

document.addEventListener('DOMContentLoaded', function() {
    initAdminMobileNav();
    
    // Handle resize events
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleAdminResize, 100);
    });
});

/**
 * Initialize admin mobile navigation
 */
function initAdminMobileNav() {
    const header = document.querySelector('.admin-header');
    if (!header) return;
    
    const container = header.querySelector('.container');
    const adminNav = header.querySelector('.admin-nav');
    const adminActions = header.querySelector('.admin-actions');
    
    if (!container || !adminNav) return;
    
    // Check if toggle already exists
    if (header.querySelector('.admin-mobile-toggle')) return;
    
    // Create mobile menu toggle button
    const mobileToggle = document.createElement('button');
    mobileToggle.className = 'admin-mobile-toggle';
    mobileToggle.setAttribute('aria-label', 'Otvori navigaciju');
    mobileToggle.setAttribute('aria-expanded', 'false');
    mobileToggle.innerHTML = `
        <span></span>
        <span></span>
        <span></span>
    `;
    
    // Insert toggle before admin actions or at end
    if (adminActions) {
        container.insertBefore(mobileToggle, adminActions);
    } else {
        container.appendChild(mobileToggle);
    }
    
    // Add event listener
    mobileToggle.addEventListener('click', toggleAdminNav);
    
    // Close menu on link click
    const navLinks = adminNav.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', closeAdminNav);
    });
    
    // Close on document click outside
    document.addEventListener('click', function(e) {
        const isClickInside = header.contains(e.target);
        if (!isClickInside) {
            closeAdminNav();
        }
    });
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAdminNav();
        }
    });
}

/**
 * Toggle admin navigation
 */
function toggleAdminNav() {
    const toggle = document.querySelector('.admin-mobile-toggle');
    const nav = document.querySelector('.admin-nav');
    
    if (!toggle || !nav) return;
    
    const isActive = toggle.classList.contains('active');
    
    if (isActive) {
        closeAdminNav();
    } else {
        openAdminNav();
    }
}

/**
 * Open admin navigation
 */
function openAdminNav() {
    const toggle = document.querySelector('.admin-mobile-toggle');
    const nav = document.querySelector('.admin-nav');
    
    if (!toggle || !nav) return;
    
    toggle.classList.add('active');
    toggle.setAttribute('aria-expanded', 'true');
    nav.classList.add('active');
}

/**
 * Close admin navigation
 */
function closeAdminNav() {
    const toggle = document.querySelector('.admin-mobile-toggle');
    const nav = document.querySelector('.admin-nav');
    
    if (!toggle || !nav) return;
    
    toggle.classList.remove('active');
    toggle.setAttribute('aria-expanded', 'false');
    nav.classList.remove('active');
}

/**
 * Handle window resize
 */
function handleAdminResize() {
    if (window.innerWidth > 768) {
        closeAdminNav();
    }
}
