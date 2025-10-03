<?php
function api_base() {
  $base = getenv('API_BASE_URL') ?: 'http://127.0.0.1';
  return rtrim($base, '/');
}

function api_get($path, $query = []) {
  $url = api_base() . '/' . ltrim($path, '/');
  if ($query) $url .= '?' . http_build_query($query);
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
  ]);
  $body = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return [$status, $body];
}

function api_post($path, $fields = []) {
  $url = api_base() . '/' . ltrim($path, '/');
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($fields),
    CURLOPT_HTTPHEADER => [
      'Accept: application/json',
      'Content-Type: application/x-www-form-urlencoded',
    ],
  ]);
  $body = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return [$status, $body];
}