<?php
require_once './config/database.php';

class EventCRUD
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDBConnection();
    }

    // CREATE - Add new event
    public function createEvent($data, $file_name = null)
    {
        $sql = "INSERT INTO events (
            event_date,event_name, client_name, mobile, address, email, 
            venu_address, venu_contact, requirements, description, 
            model, image_url, location_url, status, attachment
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        // Get values from data array
        $event_date = $this->conn->real_escape_string($data['event_date'] ?? date('Y-m-d'));
        $event_name = $this->conn->real_escape_string($data['event_name'] ?? '');
        $client_name = $this->conn->real_escape_string($data['client_name'] ?? '');
        $mobile = $this->conn->real_escape_string($data['mobile'] ?? '');
        $address = $this->conn->real_escape_string($data['address'] ?? '');
        $email = $this->conn->real_escape_string($data['email'] ?? '');
        $venu_address = $this->conn->real_escape_string($data['venu_address'] ?? '');
        $venu_contact = $this->conn->real_escape_string($data['venu_contact'] ?? '');
        $requirements = $this->conn->real_escape_string($data['requirements'] ?? '');
        $description = $this->conn->real_escape_string($data['description'] ?? '');
        $model = $this->conn->real_escape_string($data['model'] ?? '');
        $image_url = $this->conn->real_escape_string($data['image_url'] ?? '');
        $location_url = $this->conn->real_escape_string($data['location_url'] ?? '');

        $status = $this->conn->real_escape_string($data['status'] ?? 'pending');
        $attachment = $file_name;

        // print_r($event_date);
        // die;

        $stmt->bind_param(
            "sssssssssssssss",
            $event_date,
            $event_name,
            $client_name,
            $mobile,
            $address,
            $email,
            $venu_address,
            $venu_contact,
            $requirements,
            $description,
            $model,
            $image_url,
            $location_url,
            $status,
            $attachment
        );

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }

        return false;
    }

    // READ - Get all events
    public function getAllEvents() {
        try {
            $sql = "SELECT e.* FROM events e ORDER BY e.event_date DESC";
            $result = $this->conn->query($sql);
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
        } catch (Exception $e) {
            error_log("getAllEvents error: " . $e->getMessage());
            return [];
        }
    }
    // public function getAllEvents($limit = 100, $offset = 0)
    // {
    //     $sql = "SELECT * FROM events ORDER BY created_at DESC LIMIT ? OFFSET ?";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->bind_param("ii", $limit, $offset);
    //     $stmt->execute();
    //     return $stmt->get_result();
    // }

    // READ - Get single event by ID
    public function getEventById($id)
    {
        $sql = "SELECT * FROM events WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // UPDATE - Update event
    public function updateEvent($id, $data, $file_name = null) {
        try {
            // Debug log
            error_log("Updating event ID: $id");
            error_log("Location URL in data: " . ($data['location_url'] ?? 'NOT SET'));
            
            // Start transaction
            $this->conn->begin_transaction();

            // Build update query
            $updates = [];
            $params = [];
            $types = '';
            
            // Add all fields to update
            $fields = [
                'event_date', 'event_name', 'client_name', 'mobile', 
                'address', 'email', 'venu_address', 'venu_contact',
                'requirements', 'description', 'model', 'image_url',
                'location_url', 'status'
            ];
            
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                    $types .= 's';
                }
            }
            
            // Add attachment if exists
            if ($file_name !== null) {
                $updates[] = "attachment = ?";
                $params[] = $file_name;
                $types .= 's';
            }
            
            // Add updated_at
            $updates[] = "updated_at = NOW()";
            
            // Prepare SQL
            $sql = "UPDATE events SET " . implode(', ', $updates) . " WHERE id = ?";
            $params[] = $id;
            $types .= 'i';
            
            // Debug log
            error_log("SQL Query: $sql");
            error_log("Params: " . print_r($params, true));
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return false;
            }
            
            // Bind parameters
            $bindParams = array_merge([$types], $params);
            $refs = [];
            foreach ($bindParams as $key => $value) {
                $refs[$key] = &$bindParams[$key];
            }
            
            call_user_func_array([$stmt, 'bind_param'], $refs);
            
            if ($stmt->execute()) {
                $this->conn->commit();
                $stmt->close();
                error_log("Update successful for event ID: $id");
                return true;
            } else {
                error_log("Execute failed: " . $stmt->error);
                $this->conn->rollback();
                $stmt->close();
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Update exception: " . $e->getMessage());
            if (isset($this->conn)) {
                $this->conn->rollback();
            }
            return false;
        }
    }

    // DELETE - Delete event
    public function deleteEvent($id)
    {
        // First get event to delete attachment file
        $event = $this->getEventById($id);

        $sql = "DELETE FROM events WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Delete attachment file if exists
            if (!empty($event['attachment'])) {
                $file_path = "uploads/" . $event['attachment'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            return true;
        }

        return false;
    }

    // Delete attachment file
    public function deleteAttachment($filename)
    {
        if (!empty($filename)) {
            $file_path = "uploads/" . $filename;
            if (file_exists($file_path)) {
                return unlink($file_path);
            }
        }
        return false;
    }


    // Count total events
    public function countEvents()
    {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM events");
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    // Search events
    public function searchEvents($search_term)
    {
        $search = "%" . $this->conn->real_escape_string($search_term) . "%";

        $sql = "SELECT * FROM events 
                WHERE event_name LIKE ? 
                OR client_name LIKE ? 
                OR mobile LIKE ? 
                OR email LIKE ? 
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $search, $search, $search, $search);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Handle file upload
    public function uploadAttachment($file)
    {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $file_name = uniqid() . '_' . basename($file['name']);
            $upload_dir = "uploads/";

            // Create uploads directory if not exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $target_file = $upload_dir . $file_name;

            // Check file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword'];
            $file_type = mime_content_type($file['tmp_name']);

            if (in_array($file_type, $allowed_types)) {
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    return $file_name;
                }
            }
        }

        return null;
    }

    public function getCalendarColor($type)
    {
        $colors = [
            'Business' => 'background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);',
            'Holiday' => '#28c76f',
            'Personal' => '#ea5455',
            'Family' => '#ff9f43',
            'ETC' => '#00cfe8'
        ];
        return $colors[$type] ?? 'background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);';
    }

    public function __destruct()
    {
        $this->conn->close();
    }

    public function getEventsPaginated($limit, $offset, $month = null, $status = null) {
        try {
            $sql = "SELECT e.* 
                    FROM events e 
                    WHERE 1=1";
            
            $params = [];
            $types = '';
            
            if (!empty($month)) {
                // Filter by month number regardless of year
                $sql .= " AND MONTH(e.event_date) = ?";
                $params[] = (int)$month;
                $types .= 'i';
            }
            
            if (!empty($status)) {
                if ($status == 'upcoming') {
                    $sql .= " AND e.event_date >= CURDATE() AND e.status != 'cancelled'";
                    // Removed status IN check to allow all non-cancelled future events, or keep if strict
                    // Let's keep it simple: Future and not cancelled
                } else if ($status == 'completed' || $status == 'cancelled') {
                    $sql .= " AND e.status = ?";
                    $params[] = $status;
                    $types .= 's';
                } else {
                    $sql .= " AND e.status = ?";
                    $params[] = $status;
                    $types .= 's';
                }
            }
            
            // CHANGE THIS LINE: Order by event_date instead of created_at
            $sql .= " ORDER BY e.event_date DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return [];
            }
            
            // Bind parameters
            if (!empty($params)) {
                $bindParams = array_merge([$types], $params);
                $refs = [];
                foreach ($bindParams as $key => $value) {
                    $refs[$key] = &$bindParams[$key];
                }
                call_user_func_array([$stmt, 'bind_param'], $refs);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
            
        } catch (Exception $e) {
            error_log("getEventsPaginated error: " . $e->getMessage());
            return [];
        }
    }
    public function countFilteredEvents($month = null, $status = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM events e WHERE 1=1";
            $params = [];
            $types = '';
            
            // Add month filter if provided
            if (!empty($month)) {
                // Month is in "04" format, convert to "2025-04"
                $year = date('Y');
                $monthFilter = $year . '-' . $month;
                $sql .= " AND DATE_FORMAT(e.event_date, '%Y-%m') = ?";
                $params[] = $monthFilter;
                $types .= 's';
            }
            
            // Add status filter if provided
            if (!empty($status)) {
                // Handle "upcoming" status which includes multiple statuses
                // Handle "upcoming" status which includes multiple statuses
                if ($status == 'upcoming') {
                    $sql .= " AND e.event_date >= CURDATE() AND e.status != 'cancelled'";
                } else if ($status == 'completed' || $status == 'cancelled') {
                    $sql .= " AND e.status = ?";
                    $params[] = $status;
                    $types .= 's';
                } else {
                    // For other statuses like pending, confirmed, etc.
                    $sql .= " AND e.status = ?";
                    $params[] = $status;
                    $types .= 's';
                }
            }
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return 0;
            }
            
            // Bind parameters
            if (!empty($params)) {
                $bindParams = array_merge([$types], $params);
                $refs = [];
                foreach ($bindParams as $key => $value) {
                    $refs[$key] = &$bindParams[$key];
                }
                call_user_func_array([$stmt, 'bind_param'], $refs);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("countFilteredEvents error: " . $e->getMessage());
            return 0;
        }
    }
    public function getUpcomingEventsPaginated($limit, $offset, $start, $end)
    {
        $sql = "SELECT *
                FROM events
                WHERE event_date BETWEEN ? AND ?
                ORDER BY event_date ASC
                LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", $start, $end, $limit, $offset);
        $stmt->execute();

        $result = $stmt->get_result();
        $events = [];

        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }

        return $events;
    }
    public function countUpcomingEvents($start, $end)
    {
        $sql = "SELECT COUNT(*) as total
                FROM events
                WHERE event_date BETWEEN ? AND ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $start, $end);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'] ?? 0;
    }

}
