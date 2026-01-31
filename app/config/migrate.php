<?php

function runMigration(PDO $conn): void
{
    try {
        // Cek apakah table users sudah ada
        $stmt = $conn->query("
            SELECT EXISTS (
                SELECT 1 FROM information_schema.tables 
                WHERE table_name = 'users'
            )
        ");
        $tableExists = $stmt->fetchColumn();

        if (!$tableExists) {
            // Baca schema file
            $schemaFile = __DIR__ . '/../database/skema_postgresql.sql';
            
            if (file_exists($schemaFile)) {
                $schema = file_get_contents($schemaFile);
                
                // Pisahkan statements (remove CREATE DATABASE dan \c commands untuk Railway)
                $statements = array_filter(array_map('trim', preg_split('/;/', $schema)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement) && !preg_match('/^(CREATE DATABASE|\\\\c)/i', $statement)) {
                        try {
                            $conn->exec($statement . ';');
                        } catch (Exception $e) {
                            // Skip jika table sudah ada (CREATE TABLE IF NOT EXISTS)
                            if (strpos($e->getMessage(), 'already exists') === false) {
                                error_log("Migration error: " . $e->getMessage());
                            }
                        }
                    }
                }
                
                error_log("Migration completed: Tables created successfully");
            } else {
                error_log("Warning: Schema file not found at " . $schemaFile);
            }
        }
    } catch (Exception $e) {
        error_log("Migration check failed: " . $e->getMessage());
    }
}
