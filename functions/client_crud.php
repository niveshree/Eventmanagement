<?php
require_once './config/database.php';
require_once './config/upload_config.php';

class ClientCrud
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDBConnection();
    }

    // SIMPLE UPDATE METHOD - FIXED VERSION
    public function updateClientSimple($id, $data, $files = [])
    {
        try {
            // Start transaction
            $this->conn->begin_transaction();

            // Get existing client
            $existing = $this->getClientByIdSimple($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Client not found'];
            }

            // Prepare update data
            $updateData = [];
            $allowedFields = ['name', 'email', 'phone', 'note', 'address', 'url', 'location'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = trim($data[$field]);
                } else {
                    $updateData[$field] = $existing[$field] ?? '';
                }
            }

            // Handle profile picture upload
            if (!empty($files['pic']['name'])) {
                $picResult = $this->handleFileUploadSimple($files['pic'], 'pictures');
                if ($picResult['success']) {
                    $updateData['picture'] = $picResult['file_path'];
                    // Delete old picture if exists
                    if (!empty($existing['picture']) && file_exists($existing['picture'])) {
                        @unlink($existing['picture']);
                    }
                } else {
                    return ['success' => false, 'message' => 'Picture upload failed: ' . $picResult['message']];
                }
            } else {
                // Keep existing picture
                $updateData['picture'] = $existing['picture'] ?? null;
            }

            // Handle document file upload
            if (!empty($files['file']['name'])) {
                $fileResult = $this->handleFileUploadSimple($files['file'], 'files');
                if ($fileResult['success']) {
                    $updateData['file'] = $fileResult['file_path'];
                    // Delete old file if exists
                    if (!empty($existing['file']) && file_exists($existing['file'])) {
                        @unlink($existing['file']);
                    }
                } else {
                    return ['success' => false, 'message' => 'File upload failed: ' . $fileResult['message']];
                }
            } else {
                // Keep existing file
                $updateData['file'] = $existing['file'] ?? null;
            }

            // Handle multiple attachments
            $attachments = [];
            if (!empty($files['attachment']['name'][0])) {
                $attachmentsResult = $this->handleMultipleAttachmentsSimple($files['attachment']);
                if ($attachmentsResult['success']) {
                    $attachments = $attachmentsResult['files'];
                }
            }

            // Update client in database
            $updateResult = $this->updateClientBasic($id, $updateData);
            
            if (!$updateResult['success']) {
                $this->conn->rollback();
                return $updateResult;
            }

            // Save attachments
            if (!empty($attachments)) {
                $this->saveAttachmentsSimple($id, $attachments);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Client updated successfully'];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
        }
    }

    // SIMPLE UPDATE CLIENT BASIC - FIXED
    public function updateClientBasic($id, $data)
    {
        try {
            // Get existing client
            $existing = $this->getClientByIdSimple($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Client not found'];
            }

            // Build update query
            $updates = [];
            $params = [];
            $types = '';

            // Check each field and add if changed
            $fields = ['name', 'email', 'phone', 'note', 'address', 'url', 'location'];
            
            foreach ($fields as $field) {
                if (isset($data[$field]) && $data[$field] != $existing[$field]) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                    $types .= 's';
                }
            }

            // Handle picture
            if (isset($data['picture']) && $data['picture'] != $existing['picture']) {
                $updates[] = "picture = ?";
                $params[] = $data['picture'];
                $types .= 's';
            }

            // Handle file
            if (isset($data['file']) && $data['file'] != $existing['file']) {
                $updates[] = "file = ?";
                $params[] = $data['file'];
                $types .= 's';
            }

            // If no changes, return success
            if (empty($updates)) {
                return ['success' => true, 'message' => 'No changes detected'];
            }

            // Add updated_at
            $updates[] = "updated_at = NOW()";

            // Build SQL
            $sql = "UPDATE client SET " . implode(', ', $updates) . " WHERE id = ?";
            $params[] = $id;
            $types .= 'i';
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return ['success' => false, 'message' => 'Prepare failed: ' . $this->conn->error];
            }

            // Bind parameters correctly
            $bindParams = [$types];
            foreach ($params as $key => $value) {
                $bindParams[] = &$params[$key];
            }
            
            call_user_func_array([$stmt, 'bind_param'], $bindParams);

            if ($stmt->execute()) {
                $stmt->close();
                return ['success' => true, 'message' => 'Updated successfully'];
            } else {
                $error = $stmt->error;
                $stmt->close();
                return ['success' => false, 'message' => 'Execute failed: ' . $error];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
        }
    }

    // UPDATED: Accept $file array directly
    private function handleFileUploadSimple($file, $type = 'pictures')
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temp directory',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            return ['success' => false, 'message' => $errorMessages[$file['error']] ?? 'Unknown upload error'];
        }

        $uploadDir = UploadConfig::UPLOAD_DIRS[$type];
        
        // Create directory if not exists
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Sanitize filename
        $originalName = basename($file['name']);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '', pathinfo($originalName, PATHINFO_FILENAME));
        $filename = uniqid() . '_' . $safeName . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Validate file size (5MB limit)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'File size exceeds 5MB limit'];
        }

        // Validate image type for pictures
        if ($type === 'pictures') {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileInfo = @getimagesize($file['tmp_name']);
            if (!$fileInfo || !in_array($fileInfo['mime'], $allowedTypes)) {
                return ['success' => false, 'message' => 'Invalid image type. Allowed: JPG, PNG, GIF, WebP'];
            }
        }

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'file_path' => $filepath, 'original_name' => $originalName];
        }

        return ['success' => false, 'message' => 'Failed to save file'];
    }

    // UPDATED: Accept $files array directly
    private function handleMultipleAttachmentsSimple($files)
    {
        $uploadedFiles = [];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                $result = $this->handleFileUploadSimple($file, 'attachments');
                if ($result['success']) {
                    $uploadedFiles[] = [
                        'file_path' => $result['file_path'],
                        'original_name' => $result['original_name']
                    ];
                }
            }
        }

        return ['success' => !empty($uploadedFiles), 'files' => $uploadedFiles];
    }

    private function saveAttachmentsSimple($clientId, $attachments)
    {
        foreach ($attachments as $attachment) {
            $sql = "INSERT INTO client_attachments (user_id, file_path, original_name) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("iss", $clientId, $attachment['file_path'], $attachment['original_name']);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    public function getAttachmentsByUserId($id)
    {
        $sql = "SELECT * FROM client_attachments WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $attachments = [];
        while ($row = $result->fetch_assoc()) {
            $attachments[] = $row;
        }
        $stmt->close();
        return $attachments;
    }
    
    public function getClientByIdSimple($id)
    {
        $sql = "SELECT * FROM client WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $client = $result->fetch_assoc();
        $stmt->close();
        return $client;
    }

    // SIMPLE GET ALL CLIENTS
    public function getAllClientsSimple()
    {
        $sql = "SELECT * FROM client ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        $clients = [];
        while ($row = $result->fetch_assoc()) {
            $clients[] = $row;
        }
        return $clients;
    }

    // SIMPLE CREATE CLIENT
    public function createClientSimple($data, $files = [])
    {
        try {
            $insertData = [
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'note' => $data['note'] ?? '',
                'address' => $data['address'] ?? '',
                'url' => $data['url'] ?? '',
                'location' => $data['location'] ?? '',
                'picture' => null,
                'file' => null
            ];

            // Handle profile picture
            if (!empty($files['pic']['name'])) {
                $picResult = $this->handleFileUploadSimple($files['pic'], 'pictures');
                if ($picResult['success']) {
                    $insertData['picture'] = $picResult['file_path'];
                }
            }

            // Handle document file
            if (!empty($files['file']['name'])) {
                $fileResult = $this->handleFileUploadSimple($files['file'], 'files');
                if ($fileResult['success']) {
                    $insertData['file'] = $fileResult['file_path'];
                }
            }

            // Insert into database
            $sql = "INSERT INTO client (name, email, phone, note, address, url, location, picture, file, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sssssssss",
                $insertData['name'],
                $insertData['email'],
                $insertData['phone'],
                $insertData['note'],
                $insertData['address'],
                $insertData['url'],
                $insertData['location'],
                $insertData['picture'],
                $insertData['file']
            );

            if ($stmt->execute()) {
                $clientId = $this->conn->insert_id;
                $stmt->close();

                // Handle attachments
                if (!empty($files['attachment']['name'][0])) {
                    $attachmentsResult = $this->handleMultipleAttachmentsSimple($files['attachment']);
                    if ($attachmentsResult['success']) {
                        $this->saveAttachmentsSimple($clientId, $attachmentsResult['files']);
                    }
                }

                return ['success' => true, 'id' => $clientId, 'message' => 'Client created'];
            } else {
                $error = $stmt->error;
                $stmt->close();
                return ['success' => false, 'message' => $error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Create failed: ' . $e->getMessage()];
        }
    }

    // SIMPLE DELETE CLIENT
    public function deleteClientSimple($id)
    {
        try {
            // Get client first to delete files
            $client = $this->getClientByIdSimple($id);
            if ($client) {
                // Delete picture if exists
                if (!empty($client['picture']) && file_exists($client['picture'])) {
                    @unlink($client['picture']);
                }
                
                // Delete file if exists
                if (!empty($client['file']) && file_exists($client['file'])) {
                    @unlink($client['file']);
                }
                
                // Delete attachments
                $attachments = $this->getAttachmentsByUserId($id);
                foreach ($attachments as $attachment) {
                    if (file_exists($attachment['file_path'])) {
                        @unlink($attachment['file_path']);
                    }
                }
                
                // Delete attachment records
                $deleteAttachments = $this->conn->prepare("DELETE FROM client_attachments WHERE user_id = ?");
                $deleteAttachments->bind_param("i", $id);
                $deleteAttachments->execute();
                $deleteAttachments->close();
            }

            // Delete client
            $sql = "DELETE FROM client WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $stmt->close();
                return ['success' => true, 'message' => 'Client deleted'];
            } else {
                $error = $stmt->error;
                $stmt->close();
                return ['success' => false, 'message' => $error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()];
        }
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // In ClientCrud class, add this method:
    public function getClientsPaginated($limit, $offset)
    {
        try {
            $sql = "SELECT c.*, 
                    (SELECT COUNT(*) FROM client_attachments ca WHERE ca.user_id = c.id) as attachment_count 
                    FROM client c 
                    ORDER BY c.created_at DESC 
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $clients = [];
            while ($row = $result->fetch_assoc()) {
                $clients[] = $row;
            }
            
            $stmt->close();
            return $clients;
            
        } catch (Exception $e) {
            error_log("getClientsPaginated error: " . $e->getMessage());
            return [];
        }
    }
     public function countClients()
    {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM client");
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
 * Count clients created this month
 */
    public function countClientsThisMonth()
    {
        try {
            $currentMonth = date('Y-m');
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count 
                FROM client 
                WHERE DATE_FORMAT(created_at, '%Y-%m') = :currentMonth
            ");
            $stmt->execute(['currentMonth' => $currentMonth]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error counting clients this month: " . $e->getMessage());
            return 0;
        }
    }

   
}