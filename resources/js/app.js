import './bootstrap';

// =============================================
// Skena Coffee - Alpine.js Global Store & Logic
// =============================================

function initAlpineStores() {
    // --- Global Cart Store ---
    Alpine.store('cart', {
        items: JSON.parse(localStorage.getItem('skena_cart') || '[]'),
        // Get from URL first, fallback to localStorage
        tableNumber: new URLSearchParams(window.location.search).get('table') || localStorage.getItem('skena_table'),

        init() {
            const hasTableParam = new URLSearchParams(window.location.search).has('table');
            
            if (hasTableParam) {
                // Save new table to localStorage
                localStorage.setItem('skena_table', this.tableNumber);
            }
            // Removed destructive else-if block that clears cart on /menu
        },

        get count() {
            return this.items.reduce((sum, item) => sum + item.qty, 0);
        },

        get total() {
            return this.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },

        add(product) {
            const existing = this.items.find(i => i.id === product.id && i.variant === product.variant);
            if (existing) {
                existing.qty += product.qty || 1;
            } else {
                this.items.push({ ...product, qty: product.qty || 1 });
            }
            this.save();
            const variantLabel = product.variant ? ` (${product.variant})` : '';
            this.notify(`${product.name}${variantLabel} ditambahkan ke keranjang!`);
        },

        remove(index) {
            this.items.splice(index, 1);
            this.save();
        },

        updateQty(index, qty) {
            if (qty <= 0) {
                this.remove(index);
            } else {
                this.items[index].qty = qty;
                this.save();
            }
        },

        clear() {
            this.items = [];
            this.tableNumber = null;
            localStorage.removeItem('skena_table');
            this.save();
        },

        save() {
            localStorage.setItem('skena_cart', JSON.stringify(this.items));
        },

        sync() {
            this.items = JSON.parse(localStorage.getItem('skena_cart') || '[]');
            this.tableNumber = new URLSearchParams(window.location.search).get('table') || localStorage.getItem('skena_table');
        },

        notify(message) {
            Alpine.store('toast').show(message);
        }
    });

    // --- Toast Notification Store ---
    Alpine.store('toast', {
        visible: false,
        message: '',
        timeout: null,

        show(message, duration = 2500) {
            this.message = message;
            this.visible = true;
            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => {
                this.visible = false;
            }, duration);
        }
    });

    // --- Mobile Menu Store ---
    Alpine.store('nav', {
        open: false,
        toggle() { this.open = !this.open; },
        close() { this.open = false; }
    });
}

if (window.Alpine) {
    initAlpineStores();
} else {
    document.addEventListener('alpine:init', initAlpineStores);
}

// BFCache (Back/Forward Cache) handler
// When the user clicks the browser "Back" button, Safari/Chrome often loads from BFCache.
// Alpine doesn't re-initialize, so we manually sync the store with localStorage.
window.addEventListener('pageshow', (event) => {
    if (event.persisted && window.Alpine) {
        Alpine.store('cart').sync();
    }
});

// Sync cart across multiple tabs or windows
window.addEventListener('storage', (event) => {
    if (event.key === 'skena_cart' && window.Alpine) {
        Alpine.store('cart').sync();
    }
});


// =============================================
// Utility: Format currency to IDR
// =============================================
window.formatIDR = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
};

// =============================================
// Payment Countdown Timer
// =============================================
window.startCountdown = (seconds, elementId) => {
    let remaining = seconds;
    const el = document.getElementById(elementId);
    if (!el) return;

    const interval = setInterval(() => {
        remaining--;
        const mins = Math.floor(remaining / 60).toString().padStart(2, '0');
        const secs = (remaining % 60).toString().padStart(2, '0');
        el.textContent = `${mins}:${secs}`;

        if (remaining <= 0) {
            clearInterval(interval);
            el.textContent = '00:00';
            el.closest('[data-countdown-parent]')?.classList.add('opacity-50');
        }
    }, 1000);
};

// =============================================
// Smooth scroll for anchor links
// =============================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
