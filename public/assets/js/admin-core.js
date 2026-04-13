/**
 * ARS-e-commerce Admin Panel Core Interactivity
 */

// 0. Global CSRF Fetch Interceptor — automatically adds X-CSRF-Token to all POST requests
(function () {
    const _originalFetch = window.fetch;
    window.fetch = function (url, options = {}) {
        if (options.method && options.method.toUpperCase() === 'POST') {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (token) {
                options.headers = options.headers || {};
                options.headers['X-CSRF-Token'] = token;
            }
        }
        return _originalFetch.call(this, url, options);
    };
})();

/**
 * Safe API Fetch — wraps fetch() with Content-Type validation and JSON guard.
 * Returns {success, data, message} or throws with a user-friendly message.
 * Usage:  const json = await apiFetch('/api/dashboard/stats');
 *         const json = await apiFetch('/api/products', { method: 'POST', body: formData });
 */
async function apiFetch(url, options = {}) {
    // Prepend BASE_URL if the URL starts with /
    const fullUrl = url.startsWith('/') ? (window.BASE_URL || '') + url : url;
    
    const res = await fetch(fullUrl, options);
    
    // Guard: check Content-Type before parsing JSON
    const contentType = res.headers.get('content-type') || '';
    if (!contentType.includes('application/json')) {
        const text = await res.text();
        console.error(`[apiFetch] Non-JSON response from ${url}:`, text.substring(0, 500));
        throw new Error('Server returned an unexpected response. Please try again.');
    }
    
    const json = await res.json();
    return json;
}

// 1. Dark Mode Manager
class DarkModeManager {
    constructor() {
        this.prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        this.currentTheme = localStorage.getItem('theme') || (this.prefersDark ? 'dark' : 'light');
        this.toggleBtn = document.getElementById('theme-toggle');
        this.init();
    }

    init() {
        this.applyTheme(this.currentTheme);
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => this.toggle());
        }
    }

    toggle() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
        localStorage.setItem('theme', newTheme);
    }

    applyTheme(theme) {
        this.currentTheme = theme;
        document.documentElement.setAttribute('data-theme', theme);
        if (this.toggleBtn) {
            this.toggleBtn.innerHTML = theme === 'dark' ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
        }
    }
}

// 2. Sidebar Manager
class Sidebar {
    constructor() {
        this.sidebar = document.querySelector('.sidebar');
        this.main = document.querySelector('.main-content');
        this.toggleBtn = document.getElementById('sidebar-toggle');
        this.overlay = this._createOverlay();
        this.isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        this.isMobileOpen = false;

        if (this.sidebar && this.main) {
            this.init();
        }
    }

    _createOverlay() {
        const el = document.createElement('div');
        el.className = 'sidebar-overlay';
        document.body.appendChild(el);
        el.addEventListener('click', () => this.closeMobile());
        return el;
    }

    isMobile() { return window.innerWidth <= 768; }
    isTablet() { return window.innerWidth <= 1024 && window.innerWidth > 768; }

    init() {
        // Desktop: restore collapsed state
        if (!this.isMobile() && this.isCollapsed) {
            this.sidebar.classList.add('collapsed');
            this.main.classList.add('expanded');
        }

        // Tablet: auto-collapse on first visit
        if (this.isTablet() && localStorage.getItem('sidebar-collapsed') === null) {
            this.sidebar.classList.add('collapsed');
            this.main.classList.add('expanded');
            this.isCollapsed = true;
        }

        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => {
                this.isMobile() ? this.toggleMobile() : this.toggle();
            });
        }

        // Close drawer when a nav link is tapped on mobile
        this.sidebar.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                if (this.isMobile()) this.closeMobile();
            });
        });

        // Handle orientation change / resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => this._onResize(), 150);
        });
    }

    _onResize() {
        if (!this.isMobile()) {
            // Switched away from mobile — tear down mobile state
            this.sidebar.classList.remove('open');
            this.overlay.classList.remove('visible');
            this.overlay.style.display = 'none';
            document.body.style.overflow = '';
            this.isMobileOpen = false;

            // Re-apply desktop collapsed state
            if (this.isCollapsed) {
                this.sidebar.classList.add('collapsed');
                this.main.classList.add('expanded');
            } else {
                this.sidebar.classList.remove('collapsed');
                this.main.classList.remove('expanded');
            }
        } else {
            // Switched to mobile — remove desktop collapse classes
            this.sidebar.classList.remove('collapsed');
            this.main.classList.remove('expanded');
        }
    }

    /* ---- Mobile drawer ---- */
    toggleMobile() {
        this.isMobileOpen ? this.closeMobile() : this.openMobile();
    }

    openMobile() {
        this.isMobileOpen = true;
        this.sidebar.classList.add('open');
        this.overlay.style.display = 'block';
        requestAnimationFrame(() => this.overlay.classList.add('visible'));
        document.body.style.overflow = 'hidden';
    }

    closeMobile() {
        this.isMobileOpen = false;
        this.sidebar.classList.remove('open');
        this.overlay.classList.remove('visible');
        document.body.style.overflow = '';
        setTimeout(() => {
            if (!this.isMobileOpen) this.overlay.style.display = 'none';
        }, 300);
    }

    /* ---- Desktop collapse ---- */
    toggle() { this.isCollapsed ? this.expand() : this.collapse(); }

    collapse() {
        this.isCollapsed = true;
        this.sidebar.classList.add('collapsed');
        this.main.classList.add('expanded');
        localStorage.setItem('sidebar-collapsed', 'true');
    }

    expand() {
        this.isCollapsed = false;
        this.sidebar.classList.remove('collapsed');
        this.main.classList.remove('expanded');
        localStorage.setItem('sidebar-collapsed', 'false');
    }
}

// 3. Toast Notification System
class Toast {
    static container = null;

    static init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    }

    static show(message, type = 'info', duration = 5000) {
        this.init();
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icons = { 
            success: '<i class="fa-solid fa-circle-check"></i>', 
            error: '<i class="fa-solid fa-circle-xmark"></i>', 
            info: '<i class="fa-solid fa-circle-info"></i>', 
            warning: '<i class="fa-solid fa-triangle-exclamation"></i>' 
        };
        toast.innerHTML = `
            <span class="toast-icon">${icons[type]}</span>
            <span class="toast-message">${message}</span>
            <button class="toast-close">×</button>
        `;
        
        this.container.appendChild(toast);
        toast.querySelector('.toast-close').addEventListener('click', () => this.removeToast(toast));
        if (duration > 0) setTimeout(() => this.removeToast(toast), duration);
    }

    static removeToast(toast) {
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 300);
    }

    static success(message) { this.show(message, 'success'); }
    static error(message) { this.show(message, 'error'); }
}

// Initialize components on DOM load
document.addEventListener('DOMContentLoaded', () => {
    new DarkModeManager();
    new Sidebar();
});
