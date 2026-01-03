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
    const detailMetaGrid = document.getElementById('detailMetaGrid');
    const detailContent = document.getElementById('detailContent');
    const editBlogBtn = document.getElementById('editBlogBtn');
    const deleteBlogBtn = document.getElementById('deleteBlogBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const editHelperText = document.getElementById('editHelperText');

    // Delete modal elements
    const deleteBlogModal = document.getElementById('deleteBlogModal');
    const confirmDeleteBlog = document.getElementById('confirmDeleteBlog');
    const cancelDeleteBlog = document.getElementById('cancelDeleteBlog');

    let filters = {
        search: '',
        categories: [],
        statuses: [],
        min_view_filter: 0
    };

    let isFirstLoad = true;
    let allCategories = [];
    let allStatuses = [];
    let allAuthors = null;
    let currentBlog = null;
    let isEditing = false;

    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

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
                allCategories = data.categories || [];
                allStatuses = data.statuses || [];
                renderFilters(allCategories, allStatuses);
                isFirstLoad = false;
            }

            updateSliderRange(data.range_min ?? 0, data.range_max ?? 0);
            renderBlogs(data.blogs || []);
        } catch (error) {
            console.error('Error fetching blogs:', error);
            if (typeof showNotification === 'function') {
                showNotification('Greška pri učitavanju blogova.', 'error');
            }
        } finally {
            releaseLoadingGate();
        }
    }

    function renderFilters(categories, statuses) {
        categoryFilters.innerHTML = categories.map(cat => `
            <label class="checkbox-label">
                <input type="checkbox" value="${cat.idBlog_Post_Category}" class="category-checkbox">
                ${cat.name}
            </label>
        `).join('');

        statusFilters.innerHTML = statuses.map(status => `
            <label class="checkbox-label">
                <input type="checkbox" value="${status.idBlog_Post_Status}" class="status-checkbox">
                ${status.name}
            </label>
        `).join('');

        document.querySelectorAll('.category-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const checked = Array.from(document.querySelectorAll('.category-checkbox:checked')).map(c => c.value);
                filters.categories = checked;
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
        viewCountSlider.min = min;
        viewCountSlider.max = max;

        minViewLabel.textContent = min;
        maxViewLabel.textContent = max;

        const sliderValue = parseInt(viewCountSlider.value, 10);
        if (sliderValue < min) {
            viewCountSlider.value = min;
            filters.min_view_filter = min;
        }
        if (sliderValue > max) {
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
                        <span class="blog-status status-${blog.status_name.toLowerCase().replace(/\s+/g, '-')}">${blog.status_name}</span>
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

            if (blog.success === false) {
                throw new Error(blog.message || 'Greška pri učitavanju objave');
            }

            currentBlog = blog;
            isEditing = false;
            toggleActionButtons();
            renderDetail(blog);

            blogLayout.style.opacity = '0';
            setTimeout(() => {
                blogLayout.style.display = 'none';
                blogDetailView.style.display = 'block';
                void blogDetailView.offsetWidth;
                blogDetailView.classList.add('active');
            }, 300);
        } catch (error) {
            console.error('Error fetching blog details:', error);
            if (typeof showNotification === 'function') {
                showNotification('Nismo mogli učitati detalje objave.', 'error');
            }
        }
    }

    function renderDetail(blog) {
        if (!blog) return;

        detailImage.src = blog.picture_path || 'img/blogplaceholder/default.jpg';
        if (isEditing) {
            detailTitle.innerHTML = `<input id="editTitleInput" class="blog-editable-input" value="${escapeHtml(blog.title)}" />`;
        } else {
            detailTitle.textContent = blog.title;
        }

        renderMeta(blog);
        renderContent(blog);
    }

    function renderMeta(blog) {
        if (!isEditing) {
            detailMetaGrid.innerHTML = `
                <div class="meta-item">
                    <i class="fa-solid fa-user"></i>
                    <span>${blog.author_name} ${blog.author_lastname}</span>
                </div>
                <div class="meta-item">
                    <i class="fa-solid fa-calendar"></i>
                    <span>${blog.date}</span>
                </div>
                <div class="meta-item">
                    <i class="fa-solid fa-eye"></i>
                    <span>${blog.viewcount}</span>
                </div>
                <div class="meta-item">
                    <i class="fa-solid fa-tag"></i>
                    <span>${blog.category_names || 'Nema kategorije'}</span>
                </div>
                <div class="meta-item">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>${blog.status_name}</span>
                </div>
            `;
            return;
        }

        const authorOptionButtons = (allAuthors || []).map(author => `
            <button type="button" class="meta-option ${author.idUser === blog.author_id ? 'active' : ''}" data-value="${author.idUser}">
                ${author.name} ${author.last_name}
            </button>
        `).join('');

        const statusOptionButtons = allStatuses.map(status => `
            <button type="button" class="meta-option ${status.idBlog_Post_Status === blog.status_id ? 'active' : ''}" data-value="${status.idBlog_Post_Status}">
                ${status.name}
            </button>
        `).join('');

        const categoryChips = allCategories.map(cat => {
            const isChecked = (blog.category_ids || []).includes(parseInt(cat.idBlog_Post_Category, 10));
            return `
                <label class="category-chip ${isChecked ? 'active' : ''}">
                    <input type="checkbox" value="${cat.idBlog_Post_Category}" ${isChecked ? 'checked' : ''} />
                    ${cat.name}
                </label>
            `;
        }).join('');

        detailMetaGrid.innerHTML = `
            <div class="meta-item editable meta-collapsible" data-field="author">
                <button class="meta-collapsible-header" type="button">
                    <span class="header-icon"><i class="fa-solid fa-user"></i></span>
                    <span class="header-label">Autor</span>
                    <span class="header-value">${blog.author_name} ${blog.author_lastname}</span>
                    <span class="chevron"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="meta-collapsible-body">
                    <div class="meta-edit-block">
                        <input type="hidden" id="editAuthorValue" value="${blog.author_id}">
                        <div class="meta-option-list" id="editAuthorOptions">${authorOptionButtons}</div>
                    </div>
                </div>
            </div>
            <div class="meta-item editable meta-collapsible" data-field="status">
                <button class="meta-collapsible-header" type="button">
                    <span class="header-icon"><i class="fa-solid fa-circle-info"></i></span>
                    <span class="header-label">Status</span>
                    <span class="header-value">${blog.status_name}</span>
                    <span class="chevron"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="meta-collapsible-body">
                    <div class="meta-edit-block">
                        <input type="hidden" id="editStatusValue" value="${blog.status_id}">
                        <div class="meta-option-list" id="editStatusOptions">${statusOptionButtons}</div>
                    </div>
                </div>
            </div>
            <div class="meta-item editable meta-categories meta-collapsible" data-field="categories">
                <button class="meta-collapsible-header" type="button">
                    <span class="header-icon"><i class="fa-solid fa-tag"></i></span>
                    <span class="header-label">Kategorije</span>
                    <span class="header-value">${blog.category_names || 'Nema kategorije'}</span>
                    <span class="chevron"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="meta-collapsible-body">
                    <div class="meta-edit-block">
                        <div class="category-chip-group">${categoryChips}</div>
                    </div>
                </div>
            </div>
            <div class="meta-item">
                <i class="fa-solid fa-eye"></i>
                <span>${blog.viewcount}</span>
            </div>
            <div class="meta-item">
                <i class="fa-solid fa-calendar"></i>
                <span>${blog.date}</span>
            </div>
        `;

        attachCategoryChipListeners();
        attachCollapsibleListeners();
        attachOptionListListeners();
    }

    function renderContent(blog) {
        if (isEditing) {
            detailContent.innerHTML = `<div id="editContent" class="blog-editable-area" contenteditable="true">${blog.contents}</div>`;
        } else {
            detailContent.innerHTML = blog.contents;
        }
    }

    async function ensureAuthorsLoaded() {
        if (allAuthors !== null) return;
        try {
            const response = await fetch('backend/admin_fetch_blog_authors.php');
            const data = await response.json();
            if (data.success) {
                allAuthors = data.authors;
            } else {
                throw new Error(data.message || 'Greška pri učitavanju autora');
            }
        } catch (error) {
            console.error('Error fetching authors:', error);
            if (typeof showNotification === 'function') {
                showNotification('Nismo mogli učitati listu autora.', 'error');
            }
            allAuthors = [];
        }
    }

    function toggleActionButtons() {
        if (isEditing) {
            editBlogBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Sačuvaj';
            deleteBlogBtn.classList.add('disabled');
            deleteBlogBtn.setAttribute('disabled', 'disabled');
            cancelEditBtn.style.display = 'inline-flex';
            editHelperText.style.display = 'block';
        } else {
            editBlogBtn.innerHTML = '<i class="fa-solid fa-pen"></i> Uredi';
            deleteBlogBtn.classList.remove('disabled');
            deleteBlogBtn.removeAttribute('disabled');
            cancelEditBtn.style.display = 'none';
            editHelperText.style.display = 'none';
        }
    }

    async function saveBlogChanges() {
        const titleInput = document.getElementById('editTitleInput');
        const authorValueInput = document.getElementById('editAuthorValue');
        const statusValueInput = document.getElementById('editStatusValue');
        const contentEditable = document.getElementById('editContent');
        const categoryChecks = Array.from(document.querySelectorAll('.category-chip input:checked'));

        const payload = {
            id: currentBlog.idBlog_Post,
            title: titleInput ? titleInput.value.trim() : currentBlog.title,
            contents: contentEditable ? contentEditable.innerHTML : currentBlog.contents,
            author_id: authorValueInput ? parseInt(authorValueInput.value, 10) : currentBlog.author_id,
            status_id: statusValueInput ? parseInt(statusValueInput.value, 10) : currentBlog.status_id,
            category_ids: categoryChecks.map(cb => parseInt(cb.value, 10))
        };

        if (!payload.title || !payload.contents) {
            if (typeof showNotification === 'function') {
                showNotification('Naslov i sadržaj su obavezni.', 'error');
            }
            return;
        }

        try {
            const response = await fetch('backend/admin_update_blog.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Greška pri čuvanju');
            }

            const selectedAuthor = (allAuthors || []).find(a => a.idUser === payload.author_id);
            const selectedStatus = (allStatuses || []).find(s => s.idBlog_Post_Status === payload.status_id);
            const selectedCategories = (allCategories || []).filter(cat => payload.category_ids.includes(parseInt(cat.idBlog_Post_Category, 10)));

            currentBlog = {
                ...currentBlog,
                title: payload.title,
                contents: payload.contents,
                author_id: payload.author_id,
                status_id: payload.status_id,
                category_ids: payload.category_ids,
                author_name: selectedAuthor ? selectedAuthor.name : currentBlog.author_name,
                author_lastname: selectedAuthor ? selectedAuthor.last_name : currentBlog.author_lastname,
                status_name: selectedStatus ? selectedStatus.name : currentBlog.status_name,
                category_names: selectedCategories.length ? selectedCategories.map(c => c.name).join(', ') : 'Nema kategorije'
            };

            isEditing = false;
            toggleActionButtons();
            renderDetail(currentBlog);
            fetchBlogs();

            if (typeof showNotification === 'function') {
                showNotification('Objava je sačuvana.', 'success');
            }
        } catch (error) {
            console.error('Error saving blog:', error);
            if (typeof showNotification === 'function') {
                showNotification(error.message || 'Greška pri čuvanju.', 'error');
            }
        }
    }

    function closeDeleteModal() {
        deleteBlogModal.classList.remove('active');
    }

    async function performDelete() {
        if (!currentBlog) return;
        try {
            const response = await fetch('backend/admin_delete_blog.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: currentBlog.idBlog_Post })
            });
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Greška pri brisanju');
            }

            closeDeleteModal();
            blogDetailView.classList.remove('active');
            setTimeout(() => {
                blogDetailView.style.display = 'none';
                blogLayout.style.display = 'flex';
                void blogLayout.offsetWidth;
                blogLayout.style.opacity = '1';
            }, 300);

            fetchBlogs();

            if (typeof showNotification === 'function') {
                showNotification('Objava je obrisana.', 'success');
            }
        } catch (error) {
            console.error('Error deleting blog:', error);
            if (typeof showNotification === 'function') {
                showNotification(error.message || 'Greška pri brisanju.', 'error');
            }
        }
    }

    backToGridBtn.addEventListener('click', () => {
        isEditing = false;
        toggleActionButtons();
        blogDetailView.classList.remove('active');
        setTimeout(() => {
            blogDetailView.style.display = 'none';
            blogLayout.style.display = 'flex';
            void blogLayout.offsetWidth;
            blogLayout.style.opacity = '1';
        }, 300);
    });

    editBlogBtn.addEventListener('click', async () => {
        if (!currentBlog) return;
        if (!isEditing) {
            await ensureAuthorsLoaded();
            isEditing = true;
            toggleActionButtons();
            renderDetail(currentBlog);
        } else {
            saveBlogChanges();
        }
    });

    cancelEditBtn.addEventListener('click', () => {
        if (!currentBlog) return;
        isEditing = false;
        toggleActionButtons();
        renderDetail(currentBlog);
    });

    deleteBlogBtn.addEventListener('click', () => {
        if (!currentBlog || isEditing) return;
        deleteBlogModal.classList.add('active');
    });

    confirmDeleteBlog.addEventListener('click', performDelete);
    cancelDeleteBlog.addEventListener('click', closeDeleteModal);
    deleteBlogModal.addEventListener('click', (e) => {
        if (e.target === deleteBlogModal) closeDeleteModal();
    });

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
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => (cb.checked = false));
        fetchBlogs();
    });

    fetchBlogs();

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function attachCategoryChipListeners() {
        const chips = detailMetaGrid.querySelectorAll('.category-chip input');
        const headerValue = detailMetaGrid.querySelector('[data-field="categories"] .header-value');

        const updateHeader = () => {
            const selectedIds = Array.from(chips)
                .filter(cb => cb.checked)
                .map(cb => parseInt(cb.value, 10));
            const names = (allCategories || [])
                .filter(cat => selectedIds.includes(parseInt(cat.idBlog_Post_Category, 10)))
                .map(cat => cat.name);
            if (headerValue) {
                headerValue.textContent = names.length ? names.join(', ') : 'Nema kategorije';
            }
        };

        chips.forEach(input => {
            input.addEventListener('change', (e) => {
                const chip = e.target.closest('.category-chip');
                if (chip) {
                    chip.classList.toggle('active', e.target.checked);
                }
                updateHeader();
            });
        });

        updateHeader();
    }

    let collapsibleOutsideHandlerAttached = false;

    function attachCollapsibleListeners() {
        const blocks = detailMetaGrid.querySelectorAll('.meta-collapsible');

        const closeAll = () => {
            blocks.forEach(b => b.classList.remove('open'));
        };

        blocks.forEach(block => {
            const header = block.querySelector('.meta-collapsible-header');
            if (!header) return;

            header.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = block.classList.contains('open');
                closeAll();
                if (!isOpen) {
                    block.classList.add('open');
                }
            });
        });

        if (!collapsibleOutsideHandlerAttached) {
            document.addEventListener('click', (e) => {
                if (!detailMetaGrid.contains(e.target)) {
                    detailMetaGrid.querySelectorAll('.meta-collapsible.open').forEach(b => b.classList.remove('open'));
                }
            });
            collapsibleOutsideHandlerAttached = true;
        }
    }

    function attachOptionListListeners() {
        const authorOptions = document.querySelectorAll('#editAuthorOptions .meta-option');
        const statusOptions = document.querySelectorAll('#editStatusOptions .meta-option');
        const authorValue = document.getElementById('editAuthorValue');
        const statusValue = document.getElementById('editStatusValue');
        const authorHeaderValue = detailMetaGrid.querySelector('[data-field="author"] .header-value');
        const statusHeaderValue = detailMetaGrid.querySelector('[data-field="status"] .header-value');

        authorOptions.forEach(btn => {
            btn.addEventListener('click', () => {
                authorOptions.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                if (authorValue) authorValue.value = btn.dataset.value;
                if (authorHeaderValue) authorHeaderValue.textContent = btn.textContent.trim();
                const block = detailMetaGrid.querySelector('[data-field="author"]');
                if (block) block.classList.remove('open');
            });
        });

        statusOptions.forEach(btn => {
            btn.addEventListener('click', () => {
                statusOptions.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                if (statusValue) statusValue.value = btn.dataset.value;
                if (statusHeaderValue) statusHeaderValue.textContent = btn.textContent.trim();
                const block = detailMetaGrid.querySelector('[data-field="status"]');
                if (block) block.classList.remove('open');
            });
        });
    }
});
