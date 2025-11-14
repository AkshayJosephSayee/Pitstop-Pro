<?php
require_once 'config.php';

function getDashboardStats() {
    global $conn;
    try {
        $stats = [
            'totalBookings' => 0,
            'pendingServices' => 0,
            'completedToday' => 0,
            'totalRevenue' => 0
        ];
        
        // Total bookings
        $result = $conn->query("SELECT COUNT(*) as total FROM tbl_bookings");
        if ($result) {
            $stats['totalBookings'] = $result->fetch_assoc()['total'];
        }
        
        // Pending services
        $result = $conn->query("SELECT COUNT(*) as pending FROM tbl_bookings WHERE b_status = 'pending'");
        if ($result) {
            $stats['pendingServices'] = $result->fetch_assoc()['pending'];
        }
        
        // Completed today
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT COUNT(*) as completed FROM tbl_bookings WHERE DATE(booking_date) = ? AND b_status = 'completed'");
        if ($stmt) {
            $stmt->bind_param("s", $today);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $stats['completedToday'] = $result->fetch_assoc()['completed'];
            }
        }
        
        // Total revenue
        $result = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM tbl_bill WHERE Payment_status = 'paid'");
        if ($result) {
            $stats['totalRevenue'] = $result->fetch_assoc()['revenue'];
        }
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Error in getDashboardStats: " . $e->getMessage());
        return $stats;
    }
}

function getRecentBookings($limit = 5) {
    global $conn;
    try {
        $sql = "SELECT booking_id, Username, service_type, booking_date, b_status 
                FROM tbl_bookings 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $limit);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        return $bookings;
    } catch (Exception $e) {
        error_log("Error in getRecentBookings: " . $e->getMessage());
        return [];
    }
}

function getAllUsers() {
    global $conn;
    try {
        $sql = "SELECT User_id_ as id, Username as name, email, Phone as phone
                FROM tbl_customer";
                
        $result = $conn->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $row['role'] = 'customer';
            $users[] = $row;
        }
        return $users;
    } catch (Exception $e) {
        error_log("Error in getAllUsers: " . $e->getMessage());
        return [];
    }
}

function getAllServices() {
    global $conn;
    try {
        $sql = "SELECT Service_id as id, service_type as name, description, price, Estimated_duration as duration
                FROM tbl_services
                ORDER BY service_type";
                
        $result = $conn->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        $services = [];
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
        return $services;
    } catch (Exception $e) {
        error_log("Error in getAllServices: " . $e->getMessage());
        return [];
    }
}

function getCompletedBookings() {
    global $conn;
    try {
        $sql = "SELECT booking_id, Username, service_type, booking_date 
                FROM tbl_bookings 
                WHERE b_status = 'completed'";
                
        $result = $conn->query($sql);
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        return $bookings;
    } catch (Exception $e) {
        error_log("Error in getCompletedBookings: " . $e->getMessage());
        return [];
    }
}

function getAllSlots($date = null, $range = 'week') {
    global $conn;
    try {
        $sql = "SELECT s.*, a.Username as created_by_name 
                FROM tbl_slot s 
                LEFT JOIN tbl_admins a ON s.created_by = a.admin_id 
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if ($date) {
            $sql .= " AND s.slot_date = ?";
            $params[] = $date;
            $types .= "s";
        } else {
            // Apply range filter
            $today = date('Y-m-d');
            switch ($range) {
                case 'today':
                    $sql .= " AND s.slot_date = ?";
                    $params[] = $today;
                    $types .= "s";
                    break;
                case 'tomorrow':
                    $tomorrow = date('Y-m-d', strtotime('+1 day'));
                    $sql .= " AND s.slot_date = ?";
                    $params[] = $tomorrow;
                    $types .= "s";
                    break;
                case 'week':
                    $nextWeek = date('Y-m-d', strtotime('+7 days'));
                    $sql .= " AND s.slot_date BETWEEN ? AND ?";
                    $params[] = $today;
                    $params[] = $nextWeek;
                    $types .= "ss";
                    break;
                case 'month':
                    $nextMonth = date('Y-m-d', strtotime('+30 days'));
                    $sql .= " AND s.slot_date BETWEEN ? AND ?";
                    $params[] = $today;
                    $params[] = $nextMonth;
                    $types .= "ss";
                    break;
                // 'all' shows all slots
            }
        }
        
        $sql .= " ORDER BY s.slot_date, s.start_time";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $slots = [];
        while ($row = $result->fetch_assoc()) {
            $slots[] = $row;
        }
        return $slots;
    } catch (Exception $e) {
        error_log("Error in getAllSlots: " . $e->getMessage());
        return [];
    }
}

function createSlots($startDate, $endDate, $startTime, $endTime, $duration, $capacity, $adminId) {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        $currentDate = $startDate;
        $slotsCreated = 0;
        
        while (strtotime($currentDate) <= strtotime($endDate)) {
            $currentTime = $startTime;
            
            while (strtotime($currentTime) < strtotime($endTime)) {
                // Calculate end time for this slot
                $endTimeForSlot = date('H:i:s', strtotime("$currentTime + $duration minutes"));
                
                // Check if slot already exists
                $checkSql = "SELECT slot_id FROM tbl_slot WHERE slot_date = ? AND start_time = ?";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param("ss", $currentDate, $currentTime);
                $checkStmt->execute();
                
                if ($checkStmt->get_result()->num_rows === 0) {
                    // Create new slot
                    $insertSql = "INSERT INTO tbl_slot (slot_date, start_time, end_time, max_capacity, created_by) VALUES (?, ?, ?, ?, ?)";
                    $insertStmt = $conn->prepare($insertSql);
                    $insertStmt->bind_param("sssii", $currentDate, $currentTime, $endTimeForSlot, $capacity, $adminId);
                    
                    if ($insertStmt->execute()) {
                        $slotsCreated++;
                    }
                }
                
                // Move to next time slot
                $currentTime = $endTimeForSlot;
            }
            
            // Move to next day
            $currentDate = date('Y-m-d', strtotime("$currentDate +1 day"));
        }
        
        $conn->commit();
        return ['success' => true, 'slots_created' => $slotsCreated];
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in createSlots: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function updateSlotStatus($slotId, $status) {
    global $conn;
    try {
        $sql = "UPDATE tbl_slot SET S_status = ? WHERE slot_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $slotId);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error in updateSlotStatus: " . $e->getMessage());
        return false;
    }
}

function deleteSlot($slotId) {
    global $conn;
    try {
        // Check if slot has any bookings
        $checkSql = "SELECT COUNT(*) as booking_count FROM tbl_bookings WHERE slot_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("i", $slotId);
        $checkStmt->execute();
        $result = $checkStmt->get_result()->fetch_assoc();
        
        if ($result['booking_count'] > 0) {
            return ['success' => false, 'error' => 'Cannot delete slot with existing bookings'];
        }
        
        $sql = "DELETE FROM tbl_slot WHERE slot_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $slotId);
        $success = $stmt->execute();
        
        return ['success' => $success, 'error' => $success ? '' : 'Failed to delete slot'];
    } catch (Exception $e) {
        error_log("Error in deleteSlot: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function addUser($data) {
    global $conn;
    try {
        $sql = "INSERT INTO tbl_customer (Username, email, Phone, Password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $hashedPassword = password_hash($data['password'] ?? 'default123', PASSWORD_DEFAULT);
        $stmt->bind_param("ssss", $data['name'], $data['email'], $data['phone'], $hashedPassword);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error in addUser: " . $e->getMessage());
        return false;
    }
}

function updateUser($data) {
    global $conn;
    try {
        $sql = "UPDATE tbl_customer SET Username = ?, email = ?, Phone = ? WHERE User_id_ = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $data['name'], $data['email'], $data['phone'], $data['id']);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error in updateUser: " . $e->getMessage());
        return false;
    }
}

function deleteUser($userId) {
    global $conn;
    try {
        $sql = "DELETE FROM tbl_customer WHERE User_id_ = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error in deleteUser: " . $e->getMessage());
        return false;
    }
}

function addService($data) {
    global $conn;
    try {
        $sql = "INSERT INTO tbl_services (service_type, description, price, Estimated_duration) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdi", $data['name'], $data['description'], $data['price'], $data['duration']);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error in addService: " . $e->getMessage());
        return false;
    }
}

function updateService($data) {
    global $conn;
    try {
        $sql = "UPDATE tbl_services SET service_type = ?, description = ?, price = ?, Estimated_duration = ? WHERE Service_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdii", $data['name'], $data['description'], $data['price'], $data['duration'], $data['id']);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error in updateService: " . $e->getMessage());
        return false;
    }
}

function deleteService($id) {
    global $conn;
    try {
        $sql = "DELETE FROM tbl_services WHERE Service_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error in deleteService: " . $e->getMessage());
        return false;
    }
}

// Add these functions to admin_functions.php


// Add to admin_functions.php - Debug function
function getServicePrice($serviceType) {
    global $conn;
    try {
        $sql = "SELECT price FROM tbl_services WHERE service_type = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $serviceType);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['price'];
        }
        return 0;
    } catch (Exception $e) {
        error_log("Error in getServicePrice: " . $e->getMessage());
        return 0;
    }
}

// Update the getBillableBookings function to ensure we get prices
function getBillableBookings() {
    global $conn;
    try {
        $sql = "SELECT b.booking_id, b.Username, b.email, b.Phone, b.service_type, 
                       b.booking_date, b.b_status, 
                       COALESCE(s.price, 0) as service_price
                FROM tbl_bookings b
                LEFT JOIN tbl_services s ON b.service_type = s.service_type
                WHERE b.b_status IN ('completed', 'confirmed', 'pending')
                AND b.booking_id NOT IN (
                    SELECT booking_id FROM tbl_bill WHERE booking_id IS NOT NULL
                )
                ORDER BY b.booking_date DESC, b.booking_id DESC";
                
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            // If service price is 0, try to get it from services table
            if (empty($row['service_price']) || $row['service_price'] == 0) {
                $row['service_price'] = getServicePrice($row['service_type']);
            }
            $bookings[] = $row;
        }
        return $bookings;
    } catch (Exception $e) {
        error_log("Error in getBillableBookings: " . $e->getMessage());
        return [];
    }
}

function getBookingDetails($bookingId) {
    global $conn;
    try {
        $sql = "SELECT b.booking_id, b.Username as customer_name, b.email, b.Phone, 
                       b.service_type, b.booking_date, s.price as service_price,
                       b.special_request
                FROM tbl_bookings b
                LEFT JOIN tbl_services s ON b.service_type = s.service_type
                WHERE b.booking_id = ?";
                
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $bookingId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error in getBookingDetails: " . $e->getMessage());
        return null;
    }
}

function generateBillNumber() {
    return 'BIL' . date('Ymd') . rand(1000, 9999);
}

function createBill($bookingId, $customerName, $serviceType, $totalAmount) {
    global $conn;
    
    try {
        // Generate bill number
        $billNumber = generateBillNumber();
        
        // Simple insert for your tbl_bill structure
        $sql = "INSERT INTO tbl_bill (booking_id, bill_number, customer_name, service_type, total_amount, Payment_status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("isssd", $bookingId, $billNumber, $customerName, $serviceType, $totalAmount);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $billId = $conn->insert_id;
        
        return [
            'success' => true, 
            'bill_id' => $billId,
            'bill_number' => $billNumber,
            'total_amount' => $totalAmount
        ];
        
    } catch (Exception $e) {
        error_log("Error in createBill: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Debug function to check what's in the bookings table
function debugBookings() {
    global $conn;
    try {
        $sql = "SELECT COUNT(*) as total_bookings FROM tbl_bookings";
        $result = $conn->query($sql);
        $total = $result->fetch_assoc()['total_bookings'];
        
        $sql2 = "SELECT booking_id, Username, service_type, b_status FROM tbl_bookings LIMIT 5";
        $result2 = $conn->query($sql2);
        $sample = [];
        while ($row = $result2->fetch_assoc()) {
            $sample[] = $row;
        }
        
        return [
            'total_bookings' => $total,
            'sample_data' => $sample
        ];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function getBillDetails($billId) {
    global $conn;
    try {
        $sql = "SELECT b.*, bk.booking_date, bk.special_request
                FROM tbl_bill b
                LEFT JOIN tbl_bookings bk ON b.booking_id = bk.booking_id
                WHERE b.bill_id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $billId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error in getBillDetails: " . $e->getMessage());
        return null;
    }
}



function getBillItems($billId) {
    global $conn;
    try {
        $sql = "SELECT * FROM tbl_bill_items WHERE bill_id = ? ORDER BY item_id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $billId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    } catch (Exception $e) {
        error_log("Error in getBillItems: " . $e->getMessage());
        return [];
    }
}
?>