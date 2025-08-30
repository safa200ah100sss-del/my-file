<?php
session_start();
if (!isset($_SESSION['students'])) {
    $_SESSION['students'] = [
        ['stdNo' => '20003', 'stdName' => 'Ahmed Ali', 'stdEmail' => 'ahmed@gmail.com', 'stdGAP' => 88.7],
        ['stdNo' => '30304', 'stdName' => 'Mona Khalid', 'stdEmail' => 'mona@gmail.com', 'stdGAP' => 78.5],
        ['stdNo' => '10002', 'stdName' => 'Bilal Hmaza', 'stdEmail' => 'bilal@gmail.com', 'stdGAP' => 98.7],
        ['stdNo' => '10005', 'stdName' => 'Said Ali', 'stdEmail' => 'said@gmail.com', 'stdGAP' => 92.1],
        ['stdNo' => '10007', 'stdName' => 'Mohammed Ahmed', 'stdEmail' => 'mohamed@gmail.com', 'stdGAP' => 65.4],
    ];
}
if (isset($_POST['add'])) {
    $_SESSION['students'][] = [
        'stdNo' => $_POST['stdNo'],
        'stdName' => $_POST['stdName'],
        'stdEmail' => $_POST['stdEmail'],
        'stdGAP' => $_POST['stdGAP']
    ];
}
if (isset($_POST['edit'])) {
    $i = $_POST['index'];
    $_SESSION['students'][$i] = [
        'stdNo' => $_POST['stdNo'],
        'stdName' => $_POST['stdName'],
        'stdEmail' => $_POST['stdEmail'],
        'stdGAP' => $_POST['stdGAP']
    ];
}
if (isset($_GET['delete'])) {
    $i = $_GET['delete'];
    array_splice($_SESSION['students'], $i, 1);
}
$students = $_SESSION['students'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Students Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body.dark { background:#121212; color:#eee; }
.card {
  border-radius: 1rem;
  transition: all .3s ease-in-out;
  color:white;
}
.card:hover { transform: scale(1.05); box-shadow:0 0 20px rgba(0,0,0,0.4);}
.avatar {
  width:60px;height:60px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;font-weight:bold;color:#333;
}
.toast-container { position:fixed; top:1rem; right:1rem; z-index:9999;}
.gradient-1{background:linear-gradient(135deg,#6a11cb,#2575fc);}
.gradient-2{background:linear-gradient(135deg,#ff512f,#dd2476);}
.gradient-3{background:linear-gradient(135deg,#11998e,#38ef7d);}
.gradient-4{background:linear-gradient(135deg,#fc4a1a,#f7b733);}
.gradient-5{background:linear-gradient(135deg,#8360c3,#2ebf91);}
</style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">🎓 Students Manager</a>
    <div>
      <button class="btn btn-light me-2" onclick="toggleMode()">Dark/Light</button>
      <button class="btn btn-success" onclick="downloadPDF()">PDF</button>
      <button class="btn btn-warning" onclick="downloadExcel()">Excel</button>
      <button class="btn btn-info" onclick="downloadCSV()">CSV</button>
    </div>
  </div>
</nav>

<div class="container my-4">
  <div class="d-flex justify-content-between mb-3">
    <input class="form-control w-25" id="search" placeholder="🔎 Search...">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-circle"></i> Add Student</button>
  </div>

  <div class="row" id="studentCards">
    <?php 
    $gradients = ["gradient-1","gradient-2","gradient-3","gradient-4","gradient-5"];
    foreach ($students as $i=>$s): 
    ?>
    <div class="col-md-4 mb-3 student-card">
      <div class="card <?= $gradients[$i % count($gradients)] ?>">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <div class="avatar me-3"><?= strtoupper(substr($s['stdName'],0,1)) ?></div>
            <h5 class="card-title mb-0"><?= $s['stdName'] ?></h5>
          </div>
          <p><span class="badge bg-light text-dark">ID</span> <?= $s['stdNo'] ?></p>
          <p><span class="badge bg-warning">Email</span> <?= $s['stdEmail'] ?></p>
          <p><span class="badge bg-success">GPA</span> <?= $s['stdGAP'] ?></p>
          <div class="d-flex justify-content-between">
            <button class="btn btn-sm btn-light" onclick="editStudent(<?= $i ?>)">✏️ Edit</button>
            <button class="btn btn-sm btn-danger" onclick="deleteStudent(<?= $i ?>)">🗑 Delete</button>
            <button class="btn btn-sm btn-info" onclick="shareStudent(<?= $i ?>)">📤 Share</button>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="my-5">
    <h5>📊 Statistics</h5>
    <canvas id="pieChart" height="100"></canvas>
  </div>
</div>

<!-- Toast -->
<div class="toast-container"></div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="">
      <div class="modal-header"><h5 class="modal-title">Add Student</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input class="form-control mb-2" name="stdNo" placeholder="ID" required>
        <input class="form-control mb-2" name="stdName" placeholder="Name" required>
        <input class="form-control mb-2" type="email" name="stdEmail" placeholder="Email" required>
        <input class="form-control mb-2" type="number" step="0.1" name="stdGAP" placeholder="GPA" required>
      </div>
      <div class="modal-footer"><button type="submit" name="add" class="btn btn-primary">Save</button></div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="">
      <div class="modal-header"><h5 class="modal-title">Edit Student</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="index" id="editIndex">
        <input class="form-control mb-2" name="stdNo" id="editNo" required>
        <input class="form-control mb-2" name="stdName" id="editName" required>
        <input class="form-control mb-2" type="email" name="stdEmail" id="editEmail" required>
        <input class="form-control mb-2" type="number" step="0.1" name="stdGAP" id="editGAP" required>
      </div>
      <div class="modal-footer"><button type="submit" name="edit" class="btn btn-primary">Update</button></div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleMode(){document.body.classList.toggle('dark');showToast("Mode Changed");}
document.getElementById("search").addEventListener("keyup", function() {
  let val=this.value.toLowerCase();
  document.querySelectorAll(".student-card").forEach(c=>{
    c.style.display=c.innerText.toLowerCase().includes(val)?"":"none";
  });
});
function editStudent(i){
  let card=document.querySelectorAll(".student-card")[i];
  document.getElementById("editIndex").value=i;
  document.getElementById("editNo").value=card.querySelectorAll("p")[0].innerText.split(" ")[1];
  document.getElementById("editName").value=card.querySelector("h5").innerText;
  document.getElementById("editEmail").value=card.querySelectorAll("p")[1].innerText.split(" ")[1];
  document.getElementById("editGAP").value=card.querySelectorAll("p")[2].innerText.split(" ")[1];
  new bootstrap.Modal(document.getElementById('editModal')).show();
}
function deleteStudent(i){window.location.href="?delete="+i;}
function shareStudent(i){
  let card=document.querySelectorAll(".student-card")[i];
  let text=card.innerText;
  navigator.clipboard.writeText(text);
  showToast("Student info copied!");
}
function downloadPDF(){
  const { jsPDF }=window.jspdf;let doc=new jsPDF();let y=10;
  document.querySelectorAll(".student-card").forEach(c=>{
    doc.text(c.innerText,10,y);y+=50;
  });doc.save("students.pdf");showToast("PDF Downloaded");
}
function downloadExcel(){
  let wb=XLSX.utils.book_new();
  let data=[["ID","Name","Email","GPA"]];
  document.querySelectorAll(".student-card").forEach(c=>{
    let ps=c.querySelectorAll("p");
    data.push([ps[0].innerText.split(" ")[1],c.querySelector("h5").innerText,ps[1].innerText.split(" ")[1],ps[2].innerText.split(" ")[1]]);
  });
  let ws=XLSX.utils.aoa_to_sheet(data);
  XLSX.utils.book_append_sheet(wb,ws,"Students");
  XLSX.writeFile(wb,"students.xlsx");
  showToast("Excel Downloaded");
}
function downloadCSV(){
  let csv="ID,Name,Email,GPA\n";
  document.querySelectorAll(".student-card").forEach(c=>{
    let ps=c.querySelectorAll("p");
    csv+=ps[0].innerText.split(" ")[1]+","+c.querySelector("h5").innerText+","+ps[1].innerText.split(" ")[1]+","+ps[2].innerText.split(" ")[1]+"\n";
  });
  let blob=new Blob([csv],{type:"text/csv"});
  let a=document.createElement("a");
  a.href=URL.createObjectURL(blob);a.download="students.csv";a.click();
  showToast("CSV Downloaded");
}
function showToast(msg){
  let t=document.createElement("div");
  t.className="toast align-items-center text-bg-primary border-0 show mb-2";
  t.innerHTML=`<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
  document.querySelector(".toast-container").appendChild(t);
  setTimeout(()=>t.remove(),3000);
}
let gpas=[<?php foreach($students as $s){echo $s['stdGAP'].",";}?>];
let high=gpas.filter(g=>g>=90).length;
let mid=gpas.filter(g=>g>=70 && g<90).length;
let low=gpas.filter(g=>g<70).length;
new Chart(document.getElementById('pieChart'),{
  type:'pie',
  data:{labels:["High GPA","Medium GPA","Low GPA"],
  datasets:[{data:[high,mid,low],backgroundColor:["#28a745","#ffc107","#dc3545"]}]}
});
</script>
</body>
</html>

