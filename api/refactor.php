<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid JSON payload.']);
  exit;
}

$mode = $input['mode'] ?? 'safe';
$language = $input['language'] ?? 'Unknown';
$code = $input['code'] ?? '';
$failures = $input['failures'] ?? [];
$sourceSide = $input['source_side'] ?? 'panel1';

/*
 Insert real AI refactor integration here:

 - Send:
   * code
   * failures
   * language
   * mode
 - To:
   * OpenAI / Anthropic / internal AI service
 - Return:
   * refactored code
   * summary
*/

$patched = $code;
$patched = str_replace('TODO', 'DONE', $patched);
$patched = str_replace('syntax_error', 'fixed_logic', $patched);

$header = "# Refactored by JO_SHRBOT\n";
if (stripos($patched, $header) === false) {
  $patched = $header . $patched;
}

$log = "AI Refactor Mode: {$mode}\nLanguage: {$language}\nSource: {$sourceSide}\n";
$log .= "Failures received: " . count($failures) . "\n";
$log .= "Mock refactor applied.\n";

echo json_encode([
  'ok' => true,
  'refactored_code' => $patched,
  'remaining_failures' => [],
  'log' => $log
]);
