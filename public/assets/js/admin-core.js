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
        this.isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        if (this.sidebar && this.main) {
            this.init();
        }
    }

    init() {
        if (this.isCollapsed) this.collapse();
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => this.toggle());
        }
    }

    toggle() {
        this.isCollapsed ? this.expand() : this.collapse();
    }

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
