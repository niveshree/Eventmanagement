<?php
require_once './config/database.php';

class UserCRUD
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDBConnection();
    }

    // CREATE - Add new user
    public function createUser($data)
    {
        $username = $this->conn->real_escape_string($data['username']);
        $email = $this->conn->real_escape_string($data['email']);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $full_name = $this->conn->real_escape_string($data['full_name']);
        $role = isset($data['role']) ? $this->conn->real_escape_string($data['role']) : 'user';

        $sql = "INSERT INTO users (username, email, password, full_name, role) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $password, $full_name, $role);

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }

        return false;
    }

    // READ - Get all users
    public function getAllUsers($limit = 100, $offset = 0)
    {
        $sql = "SELECT id, username, email, full_name, role, status, created_at 
                FROM users 
                ORDER BY id DESC 
                LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();

        return $stmt->get_result();
    }

    // READ - Get single user by ID
    public function getUserById($id)
    {
        $sql = "SELECT id, username, email, full_name, role, status, created_at 
                FROM users 
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // UPDATE - Update user
    public function updateUser($id, $data)
    {
        $fields = [];
        $params = [];
        $types = '';

        if (isset($data['username'])) {
            $fields[] = "username = ?";
            $params[] = $this->conn->real_escape_string($data['username']);
            $types .= 's';
        }

        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $this->conn->real_escape_string($data['email']);
            $types .= 's';
        }

        if (isset($data['full_name'])) {
            $fields[] = "full_name = ?";
            $params[] = $this->conn->real_escape_string($data['full_name']);
            $types .= 's';
        }

        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $params[] = $this->conn->real_escape_string($data['role']);
            $types .= 's';
        }

        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $this->conn->real_escape_string($data['status']);
            $types .= 's';
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            $types .= 's';
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $types .= 'i';

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        return $stmt->execute();
    }

    // DELETE - Delete user
    public function deleteUser($id)
    {
        // Prevent deleting admin account
        if ($id == 1) {
            return false;
        }

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    // Search users
    public function searchUsers($search_term)
    {
        $search = "%" . $this->conn->real_escape_string($search_term) . "%";

        $sql = "SELECT id, username, email, full_name, role, status, created_at 
                FROM users 
                WHERE username LIKE ? 
                OR email LIKE ? 
                OR full_name LIKE ? 
                ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $search, $search, $search);
        $stmt->execute();

        return $stmt->get_result();
    }

    // Count total users
    public function countUsers()
    {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM users");
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}
