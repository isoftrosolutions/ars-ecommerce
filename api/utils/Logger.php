<?php
/**
 * Logger Utility Class
 * Handles application logging
 */
class Logger {
    private $logFile;
    private $maxFileSize = 10485760; // 10MB

    public function __construct($logFile = null) {
        $this->logFile = $logFile ?: __DIR__ . '/../../logs/api.log';

        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Log info message
     */
    public function info($message, $context = []) {
        $this->log('INFO', $message, $context);
    }

    /**
     * Log warning message
     */
    public function warning($message, $context = []) {
        $this->log('WARNING', $message, $context);
    }

    /**
     * Log error message
     */
    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Log debug message
     */
    public function debug($message, $context = []) {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Write log entry
     */
    private function log($level, $message, $context = []) {
        // Rotate log file if too large
        if (file_exists($this->logFile) && filesize($this->logFile) > $this->maxFileSize) {
            $this->rotateLogFile();
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        $logEntry = sprintf("[%s] %s: %s%s\n", $timestamp, $level, $message, $contextStr);

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Rotate log file
     */
    private function rotateLogFile() {
        $backupFile = $this->logFile . '.' . date('Y-m-d-H-i-s') . '.bak';
        rename($this->logFile, $backupFile);

        // Keep only last 5 backup files
        $pattern = $this->logFile . '.*.bak';
        $files = glob($pattern);
        if (count($files) > 5) {
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            for ($i = 5; $i < count($files); $i++) {
                unlink($files[$i]);
            }
        }
    }

    /**
     * Get recent log entries
     */
    public function getRecentLogs($lines = 100) {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $logs = [];
        $file = new SplFileObject($this->logFile, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);

        while (!$file->eof()) {
            $line = trim($file->fgets());
            if (!empty($line)) {
                $logs[] = $line;
            }
        }

        return array_reverse($logs);
    }
}
?>