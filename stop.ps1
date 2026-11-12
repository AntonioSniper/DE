$ErrorActionPreference='Stop'
$xampp='C:\Users\danya\Documents\Codex\xampp'
$stateFile=Join-Path $PSScriptRoot '.runtime\processes.json'
if(!(Test-Path -LiteralPath $stateFile)){Write-Host 'Нет процессов, запущенных этим скриптом.';exit 0}
$state=Get-Content -Raw -LiteralPath $stateFile|ConvertFrom-Json
if($state.phpId){
 $proc=Get-Process -Id ([int]$state.phpId) -ErrorAction SilentlyContinue
 if($proc -and $proc.Path -eq (Join-Path $xampp 'php\php.exe') -and $proc.StartTime.ToUniversalTime().Ticks.ToString() -eq $state.phpStarted){Stop-Process -Id $proc.Id}
}
if($state.mysqlId){
 $proc=Get-Process -Id ([int]$state.mysqlId) -ErrorAction SilentlyContinue
 if($proc -and $proc.Path -eq (Join-Path $xampp 'mysql\bin\mysqld.exe') -and $proc.StartTime.ToUniversalTime().Ticks.ToString() -eq $state.mysqlStarted){
  & (Join-Path $xampp 'mysql\bin\mysqladmin.exe') --host=127.0.0.1 --user=root shutdown
  if($LASTEXITCODE -ne 0){throw 'MySQL не удалось корректно остановить. Используйте панель XAMPP.'}
 }
}
Remove-Item -LiteralPath $stateFile
Write-Host 'Процессы этой части остановлены.'
