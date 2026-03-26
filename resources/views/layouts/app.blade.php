<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Add this inside <head> -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>
:root {
    --primary: #1e293b;
    --primary-light: #334155;
    --accent: #2563eb;
    --accent-hover: #1d4ed8;
    --background: #f1f5f9;
    --white: #ffffff;
    --success: #16a34a;
    --danger: #dc2626;
    --text-dark: #0f172a;
    --text-light: #64748b;
}

/* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', sans-serif;
    background: var(--background);
    display: flex;
    min-height: 100vh;
    transition: all 0.3s ease;
}

/* ================= SIDEBAR ================= */
.sidebar {
    width: 260px;
    background: var(--primary);
    color: var(--white);
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    transition: transform 0.3s ease;
    box-shadow: 4px 0 20px rgba(0,0,0,0.1);
    z-index: 1000;
}

.sidebar-header {
    padding: 20px;
    font-size: 20px;
    font-weight: 600;
    background: var(--primary-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar-close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    display: none;
}

.nav-menu {
    list-style: none;
    margin-top: 10px;
}

.nav-link {
    display: block;
    padding: 14px 20px;
    color: #cbd5e1;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.nav-link i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.nav-link:hover {
    background: rgba(255,255,255,0.05);
    color: white;
    border-left: 4px solid var(--accent);
}

.nav-link.active {
    background: rgba(37,99,235,0.15);
    color: white;
    border-left: 4px solid var(--accent);
}

/* ================= MAIN CONTENT ================= */
.main-content {
    margin-left: 260px;
    padding: 40px;
    flex: 1;
    transition: margin-left 0.3s ease;
}

h1 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 30px;
    color: var(--text-dark);
}

h3 {
    font-size: 18px;
    font-weight: 600;
    margin: 25px 0 15px 0;
    color: var(--text-dark);
}

.header {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 25px;
    color: var(--text-dark);
}

.content-box {
    background: var(--white);
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

/* ================= TABLES ================= */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

table thead {
    background: linear-gradient(135deg, var(--primary) 0%, #5a0080 100%);
}

table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: white;
    font-size: 14px;
    letter-spacing: 0.5px;
}

table td {
    padding: 15px;
    border-bottom: 1px solid #e2e8f0;
    color: var(--text-dark);
    font-size: 14px;
}

table tbody tr:hover {
    background-color: #f8fafc;
    transition: background-color 0.2s ease;
}

table tbody tr:last-child td {
    border-bottom: none;
}

/* ================= FORMS ================= */
.form-group {
    margin-bottom: 25px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-dark);
    font-size: 14px;
}

input[type="text"],
input[type="email"],
input[type="phone"],
input[type="number"],
input[type="date"],
select,
textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.3s ease;
    background-color: #f8fafc;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="phone"]:focus,
input[type="number"]:focus,
input[type="date"]:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background-color: white;
}

textarea {
    resize: vertical;
    min-height: 120px;
}

/* ================= BUTTONS ================= */
.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a68d8 0%, #6a3fa0 100%);
}

.btn-success {
    background-color: var(--success);
    color: white;
}

.btn-success:hover {
    background-color: #16a34a;
}

.btn-danger {
    background-color: var(--danger);
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 12px;
}

.btn-group {
    display: flex;
    gap: 10px;
    margin-top: 30px;
}

/* ================= ACTION BUTTONS ================= */
.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
/* ================= ALERTS ================= */
.alert {
    padding: 15px 20px;
    border-radius: 6px;
    margin-bottom: 20px;
    border-left: 4px solid;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 500;
}

.alert-success {
    background-color: #ecfdf5;
    border-color: var(--success);
    color: #065f46;
}

.alert-danger,
.alert-error {
    background-color: #fef2f2;
    border-color: var(--danger);
    color: #7f1d1d;
}

.alert-warning {
    background-color: #fef3c7;
    border-color: #f59e0b;
    color: #78350f;
}

.alert-info {
    background-color: #eff6ff;
    border-color: #3b82f6;
    color: #1e3a8a;
}

.alert i {
    font-size: 18px;
    min-width: 22px;
}

/* Legacy alert styles for compatibility */
.success {
    background: #dcfce7;
    color: #166534;
}

.error {
    background: #fee2e2;
    color: #991b1b;
}

/* ================= EMPTY STATE ================= */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.empty-state i {
    font-size: 48px;
    color: #d1d5db;
    margin-bottom: 20px;
}

.empty-state h3 {
    margin: 20px 0 10px 0;
    color: #374151;
}

.empty-state p {
    margin: 0 0 25px 0;
    color: #9ca3af;
}

/* ================= SPACING UTILITIES ================= */
.mt-10 { margin-top: 10px; }
.mt-20 { margin-top: 20px; }
.mt-30 { margin-top: 30px; }
.mb-10 { margin-bottom: 10px; }
.mb-20 { margin-bottom: 20px; }
.mb-30 { margin-bottom: 30px; }
.p-10 { padding: 10px; }
.p-20 { padding: 20px; }
.p-30 { padding: 30px; }

.text-center {
    text-align: center;
}

.text-muted {
    color: #6b7280;
}

/* ================= TOGGLE BUTTON ================= */
.toggle-btn {
    position: fixed;
    top: 20px;
    right: 20px;
    background: var(--primary);
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
    display: none;
    z-index: 1100;
}

/* ================= MOBILE ================= */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }

    body.sidebar-open .sidebar {
        transform: translateX(0);
    }

    .sidebar-close-btn {
        display: block;
    }

    .main-content {
        margin-left: 0;
        padding: 80px 20px 20px 20px;
    }

    .toggle-btn {
        display: block;
    }
}
</style>
<style>
/* Custom Pagination Styles */
.custom-pagination {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 10px;
}
.custom-pagination .pagination {
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 0;
}
.custom-pagination .page-item {
    display: inline-block;
}
.custom-pagination .page-link {
    color: var(--primary);
    background: var(--white);
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 6px 14px;
    margin: 0 2px;
    font-size: 14px;
    transition: background 0.2s, color 0.2s;
}
.custom-pagination .page-item.active .page-link {
    background: var(--accent);
    color: #fff;
    border-color: var(--accent);
}
.custom-pagination .page-item.disabled .page-link {
    color: #b0b0b0;
    background: #f3f4f6;
    border-color: #e2e8f0;
    cursor: not-allowed;
}
.custom-pagination .pagination-summary {
    font-size: 13px;
    color: var(--text-light);
    margin-top: 4px;
}
@media (max-width: 600px) {
    .custom-pagination .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
    .custom-pagination .page-link {
        padding: 6px 8px;
        font-size: 12px;
    }
    .custom-pagination .pagination-summary {
        font-size: 12px;
    }
}
</style>
</head>

<body>

<button class="toggle-btn" onclick="toggleSidebar()">☰ Menu</button>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        Order System
        <button class="sidebar-close-btn" onclick="toggleSidebar()">✕</button>
    </div>

    <ul class="nav-menu">
         <li><a href="/" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="/customers" class="nav-link"><i class="fas fa-users"></i> Customers</a></li>
        <li><a href="/products" class="nav-link"><i class="fas fa-box"></i> Products</a></li>
        <li><a href="/orders" class="nav-link"><i class="fas fa-boxes"></i> Orders</a></li>
        <li><a href="/orders/pending" class="nav-link"><i class="fas fa-hourglass-half"></i> Pending</a></li>
        <li><a href="/orders/shipping" class="nav-link"><i class="fas fa-truck"></i> Shipping</a></li>
                <li><a href="/orders/completed" class="nav-link"><i class="fas fa-check-circle"></i> Completed</a></li>
        <li><a href="/orders/rejected" class="nav-link"><i class="fas fa-times-circle"></i> Rejected</a></li>



    </ul>
</div>

<div class="main-content">
    @yield('content')
</div>

<script>
function toggleSidebar() {
    document.body.classList.toggle('sidebar-open');
}
</script>

</body>
</html>
