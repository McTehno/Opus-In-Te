  document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.main-header');
            
            // The point where the header should become solid, in pixels from the top.
            // 50px is a good starting point.
            const scrollThreshold = 50;

            window.addEventListener('scroll', function() {
                if (window.scrollY > scrollThreshold) {
                    // User has scrolled down, add the 'scrolled' class.
                    header.classList.add('scrolled');
                } else {
                    // User is at the top, remove the 'scrolled' class.
                    header.classList.remove('scrolled');
                }
            });
        });