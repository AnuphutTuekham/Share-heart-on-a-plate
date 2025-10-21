document.addEventListener('click', (e) => {
  const target = e.target.closest('.project-card, .stat-card, .card, .project-table tbody tr');
  if (!target) return;

  // ถ้ามีลิงก์ภายใน ให้ปล่อยให้ลิงก์ทำงานตามปกติ
  if (e.target.closest('a[href]')) return;

  window.location.href = 'index.php';
});