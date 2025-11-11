/**
 * Hobbly - Search App
 * JavaScript for search functionality with infinite scroll
 */

// Configuration
const CONFIG = {
    API_BASE_URL: 'api',
    POSTS_PER_PAGE: 10,
    SEARCH_DEBOUNCE_MS: 500,
    IMAGE_PLACEHOLDER: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"%3E%3Crect fill="%23f0f0f0" width="400" height="300"/%3E%3Ctext fill="%23999" font-family="Arial" font-size="20" x="50%25" y="50%25" text-anchor="middle" dominant-baseline="middle"%3ENo Image%3C/text%3E%3C/svg%3E'
};

// State management
let state = {
    posts: [],
    offset: 0,
    loading: false,
    hasMore: true,
    searchQuery: '',
    observer: null
};

// DOM Elements
const elements = {
    searchInput: document.getElementById('search-input'),
    clearSearch: document.getElementById('clear-search'),
    emptyState: document.getElementById('empty-state'),
    noResults: document.getElementById('no-results'),
    loadingSpinner: document.getElementById('search-loading-spinner'),
    searchResultsList: document.getElementById('search-results-list'),
    loadMoreTrigger: document.getElementById('search-load-more-trigger'),
    modal: document.getElementById('announcement-modal'),
    modalBody: document.getElementById('modal-body'),
    modalClose: document.getElementById('modal-close'),
    modalOverlay: document.getElementById('modal-overlay')
};

/**
 * Initialize the application
 */
function init() {
    setupEventListeners();
    setupInfiniteScroll();
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Search input with debounce
    elements.searchInput.addEventListener('input', debounce(handleSearchInput, CONFIG.SEARCH_DEBOUNCE_MS));
    
    // Clear search button
    elements.clearSearch.addEventListener('click', clearSearch);
    
    // Modal close handlers
    elements.modalClose.addEventListener('click', closeModal);
    elements.modalOverlay.addEventListener('click', closeModal);
    
    // Keyboard accessibility
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && elements.modal.classList.contains('active')) {
            closeModal();
        }
    });
    
    elements.searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            clearSearch();
        }
    });
}

/**
 * Setup Intersection Observer for infinite scroll
 */
function setupInfiniteScroll() {
    const options = {
        root: null,
        rootMargin: '100px',
        threshold: 0.1
    };

    state.observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !state.loading && state.hasMore && state.searchQuery) {
                searchPosts();
            }
        });
    }, options);

    state.observer.observe(elements.loadMoreTrigger);
}

/**
 * Handle search input
 */
function handleSearchInput(e) {
    const query = e.target.value.trim();
    
    // Show/hide clear button
    elements.clearSearch.style.display = query ? 'flex' : 'none';
    
    // Reset state
    state.searchQuery = query;
    state.posts = [];
    state.offset = 0;
    state.hasMore = true;
    elements.searchResultsList.innerHTML = '';
    
    // Hide all states initially
    elements.emptyState.style.display = 'none';
    elements.noResults.style.display = 'none';
    
    if (!query) {
        // Show initial empty state
        elements.emptyState.style.display = 'flex';
        return;
    }
    
    // Perform search
    searchPosts();
}

/**
 * Clear search
 */
function clearSearch() {
    elements.searchInput.value = '';
    elements.clearSearch.style.display = 'none';
    state.searchQuery = '';
    state.posts = [];
    state.offset = 0;
    state.hasMore = true;
    elements.searchResultsList.innerHTML = '';
    elements.emptyState.style.display = 'flex';
    elements.noResults.style.display = 'none';
    elements.searchInput.focus();
}

/**
 * Search posts from API
 */
async function searchPosts() {
    if (state.loading || !state.hasMore || !state.searchQuery) return;
    
    state.loading = true;
    showLoading();
    
    try {
        const url = `${CONFIG.API_BASE_URL}/get_posts.php?offset=${state.offset}&limit=${CONFIG.POSTS_PER_PAGE}&search=${encodeURIComponent(state.searchQuery)}`;
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        if (data.posts && data.posts.length > 0) {
            state.posts = [...state.posts, ...data.posts];
            renderPosts(data.posts);
            state.offset += data.posts.length;
            
            // Check if there are more posts to load
            if (data.posts.length < CONFIG.POSTS_PER_PAGE) {
                state.hasMore = false;
            }
        } else {
            state.hasMore = false;
            
            if (state.posts.length === 0) {
                showNoResults();
            }
        }
    } catch (error) {
        console.error('Error searching posts:', error);
        showError('Failed to search events. Please try again later.');
    } finally {
        state.loading = false;
        hideLoading();
    }
}

/**
 * Check if an event has ended
 */
function isEventEnded(eventEndTimestamp) {
    if (!eventEndTimestamp) return false; // If no end time, don't hide
    
    const now = Date.now() / 1000; // Current time in seconds
    const eventEnd = eventEndTimestamp > 10000000000 ? eventEndTimestamp / 1000 : eventEndTimestamp;
    
    return eventEnd < now;
}

/**
 * Render posts to the DOM
 */
function renderPosts(posts) {
    const fragment = document.createDocumentFragment();

    
    
    posts.forEach(post => {
        if (isEventEnded(post.event_end)) {
            return;
        }
        const card = createAnnouncementCard(post);
        fragment.appendChild(card);
    });
    
    elements.searchResultsList.appendChild(fragment);
}

/**
 * Create announcement card element
 */
function createAnnouncementCard(post) {
    const card = document.createElement('article');
    card.className = 'announcement-card';
    card.setAttribute('role', 'button');
    card.setAttribute('tabindex', '0');
    card.setAttribute('aria-label', `View details for ${post.title}`);
    
    // Fix image path - prepend ../ if image path doesn't start with http or data:
    let imageUrl = post.image || CONFIG.IMAGE_PLACEHOLDER;
    if (imageUrl && !imageUrl.startsWith('http') && !imageUrl.startsWith('data:')) {
        imageUrl = '../' + imageUrl;
    }
    
    const orgName = post.organization?.name || 'Unknown Organization';
    
    // Parse tags
    const tags = parseTagsString(post.tags);
    const tagsHtml = tags.length > 0 
        ? `<div class="announcement-tags">${tags.map(tag => `<span class="tag">${escapeHtml(tag)}</span>`).join('')}</div>`
        : '';
    
    card.innerHTML = `
        <img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(post.title)}" class="announcement-image" loading="lazy">
        <div class="announcement-content">
            <h2 class="announcement-title">${escapeHtml(post.title)}</h2>
            <p class="announcement-org">${escapeHtml(orgName)}</p>
            <p class="announcement-description">${escapeHtml(post.shortDescription)}</p>
            ${tagsHtml}
        </div>
    `;
    
    // Add click handler
    card.addEventListener('click', () => openAnnouncementDetail(post.id));
    
    // Add keyboard handler
    card.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openAnnouncementDetail(post.id);
        }
    });
    
    return card;
}

/**
 * Open announcement detail modal
 */
async function openAnnouncementDetail(postId) {
    try {
        showLoading();
        
        const url = `${CONFIG.API_BASE_URL}/get_post_detail.php?id=${postId}`;
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        if (data.post) {
            renderAnnouncementDetail(data.post);
            elements.modal.classList.add('active');
            elements.modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            
            // Focus on close button for accessibility
            elements.modalClose.focus();
        }
    } catch (error) {
        console.error('Error loading post detail:', error);
        alert('Failed to load event details. Please try again.');
    } finally {
        hideLoading();
    }
}

/**
 * Render announcement detail in modal
 */
function renderAnnouncementDetail(post) {
    // Fix image path - prepend ../ if image path doesn't start with http or data:
    let imageUrl = post.image || CONFIG.IMAGE_PLACEHOLDER;
    if (imageUrl && !imageUrl.startsWith('http') && !imageUrl.startsWith('data:')) {
        imageUrl = '../' + imageUrl;
    }
    
    const orgName = post.organization?.name || 'Unknown Organization';
    const orgEmail = post.organization?.email || '';
    const orgPhone = post.organization?.phone || '';
    const orgAddress = post.organization?.address || '';
    
    const tags = parseTagsString(post.tags);
    const tagsHtml = tags.length > 0 
        ? `<div class="announcement-tags">${tags.map(tag => `<span class="tag">${escapeHtml(tag)}</span>`).join('')}</div>`
        : '';
    
    elements.modalBody.innerHTML = `
        <img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(post.title)}" class="modal-detail-image">
        
        <h2 id="modal-title" class="modal-detail-title">${escapeHtml(post.title)}</h2>
        
        <p class="modal-detail-org">📍 ${escapeHtml(orgName)}</p>
        
        <div class="modal-detail-description">
            <p>${escapeHtml(post.description)}</p>
        </div>
        
        ${tagsHtml}
        
        ${post.activityType ? `
            <div class="modal-detail-section">
                <h3>Activity Type</h3>
                <p>${escapeHtml(post.activityType)}</p>
            </div>
        ` : ''}
        
        ${post.category ? `
            <div class="modal-detail-section">
                <h3>Category</h3>
                <p>${escapeHtml(post.category)}</p>
            </div>
        ` : ''}
        
        ${post.address ? `
            <div class="modal-detail-section">
                <h3>Location</h3>
                <p>${escapeHtml(post.address)}</p>
            </div>
        ` : ''}
        
        ${post.url ? `
            <div class="modal-detail-section">
                <h3>Website</h3>
                <p><a href="${escapeHtml(post.url)}" target="_blank" rel="noopener noreferrer">${escapeHtml(post.url)}</a></p>
            </div>
        ` : ''}
        
        <div class="modal-detail-section">
            <h3>Organization Contact</h3>
            <div class="contact-info">
                <div class="contact-item">
                    <strong>Name:</strong>
                    <span>${escapeHtml(orgName)}</span>
                </div>
                ${orgEmail ? `
                    <div class="contact-item">
                        <strong>Email:</strong>
                        <a href="mailto:${escapeHtml(orgEmail)}">${escapeHtml(orgEmail)}</a>
                    </div>
                ` : ''}
                ${orgPhone ? `
                    <div class="contact-item">
                        <strong>Phone:</strong>
                        <a href="tel:${escapeHtml(orgPhone)}">${escapeHtml(orgPhone)}</a>
                    </div>
                ` : ''}
                ${orgAddress ? `
                    <div class="contact-item">
                        <strong>Address:</strong>
                        <span>${escapeHtml(orgAddress)}</span>
                    </div>
                ` : ''}
            </div>
        </div>

        <!-- Modal Branding -->
        <div class="modal-branding">
            <span class="modal-branding-text">Powered by <img src="../images/logos/logo_primary@low-res.png" alt="Hobbly" class="modal-branding-logo"></span>
        </div>
    `;
}

/**
 * Close modal
 */
function closeModal() {
    elements.modal.classList.remove('active');
    elements.modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

/**
 * Show loading spinner
 */
function showLoading() {
    if (state.offset === 0 && state.searchQuery) {
        elements.loadingSpinner.style.display = 'flex';
        elements.emptyState.style.display = 'none';
        elements.noResults.style.display = 'none';
    }
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    elements.loadingSpinner.style.display = 'none';
}

/**
 * Show no results state
 */
function showNoResults() {
    elements.noResults.style.display = 'flex';
    elements.emptyState.style.display = 'none';
}

/**
 * Show error message
 */
function showError(message) {
    elements.searchResultsList.innerHTML = `
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
            </svg>
            <h2>Error</h2>
            <p>${escapeHtml(message)}</p>
        </div>
    `;
}

/**
 * Parse tags string to array
 */
function parseTagsString(tagsString) {
    if (!tagsString) return [];
    
    // Handle comma-separated tags
    return tagsString.split(',')
        .map(tag => tag.trim())
        .filter(tag => tag.length > 0)
        .slice(0, 5); // Limit to 5 tags for display
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Debounce function for performance
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize app when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
