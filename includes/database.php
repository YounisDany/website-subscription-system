<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    // الحصول على اتصال قاعدة البيانات
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            
            // تعيين خصائص PDO
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch(PDOException $exception) {
            echo "خطأ في الاتصال: " . $exception->getMessage();
        }

        return $this->conn;
    }

    // إغلاق الاتصال
    public function closeConnection() {
        $this->conn = null;
    }
}

// فئة لإدارة المستخدمين
class UserManager {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // إنشاء مستخدم جديد
    public function createUser($username, $email, $password, $is_admin = 0) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password, is_admin) 
                  VALUES (:username, :email, :password, :is_admin)";

        $stmt = $this->conn->prepare($query);

        // تشفير كلمة المرور
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // ربط القيم
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':is_admin', $is_admin);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // التحقق من تسجيل الدخول
// التحقق من تسجيل الدخول
public function login($username, $password) {
    $query = "SELECT id, username, email, password, is_admin 
              FROM " . $this->table_name . " 
              WHERE username = :username OR email = :email";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $username); // نفس القيمة لكن باسم مختلف
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // أفضل تحدد نوع الفتش
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return false;
}


    // الحصول على معلومات المستخدم
    public function getUserById($id) {
        $query = "SELECT id, username, email, is_admin, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    // التحقق من وجود اسم المستخدم
    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // التحقق من وجود البريد الإلكتروني
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // الحصول على جميع المستخدمين (للإدارة)
    public function getAllUsers() {
        $query = "SELECT u.id, u.username, u.email, u.is_admin, u.created_at,
                         s.start_date, s.end_date, s.is_active as subscription_active
                  FROM " . $this->table_name . " u
                  LEFT JOIN subscriptions s ON u.id = s.user_id
                  ORDER BY u.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // حذف مستخدم
    public function deleteUser($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}

// فئة لإدارة الاشتراكات
class SubscriptionManager {
    private $conn;
    private $table_name = "subscriptions";

    public function __construct($db) {
        $this->conn = $db;
    }

    // إنشاء اشتراك جديد
    public function createSubscription($user_id, $duration_seconds) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, start_date, end_date, duration_seconds, is_active) 
                  VALUES (:user_id, NOW(), DATE_ADD(NOW(), INTERVAL :duration_seconds SECOND), :duration_seconds, 1)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':duration_seconds', $duration_seconds);

        return $stmt->execute();
    }

    // الحصول على اشتراك المستخدم
    public function getUserSubscription($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND is_active = 1 
                  ORDER BY created_at DESC LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetch();
    }

    // التحقق من صحة الاشتراك
    public function isSubscriptionValid($user_id) {
        $subscription = $this->getUserSubscription($user_id);
        
        if ($subscription) {
            $now = new DateTime();
            $end_date = new DateTime($subscription['end_date']);
            
            if ($now <= $end_date) {
                return true;
            } else {
                // تعطيل الاشتراك المنتهي
                $this->deactivateSubscription($subscription['id']);
            }
        }
        
        return false;
    }

    // تعطيل الاشتراك
    public function deactivateSubscription($subscription_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_active = 0 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $subscription_id);
        return $stmt->execute();
    }

    // تحديث اشتراك المستخدم


    public function updateUserSubscription($user_id, $duration_seconds) {
        $start_date = date("Y-m-d H:i:s");
        $end_date   = date("Y-m-d H:i:s", time() + $duration_seconds);
        $now        = date("Y-m-d H:i:s");

        // لازم يكون عندك UNIQUE على user_id
        $query = "
            INSERT INTO {$this->table_name} 
                (user_id, start_date, end_date, duration_seconds, is_active, created_at, updated_at)
            VALUES 
                (:user_id, :start_date, :end_date, :duration_seconds, 1, :created_at, :updated_at)
            ON DUPLICATE KEY UPDATE 
                start_date       = VALUES(start_date),
                end_date         = VALUES(end_date),
                duration_seconds = VALUES(duration_seconds),
                is_active        = VALUES(is_active),
                updated_at       = VALUES(updated_at)
        ";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':user_id'          => $user_id,
            ':start_date'       => $start_date,
            ':end_date'         => $end_date,
            ':duration_seconds' => $duration_seconds,
            ':created_at'       => $now,
            ':updated_at'       => $now,
        ]);
    }


    // الحصول على الوقت المتبقي للاشتراك
    public function getRemainingTime($user_id) {
        $subscription = $this->getUserSubscription($user_id);
        
        if ($subscription && $this->isSubscriptionValid($user_id)) {
            $now = new DateTime();
            $end_date = new DateTime($subscription['end_date']);
            $diff = $end_date->diff($now);
            
            return [
                'days' => $diff->days,
                'hours' => $diff->h,
                'minutes' => $diff->i,
                'seconds' => $diff->s,
                'total_seconds' => $end_date->getTimestamp() - $now->getTimestamp()
            ];
        }
        
        return null;
    }
}
?>

