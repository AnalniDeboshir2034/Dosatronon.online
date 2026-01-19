
        document.querySelectorAll('.filter-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                document.querySelectorAll('.filter-item').forEach(i => {
                    i.classList.remove('active');
                });
                

                this.classList.add('active');
                

                const categoryText = this.textContent.split(' ')[0];
                document.querySelector('.current-filter').textContent = `Активная категория: ${categoryText}`;
                

                const targetId = this.getAttribute('href');
                if(targetId && targetId.startsWith('#')) {
                    const targetSection = document.querySelector(targetId);
                    if(targetSection) {
                        window.scrollTo({
                            top: targetSection.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });

        document.querySelectorAll('.dosatron-table tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'hsl(200 30% 12%)';
            });
            
            row.addEventListener('mouseleave', function() {
                if(!this.classList.contains('discontinued')) {
                    this.style.backgroundColor = '';
                }
            });
        });
            const catalogLink = document.getElementById('catalogLink');
    const catalogDropdown = document.getElementById('catalogDropdown');
    
    if (catalogLink && catalogDropdown && window.innerWidth <= 992) {
        catalogLink.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.toggle('active');
            catalogDropdown.classList.toggle('active');
        });
        
        // Закрытие при клике вне меню
        document.addEventListener('click', function(e) {
            if (!catalogLink.contains(e.target) && !catalogDropdown.contains(e.target)) {
                catalogLink.classList.remove('active');
                catalogDropdown.classList.remove('active');
            }
        });
    }
    
    
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
    