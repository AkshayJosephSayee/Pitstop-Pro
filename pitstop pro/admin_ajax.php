<?php
// admin_ajax.php
require_once 'config.php';
require_once 'admin_functions.php';

header('Content-Type: application/json');

// Check admin session
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$data = json_decode($_POST['data'] ?? '[]', true) ?? [];

try {
    switch ($action) {
        case 'get_dashboard_stats':
            $stats = getDashboardStats();
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        case 'getBookings':
            $limit = $data['limit'] ?? 10;
            $bookings = getRecentBookings($limit);
            echo json_encode(['success' => true, 'data' => $bookings]);
            break;
            
        case 'getUsers':
            $users = getAllUsers();
            echo json_encode(['success' => true, 'data' => $users]);
            break;
            
        case 'getServices':
            $services = getAllServices();
            echo json_encode(['success' => true, 'data' => $services]);
            break;
            
        case 'getSlots':
            $date = $data['date'] ?? null;
            $range = $data['range'] ?? 'week';
            $slots = getAllSlots($date, $range);
            echo json_encode(['success' => true, 'data' => $slots]);
            break;
            
        case 'addUser':
            $result = addUser($data);
            echo json_encode(['success' => $result, 'error' => $result ? '' : 'Failed to add user']);
            break;
            
        case 'updateUser':
            $result = updateUser($data);
            echo json_encode(['success' => $result, 'error' => $result ? '' : 'Failed to update user']);
            break;
            
        case 'deleteUser':
            $userId = $data['id'] ?? null;
            $result = $userId ? deleteUser($userId) : false;
            echo json_encode(['success' => $result, 'error' => $result ? '' : 'Failed to delete user']);
            break;
            
        case 'addService':
            $result = addService($data);
            echo json_encode(['success' => $result, 'error' => $result ? '' : 'Failed to add service']);
            break;
            
        case 'updateService':
            $result = updateService($data);
            echo json_encode(['success' => $result, 'error' => $result ? '' : 'Failed to update service']);
            break;
            
        case 'deleteService':
            $serviceId = $data['id'] ?? null;
            $result = $serviceId ? deleteService($serviceId) : false;
            echo json_encode(['success' => $result, 'error' => $result ? '' : 'Failed to delete service']);
            break;
            
        case 'createSlots':
            $result = createSlots(
                $data['startDate'],
                $data['endDate'], 
                $data['startTime'],
                $data['endTime'],
                $data['duration'],
                $data['capacity'],
                $_SESSION['admin_id']
            );
            echo json_encode($result);
            break;
            
        case 'updateSlotStatus':
            $result = updateSlotStatus($data['slotId'], $data['status']);
            echo json_encode(['success' => $result, 'error' => $result ? '' : 'Failed to update slot status']);
            break;
            
        case 'deleteSlot':
            $result = deleteSlot($data['slotId']);
            echo json_encode($result);
            break;
            
        case 'getBookingDetails':
            $bookingId = $data['bookingId'] ?? null;
            if (!$bookingId) {
                echo json_encode(['success' => false, 'error' => 'Booking ID required']);
                break;
            }
            
            global $conn;
            $sql = "SELECT b.booking_id, c.Username as customer_name, b.service_type as service_name 
                    FROM tbl_bookings b 
                    JOIN tbl_customer c ON b.Username = c.Username 
                    WHERE b.booking_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();
            
            echo json_encode(['success' => true, 'data' => $booking]);
            break;
            
        case 'generateBill':
            $items = $data['items'] ?? [];
            $result = generateBill($data['bookingId'], $items);
            echo json_encode($result);
            break;
            
        case 'generate_report':
            // Basic report generation - you can expand this
            $reportData = [
                'date_range' => $data['from_date'] . ' to ' . $data['to_date'],
                'total_bookings' => rand(10, 100),
                'completed' => rand(5, 50),
                'pending' => rand(1, 20),
                'cancelled' => rand(0, 10)
            ];
            echo json_encode(['success' => true, 'data' => $reportData]);
            break;
        // Add these cases to admin_ajax.php

case 'getBillableBookings':
    $bookings = getBillableBookings();
    echo json_encode(['success' => true, 'data' => $bookings]);
    break;

case 'getBookingDetails':
    $bookingId = $data['booking_id'] ?? 0;
    $bookingDetails = getBookingDetails($bookingId);
    if ($bookingDetails) {
        echo json_encode(['success' => true, 'data' => $bookingDetails]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Booking not found']);
    }
    break;

case 'generateBill':
    $bookingId = $data['bookingId'] ?? 0;
    $additionalItems = $data['items'] ?? [];
    
    if (!$bookingId) {
        echo json_encode(['success' => false, 'error' => 'Booking ID is required']);
        break;
    }
    
    // Get booking details first
    $bookingDetails = getBookingDetails($bookingId);
    if (!$bookingDetails) {
        echo json_encode(['success' => false, 'error' => 'Booking not found']);
        break;
    }
    
    $baseAmount = $bookingDetails['service_price'] ?? 0;
    
    $result = createBill(
        $bookingId,
        $bookingDetails['customer_name'],
        $bookingDetails['email'],
        $bookingDetails['Phone'],
        $bookingDetails['service_type'],
        $baseAmount,
        $additionalItems
    );
    
    echo json_encode($result);
    break;
// Add to admin_ajax.php

case 'getBillableBookings':
    $bookings = getBillableBookings();
    echo json_encode(['success' => true, 'data' => $bookings]);
    break;

case 'getBookingDetails':
    $bookingId = $data['booking_id'] ?? 0;
    if (!$bookingId) {
        echo json_encode(['success' => false, 'error' => 'Booking ID is required']);
        break;
    }
    
    $bookingDetails = getBookingDetails($bookingId);
    if ($bookingDetails) {
        echo json_encode(['success' => true, 'data' => $bookingDetails]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Booking not found']);
    }
    break;

case 'generateBill':
    $bookingId = $data['bookingId'] ?? 0;
    $additionalItems = $data['items'] ?? [];
    
    if (!$bookingId) {
        echo json_encode(['success' => false, 'error' => 'Booking ID is required']);
        break;
    }
    
    // Get booking details
    $bookingDetails = getBookingDetails($bookingId);
    if (!$bookingDetails) {
        echo json_encode(['success' => false, 'error' => 'Booking not found']);
        break;
    }
    
    // Calculate total amount
    $baseAmount = floatval($bookingDetails['service_price'] ?? 0);
    $additionalAmount = 0;
    
    foreach ($additionalItems as $item) {
        $additionalAmount += floatval($item['amount'] ?? 0);
    }
    
    $totalAmount = $baseAmount + $additionalAmount;
    
    $result = createBill(
        $bookingId,
        $bookingDetails['customer_name'],
        $bookingDetails['service_type'],
        $totalAmount
    );
    
    echo json_encode($result);
    break;

case 'debugBookings':
    $debugInfo = debugBookings();
    echo json_encode(['success' => true, 'data' => $debugInfo]);
    break;

case 'getBillDetails':
    $billId = $data['bill_id'] ?? 0;
    $billDetails = getBillDetails($billId);
    $billItems = getBillItems($billId);
    
    if ($billDetails) {
        echo json_encode([
            'success' => true, 
            'data' => [
                'bill' => $billDetails,
                'items' => $billItems
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Bill not found']);
    }
    break; 
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
    }
} catch (Exception $e) {
    error_log("AJAX Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
