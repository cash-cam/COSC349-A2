<?php
function api_get($path, $query = []) {
  global $API_BASE_URL;
  $url = rtrim($API_BASE_URL, '/') . '/' . ltrim($path, '/');
  if ($query) $url .= '?' . http_build_query($query);
  $ch = curl_init($url);
  curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>5]);
  $body = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return [$status, $body];
}