document.addEventListener('DOMContentLoaded', () => {
    loadProjects();
    const tbody = document.getElementById('projectTableBody');
    if (!tbody) return;

    // เดินทางไป index.php เมื่อคลิกที่แถว หรือปุ่มในแถว
    tbody.addEventListener('click', (e) => {
        const row = e.target.closest('tr');
        if (!row) return;
        window.location.href = 'index.php';
    });
});

document.addEventListener('click', (e) => {
  const card = e.target.closest('.project-card, .stat-card, .card');
  if (card) window.location.href = 'index.php';
});

function loadProjects() {
    const projectTableBody = document.getElementById('projectTableBody');
    
    // ตัวอย่างข้อมูล (ควรดึงจาก API จริง)
    const projects = [
        {
            name: 'แจกข้าวชุมชน',
            foundation: 'มูลนิธิแบ่งใจใส่จาน',
            startDate: '01/09/2025',
            status: 'pending',
            reviewer: 'คุณนาว'
        },
        {
            name: 'ส่งผลไม้โรงเรียน',
            foundation: 'มูลนิธิแบ่งปันสุข',
            startDate: '05/09/2025',
            status: 'approved',
            reviewer: 'คุณพลอย'
        }
    ];

    projectTableBody.innerHTML = projects.map(project => `
        <tr>
            <td>${project.name}</td>
            <td>${project.foundation}</td>
            <td>${project.startDate}</td>
            <td>
                <span class="status-badge status-${project.status}">
                    ${getStatusText(project.status)}
                </span>
            </td>
            <td>${project.reviewer}</td>
            <td>
                <button class="action-button">ตรวจสอบ</button>
            </td>
        </tr>
    `).join('');
}

function getStatusText(status) {
    const statusMap = {
        'pending': 'รออนุมัติ',
        'approved': 'อนุมัติแล้ว'
    };
    return statusMap[status] || status;
}