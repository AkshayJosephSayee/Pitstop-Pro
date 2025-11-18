<?php
// admin_dashboard.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'admin_error.log');


// Include necessary files
require_once 'config.php';
require_once 'admin_functions.php';

// Fetch data for page
$dashboardStats = getDashboardStats();
$recentBookings = getRecentBookings(5);
$allUsers = getAllUsers();
$allServices = getAllServices();
$completedBookings = getCompletedBookings();
$allSlots = getAllSlots();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pitstop Pro - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Your complete CSS styles from the original code */
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh}
        .header{background:rgba(232, 231, 231, 0.95);padding:20px 40px;display:flex;justify-content:space-between;align-items:center}
        .tab-btn{padding:12px 30px;border-radius:8px;border:none;cursor:pointer}
        .tab-btn.active{background:#667eea;color:#fff}
        .tab-content{display:none;background:rgba(230, 227, 227, 1);padding:30px;border-radius:15px}
        .tab-content.active{display:block}
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px}
        .stat-card{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:25px;border-radius:12px}
        .status-badge{padding:5px 12px;border-radius:20px;font-weight:600}
        .status-pending{background:#fff3cd;color:#856404}
        .status-confirmed{background:#d1ecf1;color:#0c5460}
        .status-completed{background:#d4edda;color:#155724}
        .status-cancelled{background:#f8d7da;color:#721c24}
        .status-available{background:#d4edda;color:#155724}
        .status-fully-booked{background:#f8d7da;color:#721c24}
        .status-blocked{background:#fff3cd;color:#856404}
        .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center}
        .modal.active{display:flex}
        .modal-content{background:white;padding:30px;border-radius:15px;max-width:600px;width:90%}
        .bill-preview{border:2px solid #e0e0e0;padding:20px;border-radius:8px;margin-top:16px}
        .bill-row{display:flex;justify-content:space-between;margin-bottom:10px}
        .bill-total{border-top:2px solid #333;padding-top:10px;margin-top:10px;font-weight:bold;font-size:18px}
        .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:20px}
        .form-group{display:flex;flex-direction:column}
        label{font-weight:600;margin-bottom:8px;color:#333}
        input,select,textarea{padding:12px;border:2px solid #e0e0e0;border-radius:8px;font-size:14px;transition:border 0.3s}
        input:focus,select:focus,textarea:focus{outline:none;border-color:#667eea}
        .slot-date-filter{margin-bottom:20px;padding:15px;background:#f8f9fa;border-radius:8px}
        .bill-preview {
    border: 2px solid #e0e0e0;
    padding: 20px;
    border-radius: 8px;
    margin-top: 16px;
    background: #f9f9f9;
}

.bill-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    padding: 5px 0;
}

.bill-total {
    border-top: 2px solid #333;
    padding-top: 10px;
    margin-top: 10px;
    font-weight: bold;
    font-size: 18px;
}

.bill-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 10px;
    border-left: 4px solid #007bff;
}

.additional-items {
    margin-bottom: 20px;
}

.bill-summary {
    margin-top: 20px;
}
@media print {
    .header, .nav-tabs, .bill-actions, .no-print {
        display: none !important;
    }
    
    .tab-content {
        display: block !important;
    }
    
    body {
        background: white !important;
        color: black !important;
        margin: 0 !important;
        padding: 20px !important;
    }
    
    .bill-preview {
        border: 2px solid #000 !important;
        box-shadow: none !important;
    }
}
.bill-actions {
    display: flex;
    gap: 10px;
}
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">🏁 Pitstop Pro</div>
        <div class="admin-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['Username']); ?></span>
            <a id="logoutBtn" class="btn btn-danger" href="admin_logout.php" style="margin-left:12px;">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="nav-tabs" style="margin-top:18px;">
            <button class="tab-btn active" data-tab="dashboard" onclick="showTab('dashboard', this)">Dashboard</button>
            <button class="tab-btn" data-tab="reports" onclick="showTab('reports', this)">Reports</button>
            <button class="tab-btn" data-tab="users" onclick="showTab('users', this)">Manage Users</button>
            <button class="tab-btn" data-tab="services" onclick="showTab('services', this)">Manage Services</button>
            <button class="tab-btn" data-tab="slots" onclick="showTab('slots', this)">Manage Slots</button>
            <button class="tab-btn" data-tab="bills" onclick="showTab('bills', this)">Generate Bills</button>
        </div>

        <!-- Dashboard Tab -->
        <div id="dashboard" class="tab-content active">
            <h2 style="margin-bottom:20px">Dashboard Overview</h2>
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value"><?php echo (int)$dashboardStats['totalBookings']; ?></div><div class="stat-label">Total Bookings</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo (int)$dashboardStats['pendingServices']; ?></div><div class="stat-label">Pending Services</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo (int)$dashboardStats['completedToday']; ?></div><div class="stat-label">Completed Today</div></div>
                <div class="stat-card"><div class="stat-value">₹<?php echo number_format($dashboardStats['totalRevenue']); ?></div><div class="stat-label">Total Revenue</div></div>
            </div>

            <h3>Recent Bookings</h3>
            <table class="table table-striped">
                <thead>
                    <tr><th>Booking ID</th><th>Customer</th><th>Service</th><th>Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach($recentBookings as $booking): ?>
                    <tr>
                        <td><?php echo $booking['booking_id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['Username']); ?></td>
                        <td><?php echo htmlspecialchars($booking['service_type']); ?></td>
                        <td><?php echo $booking['booking_date']; ?></td>
                        <td><span class="status-badge status-<?php echo strtolower($booking['b_status']); ?>"><?php echo $booking['b_status']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Reports Tab -->
        <div id="reports" class="tab-content">
            <div class="report-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                <h2>Generate Reports</h2>
                <button class="btn btn-primary" onclick="exportReport()">Export PDF</button>
            </div>
            <div class="report-filters" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px">
                <div class="form-group"><label>Report Type</label><select id="reportType"><option value="booking">Booking Status Report</option><option value="service">Service Status Report</option><option value="revenue">Revenue Report</option><option value="slots">Slot Utilization Report</option></select></div>
                <div class="form-group"><label>From Date</label><input type="date" id="reportFromDate"></div>
                <div class="form-group"><label>To Date</label><input type="date" id="reportToDate"></div>
                <div class="form-group" style="align-self:end"><button class="btn btn-primary" onclick="generateReport()">Generate Report</button></div>
            </div>
            <div id="reportResults"></div>
        </div>

        <!-- Users Tab -->
        <div id="users" class="tab-content">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><h2>Manage Users</h2><button class="btn btn-primary" onclick="showAddUserModal()">Add New User</button></div>
            <table class="table"><thead><tr><th>User ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Actions</th></tr></thead><tbody id="usersTable"></tbody></table>
        </div>

        <!-- Services Tab -->
        <div id="services" class="tab-content">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><h2>Manage Services</h2><button class="btn btn-primary" onclick="showAddServiceModal()">Add New Service</button></div>
            <table class="table"><thead><tr><th>Service ID</th><th>Service Name</th><th>Description</th><th>Price</th><th>Duration</th><th>Actions</th></tr></thead><tbody id="servicesTable"></tbody></table>
        </div>

        <!-- Slots Tab -->
        <div id="slots" class="tab-content">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <h2>Manage Time Slots</h2>
                <button class="btn btn-primary" onclick="showCreateSlotsModal()">Create Multiple Slots</button>
            </div>
            
            <!-- Date Filter -->
            <div class="slot-date-filter">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Filter by Date</label>
                        <input type="date" id="slotDateFilter" onchange="loadSlots()">
                    </div>
                    <div class="form-group">
                        <label>Show Slots for</label>
                        <select id="slotRangeFilter" onchange="loadSlots()">
                            <option value="today">Today</option>
                            <option value="tomorrow">Tomorrow</option>
                            <option value="week">Next 7 Days</option>
                            <option value="month">Next 30 Days</option>
                            <option value="all">All Dates</option>
                        </select>
                    </div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Slot ID</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Capacity</th>
                        <th>Booked</th>
                        <th>Available</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="slotsTable"></tbody>
            </table>
        </div>

<div id="bills" class="tab-content">
    <h2 style="margin-bottom:20px">Generate Bill</h2>
    
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> Select a booking to generate a bill. The system will auto-populate customer and service details.
    </div>
    
    <div class="bill-generation-section">
        <div class="form-grid">
            <div class="form-group">
                <label>Select Booking *</label>
                <select id="billBooking" onchange="loadBookingDetails()" required class="form-control">
                    <option value="">Select a booking...</option>
                </select>
                <small class="form-text text-muted">Only shows bookings that don't have bills yet</small>
            </div>
            <div class="form-group">
                <label>Customer Name</label>
                <input type="text" id="billCustomerName" readonly class="form-control" placeholder="Select a booking to auto-fill">
            </div>
            <div class="form-group">
                <label>Customer Phone</label>
                <input type="text" id="billCustomerPhone" readonly class="form-control" placeholder="Select a booking to auto-fill">
            </div>
            <div class="form-group">
                <label>Service Type</label>
                <input type="text" id="billService" readonly class="form-control" placeholder="Select a booking to auto-fill">
            </div>
            <div class="form-group">
                <label>Base Service Price (₹)</label>
                <input type="number" id="billBasePrice" readonly class="form-control" placeholder="0.00" step="0.01">
            </div>
        </div>

        <h3 style="margin-top:30px;">Additional Charges</h3>
        <p class="text-muted">Add any extra charges like parts, labor, or other services.</p>
        
        <div id="additionalItems" class="additional-items"></div>
        
        <button type="button" class="btn btn-success" onclick="addBillItem()" style="margin-bottom:20px">
            <i class="fa fa-plus"></i> Add Additional Charge
        </button>

        <div class="bill-summary" style="margin-top:30px;">
            <h3>Bill Summary</h3>
            <div class="bill-preview" id="billPreview">
                <div class="bill-header" style="background:#f8f9fa; padding:15px; border-bottom:1px solid #ddd;">
                    <h4 style="margin:0; color:#333;">Bill Preview</h4>
                </div>
                <div id="billPreviewContent" style="padding:20px;">
                    <p class="text-muted">Select a booking to preview bill</p>
                </div>
            </div>
        </div>

        <div class="bill-actions" style="margin-top:30px; padding-top:20px; border-top:1px solid #ddd;">
            <button class="btn btn-primary btn-lg" onclick="generateBill()" id="generateBillBtn">
                <i class="fa fa-file-invoice"></i> Generate Bill
            </button>
            <button class="btn btn-secondary" onclick="clearBillForm()">
                <i class="fa fa-times"></i> Clear Form
            </button>
        </div>
    </div>
</div>

    <!-- User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="userModalTitle">Add New User</h3>
                <span class="close-modal" onclick="closeModal('userModal')">&times;</span>
            </div>
            <form id="userForm">
                <input type="hidden" id="editUserId">
                <div class="form-group"><label>Full Name</label><input type="text" id="userName" required></div>
                <div class="form-group"><label>Email</label><input type="email" id="userEmail" required></div>
                <div class="form-group"><label>Phone</label><input type="tel" id="userPhone" required></div>
                <div class="form-group"><label>Password</label><input type="password" id="userPassword" required></div>
                <div class="form-group"><label>Role</label><select id="userRole" required><option value="customer">Customer</option></select></div>
                <button type="submit" class="btn btn-primary">Save User</button>
            </form>
        </div>
    </div>

    <!-- Service Modal -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="serviceModalTitle">Add New Service</h3>
                <span class="close-modal" onclick="closeModal('serviceModal')">&times;</span>
            </div>
            <form id="serviceForm">
                <input type="hidden" id="editServiceId">
                <div class="form-group"><label>Service Name</label><input type="text" id="serviceName" required></div>
                <div class="form-group"><label>Description</label><textarea id="serviceDescription" required></textarea></div>
                <div class="form-group"><label>Price (₹)</label><input type="number" id="servicePrice" required></div>
                <div class="form-group"><label>Duration (hours)</label><input type="number" id="serviceDuration" step="0.5" required></div>
                <button type="submit" class="btn btn-primary">Save Service</button>
            </form>
        </div>
    </div>

    <!-- Create Slots Modal -->
    <div id="createSlotsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create Multiple Time Slots</h3>
                <span class="close-modal" onclick="closeModal('createSlotsModal')">&times;</span>
            </div>
            <form id="createSlotsForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" id="slotStartDate" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" id="slotEndDate" required>
                    </div>
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" id="slotStartTime" value="08:00" required>
                    </div>
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" id="slotEndTime" value="18:00" required>
                    </div>
                    <div class="form-group">
                        <label>Slot Duration (minutes)</label>
                        <input type="number" id="slotDuration" value="30" min="15" step="15" required>
                    </div>
                    <div class="form-group">
                        <label>Max Capacity per Slot</label>
                        <input type="number" id="slotCapacity" value="3" min="1" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create Slots</button>
            </form>
        </div>
    </div>

   <script>
    // Your complete JavaScript code here
    let bookings = <?php echo json_encode($recentBookings); ?>;
    let users = <?php echo json_encode($allUsers); ?>;
    let services = <?php echo json_encode($allServices); ?>;
    let slots = <?php echo json_encode($allSlots); ?>;
    let billItems = [];
    
    // Bill Management Variables
    let currentBookingDetails = null;
    let additionalItems = [];

    async function postAction(action, payload = {}) {
        const form = new URLSearchParams();
        form.append('action', action);
        if (Object.keys(payload).length) {
            form.append('data', JSON.stringify(payload));
        }
        const res = await fetch('admin_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: form.toString()
        });
        return res.json();
    }

    function showTab(tabName, btn){
        document.querySelectorAll('.tab-content').forEach(t=>t.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
        document.getElementById(tabName).classList.add('active');
        if(btn) btn.classList.add('active');
        
        if(tabName==='dashboard') loadDashboard();
        if(tabName==='users') loadUsers();
        if(tabName==='services') loadServices();
        if(tabName==='slots') loadSlots();
        if(tabName==='bills') {
            console.log('Initializing bills tab...');
            loadBillableBookings();
        }
    }

    // Dashboard functions
    async function loadDashboard(){
        try {
            const response = await postAction('get_dashboard_stats');
            if (response.success) {
                renderDashboard(response.data);
            } else {
                console.error('Error loading dashboard:', response.error);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function renderDashboard(data){
        // Update dashboard stats if needed
        console.log('Dashboard data:', data);
    }

    // Report functions
    async function generateReport() {
        const reportType = document.getElementById('reportType').value;
        const fromDate = document.getElementById('reportFromDate').value;
        const toDate = document.getElementById('reportToDate').value;

        if (!fromDate || !toDate) {
            alert('Please select both From Date and To Date');
            return;
        }

        try {
            const response = await postAction('generate_report', {
                type: reportType,
                from_date: fromDate,
                to_date: toDate
            });

            if (response.success) {
                const resultsDiv = document.getElementById('reportResults');
                let html = '';

                switch (reportType) {
                    case 'booking':
                        html = generateBookingReport(response.data);
                        break;
                    case 'service':
                        html = generateServiceReport(response.data);
                        break;
                    case 'revenue':
                        html = generateRevenueReport(response.data);
                        break;
                    case 'slots':
                        html = generateSlotReport(response.data);
                        break;
                }

                resultsDiv.innerHTML = html;
            } else {
                alert(response.error || 'Failed to generate report');
            }
        } catch (error) {
            console.error('Error generating report:', error);
            alert('An error occurred while generating the report');
        }
    }

    function generateBookingReport(data) {
        return `
            <div class="report-section">
                <h3>Booking Status Report (${data.date_range})</h3>
                <div class="stats-summary">
                    <div class="stat-item">Total Bookings: ${data.total_bookings}</div>
                    <div class="stat-item">Completed: ${data.completed}</div>
                    <div class="stat-item">Pending: ${data.pending}</div>
                    <div class="stat-item">Cancelled: ${data.cancelled}</div>
                </div>
            </div>`;
    }

    function generateServiceReport(data) {
        return `
            <div class="report-section">
                <h3>Service Status Report (${data.date_range})</h3>
                <div class="stats-summary">
                    <div class="stat-item">Total Services: ${data.total_services}</div>
                    <div class="stat-item">Most Popular: ${data.most_popular}</div>
                    <div class="stat-item">Average Duration: ${data.avg_duration}h</div>
                </div>
            </div>`;
    }

    function generateRevenueReport(data) {
        return `
            <div class="report-section">
                <h3>Revenue Report (${data.date_range})</h3>
                <div class="stats-summary">
                    <div class="stat-item">Total Revenue: ₹${data.total_revenue}</div>
                    <div class="stat-item">Average Daily Revenue: ₹${data.avg_daily_revenue}</div>
                    <div class="stat-item">Outstanding Amount: ₹${data.outstanding_amount}</div>
                </div>
            </div>`;
    }

    function generateSlotReport(data) {
        return `
            <div class="report-section">
                <h3>Slot Utilization Report (${data.date_range})</h3>
                <div class="stats-summary">
                    <div class="stat-item">Total Slots: ${data.total_slots}</div>
                    <div class="stat-item">Utilized: ${data.utilized_slots}</div>
                    <div class="stat-item">Utilization Rate: ${data.utilization_rate}%</div>
                </div>
            </div>`;
    }

    // Users functions
    async function loadUsers(){
        const res = await postAction('getUsers');
        if (res.success) {
            users = res.data || [];
            renderUsers();
        }
    }

    function renderUsers(){
        document.getElementById('usersTable').innerHTML = users.map(u=>`<tr><td>${u.id||''}</td><td>${u.name||''}</td><td>${u.email||''}</td><td>${u.phone||''}</td><td>${u.role||'customer'}</td><td><button class="btn btn-warning" onclick="showEditUser(${u.id||0})">Edit</button> <button class="btn btn-danger" onclick="deleteUser(${u.id||0})">Delete</button></td></tr>`).join('');
    }

    // Services functions
    async function loadServices(){
        const res = await postAction('getServices');
        if (res.success) { services = res.data || []; renderServices(); }
    }

    function renderServices(){
        document.getElementById('servicesTable').innerHTML = services.map(s=>`<tr><td>${s.id||s.Service_id||''}</td><td>${s.name||s.service_type||''}</td><td>${s.description||''}</td><td>₹${s.price||0}</td><td>${s.duration||s.Estimated_duration||''} hours</td><td><button class="btn btn-warning" onclick="showEditService(${s.id||s.Service_id||0})">Edit</button> <button class="btn btn-danger" onclick="deleteService(${s.id||s.Service_id||0})">Delete</button></td></tr>`).join('');
    }

    // Slots Management
    async function loadSlots() {
        try {
            const dateFilter = document.getElementById('slotDateFilter').value;
            const rangeFilter = document.getElementById('slotRangeFilter').value;
            
            const res = await postAction('getSlots', { 
                date: dateFilter, 
                range: rangeFilter 
            });
            
            if (res.success) {
                slots = res.data || [];
                renderSlots();
            }
        } catch (e) { console.error(e); }
    }

    function renderSlots() {
        const tbody = document.getElementById('slotsTable');
        tbody.innerHTML = slots.map(slot => `
            <tr>
                <td>${slot.slot_id}</td>
                <td>${slot.slot_date}</td>
                <td>${formatTime(slot.start_time)} - ${formatTime(slot.end_time)}</td>
                <td>${slot.max_capacity}</td>
                <td>${slot.current_bookings || 0}</td>
                <td>${slot.max_capacity - (slot.current_bookings || 0)}</td>
                <td>
                    <span class="status-badge status-${(slot.S_status || 'Available').toLowerCase().replace(' ', '-')}">
                        ${slot.S_status || 'Available'}
                    </span>
                </td>
                <td>
                    <select class="form-control" onchange="updateSlotStatus(${slot.slot_id}, this.value)" style="display:inline-block; width:auto;">
                        <option value="Available" ${(slot.S_status || 'Available') === 'Available' ? 'selected' : ''}>Available</option>
                        <option value="Blocked" ${(slot.S_status || 'Available') === 'Blocked' ? 'selected' : ''}>Blocked</option>
                    </select>
                    <button class="btn btn-danger btn-sm" onclick="deleteSlot(${slot.slot_id})">Delete</button>
                </td>
            </tr>
        `).join('');
    }

    function formatTime(timeString) {
        if (!timeString) return '';
        const time = new Date('1970-01-01T' + timeString + 'Z');
        return time.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    function showCreateSlotsModal() {
        document.getElementById('createSlotsModal').classList.add('active');
        // Set default dates
        const today = new Date().toISOString().split('T')[0];
        const nextWeek = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        document.getElementById('slotStartDate').value = today;
        document.getElementById('slotEndDate').value = nextWeek;
    }

    async function updateSlotStatus(slotId, newStatus) {
        try {
            const res = await postAction('updateSlotStatus', { 
                slotId: slotId, 
                status: newStatus 
            });
            
            if (res.success) {
                loadSlots();
            } else {
                alert(res.error || 'Failed to update slot status');
            }
        } catch (e) { console.error(e); }
    }

    async function deleteSlot(slotId) {
        if (!confirm('Are you sure you want to delete this slot? This action cannot be undone.')) {
            return;
        }
        
        try {
            const res = await postAction('deleteSlot', { slotId: slotId });
            
            if (res.success) {
                loadSlots();
            } else {
                alert(res.error || 'Failed to delete slot');
            }
        } catch (e) { console.error(e); }
    }

    // Modal functions
    function showAddUserModal() {
        document.getElementById('userModalTitle').textContent = 'Add New User';
        document.getElementById('editUserId').value = '';
        document.getElementById('userName').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userPhone').value = '';
        document.getElementById('userPassword').value = '';
        document.getElementById('userRole').value = 'customer';
        document.getElementById('userModal').classList.add('active');
    }

    function showAddServiceModal() {
        document.getElementById('serviceModalTitle').textContent = 'Add New Service';
        document.getElementById('editServiceId').value = '';
        document.getElementById('serviceName').value = '';
        document.getElementById('serviceDescription').value = '';
        document.getElementById('servicePrice').value = '';
        document.getElementById('serviceDuration').value = '';
        document.getElementById('serviceModal').classList.add('active');
    }

    // CORRECTED JavaScript Bill Functions

    async function loadBillableBookings() {
        try {
            console.log('Loading billable bookings...');
            
            const res = await postAction('getBillableBookings');
            console.log('Billable bookings response:', res);
            
            const select = document.getElementById('billBooking');
            select.innerHTML = '<option value="">Select a booking...</option>';
            
            if (res.success && res.data && res.data.length > 0) {
                res.data.forEach(booking => {
                    const option = document.createElement('option');
                    option.value = booking.booking_id;
                    
                    // Extract the data we need
                    const bookingId = booking.booking_id;
                    const customerName = booking.Username || 'Unknown Customer';
                    const serviceType = booking.service_type || 'Unknown Service';
                    const bookingDate = booking.booking_date || 'Unknown Date';
                    const servicePrice = parseFloat(booking.service_price) || 0;
                    
                    option.textContent = `#${bookingId} - ${customerName} - ${serviceType} (${bookingDate})`;
                    option.setAttribute('data-booking-id', bookingId);
                    option.setAttribute('data-customer-name', customerName);
                    option.setAttribute('data-customer-phone', booking.Phone || '');
                    option.setAttribute('data-customer-email', booking.email || '');
                    option.setAttribute('data-service-type', serviceType);
                    option.setAttribute('data-service-price', servicePrice);
                    
                    select.appendChild(option);
                });
                
                console.log(`Loaded ${res.data.length} billable bookings`);
            } else {
                console.log('No billable bookings found');
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No billable bookings available';
                option.disabled = true;
                select.appendChild(option);
            }
        } catch (error) {
            console.error('Error loading billable bookings:', error);
            const select = document.getElementById('billBooking');
            select.innerHTML = '<option value="">Error loading bookings</option>';
        }
    }

 async function loadBookingDetails() {
    const select = document.getElementById('billBooking');
    const selectedOption = select.selectedOptions[0];
    const bookingId = select.value;
    
    console.log('Selected booking ID:', bookingId);
    
    if (!bookingId || !selectedOption) {
        clearBookingDetails();
        return;
    }

    // Immediately update form with data from dropdown (fast)
    updateFormFromDropdown(selectedOption);
    
    // Then try to get more detailed information from server
    try {
        console.log('Calling getBookingDetails API with booking_id:', bookingId);
        
        const res = await postAction('getBookingDetails', { booking_id: parseInt(bookingId) });
        console.log('Booking details API response:', res);
        
        if (res.success && res.data) {
            currentBookingDetails = res.data;
            console.log('Received booking details:', res.data);
            
            // Update form with server data
            updateBookingForm(res.data);
        } else {
            console.error('Failed to load booking details:', res.error);
            // Show error but keep the dropdown data
            document.getElementById('billPreviewContent').innerHTML = 
                '<div class="alert alert-warning">Basic details loaded. Some details unavailable: ' + (res.error || 'Unknown error') + '</div>';
        }
    } catch (error) {
        console.error('Error loading booking details from API:', error);
        // Show error but keep the dropdown data
        document.getElementById('billPreviewContent').innerHTML = 
            '<div class="alert alert-warning">Basic details loaded. Network error loading additional details.</div>';
    }
    
    updateBillPreview();
}
    function updateFormFromDropdown(selectedOption) {
        if (!selectedOption) return;
        
        const customerName = selectedOption.getAttribute('data-customer-name') || '';
        const customerPhone = selectedOption.getAttribute('data-customer-phone') || '';
        const serviceType = selectedOption.getAttribute('data-service-type') || '';
        const servicePrice = parseFloat(selectedOption.getAttribute('data-service-price')) || 0;
        
        document.getElementById('billCustomerName').value = customerName;
        document.getElementById('billCustomerPhone').value = customerPhone;
        document.getElementById('billService').value = serviceType;
        document.getElementById('billBasePrice').value = servicePrice.toFixed(2);
        
        console.log('Form updated from dropdown:', {
            customerName,
            customerPhone,
            serviceType,
            servicePrice
        });
    }

    function updateBookingForm(booking) {
        if (booking) {
            // Only update if we have better data from server
            if (booking.customer_name && booking.customer_name !== 'Unknown Customer') {
                document.getElementById('billCustomerName').value = booking.customer_name;
            }
            if (booking.Phone) {
                document.getElementById('billCustomerPhone').value = booking.Phone;
            }
            if (booking.service_type && booking.service_type !== 'Unknown Service') {
                document.getElementById('billService').value = booking.service_type;
            }
            if (booking.service_price && booking.service_price > 0) {
                document.getElementById('billBasePrice').value = parseFloat(booking.service_price).toFixed(2);
            }
            
            console.log('Form updated from API:', booking);
        }
    }

    function clearBookingDetails() {
        currentBookingDetails = null;
        document.getElementById('billCustomerName').value = '';
        document.getElementById('billCustomerPhone').value = '';
        document.getElementById('billService').value = '';
        document.getElementById('billBasePrice').value = '';
        document.getElementById('additionalItems').innerHTML = '';
        additionalItems = [];
        document.getElementById('billPreviewContent').innerHTML = '<p class="text-muted">Select a booking to preview bill</p>';
    }

    function addBillItem() {
        const itemId = Date.now();
        const itemDiv = document.createElement('div');
        itemDiv.className = 'form-grid bill-item';
        itemDiv.setAttribute('data-item-id', itemId);
        itemDiv.innerHTML = `
            <div class="form-group">
                <label>Item Description *</label>
                <input type="text" class="form-control bill-item-desc" placeholder="e.g., Additional parts, Labor charges..." required oninput="updateBillItem(${itemId})">
            </div>
            <div class="form-group">
                <label>Amount (₹) *</label>
                <input type="number" class="form-control bill-item-amount" placeholder="0.00" min="0" step="0.01" required oninput="updateBillItem(${itemId})">
            </div>
            <div class="form-group" style="display:flex;align-items:end;">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeBillItem(${itemId})" title="Remove item">
                    <i class="fa fa-trash"></i> Remove
                </button>
            </div>
        `;
        document.getElementById('additionalItems').appendChild(itemDiv);
        
        // Add to items array
        additionalItems.push({
            id: itemId,
            description: '',
            amount: 0
        });
        
        updateBillPreview();
    }

    function updateBillItem(itemId) {
        const itemDiv = document.querySelector(`.bill-item[data-item-id="${itemId}"]`);
        if (!itemDiv) return;
        
        const description = itemDiv.querySelector('.bill-item-desc').value;
        const amount = parseFloat(itemDiv.querySelector('.bill-item-amount').value) || 0;
        
        const itemIndex = additionalItems.findIndex(item => item.id === itemId);
        if (itemIndex !== -1) {
            additionalItems[itemIndex] = {
                ...additionalItems[itemIndex],
                description: description,
                amount: amount
            };
        }
        
        updateBillPreview();
    }

    function removeBillItem(itemId) {
        const itemDiv = document.querySelector(`.bill-item[data-item-id="${itemId}"]`);
        if (itemDiv) {
            itemDiv.remove();
        }
        
        additionalItems = additionalItems.filter(item => item.id !== itemId);
        updateBillPreview();
    }

    function updateBillPreview() {
        const basePrice = parseFloat(document.getElementById('billBasePrice').value) || 0;
        const customerName = document.getElementById('billCustomerName').value;
        const serviceType = document.getElementById('billService').value;
        const additionalTotal = additionalItems.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
        const totalAmount = basePrice + additionalTotal;
        
        let previewHTML = '';
        
        if (customerName && serviceType) {
            previewHTML = `
                <div class="bill-details">
                    <div class="bill-row">
                        <strong>Customer:</strong>
                        <span>${customerName}</span>
                    </div>
                    <div class="bill-row">
                        <strong>Service:</strong>
                        <span>${serviceType}</span>
                    </div>
                    <div class="bill-row" style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">
                        <strong>Base Service Charge:</strong>
                        <span>₹${basePrice.toFixed(2)}</span>
                    </div>
            `;
            
            // Show additional items
            const validAdditionalItems = additionalItems.filter(item => item.description && item.amount > 0);
            if (validAdditionalItems.length > 0) {
                previewHTML += `<div style="margin-top:10px;"><strong>Additional Charges:</strong></div>`;
                validAdditionalItems.forEach(item => {
                    previewHTML += `
                        <div class="bill-row" style="padding-left:20px;">
                            <span>${item.description}</span>
                            <span>₹${parseFloat(item.amount).toFixed(2)}</span>
                        </div>
                    `;
                });
            }
            
            previewHTML += `
                <div class="bill-total">
                    <strong>Total Amount:</strong>
                    <strong>₹${totalAmount.toFixed(2)}</strong>
                </div>
            </div>
            `;
        } else {
            previewHTML = '<p class="text-muted">Select a booking to preview bill</p>';
        }
        
        document.getElementById('billPreviewContent').innerHTML = previewHTML;
    }

   async function generateBill() {
    const bookingId = document.getElementById('billBooking').value;
    const basePrice = parseFloat(document.getElementById('billBasePrice').value) || 0;
    
    if (!bookingId) {
        alert('❌ Please select a booking first');
        return;
    }
    
    if (basePrice === 0) {
        const confirmZero = confirm('⚠️ The base service price is ₹0.00. Are you sure you want to continue?');
        if (!confirmZero) {
            return;
        }
    }
    
    // Filter out empty items
    const validItems = additionalItems.filter(item => item.description && item.amount > 0);
    
    const generateBtn = document.getElementById('generateBillBtn');
    const originalText = generateBtn.innerHTML;
    generateBtn.disabled = true;
    generateBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generating Bill...';
    
    try {
        console.log('Generating bill for booking:', bookingId, 'with items:', validItems);
        
        const res = await postAction('generateBill', {
            bookingId: parseInt(bookingId),
            items: validItems
        });
        
        console.log('Bill generation response:', res);
        
        if (res.success) {
            // After generating bill successfully, print it
            await printGeneratedBill(res.data.bill_id);
            
            alert(`✅ Bill generated successfully!\n\n📄 Bill Number: ${res.data.bill_number}\n💰 Total Amount: ₹${res.data.total_amount.toFixed(2)}\n\nThe bill has been saved to the database and a print window has been opened.`);
            clearBillForm();
            await loadBillableBookings(); // Refresh the list
        } else {
            alert('❌ Error generating bill: ' + (res.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error generating bill:', error);
        alert('❌ Network error generating bill. Please check your connection and try again.');
    } finally {
        generateBtn.disabled = false;
        generateBtn.innerHTML = originalText;
    }
}

async function printGeneratedBill(billId) {
    try {
        const res = await postAction('printBill', { billId: billId });
        
        if (res.success) {
            // Open print window with the bill HTML
            const printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write(res.html);
            printWindow.document.close();
            
            // Wait for the window to load then trigger print
            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                // Don't close automatically - let user decide when to close
            };
        } else {
            console.error('Error generating print bill:', res.error);
            alert('Bill was generated but there was an error creating the print version.');
        }
    } catch (error) {
        console.error('Error printing bill:', error);
        alert('Bill was generated but there was an error opening the print window.');
    }
}

    function clearBillForm() {
        document.getElementById('billBooking').value = '';
        document.getElementById('additionalItems').innerHTML = '';
        additionalItems = [];
        clearBookingDetails();
    }

    async function exportReport() {
        alert('PDF export functionality would be implemented here');
        // This would typically generate a PDF file for download
    }

    // Form handlers
    document.getElementById('userForm').addEventListener('submit', async function(e){ 
        e.preventDefault(); 
        const id = document.getElementById('editUserId').value; 
        const payload = { 
            name: document.getElementById('userName').value, 
            email: document.getElementById('userEmail').value, 
            phone: document.getElementById('userPhone').value,
            password: document.getElementById('userPassword').value
        }; 
        if(id) payload.id = id; 
        const action = id ? 'updateUser' : 'addUser'; 
        const res = await postAction(action, payload); 
        if(res.success){ 
            document.getElementById('userModal').classList.remove('active'); 
            loadUsers(); 
        } else alert(res.error || 'Error'); 
    });

    document.getElementById('serviceForm').addEventListener('submit', async function(e){ 
        e.preventDefault(); 
        const id = document.getElementById('editServiceId').value; 
        const payload = { 
            name: document.getElementById('serviceName').value, 
            description: document.getElementById('serviceDescription').value, 
            price: parseFloat(document.getElementById('servicePrice').value)||0, 
            duration: parseFloat(document.getElementById('serviceDuration').value)||0 
        }; 
        if(id) payload.id = id; 
        const action = id ? 'updateService' : 'addService'; 
        const res = await postAction(action, payload); 
        if(res.success){ 
            document.getElementById('serviceModal').classList.remove('active'); 
            loadServices(); 
        } else alert(res.error || 'Error'); 
    });

    document.getElementById('createSlotsForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const payload = {
            startDate: document.getElementById('slotStartDate').value,
            endDate: document.getElementById('slotEndDate').value,
            startTime: document.getElementById('slotStartTime').value,
            endTime: document.getElementById('slotEndTime').value,
            duration: parseInt(document.getElementById('slotDuration').value),
            capacity: parseInt(document.getElementById('slotCapacity').value)
        };
        
        try {
            const res = await postAction('createSlots', payload);
            
            if (res.success) {
                alert('Slots created successfully!');
                document.getElementById('createSlotsModal').classList.remove('active');
                loadSlots();
            } else {
                alert(res.error || 'Failed to create slots');
            }
        } catch (e) {
            console.error(e);
            alert('Error creating slots');
        }
    });

    function closeModal(modalId){ 
        document.getElementById(modalId).classList.remove('active'); 
    }

    // Edit functions
    function showEditUser(userId) {
        const user = users.find(u => u.id == userId);
        if (user) {
            document.getElementById('userModalTitle').textContent = 'Edit User';
            document.getElementById('editUserId').value = user.id;
            document.getElementById('userName').value = user.name;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userPhone').value = user.phone;
            document.getElementById('userPassword').value = ''; // Don't show password
            document.getElementById('userRole').value = user.role;
            document.getElementById('userModal').classList.add('active');
        }
    }

    function showEditService(serviceId) {
        const service = services.find(s => (s.id || s.Service_id) == serviceId);
        if (service) {
            document.getElementById('serviceModalTitle').textContent = 'Edit Service';
            document.getElementById('editServiceId').value = service.id || service.Service_id;
            document.getElementById('serviceName').value = service.name || service.service_type;
            document.getElementById('serviceDescription').value = service.description;
            document.getElementById('servicePrice').value = service.price;
            document.getElementById('serviceDuration').value = service.duration || service.Estimated_duration;
            document.getElementById('serviceModal').classList.add('active');
        }
    }

    async function deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user?')) return;
        
        const res = await postAction('deleteUser', { id: userId });
        if (res.success) {
            loadUsers();
        } else {
            alert(res.error || 'Failed to delete user');
        }
    }

    async function deleteService(serviceId) {
        if (!confirm('Are you sure you want to delete this service?')) return;
        
        const res = await postAction('deleteService', { id: serviceId });
        if (res.success) {
            loadServices();
        } else {
            alert(res.error || 'Failed to delete service');
        }
    }

    // Initialize
    loadDashboard();
    loadUsers(); 
    loadServices();
    loadSlots();
</script>
</body>
</html>