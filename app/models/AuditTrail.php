<?php

require_once __DIR__ . '/../config/database.php';

class AuditTrail {
    private $conn;
    private $table = 'audit_logs';

    public function __construct(?PDO $conn = null) {
        if ($conn instanceof PDO) {
            $this->conn = $conn;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
        $this->ensureTable();
    }

    private function ensureTable(): void {
        try {
            $this->conn->exec("CREATE TABLE IF NOT EXISTS audit_logs (
                id_audit SERIAL PRIMARY KEY,
                modul VARCHAR(60) NOT NULL,
                aksi VARCHAR(60) NOT NULL,
                entitas VARCHAR(60) NOT NULL,
                id_entitas VARCHAR(64) NULL,
                deskripsi TEXT NULL,
                data_lama JSONB NULL,
                data_baru JSONB NULL,
                metadata JSONB NULL,
                id_user INT NULL REFERENCES users(id_user) ON DELETE SET NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_audit_logs_modul_created ON audit_logs(modul, created_at DESC)");
            $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_audit_logs_entitas ON audit_logs(entitas, id_entitas)");
        } catch (Exception $e) {
            error_log('ensure audit_logs error: ' . $e->getMessage());
        }
    }

    public function log(array $payload): bool {
        try {
            $query = "INSERT INTO " . $this->table . "
                      (modul, aksi, entitas, id_entitas, deskripsi, data_lama, data_baru, metadata, id_user)
                      VALUES
                      (:modul, :aksi, :entitas, :id_entitas, :deskripsi, :data_lama::jsonb, :data_baru::jsonb, :metadata::jsonb, :id_user)";
            $stmt = $this->conn->prepare($query);

            $modul = (string)($payload['modul'] ?? 'sistem');
            $aksi = (string)($payload['aksi'] ?? 'unknown');
            $entitas = (string)($payload['entitas'] ?? 'unknown');
            $idEntitas = isset($payload['id_entitas']) ? (string)$payload['id_entitas'] : null;
            $deskripsi = isset($payload['deskripsi']) ? (string)$payload['deskripsi'] : null;
            $dataLama = json_encode($payload['data_lama'] ?? null, JSON_UNESCAPED_UNICODE);
            $dataBaru = json_encode($payload['data_baru'] ?? null, JSON_UNESCAPED_UNICODE);
            $metadata = json_encode($payload['metadata'] ?? null, JSON_UNESCAPED_UNICODE);
            $idUser = isset($payload['id_user']) && $payload['id_user'] !== '' ? (int)$payload['id_user'] : null;

            $stmt->bindParam(':modul', $modul);
            $stmt->bindParam(':aksi', $aksi);
            $stmt->bindParam(':entitas', $entitas);
            $stmt->bindValue(':id_entitas', $idEntitas, $idEntitas === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':deskripsi', $deskripsi, $deskripsi === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':data_lama', $dataLama, PDO::PARAM_STR);
            $stmt->bindValue(':data_baru', $dataBaru, PDO::PARAM_STR);
            $stmt->bindValue(':metadata', $metadata, PDO::PARAM_STR);
            $stmt->bindValue(':id_user', $idUser, $idUser === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log('audit log error: ' . $e->getMessage());
            return false;
        }
    }
}
