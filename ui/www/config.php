<?php
// set via Apache: SetEnv API_BASE_URL http://<API_PRIVATE_IP>/   (no trailing slash)
$API_BASE_URL = getenv('API_BASE_URL') ?: 'http://127.0.0.1';