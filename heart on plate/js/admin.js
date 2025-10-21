// Mock data for testing
const mockData = {
    projects: 15,
    items: 45,
    donors: 30,
    notShipped: 12,
    shipped: 33
};

// Update stats numbers
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('projectsCount').textContent = mockData.projects;
    document.getElementById('itemsCount').textContent = mockData.items;
    document.getElementById('donorsCount').textContent = mockData.donors;
    document.getElementById('notShippedCount').textContent = mockData.notShipped;
    document.getElementById('shippedCount').textContent = mockData.shipped;
});

// สร้างกราฟตัวอย่างด้วย Chart.js (ข้อมูลตัวอย่าง) 
document.addEventListener('DOMContentLoaded', function(){
  const ctx = document.getElementById('adminChart').getContext('2d');

  const labels = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.'];
  const data = {
    labels,
    datasets: [{
      label: 'จำนวนรายการ (ตัวอย่าง)',
      backgroundColor: '#bfe6c9',
      borderColor: '#9fd6a8',
      borderWidth: 1,
      data: [2,1,3,2,4,1,2,0,0],
    }]
  };

  new Chart(ctx, {
    type: 'bar',
    data,
    options: {
      plugins: { legend:{ display:false } },
      scales: {
        y: { beginAtZero:true, ticks:{ stepSize:1 } }
      }
    }
  });
});

(async function () {
    const candidates = [
        '/index.php?format=json',
        '/sql/index.php?format=json'
    ];
    function el(id){ return document.getElementById(id); }

    let data = null;
    for (const url of candidates) {
        try {
            const res = await fetch(url, { cache: 'no-store' });
            console.log('fetch', url, res.status);
            if (!res.ok) continue;
            // พยายาม parse JSON — ถ้าไม่ใช่ JSON จะโยน
            const json = await res.json();
            if (json) { data = json; break; }
        } catch (err) {
            console.warn('fetch failed', url, err);
        }
    }

    if (!data) {
        console.error('No JSON summary found from index.php/sql/index.php');
        return;
    }

    // summary อาจเป็น array หรือ object
    const summaryArr = data.summary || data.summaryRows || [];
    const s = (Array.isArray(summaryArr) && summaryArr.length) ? summaryArr[0] : (data.summary || data || {});

    const project_sum = Number(s.project_sum ?? s.projectSum ?? 0) || 0;
    const food_sum = Number(s.food_sum ?? s.foodSum ?? 0) || 0;
    const donate_sum = Number(s.donate_sum ?? s.donateSum ?? 0) || 0;
    const not_shipped_sum = Number(s.not_shipped_sum ?? s.notShippedSum ?? 0) || 0;
    const shipped_sum = Number(s.shipped_sum ?? s.shippedSum ?? 0) || 0;

    if (el('projectsCount')) el('projectsCount').innerText = project_sum;
    if (el('itemsCount')) el('itemsCount').innerText = food_sum;
    if (el('donorsCount')) el('donorsCount').innerText = donate_sum;
    if (el('notShippedCount')) el('notShippedCount').innerText = not_shipped_sum;
    if (el('shippedCount')) el('shippedCount').innerText = shipped_sum;

    // chart (Chart.js ต้องโหลดในหน้า)
    const labels = ['โครงการ', 'รายการอาหาร', 'ผู้บริจาค', 'รอจัดส่ง', 'จัดส่งแล้ว'];
    const values = [project_sum, food_sum, donate_sum, not_shipped_sum, shipped_sum];

    const canvas = document.getElementById('adminChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'สถิติ',
                    data: values,
                    backgroundColor: ['#4caf50','#2196f3','#ff9800','#f44336','#9c27b0'],
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false }, tooltip: { enabled: true } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
})();

document.addEventListener('DOMContentLoaded', async () => {
    try {
        // เรียกข้อมูลจาก API
        const response = await fetch('sql/index.php?format=json');
        const data = await response.json();

        // ดึงข้อมูลสรุปจาก response
        const summary = data.summary[0]; // เอาแถวแรกจากผลลัพธ์ SELECT

        // อัพเดตค่าในแต่ละ card
        document.getElementById('projectsCount').textContent = summary.project_sum || '0';
        document.getElementById('itemsCount').textContent = summary.food_sum || '0';
        document.getElementById('donorsCount').textContent = summary.donate_sum || '0';
        document.getElementById('notShippedCount').textContent = summary.not_shipped_sum || '0';
        document.getElementById('shippedCount').textContent = summary.shipped_sum || '0';

    } catch (error) {
        console.error('Error fetching data:', error);
        // กรณีมี error ให้แสดง — ในทุก card
        const cards = ['projectsCount', 'itemsCount', 'donorsCount', 'notShippedCount', 'shippedCount'];
        cards.forEach(id => document.getElementById(id).textContent = '—');
    }
});

// เพิ่มฟังก์ชันโหลดและแสดงโครงการ
async function loadProjects() {
    const projectTableBody = document.getElementById('projectTableBody');
    
    // ตัวอย่างข้อมูล (ควรดึงจาก API จริง)
    const projects = [
        {
            name: 'แจกข้าวชุมชน',
            foundation: 'มูลนิธิแบ่งใจใส่จาน',
            startDate: '01/09/2025',
            status: 'pending',
            reviewer: 'คุณนาว',
        },
        {
            name: 'ส่งผลไม้โรงเรียน',
            foundation: 'มูลนิธิแบ่งปันสุข',
            startDate: '05/09/2025',
            status: 'approved',
            reviewer: 'คุณพลอย',
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

// เรียกใช้ฟังก์ชันเมื่อโหลดหน้า
document.addEventListener('DOMContentLoaded', () => {
    loadProjects();
    // ...existing stats loading code...

    // Event listeners สำหรับเมนู
    document.getElementById('menuDashboard').addEventListener('click', (e) => {
        e.preventDefault();
        showSection('dashboardSection');
        updateActiveMenu(e.target);
    });

    document.getElementById('menuProjects').addEventListener('click', (e) => {
        e.preventDefault();
        showSection('projectsSection');
        updateActiveMenu(e.target);
        loadProjects(); // โหลดข้อมูลโครงการเมื่อเปิดแท็บ
    });

    // ฟังก์ชันแสดง/ซ่อนเนื้อหา
    function showSection(sectionId) {
        // ซ่อนทุกส่วน
        document.querySelectorAll('.section-content').forEach(section => {
            section.classList.add('hidden');
        });
        // แสดงส่วนที่เลือก
        document.getElementById(sectionId).classList.remove('hidden');
    }

    // อัพเดตเมนูที่เลือก
    function updateActiveMenu(clickedItem) {
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });
        clickedItem.classList.add('active');
    }

    // แสดง Dashboard เป็นหน้าแรก
    showSection('dashboardSection');
});

// ...existing code for loadProjects, formatDate, getStatusText...