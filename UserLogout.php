<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Send response to confirm logout
echo json_encode(['status' => 'success']);
