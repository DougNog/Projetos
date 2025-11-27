document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.querySelector('.toggle-password');
  const senhaInput = document.querySelector('#senha');

  if (toggleBtn && senhaInput) {
    toggleBtn.addEventListener('click', () => {
      const icon = toggleBtn.querySelector('i');
      if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        if (icon) {
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        }
      } else {
        senhaInput.type = 'password';
        if (icon) {
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        }
      }
    });
  }
});
