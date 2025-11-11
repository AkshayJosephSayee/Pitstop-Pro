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

function generateBill($bookingId, $items) {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        // Get booking details
        $sql = "SELECT service_type FROM tbl_bookings WHERE booking_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        
        if (!$booking) {
            throw new Exception("Booking not found");
        }
        
        // Get service price
        $stmt = $conn->prepare("SELECT price FROM tbl_services WHERE service_type = ?");
        $stmt->bind_param("s", $booking['service_type']);
        $stmt->execute();
        $service = $stmt->get_result()->fetch_assoc();
        
        $basePrice = $service ? $service['price'] : 0;
        
        // Calculate total with additional items
        $totalAmount = $basePrice;
        foreach ($items as $item) {
            $totalAmount += $item['amount'];
        }
        
        // Create bill
        $sql = "INSERT INTO tbl_bill (booking_id, total_amount, Payment_status) VALUES (?, ?, 'not paid')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $bookingId, $totalAmount);
        $stmt->execute();
        
        $billId = $conn->insert_id;
        
        $conn->commit();
        return ['success' => true, 'bill_id' => $billId, 'total' => $totalAmount];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>