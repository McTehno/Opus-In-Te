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
    let currentCategory = null;
    let currentSearch = '';
    let currentSort = 'date_desc';

    // Initial Fetch
    fetchPosts();

    // Event Listeners
    if (backToBlogBtn) {
        backToBlogBtn.addEventListener('click', closePost);
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
            
            updateIsotope();
        });
    });

    searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        currentSearch = searchInput.value.trim().toLowerCase();
        updateIsotope();
    });
    
    // Real-time search (optional, but nice)
    searchInput.addEventListener('input', (e) => {
        currentSearch = e.target.value.trim().toLowerCase();
        updateIsotope();
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
            renderAllPosts(data.posts);

        } catch (error) {
            console.error('Error fetching posts:', error);
        }
    }

    function renderCategories(categories) {
        categoriesList.innerHTML = '';
        
        // "All" category option
        const allLi = document.createElement('li');
        const allLink = document.createElement('a');
        allLink.href = '#';
        allLink.className = 'category-item active';
        allLink.innerHTML = `<span>Sve kategorije</span>`;
        allLink.addEventListener('click', (e) => {
            e.preventDefault();
            // Remove active class from all
            document.querySelectorAll('.category-item').forEach(el => el.classList.remove('active'));
            allLink.classList.add('active');
            
            currentCategory = null;
            updateIsotope();
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
                updateIsotope();
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
                    <img src="${post.picture_path || 'img/blogplaceholder/default.jpg'}" alt="${post.title}">
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

    function renderAllPosts(posts) {
        if (posts.length === 0) {
            blogGrid.innerHTML = '<p>Nema pronađenih članaka.</p>';
            return;
        }

        // Helper to fix image paths
        const getImgPath = (path) => {
            if (!path) return '/img/blogplaceholder/default.jpg';
            if (path.startsWith('C:')) return '/img/blogplaceholder/default.jpg'; // Fallback for local paths
            if (path.startsWith('http')) return path;
            if (path.startsWith('/')) return path;
            return '/' + path;
        };

        // 1. Render Featured Post (Static, outside Isotope)
        // We assume the first post in the list (sorted by date desc from backend) is the latest
        const latestPost = posts[0];
        
        featuredContainer.innerHTML = `
            <article class="featured-post-card" id="featured-post-static">
                <div class="card-image-container">
                    <img src="${getImgPath(latestPost.picture_path)}" alt="${latestPost.title}" loading="lazy" onload="this.classList.add('img-loaded')">
                </div>
                <div class="card-content">
                    <span class="card-category">${latestPost.category_names || 'Opus in te'}</span>
                    <h2 class="card-title"><a href="BlogPost.php?id=${latestPost.idBlog_Post}">${latestPost.title}</a></h2>
                    <p class="card-excerpt">${latestPost.excerpt}</p>
                    <a href="BlogPost.php?id=${latestPost.idBlog_Post}" class="read-more-link">Pročitaj više →</a>
                </div>
            </article>
        `;

        // 2. Render Grid Posts (ALL posts, including the latest one)
        // We mark the latest one so we can hide it in default view
        blogGrid.innerHTML = '';
        
        posts.forEach((post, index) => {
            // Create Wrapper for Isotope
            const wrapper = document.createElement('div');
            wrapper.className = 'grid-item';
            
            // Add data attributes for sorting/filtering to the WRAPPER
            wrapper.dataset.id = post.idBlog_Post;
            wrapper.dataset.timestamp = post.timestamp;
            wrapper.dataset.views = post.viewcount;
            wrapper.dataset.categories = post.category_ids ? `,${post.category_ids},` : ''; 
            wrapper.dataset.title = post.title.toLowerCase();
            
            if (index === 0) {
                wrapper.classList.add('is-latest');
            }

            // Create the actual Card
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
        });

        // 3. Initialize Isotope
        initIsotope();
    }

    function initIsotope() {
        if (typeof Isotope === 'undefined') {
            console.error('Isotope library not loaded.');
            return;
        }
        iso = new Isotope(blogGrid, {
            itemSelector: '.grid-item',
            layoutMode: 'fitRows', // or 'masonry'
            percentPosition: true,
            getSortData: {
                date: '[data-timestamp] parseInt',
                views: '[data-views] parseInt',
                title: '[data-title]'
            },
            // Initial filter: Hide the latest post (because it's shown in featured)
            filter: function(itemElem) {
                return !itemElem.classList.contains('is-latest');
            }
        });
    }

    function updateIsotope() {
        if (!iso) return;

        // Determine if we are in "Default View" (Date Desc, No Filters)
        const isDefaultView = currentSort === 'date_desc' && !currentCategory && !currentSearch;

        // Toggle Featured Container Visibility
        if (typeof gsap !== 'undefined') {
            if (isDefaultView) {
                featuredContainer.classList.remove('hidden');
                gsap.to(featuredContainer, { height: 'auto', opacity: 1, duration: 0.4 });
            } else {
                gsap.to(featuredContainer, { height: 0, opacity: 0, duration: 0.4, onComplete: () => {
                    featuredContainer.classList.add('hidden');
                }});
            }
        } else {
             // Fallback if GSAP missing
             if (isDefaultView) {
                featuredContainer.classList.remove('hidden');
                featuredContainer.style.display = 'block';
            } else {
                featuredContainer.classList.add('hidden');
                featuredContainer.style.display = 'none';
            }
        }

        // Configure Filter Function
        const filterFn = function(itemElem) {
            // 1. Check "Latest" logic
            // If default view, hide latest (it's in featured). Else show it.
            if (isDefaultView && itemElem.classList.contains('is-latest')) {
                return false;
            }

            // 2. Check Category
            if (currentCategory) {
                const cats = itemElem.dataset.categories;
                if (!cats.includes(`,${currentCategory},`)) {
                    return false;
                }
            }

            // 3. Check Search
            if (currentSearch) {
                const title = itemElem.dataset.title;
                if (!title.includes(currentSearch)) {
                    return false;
                }
            }

            return true;
        };

        // Configure Sort
        let sortValue = 'original-order'; // Default
        let sortAscending = false; // Default desc

        switch (currentSort) {
            case 'date_asc':
                sortValue = 'date';
                sortAscending = true;
                break;
            case 'date_desc':
                sortValue = 'date';
                sortAscending = false;
                break;
            case 'views_asc':
                sortValue = 'views';
                sortAscending = true;
                break;
            case 'views_desc':
                sortValue = 'views';
                sortAscending = false;
                break;
        }

        // Apply to Isotope
        iso.arrange({
            filter: filterFn,
            sortBy: sortValue,
            sortAscending: sortAscending
        });
    }

    function openPost(id) {
        const post = allPosts.find(p => p.idBlog_Post == id);
        if (!post) return;

        // Increment View Count (Backend)
        fetch('/backend/increment_view.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        }).catch(err => console.error('Error incrementing view:', err));

        // Optimistic Update (Frontend)
        post.viewcount = parseInt(post.viewcount) + 1;

        // Populate Data
        detailImg.src = post.picture_path || 'img/blogplaceholder/default.jpg';
        detailCategory.textContent = post.category_names || 'Opus in te';
        detailTitle.textContent = post.title;
        
        // Format Date
        const dateObj = new Date(post.date);
        detailDate.textContent = dateObj.toLocaleDateString('bs-BA');
        
        detailViews.textContent = post.viewcount;
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
