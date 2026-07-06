<?php
class DbSessionHandler implements SessionHandlerInterface
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function open(string $savePath, string $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = $this->conn->prepare("SELECT session_data FROM sessions WHERE session_id = ? AND expires_at > NOW()");
        $stmt->execute([$id]);
        $row = $stmt->fetchColumn();
        return $row ?: '';
    }

    public function write(string $id, string $data): bool
    {
        $lifetime = (int)ini_get('session.gc_maxlifetime');
        $stmt = $this->conn->prepare(
            "INSERT INTO sessions (session_id, session_data, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))
             ON DUPLICATE KEY UPDATE session_data = VALUES(session_data), expires_at = VALUES(expires_at)"
        );
        return $stmt->execute([$id, $data, $lifetime]);
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE session_id = ?");
        return $stmt->execute([$id]);
    }

    public function gc(int $maxLifetime): int|false
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE expires_at <= NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }
}

function initDbSession(PDO $conn): void
{
    $handler = new DbSessionHandler($conn);
    session_set_save_handler($handler, true);
}
