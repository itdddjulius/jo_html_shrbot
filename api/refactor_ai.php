<?php
declare(strict_types=1);
header('Content-Type: application/json');
function respond(array $payload, int $status = 200): never { http_response_code($status); echo json_encode($payload, JSON_UNESCAPED_SLASHES); exit; }
function read_json(): array { $raw = file_get_contents('php://input') ?: ''; $data = json_decode($raw, true); return is_array($data) ? $data : []; }
function log_line(string $message): void { $dir = dirname(__DIR__) . '/storage/logs'; if(!is_dir($dir)) @mkdir($dir,0775,true); file_put_contents($dir.'/jo_shrbot.log','['.date('Y-m-d H:i:s').'] '.$message.PHP_EOL,FILE_APPEND); }
function refactor_stub(string $code, array $failures): string { $refactored=str_replace(["\r\n","\r"],"\n",$code); $refactored=str_replace("\t",'    ',$refactored); $refactored=preg_replace('/[ \t]+$/m','',$refactored)??$refactored; $refactored=str_replace(['<<<<<<<','=======','>>>>>>>'],'',$refactored); $refactored=preg_replace('/TODO|FIXME/i','RESOLVED_NOTE',$refactored)??$refactored; $refactored=preg_replace("/\n{3,}/","\n\n",$refactored)??$refactored; $summary="\n\n/* REFACTOR SUMMARY\n"; foreach($failures as $failure){$summary.=" - ".$failure."\n";} $summary.="*/\n"; return trim($refactored).$summary; }
$data = read_json(); $code = (string)($data['code'] ?? ''); $failures = $data['failures'] ?? []; $backend = (string)($data['backend'] ?? 'stub'); if(!is_array($failures)) $failures=[];
if ($backend !== 'ai_api') { log_line('refactor_ai stub used'); respond(['ok'=>true,'refactored_code'=>refactor_stub($code,$failures)]); }
$aiUrl = getenv('AI_API_URL') ?: ''; $aiKey = getenv('AI_API_KEY') ?: ''; $aiModel = getenv('AI_MODEL') ?: '';
if ($aiUrl === '' || $aiKey === '' || $aiModel === '') respond(['ok'=>false,'failures'=>['AI API is not configured.','Set AI_API_URL, AI_API_KEY, and AI_MODEL in environment variables.']],500);
$prompt = "You are a senior software engineer. Refactor the PR code to address the failures below.\n\n" . "FAILURES:\n - " . implode("\n - ", $failures) . "\n\n" . "Return only the refactored code.\n\nCODE:\n" . $code;
$payload = json_encode(['model'=>$aiModel,'messages'=>[['role'=>'system','content'=>'You refactor code safely and conservatively.'],['role'=>'user','content'=>$prompt]],'temperature'=>0.2], JSON_UNESCAPED_SLASHES);
$ch = curl_init($aiUrl); curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$aiKey],CURLOPT_POSTFIELDS=>$payload,CURLOPT_TIMEOUT=>60]); $raw=curl_exec($ch); $http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
if ($raw === false || $http < 200 || $http >= 300) respond(['ok'=>false,'failures'=>['AI refactor request failed.','Check AI_API_URL / AI_API_KEY / AI_MODEL and provider response.']],502);
$dataOut = json_decode($raw, true); $refactored = $dataOut['choices'][0]['message']['content'] ?? null; if(!is_string($refactored) || trim($refactored)==='') respond(['ok'=>false,'failures'=>['AI service returned no refactored code.']],502);
log_line('refactor_ai real backend used'); respond(['ok'=>true,'refactored_code'=>$refactored]);
