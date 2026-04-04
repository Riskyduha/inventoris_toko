<?php

function runMigration(PDO $conn): void
{
    try {
        // BETTER: Cek apakah table users sudah ada menggunakan information_schema
        // yang lebih reliable untuk PostgreSQL
        try {
            $query = "SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'users' LIMIT 1";
            $stmt = $conn->query($query);
            $result = $stmt->fetch();
            $tableExists = ($result !== false);
            
            if ($tableExists) {
                // Pastikan constraint role sudah mendukung role terbaru.
                syncUsersRoleConstraint($conn);
                error_log("✓ Migration: users table already exists, skipping creation");
                return;
            }
        } catch (Exception $checkError) {
            error_log("⚠ Migration: Table check query failed, will attempt to create tables: " . $checkError->getMessage());
        }

        error_log("→ Migration: Starting table creation...");
        
        // Load schema dari file atau embedded fallback
        $schema = null;
        $projectRoot = dirname(__DIR__, 2);
        $schemaFile = $projectRoot . '/database/skema_postgresql.sql';
        
        // Try file first
        if (file_exists($schemaFile)) {
            error_log("→ Migration: Reading schema from file: " . $schemaFile);
            $schema = file_get_contents($schemaFile);
        } else {
            // Fallback ke embedded schema
            error_log("→ Migration: File not found, using embedded schema");
            require_once __DIR__ . '/schema_embedded.php';
            $schema = getEmbeddedSchema();
        }
        
        if (!$schema) {
            error_log("✗ Migration: FATAL - No schema available!");
            return;
        }

        // Hapus komentar baris tunggal SQL agar statement CREATE TABLE tidak ikut ter-skip
        $schema = preg_replace('/^\s*--.*$/m', '', $schema);

        // Parse dan execute statements
        $statements = array_filter(array_map('trim', preg_split('/;/', $schema)));
        error_log("→ Migration: Found " . count($statements) . " statements to execute");
        
        $created = 0;
        $skipped = 0;
        $errors = [];
        
        foreach ($statements as $index => $statement) {
            if (empty($statement) || preg_match('/^(CREATE DATABASE|\\\\c)/i', $statement)) {
                $skipped++;
                continue;
            }
            
            try {
                $conn->exec($statement . ';');
                $created++;
                
                // Log short statement for debugging
                $stmt_short = substr(trim($statement), 0, 50);
                error_log("  ✓ " . $stmt_short . "...");
                
            } catch (Exception $e) {
                $error_msg = $e->getMessage();
                
                // Skip if already exists
                if (strpos($error_msg, 'already exists') !== false || 
                    strpos($error_msg, 'duplicate key') !== false) {
                    $skipped++;
                    error_log("  ↻ Already exists (skipped)");
                } else {
                    $errors[] = $error_msg;
                    error_log("  ⚠ Execution continued despite error: " . $error_msg);
                }
            }
        }
        
        error_log("✓ Migration completed: $created statements executed, $skipped skipped");
        
        if (!empty($errors)) {
            error_log("⚠ Migration had some warnings: " . implode("; ", array_slice($errors, 0, 3)));
        }

        // Pastikan constraint role users konsisten setelah migration awal.
        syncUsersRoleConstraint($conn);
        
    } catch (Exception $e) {
        error_log("✗ Migration FATAL ERROR: " . $e->getMessage());
        error_log($e->getTraceAsString());
    }
}

function syncUsersRoleConstraint(PDO $conn): void
{
    try {
        $conn->exec(<<<'SQL'
DO $$
DECLARE
    constraint_name TEXT;
BEGIN
    FOR constraint_name IN
        SELECT c.conname
        FROM pg_constraint c
        JOIN pg_class t ON t.oid = c.conrelid
        WHERE t.relname = 'users'
          AND c.contype = 'c'
          AND pg_get_constraintdef(c.oid) ILIKE '%role%'
    LOOP
        EXECUTE format('ALTER TABLE users DROP CONSTRAINT %I', constraint_name);
    END LOOP;

    ALTER TABLE users
    ADD CONSTRAINT users_role_check
    CHECK (role IN ('admin', 'manager', 'kasir', 'inspeksi'));
END $$;
SQL);
        error_log("✓ Migration: users.role constraint synced (admin, manager, kasir, inspeksi)");
    } catch (Exception $e) {
        error_log("⚠ Migration: Failed to sync users.role constraint: " . $e->getMessage());
    }
}
?>
