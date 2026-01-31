<?php

/**
 * Application Configuration
 * Environment-based settings untuk development dan production
 */

class Config {
    
    // Get environment
    public static function getEnv() {
        $env = getenv('APP_ENV') ?: 'development';
        return strtolower($env);
    }
    
    // Check if production
    public static function isProduction() {
        return self::getEnv() === 'production';
    }
    
    // Check if development
    public static function isDevelopment() {
        return self::getEnv() === 'development';
    }
    
    // Get debug mode
    public static function isDebug() {
        if (self::isProduction()) {
            return false; // Selalu false di production
        }
        $debug = getenv('APP_DEBUG') ?: 'true';
        return strtolower($debug) === 'true';
    }
    
    // Configure PHP untuk production
    public static function setupProduction() {
        if (self::isProduction()) {
            // Disable error display
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            
            // Enable error logging
            ini_set('log_errors', '1');
            ini_set('error_log', dirname(__DIR__, 2) . '/logs/error.log');
            
            // Set error reporting
            error_reporting(E_ALL);
        } else {
            // Development mode - tampilkan error
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            error_reporting(E_ALL);
        }
    }
    
    // Get app URL
    public static function getAppUrl() {
        return getenv('APP_URL') ?: 'http://localhost:8000';
    }
    
    // Get timezone
    public static function getTimezone() {
        $tz = getenv('TIMEZONE') ?: 'UTC';
        date_default_timezone_set($tz);
        return $tz;
    }
    
    // Security headers untuk production
    public static function setSecurityHeaders() {
        if (self::isProduction()) {
            // Prevent clickjacking
            header('X-Frame-Options: SAMEORIGIN');
            
            // Prevent MIME type sniffing
            header('X-Content-Type-Options: nosniff');
            
            // Enable XSS protection
            header('X-XSS-Protection: 1; mode=block');
            
            // Force HTTPS
            if (strpos(self::getAppUrl(), 'https') === 0) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
            
            // Content Security Policy (allow required CDNs)
            header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com data:");
        }
    }
}

// Initialize configuration
Config::setupProduction();
Config::getTimezone();
Config::setSecurityHeaders();

?>
