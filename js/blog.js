document.addEventListener('DOMContentLoaded', () => {
    const featuredContainer = document.getElementById('featured-post-container');
    const blogGrid = document.getElementById('blog-grid');
    const categoriesList = document.getElementById('categories-list');
    const popularList = document.getElementById('popular-posts-list');
    const sortBtn = document.getElementById('sort-btn');
    const sortOptions = document.getElementById('sort-options');
    const sortLinks = sortOptions.querySelectorAll('a');
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    const loadMoreBtnContainer = document.querySelector('.load-more-container');
    const loadMoreBtn = document.getElementById('load-more-btn');

    // View Elements
    const blogContainer = document.querySelector('.blog-container');
    const blogDetailView = document.getElementById('blog-detail-view');
    const blogPostsMain = document.querySelector('.blog-posts-main');
    const blogSidebar = document.querySelector('.blog-sidebar');
    const backToBlogBtn = document.getElementById('back-to-blog-btn');
    
    // Detail Content Elements
    const detailImg = document.getElementById('detail-img');
    const detailCategory = document.getElementById('detail-category');
    const detailTitle = document.getElementById('detail-title');
    const detailDate = document.querySelector('#detail-date span');
    const detailViews = document.querySelector('#detail-views span');
    const detailContent = document.getElementById('detail-content');

    let iso; // Isotope instance
    let allPosts = []; // Store all posts data
    let filteredPosts = []; // Store currently filtered/sorted posts
    let currentCategory = null;
    let currentSearch = '';
    let currentSort = 'date_desc';
    let visibleCount = 4;
    const LOAD_STEP = 4;

    // Initial Fetch
    fetchPosts();

    // Event Listeners
    if (backToBlogBtn) {
        backToBlogBtn.addEventListener('click', closePost);
    }

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            visibleCount += LOAD_STEP;
            renderVisiblePosts(true); // Append mode
        });
    }

    // Global click listener for delegation (handles all blog post links)
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (link && link.href.includes('BlogPost.php?id=')) {
            e.preventDefault();
            const id = link.href.split('id=')[1];
            openPost(id);
        }
    });

    sortBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        sortOptions.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
        if (!sortBtn.contains(e.target) && !sortOptions.contains(e.target)) {
            sortOptions.classList.remove('show');
        }
    });

    sortLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            sortLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            currentSort = link.dataset.sort;
            sortOptions.classList.remove('show');
            
            // Update button text
            sortBtn.innerHTML = `Sortiraj po: ${link.textContent} <i class="fas fa-chevron-down"></i>`;
            
            applyFiltersAndSort();
        });
    });

    searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        currentSearch = searchInput.value.trim().toLowerCase();
        applyFiltersAndSort();
    });
    
    searchInput.addEventListener('input', (e) => {
        currentSearch = e.target.value.trim().toLowerCase();
        applyFiltersAndSort();
    });

    // Fetch Data
    async function fetchPosts() {
        try {
            const response = await fetch('/backend/fetch_posts.php');
            const data = await response.json();

            if (data.error) {
                console.error(data.error);
                return;
            }

            allPosts = data.posts;
            renderCategories(data.categories);
            renderPopular(data.popular);
            
            // Initial Render
            applyFiltersAndSort();

        } catch (error) {
            console.error('Error fetching posts:', error);
        }
    }

    function renderCategories(categories) {
        categoriesList.innerHTML = '';
        
        const allLi = document.createElement('li');
        const allLink = document.createElement('a');
        allLink.href = '#';
        allLink.className = 'category-item active';
        allLink.innerHTML = `<span>Sve kategorije</span>`;
        allLink.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.category-item').forEach(el => el.classList.remove('active'));
            allLink.classList.add('active');
            
            currentCategory = null;
            applyFiltersAndSort();
        });
        allLi.appendChild(allLink);
        categoriesList.appendChild(allLi);

        categories.forEach(cat => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = '#';
            a.className = 'category-item';
            
            a.innerHTML = `
                <span>${cat.name}</span>
                <span class="category-count">${cat.count}</span>
            `;
            
            a.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.category-item').forEach(el => el.classList.remove('active'));
                a.classList.add('active');
                
                currentCategory = cat.idBlog_Post_Category;
                applyFiltersAndSort();
            });
            
            li.appendChild(a);
            categoriesList.appendChild(li);
        });
    }

    function renderPopular(posts) {
        if (popularList.children.length > 0 && popularList.dataset.loaded === 'true') return;
        
        popularList.innerHTML = '';
        posts.forEach(post => {
            const li = document.createElement('li');
            li.className = 'popular-post-item';
            li.innerHTML = `
                <a href="BlogPost.php?id=${post.idBlog_Post}">
                    <img src="${post.picture_path || 'img/blogplaceholder/blog_placeholder_2.jpg'}" alt="${post.title}">
                </a>
                <div class="popular-post-info">
                    <h5><a href="BlogPost.php?id=${post.idBlog_Post}">${post.title}</a></h5>
                    <span class="popular-post-views"><i class="far fa-eye"></i> ${post.viewcount}</span>
                </div>
            `;
            popularList.appendChild(li);
        });
        popularList.dataset.loaded = 'true';
    }

    function applyFiltersAndSort() {
        // 1. Filter
        let temp = allPosts.filter(post => {
            // Category
            if (currentCategory) {
                const cats = post.category_ids ? `,${post.category_ids},` : '';
                if (!cats.includes(`,${currentCategory},`)) return false;
            }
            // Search
            if (currentSearch) {
                if (!post.title.toLowerCase().includes(currentSearch)) return false;
            }
            return true;
        });

        // 2. Sort
        temp.sort((a, b) => {
            switch (currentSort) {
                case 'date_asc': return a.timestamp - b.timestamp;
                case 'date_desc': return b.timestamp - a.timestamp;
                case 'views_asc': return a.viewcount - b.viewcount;
                case 'views_desc': return b.viewcount - a.viewcount;
                default: return b.timestamp - a.timestamp;
            }
        });

        // 3. Handle Featured Post Logic
        const isDefaultView = currentSort === 'date_desc' && !currentCategory && !currentSearch;
        
        if (isDefaultView && allPosts.length > 0) {
            // Show Featured Section
            renderFeaturedPost(allPosts[0]);
            featuredContainer.classList.remove('hidden');
            if (typeof gsap !== 'undefined') {
                gsap.to(featuredContainer, { height: 'auto', opacity: 1, duration: 0.4 });
            } else {
                featuredContainer.style.display = 'block';
            }

            // Remove the featured post (latest) from the grid list
            const featuredId = allPosts[0].idBlog_Post;
            filteredPosts = temp.filter(p => p.idBlog_Post !== featuredId);
        } else {
            // Hide Featured Section
            if (typeof gsap !== 'undefined') {
                gsap.to(featuredContainer, { height: 0, opacity: 0, duration: 0.4, onComplete: () => {
                    featuredContainer.classList.add('hidden');
                }});
            } else {
                featuredContainer.classList.add('hidden');
                featuredContainer.style.display = 'none';
            }
            filteredPosts = temp;
        }

        // Reset Pagination
        visibleCount = 4;
        renderVisiblePosts(false);
    }

    function renderFeaturedPost(post) {
        const getImgPath = (path) => {
            if (!path) return '/img/blogplaceholder/blog_placeholder_2.jpg';
            if (path.startsWith('C:')) return '/img/blogplaceholder/blog_placeholder_2.jpg';
            if (path.startsWith('http')) return path;
            if (path.startsWith('/')) return path;
            return '/' + path;
        };

        featuredContainer.innerHTML = `
            <article class="featured-post-card" id="featured-post-static">
                <div class="card-image-container">
                    <img src="${getImgPath(post.picture_path)}" alt="${post.title}" loading="lazy" onload="this.classList.add('img-loaded')">
                </div>
                <div class="card-content">
                    <span class="card-category">${post.category_names || 'Opus in te'}</span>
                    <h2 class="card-title"><a href="BlogPost.php?id=${post.idBlog_Post}">${post.title}</a></h2>
                    <p class="card-excerpt">${post.excerpt}</p>
                    <a href="BlogPost.php?id=${post.idBlog_Post}" class="read-more-link">Pročitaj više →</a>
                </div>
            </article>
        `;
    }

    function renderVisiblePosts(append = false) {
        const postsToShow = filteredPosts.slice(0, visibleCount);
        
        // Helper to fix image paths
        const getImgPath = (path) => {
            if (!path) return '/img/blogplaceholder/blog_placeholder_2.jpg';
            if (path.startsWith('C:')) return '/img/blogplaceholder/blog_placeholder_2.jpg';
            if (path.startsWith('http')) return path;
            if (path.startsWith('/')) return path;
            return '/' + path;
        };

        if (!append) {
            // Clear Grid
            if (iso) {
                iso.destroy();
                iso = null;
            }
            blogGrid.innerHTML = '';
            
            if (postsToShow.length === 0) {
                blogGrid.innerHTML = '<p>Nema pronađenih članaka.</p>';
                loadMoreBtnContainer.style.display = 'none';
                return;
            }
        }

        // Determine which items are new (for appending)
        let newItems = [];
        const startIndex = append ? visibleCount - LOAD_STEP : 0;
        const itemsToRender = filteredPosts.slice(startIndex, visibleCount);

        itemsToRender.forEach(post => {
            const wrapper = document.createElement('div');
            wrapper.className = 'grid-item';
            
            wrapper.innerHTML = `
                <article class="blog-card">
                    <div class="card-image-container">
                        <img src="${getImgPath(post.picture_path)}" alt="${post.title}" loading="lazy" onload="this.classList.add('img-loaded')">
                    </div>
                    <div class="card-content">
                        <span class="card-category">${post.category_names || 'Opus in te'}</span>
                        <h3 class="card-title"><a href="BlogPost.php?id=${post.idBlog_Post}">${post.title}</a></h3>
                        <a href="BlogPost.php?id=${post.idBlog_Post}" class="read-more-link">Pročitaj više →</a>
                    </div>
                </article>
            `;
            
            blogGrid.appendChild(wrapper);
            newItems.push(wrapper);
        });

        // Initialize or Update Isotope
        if (!iso) {
            iso = new Isotope(blogGrid, {
                itemSelector: '.grid-item',
                layoutMode: 'masonry',
                percentPosition: true
            });
        } else {
            if (newItems.length > 0) {
                iso.appended(newItems);
            }
            iso.layout();
        }

        // Update Load More Button Visibility
        if (visibleCount >= filteredPosts.length) {
            loadMoreBtnContainer.style.display = 'none';
        } else {
            loadMoreBtnContainer.style.display = 'block';
        }
    }

    async function openPost(id) {
        // Fetch full post details
        try {
            const response = await fetch(`/backend/get_blog_post.php?id=${id}`);
            const post = await response.json();

            if (post.error) {
                console.error(post.error);
                return;
            }

            // Increment View Count (Backend)
            fetch('/backend/increment_view.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            }).catch(err => console.error('Error incrementing view:', err));

            // Populate Data
            detailImg.src = post.picture_path || 'img/blogplaceholder/blog_placeholder_2.jpg';
            detailCategory.textContent = post.category_names || 'Opus in te';
            detailTitle.textContent = post.title;
            
            // Format Date
            const dateObj = new Date(post.date);
            detailDate.textContent = dateObj.toLocaleDateString('bs-BA');
            
            // Optimistic view count update (or use fetched one)
            detailViews.textContent = parseInt(post.viewcount) + 1;
            detailContent.innerHTML = post.contents;

            // Lock container height to prevent footer jump
            blogContainer.style.minHeight = `${blogContainer.offsetHeight}px`;

            // Switch View with GSAP
            const tl = gsap.timeline({
                onComplete: () => {
                    // Release height lock
                    blogContainer.style.minHeight = '';
                }
            });

            // 1. Fade out grid
            tl.to([blogPostsMain, blogSidebar], {
                opacity: 0,
                y: -20,
                duration: 0.3,
                onComplete: () => {
                    blogPostsMain.classList.add('hidden');
                    blogSidebar.classList.add('hidden');
                    blogContainer.classList.add('detail-active');
                    
                    // Prepare detail view
                    blogDetailView.classList.remove('hidden');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            })
            // 2. Fade in detail view
            .fromTo(blogDetailView, 
                { opacity: 0, y: 30 },
                { opacity: 1, y: 0, duration: 0.6, ease: "power3.out" }
            );

        } catch (error) {
            console.error('Error opening post:', error);
        }
    }

    function closePost() {
        // Lock container height to prevent footer jump
        blogContainer.style.minHeight = `${blogContainer.offsetHeight}px`;

        const tl = gsap.timeline({
            onComplete: () => {
                // Release height lock
                blogContainer.style.minHeight = '';
            }
        });

        // 1. Fade out detail view
        tl.to(blogDetailView, {
            opacity: 0,
            y: 30,
            duration: 0.3,
            onComplete: () => {
                blogDetailView.classList.add('hidden');
                blogContainer.classList.remove('detail-active');
                
                // Prepare grid
                blogPostsMain.classList.remove('hidden');
                blogSidebar.classList.remove('hidden');
                
                // Re-layout Isotope
                if (iso) iso.layout();
            }
        })
        // 2. Fade in grid
        .fromTo([blogPostsMain, blogSidebar], 
            { opacity: 0, y: -20 },
            { opacity: 1, y: 0, duration: 0.5, ease: "power2.out" }
        );
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('mobile-sidebar-toggle');
    const sidebar = document.getElementById('blog-sidebar');
    
    // Create an overlay element dynamically
    const overlay = document.createElement('div');
    overlay.className = 'mobile-nav-overlay'; // Reusing your existing overlay class
    document.body.appendChild(overlay);

    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    });

    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = ''; 
    });
});