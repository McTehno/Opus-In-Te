document.addEventListener('DOMContentLoaded', () => {
    let resolveLoadingGate;
    let loadingGateReleased = false;
    const loadingGate = new Promise((resolve) => {
        resolveLoadingGate = resolve;
    });
    window.loadingScreenGate = () => loadingGate;

    const releaseLoadingGate = () => {
        if (loadingGateReleased) return;
        loadingGateReleased = true;
        resolveLoadingGate();
    };

    const searchInput = document.getElementById('searchInput');
    const categoryFilters = document.getElementById('categoryFilters');
    const statusFilters = document.getElementById('statusFilters');
    const viewCountSlider = document.getElementById('viewCountSlider');
    const minViewLabel = document.getElementById('minViewLabel');
    const maxViewLabel = document.getElementById('maxViewLabel');
    const currentViewLabel = document.getElementById('currentViewLabel');
    const blogGrid = document.getElementById('blogGrid');
    const noResults = document.getElementById('noResults');
    const resetBtn = document.getElementById('resetFilters');
    const toggleFiltersBtn = document.getElementById('toggleFilters');
    const filtersSidebar = document.getElementById('filtersSidebar');

    // Toggle Filters on Mobile
    if (toggleFiltersBtn) {
        toggleFiltersBtn.addEventListener('click', () => {
            filtersSidebar.classList.toggle('active');
        });
    }

    // View Elements
    const blogLayout = document.querySelector('.blog-layout');
    const blogDetailView = document.getElementById('blogDetailView');
    const backToGridBtn = document.getElementById('backToGridBtn');

    // Detail Elements
    const detailTitle = document.getElementById('detailTitle');
    const detailImage = document.getElementById('detailImage');
    const detailAuthor = document.getElementById('detailAuthor');
    const detailDate = document.getElementById('detailDate');
    const detailViews = document.getElementById('detailViews');
    const detailCategories = document.getElementById('detailCategories');
    const detailStatus = document.getElementById('detailStatus');
    const detailContent = document.getElementById('detailContent');

    let filters = {
        search: '',
        categories: [],
        statuses: [],
        min_view_filter: 0
    };

    let isFirstLoad = true;

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Fetch Data
    async function fetchBlogs() {
        const params = new URLSearchParams();
        if (filters.search) params.append('search', filters.search);
        if (filters.categories.length) params.append('categories', filters.categories.join(','));
        if (filters.statuses.length) params.append('statuses', filters.statuses.join(','));
        params.append('min_view_filter', filters.min_view_filter);

        try {
            const response = await fetch(`backend/admin_fetch_blogs.php?${params.toString()}`);
            const data = await response.json();

            if (isFirstLoad) {
                renderFilters(data.categories, data.statuses);
                isFirstLoad = false;
            }

            updateSliderRange(data.range_min, data.range_max);
            renderBlogs(data.blogs);

        } catch (error) {
            console.error('Error fetching blogs:', error);
        } finally {
            releaseLoadingGate();
        }
    }

    function renderFilters(categories, statuses) {
        // Categories
        categoryFilters.innerHTML = categories.map(cat => `
            <label class="checkbox-label">
                <input type="checkbox" value="${cat.idBlog_Post_Category}" class="category-checkbox">
                ${cat.name}
            </label>
        `).join('');

        // Statuses
        statusFilters.innerHTML = statuses.map(status => `
            <label class="checkbox-label">
                <input type="checkbox" value="${status.idBlog_Post_Status}" class="status-checkbox">
                ${status.name}
            </label>
        `).join('');

        // Add listeners to new checkboxes
        document.querySelectorAll('.category-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const checked = Array.from(document.querySelectorAll('.category-checkbox:checked')).map(c => c.value);
                filters.categories = checked;
                // Reset slider filter when category changes? 
                // Maybe not, but the range will change.
                fetchBlogs();
            });
        });

        document.querySelectorAll('.status-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const checked = Array.from(document.querySelectorAll('.status-checkbox:checked')).map(c => c.value);
                filters.statuses = checked;
                fetchBlogs();
            });
        });
    }

    function updateSliderRange(min, max) {
        // Update slider attributes
        viewCountSlider.min = min;
        viewCountSlider.max = max;
        
        minViewLabel.textContent = min;
        maxViewLabel.textContent = max;

        // If current filter value is less than new min, bump it up
        if (parseInt(viewCountSlider.value) < min) {
            viewCountSlider.value = min;
            filters.min_view_filter = min;
        }
        // If current filter value is more than new max, clamp it down
        if (parseInt(viewCountSlider.value) > max) {
            viewCountSlider.value = max;
            filters.min_view_filter = max;
        }

        currentViewLabel.textContent = viewCountSlider.value + '+';
    }

    function renderBlogs(blogs) {
        blogGrid.innerHTML = '';
        if (blogs.length === 0) {
            noResults.style.display = 'block';
            return;
        }
        noResults.style.display = 'none';

        blogs.forEach(blog => {
            const card = document.createElement('div');
            card.className = 'blog-card';
            card.innerHTML = `
                <div class="blog-card-image" style="background-image: url('${blog.picture_path || 'img/blogplaceholder/default.jpg'}');"></div>
                <div class="blog-card-content">
                    <div class="blog-card-header">
                        <span class="blog-category">${blog.category_names || 'Nema kategorije'}</span>
                        <span class="blog-status status-${blog.status_name.toLowerCase()}">${blog.status_name}</span>
                    </div>
                    <h3 class="blog-title">${blog.title}</h3>
                    <div class="blog-meta">
                        <span><i class="fa-solid fa-user"></i> ${blog.author_name} ${blog.author_lastname}</span>
                        <span><i class="fa-solid fa-eye"></i> ${blog.viewcount}</span>
                    </div>
                </div>
            `;
            card.addEventListener('click', () => showBlogDetail(blog.idBlog_Post));
            blogGrid.appendChild(card);
        });
    }

    async function showBlogDetail(id) {
        try {
            const response = await fetch(`backend/admin_get_blog.php?id=${id}`);
            const blog = await response.json();

            detailTitle.textContent = blog.title;
            detailImage.src = blog.picture_path || 'img/blogplaceholder/default.jpg';
            detailAuthor.textContent = `${blog.author_name} ${blog.author_lastname}`;
            detailDate.textContent = blog.date;
            detailViews.textContent = blog.viewcount;
            detailCategories.textContent = blog.category_names;
            detailStatus.textContent = blog.status_name;
            detailContent.innerHTML = blog.contents;

            // Transition
            blogLayout.style.opacity = '0';
            setTimeout(() => {
                blogLayout.style.display = 'none';
                blogDetailView.style.display = 'block';
                // Trigger reflow
                void blogDetailView.offsetWidth;
                blogDetailView.classList.add('active');
            }, 300);

        } catch (error) {
            console.error('Error fetching blog details:', error);
        }
    }

    backToGridBtn.addEventListener('click', () => {
        blogDetailView.classList.remove('active');
        setTimeout(() => {
            blogDetailView.style.display = 'none';
            blogLayout.style.display = 'flex';
            // Trigger reflow
            void blogLayout.offsetWidth;
            blogLayout.style.opacity = '1';
        }, 300);
    });

    // Event Listeners
    searchInput.addEventListener('input', debounce((e) => {
        filters.search = e.target.value;
        fetchBlogs();
    }, 300));

    viewCountSlider.addEventListener('input', (e) => {
        currentViewLabel.textContent = e.target.value + '+';
    });

    viewCountSlider.addEventListener('change', (e) => {
        filters.min_view_filter = e.target.value;
        fetchBlogs();
    });

    resetBtn.addEventListener('click', () => {
        filters = {
            search: '',
            categories: [],
            statuses: [],
            min_view_filter: 0
        };
        searchInput.value = '';
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        // Slider will be reset by updateSliderRange on fetch
        fetchBlogs();
    });

    // Initial Fetch
    fetchBlogs();
});
