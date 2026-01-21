// compare.js - фиксированная версия
document.addEventListener('DOMContentLoaded', function() {
    // Функция для обновления localStorage на основе текущих ID в URL
    function updateLocalStorageFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const idsParam = urlParams.get('ids');
        
        if (idsParam) {
            const ids = idsParam.split(',').map(id => parseInt(id)).filter(id => !isNaN(id));
            
            // Обновляем localStorage
            const items = ids.map(id => ({
                id: id,
                name: document.querySelector(`[data-product-id="${id}"]`)?.textContent || `Товар ${id}`
            }));
            
            localStorage.setItem('compareItems', JSON.stringify(items));
        } else {
            // Если нет ID в URL, очищаем localStorage
            localStorage.removeItem('compareItems');
        }
    }
    
    // Функция для удаления товара
    function handleRemoveClick(e) {
        if (e.target.classList.contains('compare-remove-btn')) {
            e.preventDefault();
            
            const confirmDelete = confirm('Удалить товар из сравнения?');
            if (!confirmDelete) return;
            
            const removeUrl = e.target.getAttribute('href');
            
            // Обновляем localStorage перед переходом
            const urlParams = new URLSearchParams(removeUrl.split('?')[1]);
            const newIdsParam = urlParams.get('ids');
            
            if (newIdsParam) {
                const newIds = newIdsParam.split(',').filter(id => id !== '');
                const items = newIds.map(id => ({
                    id: parseInt(id),
                    name: `Товар ${id}`
                }));
                localStorage.setItem('compareItems', JSON.stringify(items));
            } else {
                localStorage.removeItem('compareItems');
            }
            
            // Переходим по обновленной ссылке
            window.location.href = removeUrl;
        }
    }
    
    // Функция для очистки всего сравнения
    function handleClearClick(e) {
        if (e.target.classList.contains('clear-compare-btn')) {
            e.preventDefault();
            
            const confirmClear = confirm('Очистить весь список сравнения?');
            if (!confirmClear) return;
            
            // Очищаем localStorage
            localStorage.removeItem('compareItems');
            
            // Переходим на страницу без параметров
            window.location.href = 'compare.php';
        }
    }
    
    // Инициализация
    updateLocalStorageFromURL();
    
    // Обработчики событий
    document.addEventListener('click', handleRemoveClick);
    document.addEventListener('click', handleClearClick);
    
    // Синхронизация при загрузке страницы
    const urlParams = new URLSearchParams(window.location.search);
    const idsParam = urlParams.get('ids');
    const compareItems = JSON.parse(localStorage.getItem('compareItems')) || [];
    
    if (compareItems.length > 0 && !idsParam) {
        // Если есть товары в localStorage, но нет в URL - перенаправляем с ID
        const ids = compareItems.map(item => item.id).join(',');
        window.location.href = `compare.php?ids=${ids}`;
        return;
    }
    
    if (compareItems.length === 0 && idsParam) {
        // Если нет товаров в localStorage, но есть в URL - очищаем URL
        window.location.href = 'compare.php';
        return;
    }
});
    
    
    // 6. БУРГЕР МЕНЮ (если есть)
    const burgerBtn = document.getElementById('burgerBtn');
    const navOverlay = document.getElementById('navOverlay');
    const mainNav = document.getElementById('mainNav');
    
    if (burgerBtn && mainNav) {
        burgerBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            mainNav.classList.toggle('active');
            if (navOverlay) navOverlay.classList.toggle('active');
            document.body.classList.toggle('menu-open');
            
            // Обновляем aria атрибуты
            const isExpanded = this.classList.contains('active');
            this.setAttribute('aria-expanded', isExpanded);
        });
        
        if (navOverlay) {
            navOverlay.addEventListener('click', function() {
                burgerBtn.classList.remove('active');
                mainNav.classList.remove('active');
                this.classList.remove('active');
                document.body.classList.remove('menu-open');
                burgerBtn.setAttribute('aria-expanded', 'false');
            });
        }
    }