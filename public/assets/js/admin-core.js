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
        this.applyTheme(this.currentTheme, false);
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => this.toggle());
            this.toggleBtn.setAttribute('aria-label', `Switch to ${this.currentTheme === 'light' ? 'dark' : 'light'} mode`);
            this.toggleBtn.setAttribute('role', 'button');
        }

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) {
                this.applyTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    toggle() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
        localStorage.setItem('theme', newTheme);

        // Announce theme change for screen readers
        Toast.show(`Switched to ${newTheme} mode`, 'info', 2000);
    }

    applyTheme(theme, animate = true) {
        this.currentTheme = theme;
        const html = document.documentElement;

        if (animate) {
            html.style.transition = 'none';
            setTimeout(() => {
                html.style.transition = '';
            }, 50);
        }

        html.setAttribute('data-theme', theme);

        if (this.toggleBtn) {
            const icon = theme === 'dark' ? '<i class="fa-solid fa-sun" aria-hidden="true"></i>' : '<i class="fa-solid fa-moon" aria-hidden="true"></i>';
            this.toggleBtn.innerHTML = icon;
            this.toggleBtn.setAttribute('aria-label', `Switch to ${theme === 'light' ? 'dark' : 'light'} mode`);
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
        this.animationDuration = 300;

        if (this.sidebar && this.main) {
            this.init();
        }
    }

    _createOverlay() {
        const el = document.createElement('div');
        el.className = 'sidebar-overlay';
        el.setAttribute('aria-hidden', 'true');
        el.setAttribute('role', 'presentation');
        document.body.appendChild(el);
        el.addEventListener('click', () => this.closeMobile());
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.closeMobile();
        });
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
            this.container.setAttribute('role', 'region');
            this.container.setAttribute('aria-label', 'Notifications');
            this.container.setAttribute('aria-live', 'polite');
            document.body.appendChild(this.container);
        }
    }

    static show(message, type = 'info', duration = 5000) {
        this.init();
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-atomic', 'true');

        const icons = {
            success: '<i class="fa-solid fa-circle-check" aria-hidden="true"></i>',
            error: '<i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>',
            info: '<i class="fa-solid fa-circle-info" aria-hidden="true"></i>',
            warning: '<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>'
        };

        const typeLabels = {
            success: 'Success',
            error: 'Error',
            info: 'Information',
            warning: 'Warning'
        };

        toast.innerHTML = `
            <span class="toast-icon" aria-hidden="true">${icons[type]}</span>
            <span class="toast-message">${message}</span>
            <button class="toast-close" aria-label="Close ${typeLabels[type]} notification" type="button">×</button>
        `;

        this.container.appendChild(toast);

        // Focus management for accessibility
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => this.removeToast(toast));
        closeBtn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.removeToast(toast);
            }
        });

        // Auto-dismiss with timer
        if (duration > 0) {
            const timer = setTimeout(() => this.removeToast(toast), duration);

            // Pause timer on hover for better UX
            toast.addEventListener('mouseenter', () => clearTimeout(timer));
            toast.addEventListener('mouseleave', () => setTimeout(() => this.removeToast(toast), duration));
        }

        // Announce to screen readers
        setTimeout(() => {
            toast.setAttribute('aria-label', `${typeLabels[type]}: ${message}`);
        }, 100);
    }

    static removeToast(toast) {
        toast.classList.add('removing');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    static success(message) { this.show(message, 'success'); }
    static error(message) { this.show(message, 'error'); }
    static warning(message) { this.show(message, 'warning'); }
    static info(message) { this.show(message, 'info'); }
}

// 4. Loading State Manager
class LoadingManager {
    static show(element, text = 'Loading...') {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }

        if (!element) return;

        element.classList.add('loading');
        element.setAttribute('aria-busy', 'true');
        element.setAttribute('aria-live', 'polite');

        // Add loading spinner and text if not already present
        if (!element.querySelector('.loading-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="loading-content">
                    <div class="spinner"></div>
                    <span class="loading-text">${text}</span>
                </div>
            `;
            overlay.setAttribute('aria-hidden', 'false');
            element.appendChild(overlay);
        }
    }

    static hide(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }

        if (!element) return;

        element.classList.remove('loading');
        element.removeAttribute('aria-busy');

        const overlay = element.querySelector('.loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }
}

// 5. Keyboard Navigation Manager
class KeyboardNav {
    static init() {
        document.addEventListener('keydown', (e) => {
            // Global keyboard shortcuts
            if (e.ctrlKey || e.metaKey) {
                switch (e.key.toLowerCase()) {
                    case 'b':
                        e.preventDefault();
                        const sidebarToggle = document.getElementById('sidebar-toggle');
                        if (sidebarToggle) sidebarToggle.click();
                        break;
                    case 't':
                        e.preventDefault();
                        const themeToggle = document.getElementById('theme-toggle');
                        if (themeToggle) themeToggle.click();
                        break;
                }
            }

            // Focus trap for modals
            if (e.key === 'Tab' && document.querySelector('.modal-overlay.open')) {
                this.handleModalFocusTrap(e);
            }
        });
    }

    static handleModalFocusTrap(e) {
        const modal = document.querySelector('.modal-overlay.open .modal');
        if (!modal) return;

        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (e.shiftKey) {
            if (document.activeElement === firstElement) {
                e.preventDefault();
                lastElement.focus();
            }
        } else {
            if (document.activeElement === lastElement) {
                e.preventDefault();
                firstElement.focus();
            }
        }
    }
}

// Enhanced API Fetch with loading states
async function apiFetchWithLoading(url, options = {}, loadingElement = null, loadingText = 'Loading...') {
    if (loadingElement) {
        LoadingManager.show(loadingElement, loadingText);
    }

    try {
        const result = await apiFetch(url, options);
        return result;
    } catch (error) {
        Toast.error(error.message || 'An error occurred');
        throw error;
    } finally {
        if (loadingElement) {
            LoadingManager.hide(loadingElement);
        }
    }
}

// Initialize components on DOM load
document.addEventListener('DOMContentLoaded', () => {
    new DarkModeManager();
    new Sidebar();
    KeyboardNav.init();

    // Add loading overlay styles dynamically
    const style = document.createElement('style');
    style.textContent = `
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            backdrop-filter: blur(2px);
        }

        [data-theme="dark"] .loading-overlay {
            background: rgba(15, 23, 42, 0.8);
        }

        .loading-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 20px;
            background: var(--bg-primary);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
        }

        .loading-text {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }
    `;
    document.head.appendChild(style);
});
