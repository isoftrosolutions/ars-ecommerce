<?php

class ContactController
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function store($params)
    {
        $user = require_auth();
        $data = get_json_input();
        validate_required($data, ['subject', 'message']);
        ValidationErrors::throwIfInvalid();

        $subject = sanitize_string($data['subject']);
        $message = sanitize_string($data['message']);

        // Ensure contacts table exists
        try {
            $stmt = $this->pdo->query("SELECT 1 FROM contacts LIMIT 1");
        } catch (\Exception $e) {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS contacts (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    user_id INT(11) NOT NULL,
                    subject VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    created_at TIMESTAMP NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (id),
                    KEY idx_contacts_user (user_id),
                    CONSTRAINT fk_contacts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
            ");
        }

        $stmt = $this->pdo->prepare("INSERT INTO contacts (user_id, subject, message) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $subject, $message]);

        json_success(null, 'Your message has been submitted successfully', 201);
    }
}
