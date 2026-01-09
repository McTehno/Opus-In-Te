/**
 * Mobile Navigation Handler
 * Opus in te - Psychology Practice Website
 * Handles hamburger menu toggle and mobile navigation
 */

document.addEventListener('DOMContentLoaded', function() {
    // Create mobile menu elements if they don't exist
    initMobileNavigation();
    
    // Handle resize events
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleResize, 100);
    });
});

/**
 * Initialize mobile navigation elements
 */
function initMobileNavigation() {
    const header = document.querySelector('.main-header');
    if (!header) return;
    
    const container = header.querySelector('.container');
    const mainNav = header.querySelector('.main-nav');
    const headerActions = header.querySelector('.header-actions');
    
    if (!container || !mainNav) return;
    
    // 1. Get or Create Mobile Toggle
    let mobileToggle = header.querySelector('.mobile-menu-toggle');
    
    if (!mobileToggle) {
        // Create mobile menu toggle button (hamburger)
        mobileToggle = document.createElement('button');
        mobileToggle.className = 'mobile-menu-toggle';
        mobileToggle.setAttribute('aria-label', 'Otvori navigaciju');
        mobileToggle.setAttribute('aria-expanded', 'false');
        mobileToggle.innerHTML = `
            <span></span>
            <span></span>
            <span></span>
        `;
        
        // Insert elements
        if (headerActions) {
            headerActions.insertBefore(mobileToggle, headerActions.firstChild);
        } else {
            container.appendChild(mobileToggle);
        }
    }
    
    // 2. Get or Create Overlay
    let overlay = document.querySelector('.mobile-nav-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'mobile-nav-overlay';
        document.body.appendChild(overlay);
    }
    
    // Add CTA button to mobile menu if it exists in header actions
    const navCta = header.querySelector('.nav-cta');
    if (navCta && mainNav.querySelector('ul')) {
        // Check if mobile CTA already exists
        if (!mainNav.querySelector('.mobile-nav-cta')) {
            const mobileCtaClone = navCta.cloneNode(true);
            mobileCtaClone.className = 'cta-button mobile-nav-cta';
            mainNav.appendChild(mobileCtaClone);
        }
    }
    
    // Add event listeners (remove old ones first to avoid duplicates if re-initialized)
    mobileToggle.removeEventListener('click', toggleMobileNav);
    mobileToggle.addEventListener('click', toggleMobileNav);
    
    overlay.removeEventListener('click', closeMobileNav);
    overlay.addEventListener('click', closeMobileNav);
    
    // Close menu on link click
    const navLinks = mainNav.querySelectorAll('a');
    navLinks.forEach(link => {
        link.removeEventListener('click', closeMobileNav);
        link.addEventListener('click', closeMobileNav);
    });
    
    // Close menu on escape key
    // document listener logic remains but ensuring it doesn't cause issues if added multiple times is hard unless named. 
    // For now keeping it inside is fine as this runs once.
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMobileNav();
        }
    });
}

/**
 * Toggle mobile navigation
 */
function toggleMobileNav() {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('.main-nav');
    const overlay = document.querySelector('.mobile-nav-overlay');
    
    if (!toggle || !nav) return;
    
    const isActive = toggle.classList.contains('active');
    
    if (isActive) {
        closeMobileNav();
    } else {
        openMobileNav();
    }
}

/**
 * Open mobile navigation
 */
function openMobileNav() {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('.main-nav');
    const overlay = document.querySelector('.mobile-nav-overlay');
    
    if (!toggle || !nav) return;
    
    toggle.classList.add('active');
    toggle.setAttribute('aria-expanded', 'true');
    nav.classList.add('active');
    
    if (overlay) {
        overlay.classList.add('active');
    }
    
    // Prevent body scroll
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    if (scrollbarWidth > 0) {
        document.body.style.paddingRight = scrollbarWidth + 'px';
        const header = document.querySelector('.main-header');
        if (header) header.style.paddingRight = scrollbarWidth + 'px';
    }
    document.body.style.overflow = 'hidden';
}

/**
 * Close mobile navigation
 */
function closeMobileNav() {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('.main-nav');
    const overlay = document.querySelector('.mobile-nav-overlay');
    
    // Always clean up styles even if elements missing
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
    const header = document.querySelector('.main-header');
    if (header) header.style.paddingRight = '';

    if (!toggle || !nav) return;
    
    toggle.classList.remove('active');
    toggle.setAttribute('aria-expanded', 'false');
    nav.classList.remove('active');
    
    if (overlay) {
        overlay.classList.remove('active');
    }
    
    // Restore body scroll
    document.body.style.overflow = '';
}

/**
 * Handle window resize
 */
function handleResize() {
    const windowWidth = window.innerWidth;
    
    // Close mobile nav when resizing to desktop
    if (windowWidth > 768) {
        closeMobileNav();
    }
}

/**
 * Check if current viewport is mobile
 */
function isMobileViewport() {
    return window.innerWidth <= 768;
}
