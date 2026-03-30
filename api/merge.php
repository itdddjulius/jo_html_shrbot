<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid JSON payload.']);
  exit;
}

$provider = $input['provider'] ?? 'mock';
$language = $input['language'] ?? 'Unknown';
$pr = $input['pr'] ?? '';
$cb = $input['cb'] ?? '';
$direction = $input['direction'] ?? '1to2';

function mockFailures(string $pr, string $cb): array {
  $failures = [];
  if (trim($pr) === '' || trim($cb) === '') {
    $failures[] = 'One or both code panels are empty.';
  }
  if (stripos($pr, 'TODO') !== false) {
    $failures[] = 'PR contains TODO markers.';
  }
  if (stripos($pr, 'syntax_error') !== false) {
    $failures[] = 'Possible syntax error marker found in PR.';
  }
  if (stripos($cb, 'protected_branch') !== false) {
    $failures[] = 'Target codebase marked as protected_branch.';
  }
  if (!$failures && similar_text($pr, $cb) < 10) {
    $failures[] = 'Large divergence detected between PR and target codebase.';
  }
  return $failures;
}

$log = "Provider: {$provider}\nLanguage: {$language}\nDirection: {$direction}\n";
$log .= "Starting merge analysis...\n";

/*
 Insert real integration here:

 1. Git CLI:
    - checkout temp branch
    - write PR patch or branch
    - attempt merge
    - capture stderr/stdout

 2. GitHub API:
    - call your backend service that uses GitHub mergeability APIs
    - inspect mergeable_state
    - return conflicts

 3. GitLab API:
    - call your backend service that checks MR merge status
    - collect conflict details
*/

$failures = mockFailures($pr, $cb);

if (empty($failures)) {
  $log .= "Merge check passed.\n";
  echo json_encode([
    'ok' => true,
    'merge_ok' => true,
    'failures' => [],
    'log' => $log
  ]);
  exit;
}

$log .= "Merge check failed.\n";
echo json_encode([
  'ok' => true,
  'merge_ok' => false,
  'failures' => $failures,
  'log' => $log
]);
