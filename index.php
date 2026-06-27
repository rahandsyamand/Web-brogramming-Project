<?php

$conn = mysqli_connect("localhost", "root", "", "hospitalsystem_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_patient') {
        $name   = mysqli_real_escape_string($conn, trim($_POST['name']));
        $age    = intval($_POST['age']);
        $gender = mysqli_real_escape_string($conn, $_POST['gender']);
        $phone  = mysqli_real_escape_string($conn, trim($_POST['phone']));
        $blood  = mysqli_real_escape_string($conn, $_POST['blood_type']);
        $doctor = mysqli_real_escape_string($conn, $_POST['doctor']);
        $notes  = mysqli_real_escape_string($conn, trim($_POST['notes']));

        if ($name !== '') {
            $res = mysqli_query($conn, "INSERT INTO patients (name,age,gender,phone,blood_type,doctor,notes,status)
                VALUES ('$name','$age','$gender','$phone','$blood','$doctor','$notes','Waiting')");
            if ($res) {
                $new_id = mysqli_insert_id($conn);
                $code   = 'P' . str_pad($new_id, 3, '0', STR_PAD_LEFT);
                mysqli_query($conn, "UPDATE patients SET patient_code='$code' WHERE id=$new_id");
                $message = "<div class='alert alert-success'>✔ Patient <strong>$name</strong> added successfully.</div>";
            }
        }
    }

    elseif ($action === 'delete_patient') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "DELETE FROM patients WHERE id=$id");
        $message = "<div class='alert alert-danger'>✔ Patient deleted.</div>";
    }

    elseif ($action === 'add_doctor') {
        $name  = mysqli_real_escape_string($conn, trim($_POST['name']));
        $spec  = mysqli_real_escape_string($conn, $_POST['specialization']);
        $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
        $days  = mysqli_real_escape_string($conn, trim($_POST['working_days']));
        $shift = mysqli_real_escape_string($conn, $_POST['shift']);

        if (!str_starts_with($name, 'Dr.')) $name = 'Dr. ' . $name;

        if ($name !== 'Dr. ') {
            $res = mysqli_query($conn, "INSERT INTO doctors (name,specialization,phone,working_days,shift,status)
                VALUES ('$name','$spec','$phone','$days','$shift','Available')");
            if ($res) {
                $new_id = mysqli_insert_id($conn);
                $code   = 'D' . str_pad($new_id, 3, '0', STR_PAD_LEFT);
                mysqli_query($conn, "UPDATE doctors SET doctor_code='$code' WHERE id=$new_id");
                $message = "<div class='alert alert-success'>✔ Doctor <strong>$name</strong> added successfully.</div>";
            }
        }
    }

    elseif ($action === 'delete_doctor') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "DELETE FROM doctors WHERE id=$id");
        $message = "<div class='alert alert-danger'>✔ Doctor deleted.</div>";
    }

    elseif ($action === 'add_room') {
        $num     = mysqli_real_escape_string($conn, trim($_POST['room_number']));
        $type    = mysqli_real_escape_string($conn, $_POST['type']);
        $floor   = mysqli_real_escape_string($conn, trim($_POST['floor']));
        $patient = mysqli_real_escape_string($conn, trim($_POST['patient']));
        $status  = ($patient !== '') ? 'Occupied' : 'Available';

        if ($num !== '') {
            mysqli_query($conn, "INSERT INTO rooms (room_number,type,floor,patient,status)
                VALUES ('$num','$type','$floor','$patient','$status')");
            $message = "<div class='alert alert-success'>✔ Room <strong>$num</strong> added successfully.</div>";
        }
    }

    elseif ($action === 'delete_room') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "DELETE FROM rooms WHERE id=$id");
        $message = "<div class='alert alert-danger'>✔ Room deleted.</div>";
    }

    elseif ($action === 'discharge_room') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "UPDATE rooms SET patient='', status='Available' WHERE id=$id");
        $message = "<div class='alert alert-success'>✔ Patient discharged. Room is now available.</div>";
    }

    elseif ($action === 'assign_room') {
        $id      = intval($_POST['id']);
        $patient = mysqli_real_escape_string($conn, trim($_POST['patient']));
        if ($patient !== '') {
            mysqli_query($conn, "UPDATE rooms SET patient='$patient', status='Occupied' WHERE id=$id");
            $message = "<div class='alert alert-success'>✔ Room assigned to <strong>$patient</strong>.</div>";
        }
    }

    elseif ($action === 'add_billing') {
        $patient  = mysqli_real_escape_string($conn, trim($_POST['patient']));
        $room_c   = floatval($_POST['room_charge']);
        $doc_fee  = floatval($_POST['doctor_fee']);
        $medicine = floatval($_POST['medicine']);
        $lab      = floatval($_POST['lab_tests']);
        $other    = floatval($_POST['other']);
        $total    = $room_c + $doc_fee + $medicine + $lab + $other;

        if ($patient !== '') {
            $res = mysqli_query($conn, "INSERT INTO billing (patient,room_charge,doctor_fee,medicine,lab_tests,other,total,status)
                VALUES ('$patient','$room_c','$doc_fee','$medicine','$lab','$other','$total','Pending')");
            if ($res) {
                $new_id = mysqli_insert_id($conn);
                $code   = 'INV-' . str_pad($new_id, 3, '0', STR_PAD_LEFT);
                mysqli_query($conn, "UPDATE billing SET invoice_code='$code' WHERE id=$new_id");
                $message = "<div class='alert alert-success'>✔ Invoice <strong>$code</strong> generated for <strong>$patient</strong>.</div>";
            }
        }
    }

    elseif ($action === 'add_appointment') {
        $patient = mysqli_real_escape_string($conn, trim($_POST['patient']));
        $doctor  = mysqli_real_escape_string($conn, trim($_POST['doctor']));
        $date    = mysqli_real_escape_string($conn, $_POST['appt_date']);
        $time    = mysqli_real_escape_string($conn, $_POST['appt_time']);
        $reason  = mysqli_real_escape_string($conn, trim($_POST['reason']));

        if ($patient !== '' && $doctor !== '' && $date !== '' && $time !== '') {
            $res = mysqli_query($conn, "INSERT INTO appointments (patient,doctor,appt_date,appt_time,reason,status)
                VALUES ('$patient','$doctor','$date','$time','$reason','Pending')");
            if ($res) {
                $new_id = mysqli_insert_id($conn);
                $code   = 'APT-' . str_pad($new_id, 3, '0', STR_PAD_LEFT);
                mysqli_query($conn, "UPDATE appointments SET appt_code='$code' WHERE id=$new_id");
                $message = "<div class='alert alert-success'>✔ Appointment <strong>$code</strong> booked for <strong>$patient</strong>.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>✘ Please fill in all required fields.</div>";
        }
    }

    elseif ($action === 'update_appt_status') {
        $id     = intval($_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        mysqli_query($conn, "UPDATE appointments SET status='$status' WHERE id=$id");
        $message = "<div class='alert alert-success'>✔ Appointment status updated to <strong>$status</strong>.</div>";
    }

    elseif ($action === 'delete_appointment') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "DELETE FROM appointments WHERE id=$id");
        $message = "<div class='alert alert-danger'>✔ Appointment deleted.</div>";
    }

    elseif ($action === 'delete_billing') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "DELETE FROM billing WHERE id=$id");
        $message = "<div class='alert alert-danger'>✔ Invoice deleted.</div>";
    }
}


$patients     = mysqli_query($conn, "SELECT * FROM patients     ORDER BY id DESC");
$doctors      = mysqli_query($conn, "SELECT * FROM doctors      ORDER BY id DESC");
$rooms        = mysqli_query($conn, "SELECT * FROM rooms        ORDER BY id ASC");
$invoices     = mysqli_query($conn, "SELECT * FROM billing      ORDER BY id DESC");
$appointments = mysqli_query($conn, "SELECT * FROM appointments ORDER BY appt_date ASC, appt_time ASC");

$patient_count  = mysqli_num_rows($patients);
$doctor_count   = mysqli_num_rows($doctors);
$room_occupied  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM rooms WHERE status='Occupied'"));
$today_appts    = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM appointments WHERE appt_date=CURDATE()"));
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Hospital Management System</title>
<style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: #f0f4f8;
      color: #333;
    }

  
    header {
      background-color: #1a6fa8;
      color: white;
      padding: 15px 30px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    header h1 {
      font-size: 22px;
      font-weight: bold;
    }

    header p {
      font-size: 13px;
      opacity: 0.85;
    }

    header .logo {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    header .logo-icon {
      width: 42px;
      height: 42px;
      background-color: white;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #1a6fa8;
      font-size: 22px;
      font-weight: bold;
    }

    nav {
      background-color: #155d8e;
      display: flex;
      gap: 0;
    }

    nav a {
      color: white;
      text-decoration: none;
      padding: 12px 20px;
      font-size: 14px;
      display: block;
    }

    nav a:hover, nav a.active {
      background-color: #1a6fa8;
    }

    
    .container {
      max-width: 1100px;
      margin: 25px auto;
      padding: 0 20px;
    }

    
    .dashboard-cards {
      display: flex;
      gap: 15px;
      margin-bottom: 25px;
      flex-wrap: wrap;
    }

    .card {
      background: white;
      border-radius: 8px;
      padding: 18px 22px;
      flex: 1;
      min-width: 180px;
      border-left: 5px solid #1a6fa8;
      box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    }

    .card h3 {
      font-size: 13px;
      color: #777;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .card .number {
      font-size: 28px;
      font-weight: bold;
      color: #1a6fa8;
    }

    .card.green { border-left-color: #28a745; }
    .card.green .number { color: #28a745; }
    .card.orange { border-left-color: #fd7e14; }
    .card.orange .number { color: #fd7e14; }
    .card.red { border-left-color: #dc3545; }
    .card.red .number { color: #dc3545; }

    
    .section {
      background: white;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    }

    .section h2 {
      font-size: 17px;
      margin-bottom: 15px;
      color: #1a6fa8;
      border-bottom: 1px solid #e0e0e0;
      padding-bottom: 8px;
    }

    
    .form-row {
      display: flex;
      gap: 15px;
      margin-bottom: 12px;
      flex-wrap: wrap;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      flex: 1;
      min-width: 180px;
    }

    .form-group label {
      font-size: 13px;
      margin-bottom: 4px;
      color: #555;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      padding: 8px 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
      outline: none;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: #1a6fa8;
    }

    .form-group textarea {
      resize: vertical;
      height: 70px;
    }

    .btn {
      padding: 9px 20px;
      border: none;
      border-radius: 5px;
      font-size: 14px;
      cursor: pointer;
    }

    .btn-primary {
      background-color: #1a6fa8;
      color: white;
    }

    .btn-primary:hover {
      background-color: #155d8e;
    }

    .btn-success {
      background-color: #28a745;
      color: white;
    }

    .btn-danger {
      background-color: #dc3545;
      color: white;
      font-size: 12px;
      padding: 5px 10px;
    }

    .btn-sm {
      font-size: 12px;
      padding: 5px 10px;
    }

    
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    table th {
      background-color: #1a6fa8;
      color: white;
      padding: 10px 12px;
      text-align: left;
    }

    table td {
      padding: 9px 12px;
      border-bottom: 1px solid #eee;
    }

    table tr:hover {
      background-color: #f5f9fd;
    }

    
    .badge {
      display: inline-block;
      padding: 3px 9px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: bold;
    }

    .badge-success { background-color: #d4edda; color: #155724; }
    .badge-warning { background-color: #fff3cd; color: #856404; }
    .badge-danger  { background-color: #f8d7da; color: #721c24; }
    .badge-info    { background-color: #d1ecf1; color: #0c5460; }

  
    .page { display: none; }
    .page.active { display: block; }

    
    footer {
      text-align: center;
      padding: 15px;
      font-size: 13px;
      color: #888;
      margin-top: 10px;
    }

    .two-col {
      display: flex;
      gap: 20px;
    }

    .two-col .section {
      flex: 1;
    }

    @media (max-width: 700px) {
      .two-col { flex-direction: column; }
      .dashboard-cards { flex-direction: column; }
    }
  </style>
</head>
<body>


<header>
  <div class="logo">
    <div class="logo-icon">+</div>
    <div>
      <h1>City General Hospital</h1>
      <p>Hospital Management System</p>
    </div>
  </div>
  <div style="font-size:13px; text-align:right;">
    <div>Welcome, <strong>Admin</strong></div>
    <div id="datetime" style="opacity:0.8;"></div>
  </div>
</header>


<nav>
  <a href="#" class="active" onclick="showPage('dashboard', this)">Dashboard</a>
  <a href="#" onclick="showPage('patients', this)">Patients</a>
  <a href="#" onclick="showPage('doctors', this)">Doctors</a>
  <a href="#" onclick="showPage('appointments', this)">Appointments</a>
  <a href="#" onclick="showPage('rooms', this)">Rooms</a>
  <a href="#" onclick="showPage('billing', this)">Billing</a>
</nav>

<div class="container">

  
  <div id="dashboard" class="page active">
    <div class="dashboard-cards">
      <div class="card">
        <h3>Total Patients</h3>
        <div class="number">248</div>
      </div>
      <div class="card green">
        <h3>Available Doctors</h3>
        <div class="number">18</div>
      </div>
      <div class="card orange">
        <h3>Today's Appointments</h3>
        <div class="number">34</div>
      </div>
      <div class="card red">
        <h3>Occupied Rooms</h3>
        <div class="number">61</div>
      </div>
    </div>

    <div class="two-col">
      <div class="section">
        <h2>Recent Patients</h2>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Age</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>P001</td><td>Ahmed Ali</td><td>34</td><td><span class="badge badge-success">Admitted</span></td></tr>
            <tr><td>P002</td><td>Sara Hassan</td><td>27</td><td><span class="badge badge-warning">Waiting</span></td></tr>
            <tr><td>P003</td><td>Omar Khalid</td><td>52</td><td><span class="badge badge-info">Discharged</span></td></tr>
            <tr><td>P004</td><td>Noor Salam</td><td>19</td><td><span class="badge badge-danger">Critical</span></td></tr>
            <tr><td>P005</td><td>Yusuf Kareem</td><td>45</td><td><span class="badge badge-success">Admitted</span></td></tr>
          </tbody>
        </table>
      </div>

      <div class="section">
        <h2>Today's Appointments</h2>
        <table>
          <thead>
            <tr>
              <th>Time</th>
              <th>Patient</th>
              <th>Doctor</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>09:00 AM</td><td>Ahmed Ali</td><td>Dr. Raza</td></tr>
            <tr><td>09:30 AM</td><td>Sara Hassan</td><td>Dr. Fatima</td></tr>
            <tr><td>10:00 AM</td><td>Omar Khalid</td><td>Dr. Karwan</td></tr>
            <tr><td>10:30 AM</td><td>Noor Salam</td><td>Dr. Raza</td></tr>
            <tr><td>11:00 AM</td><td>Yusuf Kareem</td><td>Dr. Layla</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  
  <div id="patients" class="page">
    <div class="section">
      <h2>Add New Patient</h2>
      <div class="form-row">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" placeholder="Enter patient name" />
        </div>
        <div class="form-group">
          <label>Age</label>
          <input type="number" placeholder="Age" />
        </div>
        <div class="form-group">
          <label>Gender</label>
          <select>
            <option>Male</option>
            <option>Female</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Phone Number</label>
          <input type="text" placeholder="Phone number" />
        </div>
        <div class="form-group">
          <label>Blood Type</label>
          <select>
            <option>A+</option><option>A-</option>
            <option>B+</option><option>B-</option>
            <option>AB+</option><option>AB-</option>
            <option>O+</option><option>O-</option>
          </select>
        </div>
        <div class="form-group">
          <label>Assigned Doctor</label>
          <select>
            <option>Dr. Raza - Cardiology</option>
            <option>Dr. Fatima - General</option>
            <option>Dr. Karwan - Neurology</option>
            <option>Dr. Layla - Pediatrics</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Diagnosis / Notes</label>
          <textarea placeholder="Enter diagnosis or notes..."></textarea>
        </div>
      </div>
      <button class="btn btn-primary" onclick="addPatient()">Add Patient</button>
    </div>

    <div class="section">
      <h2>Patient List</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Blood</th>
            <th>Doctor</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="patientTable">
          <tr><td>P001</td><td>Ahmed Ali</td><td>34</td><td>Male</td><td>O+</td><td>Dr. Raza</td><td><span class="badge badge-success">Admitted</span></td><td><button class="btn btn-danger">Delete</button></td></tr>
          <tr><td>P002</td><td>Sara Hassan</td><td>27</td><td>Female</td><td>A+</td><td>Dr. Fatima</td><td><span class="badge badge-warning">Waiting</span></td><td><button class="btn btn-danger">Delete</button></td></tr>
          <tr><td>P003</td><td>Omar Khalid</td><td>52</td><td>Male</td><td>B-</td><td>Dr. Karwan</td><td><span class="badge badge-info">Discharged</span></td><td><button class="btn btn-danger">Delete</button></td></tr>
          <tr><td>P004</td><td>Noor Salam</td><td>19</td><td>Female</td><td>AB+</td><td>Dr. Layla</td><td><span class="badge badge-danger">Critical</span></td><td><button class="btn btn-danger">Delete</button></td></tr>
        </tbody>
      </table>
    </div>
  </div>

  
  <div id="doctors" class="page">
    <div class="section">
      <h2>Add New Doctor</h2>
      <div class="form-row">
        <div class="form-group">
          <label>Doctor Name</label>
          <input type="text" placeholder="Full name" />
        </div>
        <div class="form-group">
          <label>Specialization</label>
          <select>
            <option>General Medicine</option>
            <option>Cardiology</option>
            <option>Neurology</option>
            <option>Pediatrics</option>
            <option>Orthopedics</option>
            <option>Surgery</option>
          </select>
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="text" placeholder="Contact number" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Working Days</label>
          <input type="text" placeholder="e.g. Mon - Fri" />
        </div>
        <div class="form-group">
          <label>Shift</label>
          <select>
            <option>Morning (8AM - 2PM)</option>
            <option>Evening (2PM - 8PM)</option>
            <option>Night (8PM - 8AM)</option>
          </select>
        </div>
      </div>
      <button class="btn btn-primary">Add Doctor</button>
    </div>

    <div class="section">
      <h2>Doctor List</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Specialization</th>
            <th>Phone</th>
            <th>Shift</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr><td>D001</td><td>Dr. Raza</td><td>Cardiology</td><td>0770-111-2233</td><td>Morning</td><td><span class="badge badge-success">Available</span></td></tr>
          <tr><td>D002</td><td>Dr. Fatima</td><td>General Medicine</td><td>0770-222-3344</td><td>Evening</td><td><span class="badge badge-success">Available</span></td></tr>
          <tr><td>D003</td><td>Dr. Karwan</td><td>Neurology</td><td>0770-333-4455</td><td>Morning</td><td><span class="badge badge-warning">On Leave</span></td></tr>
          <tr><td>D004</td><td>Dr. Layla</td><td>Pediatrics</td><td>0770-444-5566</td><td>Morning</td><td><span class="badge badge-success">Available</span></td></tr>
          <tr><td>D005</td><td>Dr. Hassan</td><td>Orthopedics</td><td>0770-555-6677</td><td>Night</td><td><span class="badge badge-success">Available</span></td></tr>
        </tbody>
      </table>
    </div>
  </div>

  
  <div id="appointments" class="page">
    <div class="section">
      <h2>Book Appointment</h2>
      <div class="form-row">
        <div class="form-group">
          <label>Patient Name</label>
          <input type="text" placeholder="Patient name" />
        </div>
        <div class="form-group">
          <label>Select Doctor</label>
          <select>
            <option>Dr. Raza - Cardiology</option>
            <option>Dr. Fatima - General</option>
            <option>Dr. Karwan - Neurology</option>
            <option>Dr. Layla - Pediatrics</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Date</label>
          <input type="date" />
        </div>
        <div class="form-group">
          <label>Time</label>
          <input type="time" />
        </div>
        <div class="form-group">
          <label>Reason</label>
          <input type="text" placeholder="Reason for visit" />
        </div>
      </div>
      <button class="btn btn-primary">Book Appointment</button>
    </div>

    <div class="section">
      <h2>Appointment Schedule</h2>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Date</th>
            <th>Time</th>
            <th>Reason</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr><td>1</td><td>Ahmed Ali</td><td>Dr. Raza</td><td>2025-05-01</td><td>09:00 AM</td><td>Chest pain</td><td><span class="badge badge-warning">Pending</span></td></tr>
          <tr><td>2</td><td>Sara Hassan</td><td>Dr. Fatima</td><td>2025-05-01</td><td>09:30 AM</td><td>Fever</td><td><span class="badge badge-success">Confirmed</span></td></tr>
          <tr><td>3</td><td>Omar Khalid</td><td>Dr. Karwan</td><td>2025-05-01</td><td>10:00 AM</td><td>Headache</td><td><span class="badge badge-success">Confirmed</span></td></tr>
          <tr><td>4</td><td>Noor Salam</td><td>Dr. Layla</td><td>2025-05-02</td><td>11:00 AM</td><td>Checkup</td><td><span class="badge badge-info">Completed</span></td></tr>
        </tbody>
      </table>
    </div>
  </div>

  
  <div id="rooms" class="page">
    <div class="section">
      <h2>Room Management</h2>
      <table>
        <thead>
          <tr>
            <th>Room No.</th>
            <th>Type</th>
            <th>Floor</th>
            <th>Patient</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr><td>101</td><td>General</td><td>1</td><td>Ahmed Ali</td><td><span class="badge badge-danger">Occupied</span></td><td><button class="btn btn-sm btn-success">Discharge</button></td></tr>
          <tr><td>102</td><td>General</td><td>1</td><td>-</td><td><span class="badge badge-success">Available</span></td><td><button class="btn btn-sm btn-primary">Assign</button></td></tr>
          <tr><td>201</td><td>ICU</td><td>2</td><td>Noor Salam</td><td><span class="badge badge-danger">Occupied</span></td><td><button class="btn btn-sm btn-success">Discharge</button></td></tr>
          <tr><td>202</td><td>ICU</td><td>2</td><td>-</td><td><span class="badge badge-success">Available</span></td><td><button class="btn btn-sm btn-primary">Assign</button></td></tr>
          <tr><td>301</td><td>Private</td><td>3</td><td>Yusuf Kareem</td><td><span class="badge badge-danger">Occupied</span></td><td><button class="btn btn-sm btn-success">Discharge</button></td></tr>
          <tr><td>302</td><td>Private</td><td>3</td><td>-</td><td><span class="badge badge-success">Available</span></td><td><button class="btn btn-sm btn-primary">Assign</button></td></tr>
          <tr><td>401</td><td>VIP</td><td>4</td><td>-</td><td><span class="badge badge-success">Available</span></td><td><button class="btn btn-sm btn-primary">Assign</button></td></tr>
        </tbody>
      </table>
    </div>
  </div>

  
  <div id="billing" class="page">
    <div class="section">
      <h2>Generate Invoice</h2>
      <div class="form-row">
        <div class="form-group">
          <label>Patient Name</label>
          <input type="text" placeholder="Patient name" />
        </div>
        <div class="form-group">
          <label>Room Charges ($)</label>
          <input type="number" placeholder="0.00" />
        </div>
        <div class="form-group">
          <label>Doctor Fee ($)</label>
          <input type="number" placeholder="0.00" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Medicine ($)</label>
          <input type="number" placeholder="0.00" />
        </div>
        <div class="form-group">
          <label>Lab Tests ($)</label>
          <input type="number" placeholder="0.00" />
        </div>
        <div class="form-group">
          <label>Other Charges ($)</label>
          <input type="number" placeholder="0.00" />
        </div>
      </div>
      <button class="btn btn-primary">Generate Invoice</button>
    </div>

    <div class="section">
      <h2>Billing Records</h2>
      <table>
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Patient</th>
            <th>Date</th>
            <th>Total ($)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr><td>INV-001</td><td>Ahmed Ali</td><td>2025-04-28</td><td>$450.00</td><td><span class="badge badge-success">Paid</span></td></tr>
          <tr><td>INV-002</td><td>Sara Hassan</td><td>2025-04-29</td><td>$180.00</td><td><span class="badge badge-warning">Pending</span></td></tr>
          <tr><td>INV-003</td><td>Omar Khalid</td><td>2025-04-30</td><td>$720.00</td><td><span class="badge badge-success">Paid</span></td></tr>
          <tr><td>INV-004</td><td>Noor Salam</td><td>2025-04-30</td><td>$1,200.00</td><td><span class="badge badge-danger">Unpaid</span></td></tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<footer>
  &copy; 2025 City General Hospital &mdash; Hospital Management System
</footer>

<script>

  function updateTime() {
    const now = new Date();
    document.getElementById('datetime').textContent =
      now.toLocaleDateString('en-GB') + '  ' + now.toLocaleTimeString('en-GB', {hour:'2-digit', minute:'2-digit'});
  }
  updateTime();
  setInterval(updateTime, 60000);

  function showPage(id, link) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('nav a').forEach(a => a.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    link.classList.add('active');
  }


  let patientCount = 5;
  function addPatient() {
    const name = document.querySelector('#patients input').value;
    if (!name) { alert('Please enter patient name.'); return; }
    patientCount++;
    const row = document.createElement('tr');
    row.innerHTML = `<td>P00${patientCount}</td><td>${name}</td><td>-</td><td>-</td><td>-</td><td>-</td><td><span class="badge badge-warning">Waiting</span></td><td><button class="btn btn-danger" onclick="this.closest('tr').remove()">Delete</button></td>`;
    document.getElementById('patientTable').appendChild(row);
  }
  
</script>


</table>
</div>
?>
</body>
</html>
