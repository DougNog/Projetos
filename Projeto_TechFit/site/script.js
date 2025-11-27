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

// Validação do formulário de contato
function validateContactForm() {
    const form = document.querySelector('.contact-form form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const name = form.querySelector('input[type="text"]');
        const email = form.querySelector('input[type="email"]');
        const phone = form.querySelector('input[type="tel"]');
        const subject = form.querySelector('select');
        const message = form.querySelector('textarea');

        let isValid = true;
        let errors = [];

        // Validação do nome
        if (!name.value.trim()) {
            errors.push('Nome é obrigatório');
            name.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            name.style.borderColor = '#28a745';
        }

        // Validação do email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value.trim()) {
            errors.push('E-mail é obrigatório');
            email.style.borderColor = '#dc3545';
            isValid = false;
        } else if (!emailRegex.test(email.value)) {
            errors.push('E-mail inválido');
            email.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            email.style.borderColor = '#28a745';
        }

        // Validação do telefone (opcional)
        if (phone.value.trim()) {
            const phoneRegex = /^\(\d{2}\)\s\d{4,5}-\d{4}$/;
            if (!phoneRegex.test(phone.value)) {
                errors.push('Telefone deve estar no formato (11) 99999-9999');
                phone.style.borderColor = '#dc3545';
                isValid = false;
            } else {
                phone.style.borderColor = '#28a745';
            }
        }

        // Validação do assunto
        if (!subject.value) {
            errors.push('Assunto é obrigatório');
            subject.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            subject.style.borderColor = '#28a745';
        }

        // Validação da mensagem
        if (!message.value.trim()) {
            errors.push('Mensagem é obrigatória');
            message.style.borderColor = '#dc3545';
            isValid = false;
        } else if (message.value.trim().length < 10) {
            errors.push('Mensagem deve ter pelo menos 10 caracteres');
            message.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            message.style.borderColor = '#28a745';
        }

        // Exibir erros ou enviar formulário
        if (!isValid) {
            showNotification('Erro na validação: ' + errors.join(', '), 'error');
        } else {
            // Simular envio do formulário
            showNotification('Mensagem enviada com sucesso! Entraremos em contato em breve.', 'success');
            form.reset();
            // Resetar cores das bordas
            [name, email, phone, subject, message].forEach(field => {
                if (field) field.style.borderColor = '#ddd';
            });
        }
    });
}

// Sistema de notificações
function showNotification(message, type = 'info') {
    // Remover notificações existentes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    // Criar nova notificação
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    // Mostrar notificação
    setTimeout(() => notification.classList.add('show'), 100);

    // Esconder notificação após 5 segundos
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Máscara para telefone
function applyPhoneMask() {
    const phoneInput = document.querySelector('input[type="tel"]');
    if (!phoneInput) return;

    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');

        if (value.length <= 11) {
            if (value.length <= 2) {
                value = value;
            } else if (value.length <= 6) {
                value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
            } else if (value.length <= 10) {
                value = `(${value.slice(0, 2)}) ${value.slice(2, 6)}-${value.slice(6)}`;
            } else {
                value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`;
            }
        }

        e.target.value = value;
    });
}

// Animações de entrada dos elementos
function animateOnScroll() {
    const elements = document.querySelectorAll('.service-card, .about-content, .pricing-card, .contact-content, .stat-card');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('fade-in-up');
                }, index * 150);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    elements.forEach(element => {
        observer.observe(element);
    });
}

// Animação do hero
function animateHero() {
    const heroContent = document.querySelector('.hero-content');
    const heroButtons = document.querySelector('.hero-buttons');

    if (heroContent) {
        heroContent.classList.add('fade-in-up');
    }

    if (heroButtons) {
        setTimeout(() => {
            heroButtons.classList.add('fade-in-up');
        }, 500);
    }
}

// Animação do logo pulsante
function animateLogo() {
    const logo = document.querySelector('.logo');
    if (logo) {
        logo.classList.add('pulse-animation');
    }
}

// Inicializar animações quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    animateHero();
    animateLogo();
    animateOnScroll();
    validateContactForm();
    applyPhoneMask();
});

// Animação do header ao rolar
let lastScrollTop = 0;
window.addEventListener('scroll', function() {
    const header = document.querySelector('header');
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    if (scrollTop > lastScrollTop && scrollTop > 100) {
        // Rolando para baixo
        header.style.transform = 'translateY(-100%)';
    } else {
        // Rolando para cima
        header.style.transform = 'translateY(0)';
    }

    lastScrollTop = scrollTop;
});
