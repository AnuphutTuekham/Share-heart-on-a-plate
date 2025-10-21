// โหลด header กลาง และตั้งค่า dropdown + สิทธิ์เมนูตาม role_id
(async function initSharedHeader() {
  const host = document.querySelector('header.header');
  if (!host) return;

  // โหลดส่วนหัวจาก component
  const res = await fetch('components/header.html', { cache: 'no-cache' });
  host.innerHTML = await res.text();

  // toggle dropdown
  const profile = host.querySelector('#userProfile');
  const dropdown = host.querySelector('#headerDropdown');
  const close = () => dropdown?.classList.remove('show');

  profile?.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown?.classList.toggle('show');
  });
  document.addEventListener('click', (e) => {
    if (!e.target.closest('#userProfile')) close();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') close();
  });

  // แสดงเมนูตาม role
  const showItem = (key, visible) => {
    const el = host.querySelector(`[data-item="${key}"]`);
    if (el) el.style.display = visible ? '' : 'none';
  };
  ['profile','my-projects','review-projects'].forEach(k => showItem(k, false));

  try {
    const r = await fetch('get_role.php', { cache: 'no-cache' });
    const data = await r.json();
    const role = Number(data.role_id || 0);
    if (role === 1) {
      showItem('profile', true);
    } else if (role === 2) {
      showItem('profile', true);
      showItem('my-projects', true);
    } else if (role === 3) {
      showItem('profile', true);
      showItem('my-projects', true);
      showItem('review-projects', true);
    } else {
      showItem('profile', true);
    }
  } catch {
    showItem('profile', true);
  }
})();