<?php
$ch = curl_init('http://localhost:8000/api/users');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer 283|48oQC33T7wXwwmP5GvrFZ6YcsMPzwASMo1N7RAki6cead3fa',
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
$payload = json_encode([
    'first_name' => 'dev',
    'last_name' => 'test',
    'username' => 'dev_test_' . rand(100, 999),
    'email' => 'dev_' . rand(100, 999) . '@example.com',
    'contact_number' => '0912',
    'password' => 'password123',
    'role' => 'Developer',
    'permissions' => []
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpcode\n";
echo "Response: $response\n";
