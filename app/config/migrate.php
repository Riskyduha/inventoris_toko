<?php

function runMigration(PDO $conn): void
{
    try {
        // Cek apakah table users sudah ada (gunakan simple query)
        try {
            $stmt = $conn->query("SELECT 1 FROM users LIMIT 1");
            $tableExists = true;
            error_log("Migration check: users table EXISTS (query succeeded)");
        } catch (Exception $checkError) {
            $tableExists = false;
            error_log("Migration check: users table DOES NOT EXIST (query failed as expected)");
        }

        if (!$tableExists) {
            // Baca schema file - dari root project directory
            $projectRoot = dirname(__DIR__, 2); // Go up from app/config to root
            $schemaFile = $projectRoot . '/database/skema_postgresql.sql';
            
            error_log("Looking for schema at: " . $schemaFile);
            
            if (file_exists($schemaFile)) {
                error_log("Schema file found, starting migration...");
                $schema = file_get_contents($schemaFile);
                
                // Pisahkan statements (remove CREATE DATABASE dan \c commands untuk Railway)
                $statements = array_filter(array_map('trim', preg_split('/;/', $schema)));
                
                error_log("Total statements to execute: " . count($statements));
                
                $successCount = 0;
                foreach ($statements as $index => $statement) {
                    if (!empty($statement) && !preg_match('/^(CREATE DATABASE|\\\\c)/i', $statement)) {
                        try {
                            $conn->exec($statement . ';');
                            $successCount++;
                        } catch (Exception $e) {
                            // Skip jika table sudah ada (CREATE TABLE IF NOT EXISTS)
                            if (strpos($e->getMessage(), 'already exists') === false) {
                                error_log("Statement error: " . $e->getMessage());
                            } else {
                                $successCount++;
                            }
                        }
                    }
                }
                
                error_log("Migration completed: " . $successCount . " table statements executed");
            } else {
                error_log("ERROR: Schema file not found at " . $schemaFile);
            }
        }
    } catch (Exception $e) {
        error_log("Migration check failed: " . $e->getMessage());
    }
}
