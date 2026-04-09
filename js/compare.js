// compare.js - фиксированная версия
document.addEventListener('DOMContentLoaded', function() {
    const STORAGE_KEY = 'compareItems';

    function parseIds(idsParam) {
        if (!idsParam) return [];
        const unique = new Set();
        idsParam.split(',').forEach(rawId => {
            const id = Number.parseInt(rawId, 10);
            if (!Number.isNaN(id) && id > 0) {
                unique.add(id);
            }
        });
        return Array.from(unique);
    }

    function readCompareItems() {
        try {
            const raw = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
            const unique = new Map();

            raw.forEach(item => {
                const id = Number.parseInt(item?.id, 10);
                if (!Number.isNaN(id) && id > 0 && !unique.has(id)) {
                    unique.set(id, {
                        id,
                        name: item?.name || `Товар ${id}`,
                        date: item?.date || null
                    });
                }
            });

            return Array.from(unique.values());
        } catch (error) {
            return [];
        }
    }

    function writeCompareItems(items) {
        if (!items.length) {
            localStorage.removeItem(STORAGE_KEY);
            return;
        }
        localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    }

    function syncStorageFromUrlIds(urlIds) {
        const currentItems = readCompareItems();
        const itemsById = new Map(currentItems.map(item => [item.id, item]));

        const nextItems = urlIds.map(id => {
            if (itemsById.has(id)) {
                return itemsById.get(id);
            }
            return { id, name: `Товар ${id}`, date: null };
        });

        writeCompareItems(nextItems);
    }

    function buildCompareUrl(ids) {
        return ids.length ? `compare.php?ids=${ids.join(',')}` : 'compare.php';
    }

    function handleRemoveClick(e) {
        const removeBtn = e.target.closest('.compare-remove-btn');
        if (!removeBtn) return;

        e.preventDefault();
        if (!confirm('Удалить товар из сравнения?')) return;

        const removeHref = removeBtn.getAttribute('href') || '';
        const removeParams = new URLSearchParams(removeHref.split('?')[1] || '');
        const removeId = Number.parseInt(removeParams.get('remove_id'), 10);

        if (!Number.isNaN(removeId)) {
            const currentItems = readCompareItems();
            const nextItems = currentItems.filter(item => item.id !== removeId);
            writeCompareItems(nextItems);
        }

        window.location.href = removeHref;
    }

    function handleClearClick(e) {
        const clearBtn = e.target.closest('.clear-compare-btn');
        if (!clearBtn) return;

        e.preventDefault();
        if (!confirm('Очистить весь список сравнения?')) return;

        localStorage.removeItem(STORAGE_KEY);
        window.location.href = 'compare.php';
    }

    const urlParams = new URLSearchParams(window.location.search);
    const urlIds = parseIds(urlParams.get('ids'));
    const storageItems = readCompareItems();
    const storageIds = storageItems.map(item => item.id);

    if (urlIds.length) {
        syncStorageFromUrlIds(urlIds);
    } else if (storageIds.length) {
        window.location.replace(buildCompareUrl(storageIds));
        return;
    }

    document.addEventListener('click', handleRemoveClick);
    document.addEventListener('click', handleClearClick);
});