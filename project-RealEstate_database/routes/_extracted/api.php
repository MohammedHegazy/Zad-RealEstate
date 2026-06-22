<?php

/**
 * API route entry point.
 *
 * Structure:
 *   routes/api/v1.php       — Public & user API (prefix: /api/v1)
 *   routes/api/admin.php    — Admin API (prefix: /api/v1/admin)
 *
 * @see routes/API.md for the full endpoint reference.
 */

require __DIR__.'/api/v1.php';
require __DIR__.'/api/admin.php';
