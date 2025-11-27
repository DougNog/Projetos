// Menu Mobile
document.querySelector('.mobile-menu').addEventListener('click', function() {
    document.querySelector('.nav-links').classList.toggle('active');
});

// Fechar menu ao clicar em um link
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
        document.querySelector('.nav-links').classList.remove('active');
    });
});

// Efeito de rolagem suave (exceto para links de login)
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        // Se o link for para login, não aplicar rolagem suave
        if(this.getAttribute('href').startsWith('#') && !this.classList.contains('btn-login')) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if(targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        }
    });
});