document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    toggle?.addEventListener('click', () => sidebar?.classList.toggle('open'));

    document.addEventListener('click', (event) => {
        if (window.innerWidth <= 800 && sidebar?.classList.contains('open') && !sidebar.contains(event.target) && !toggle?.contains(event.target)) {
            sidebar.classList.remove('open');
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-confirm]');
        if (form && !window.confirm(form.dataset.confirm)) event.preventDefault();
    });

    document.querySelectorAll('[data-live-refresh]').forEach((region) => {
        let pending = false;
        const regionName = region.dataset.liveRefresh;
        const interval = Number(region.dataset.refreshMs || 15000);
        window.setInterval(async () => {
            if (pending || document.hidden) return;
            pending = true;
            try {
                const response = await fetch(window.location.href, {
                    cache: 'no-store',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                });
                if (!response.ok) return;
                const html = await response.text();
                const latestDocument = new DOMParser().parseFromString(html, 'text/html');
                const latestRegion = latestDocument.querySelector(`[data-live-refresh="${CSS.escape(regionName)}"]`);
                if (latestRegion) region.innerHTML = latestRegion.innerHTML;
            } catch (_) {
                // A temporary network error leaves the last rendered values visible.
            } finally {
                pending = false;
            }
        }, interval);
    });
});
