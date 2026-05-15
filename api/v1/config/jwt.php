<?php
/**
 * JWT Configuration
 */

define('JWT_SECRET', env('JWT_SECRET', 'change-me-in-production-' . bin2hex(random_bytes(16))));
define('JWT_EXPIRY_DAYS', (int)env('JWT_EXPIRY_DAYS', 7));
define('JWT_ALGORITHM', 'HS256');
