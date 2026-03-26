<?php
declare(strict_types=1);
header('Content-Type: application/json');
function respond(array $payload, int $status = 200): never { http_response_code($status); echo json_encode($payload, JSON_UNESCAPED_SLASHES); exit; }
function read_json(): array { $raw = file_get_contents('php://input') ?: ''; $data = json_decode($raw, true); return is_array($data) ? $data : []; }
function analyze_merge_text(string $pr, string $cb): array { $failures=[]; if(trim($pr)==='') $failures[]='PR code is empty.'; if(trim($cb)==='') $failures[]='CODE-BASE is empty.'; if(preg_match('/<<<<<<<|=======|>>>>>>>/', $pr)) $failures[]='PR contains unresolved conflict markers.'; if(preg_match('/<<<<<<<|=======|>>>>>>>/', $cb)) $failures[]='CODE-BASE contains unresolved conflict markers.'; if(preg_match('/TODO|FIXME/i', $pr)) $failures[]='PR still contains TODO/FIXME markers.'; $prLines=preg_split("/\R/", $pr) ?: []; foreach($prLines as $i=>$line){ if(strlen($line)>180){ $failures[]='PR contains an overlong line at '.($i+1).'.'; break; } } return $failures; }
function merged_output(string $pr, string $cb): string { return trim($cb)."\n\n/* ===== MERGED PR ===== */\n\n".trim($pr); }
function log_line(string $message): void { $dir = dirname(__DIR__) . '/storage/logs'; if(!is_dir($dir)) @mkdir($dir,0775,true); file_put_contents($dir.'/jo_shrbot.log','['.date('Y-m-d H:i:s').'] '.$message.PHP_EOL,FILE_APPEND); }
function run_command(array $cmd, ?string $cwd = null): array { $descriptorSpec=[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']]; $process=proc_open($cmd,$descriptorSpec,$pipes,$cwd); if(!is_resource($process)) return ['ok'=>false,'code'=>1,'stdout'=>'','stderr'=>'Unable to start process']; fclose($pipes[0]); $stdout=stream_get_contents($pipes[1])?:''; $stderr=stream_get_contents($pipes[2])?:''; fclose($pipes[1]); fclose($pipes[2]); $code=proc_close($process); return ['ok'=>$code===0,'code'=>$code,'stdout'=>$stdout,'stderr'=>$stderr]; }
$data = read_json();
$pr = (string)($data['pr'] ?? '');
$cb = (string)($data['cb'] ?? '');
$repoName = preg_replace('/[^a-zA-Z0-9._-]/', '_', (string)($data['repo_name'] ?? 'repo')) ?: 'repo';
$fallbackFailures = analyze_merge_text($pr, $cb);
if ($fallbackFailures) respond(['ok' => false, 'failures' => $fallbackFailures], 422);
$workspaceRoot = dirname(__DIR__) . '/storage/workspaces'; if (!is_dir($workspaceRoot)) @mkdir($workspaceRoot, 0775, true);
$workspace = $workspaceRoot . '/job_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
$repoDir = $workspace . '/' . $repoName; @mkdir($repoDir, 0775, true);
$git = 'git';
$init = run_command([$git, 'init', '-b', 'main'], $repoDir); if (!$init['ok']) respond(['ok'=>false,'failures'=>['Git init failed: '.trim($init['stderr'])]],500);
run_command([$git, 'config', 'user.email', 'shrbot@example.local'], $repoDir); run_command([$git, 'config', 'user.name', 'JO_SHRBOT'], $repoDir);
file_put_contents($repoDir . '/shrbot_payload.txt', $cb); run_command([$git, 'add', '.'], $repoDir);
$baseCommit = run_command([$git, 'commit', '-m', 'base code'], $repoDir); if (!$baseCommit['ok']) respond(['ok'=>false,'failures'=>['Base commit failed: '.trim($baseCommit['stderr'])]],500);
$branchCreate = run_command([$git, 'checkout', '-b', 'pr-branch'], $repoDir); if (!$branchCreate['ok']) respond(['ok'=>false,'failures'=>['PR branch creation failed: '.trim($branchCreate['stderr'])]],500);
file_put_contents($repoDir . '/shrbot_payload.txt', $pr); run_command([$git, 'add', '.'], $repoDir);
$prCommit = run_command([$git, 'commit', '-m', 'pr code'], $repoDir); if (!$prCommit['ok']) respond(['ok'=>false,'failures'=>['PR commit failed: '.trim($prCommit['stderr'])]],500);
$checkoutMain = run_command([$git, 'checkout', 'main'], $repoDir); if (!$checkoutMain['ok']) respond(['ok'=>false,'failures'=>['Checkout main failed: '.trim($checkoutMain['stderr'])]],500);
$merge = run_command([$git, 'merge', 'pr-branch', '--no-ff', '--no-commit'], $repoDir);
if (!$merge['ok']) { $failures=[]; if(trim($merge['stdout'])!=='') $failures[]='Git stdout: '.trim($merge['stdout']); if(trim($merge['stderr'])!=='') $failures[]='Git stderr: '.trim($merge['stderr']); $status=run_command([$git,'status','--porcelain'],$repoDir); if(trim($status['stdout'])!=='') $failures[]='Git status: '.trim($status['stdout']); log_line('merge_git_cli failed'); respond(['ok'=>false,'failures'=>$failures ?: ['Merge failed for an unknown Git reason.']],409); }
$merged = file_get_contents($repoDir . '/shrbot_payload.txt') ?: merged_output($pr, $cb); log_line('merge_git_cli succeeded'); respond(['ok'=>true,'failures'=>[],'merged_code'=>$merged]);
