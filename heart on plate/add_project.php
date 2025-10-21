<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>เพิ่มโครงการ — แบ่งใจใส่จาน</title>
  <link rel="stylesheet" href="CSS/header.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <link rel="stylesheet" href="CSS/add_project.css" />
  <script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
  <script type="text/javascript" src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dependencies/JQL.min.js"></script>
  <script type="text/javascript" src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dependencies/typeahead.bundle.js"></script>
  <link rel="stylesheet" href="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dist/jquery.Thailand.min.css">
  <script type="text/javascript" src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dist/jquery.Thailand.min.js"></script>
</head>
<body>
  <!-- ใช้ header กลาง (header.js จะโหลด components/header.html มาใส่ให้) -->
  <header class="header"></header>

  <main class="page-wrap">
    <section class="form-container" aria-labelledby="formTitle">
      <h1 id="formTitle" class="form-title">เพิ่มโครงการใหม่</h1>

      <?php if (isset($_GET['error'])): ?>
        <div class="error-message" style="color:#c00;padding:10px;background:#ffe6e6;border-radius:8px;margin-bottom:12px;">
          <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
      <?php endif; ?>

      <form class="project-form"
            action="add_project_submit.php"
            method="post"
            enctype="multipart/form-data"
            novalidate
            id="projectForm">
        <!-- ชื่อโครงการ -->
        <div class="form-group">
          <label for="title">ชื่อโครงการ</label>
          <input id="title" name="project_name" type="text" placeholder="ระบุชื่อโครงการ" required />
        </div>

        <!-- วันที่สิ้นสุดโครงการ -->
        <div class="form-group">
          <label for="end_date">วันที่สิ้นสุดโครงการ</label>
          <input id="end_date" name="end_date" type="date" required />
        </div>

        <!-- เบอร์โทรศัพท์ -->
        <div class="form-group">
          <label for="phone">เบอร์โทรศัพท์</label>
          <input id="phone" name="phone" type="tel" inputmode="numeric" pattern="[0-9]{9,10}" placeholder="เช่น 0812345678" required />
          <small class="hint">กรอกเป็นตัวเลข 9–10 หลัก</small>
        </div>

        <!-- อีเมล -->
        <div class="form-group">
          <label for="email">อีเมล</label>
          <input id="email" name="email" type="email" placeholder="you@example.com" required />
        </div>

        <!-- เป้าหมายโครงการ -->
        <div class="form-group">
          <label for="goal">เป้าหมายโครงการ (จำนวนมื้อ/หน่วย)</label>
          <input id="goal" name="goal" type="number" min="1" step="1" placeholder="เช่น 1000" required />
        </div>

        <!-- จุดรับบริจาค -->
        <div class="form-group form-group--full">
          <label>จุดรับบริจาค</label>
          <textarea id="address_detail" rows="2" placeholder="รายละเอียดเพิ่มเติม เช่น บ้านเลขที่ ถนน หมู่บ้าน จุดสังเกต"></textarea>
          <input id="district" type="text" placeholder="ตำบล" required />
          <input id="amphoe" type="text" placeholder="อำเภอ" required />
          <input id="province" type="text" placeholder="จังหวัด" required />
          <input id="zipcode" type="text" placeholder="รหัสไปรษณีย์" required />
          <input type="hidden" name="address" id="address_hidden">
        </div>

        <!-- รายละเอียด -->
        <div class="form-group form-group--full">
          <label for="description">รายละเอียดของโครงการ</label>
          <textarea id="description" name="description" rows="5" placeholder="อธิบายจุดประสงค์ วิธีดำเนินงาน และข้อมูลที่เกี่ยวข้อง" required></textarea>
        </div>

        <!-- รูปภาพปกโครงการ -->
        <div class="form-group form-group--full">
          <label for="img">รูปภาพปกโครงการ</label>
          <input id="img" name="img" type="file" accept="image/*" />
        </div>

        <div class="actions">
          <a class="btn btn-secondary" href="index.php">ยกเลิก</a>
          <button class="btn btn-primary" type="submit">ส่งข้อมูล</button>
        </div>
      </form>
    </section>
  </main>

  <script src="js/header.js"></script>
  <script>
  $.Thailand({
      $district: $('#district'),
      $amphoe: $('#amphoe'),
      $province: $('#province'),
      $zipcode: $('#zipcode'),
  });

  // รวม address ก่อน submit
  $('#projectForm').on('submit', function(e){
    const addrDetail = $('#address_detail').val().trim();
    const district   = $('#district').val().trim();
    const amphoe     = $('#amphoe').val().trim();
    const province   = $('#province').val().trim();
    const zipcode    = $('#zipcode').val().trim();

    if (!district || !amphoe || !province || !zipcode) {
      e.preventDefault();
      alert('กรุณากรอกจุดรับบริจาคให้ครบถ้วน');
      return false;
    }

    const prefix = addrDetail ? (addrDetail + ', ') : '';
    const fullAddress = `${prefix}ต.${district} อ.${amphoe} จ.${province} ${zipcode}`;
    $('#address_hidden').val(fullAddress);
  });
  </script>
</body>
</html>
