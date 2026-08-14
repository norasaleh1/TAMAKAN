const container   = document.querySelector('.container');
const registerBtn = document.querySelector('.register-btn');
const loginBtn    = document.querySelector('.login-btn');

registerBtn?.addEventListener('click', () => container?.classList.add('active'));
loginBtn?.addEventListener('click',    () => container?.classList.remove('active'));

const registerForm = document.querySelector('.form-box.register form');

function getSelectedRole() {
  return registerForm?.querySelector('input[name="role"]:checked')?.value || '';
}

function toggleTopics() {
  if (!registerForm) return;
  const topicsBlock = registerForm.querySelector('.topics');
  if (!topicsBlock) return;

  if (getSelectedRole() === 'educator') {
    topicsBlock.classList.remove('hidden');
    topicsBlock.style.display = '';
  } else {
    topicsBlock.classList.add('hidden');
    topicsBlock.style.display = 'none';
    topicsBlock.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
  }
}

registerForm?.querySelectorAll('input[name="role"]').forEach(r => {
  r.addEventListener('change', toggleTopics);
});
toggleTopics();

registerForm?.addEventListener('submit', (e) => {
  if (getSelectedRole() === 'educator') {
    const checked = registerForm.querySelectorAll('.topics input[type="checkbox"]:checked').length;
    if (!checked) {
      e.preventDefault();
      alert('Please choose at least one topic for educator.');
    }
  }
});
