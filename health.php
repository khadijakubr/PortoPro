<?php
// Health check 
// /health.php selalu 200 "OK" tanpa butuh DB (sengaja), agar
// healthcheck tidak ikut gagal saat DB sedang migrasi.

http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');
echo 'OK';
