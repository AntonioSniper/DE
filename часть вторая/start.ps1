$ErrorActionPreference='Stop'
$candidates = @(
    $env:XAMPP_HOME
    (Join-Path $env:SystemDrive 'xampp')
    (Join-Path ([Environment]::GetFolderPath('MyDocuments')) 'Codex\xampp')
)
$xampp = $candidates | Where-Object {
    $_ -and (Test-Path -LiteralPath (Join-Path $_ 'php\php.exe')) -and
    (Test-Path -LiteralPath (Join-Path $_ 'mysql\bin\mysqld.exe'))
} | Select-Object -First 1
if (!$xampp) { throw 'Укажите каталог установки XAMPP в переменной среды XAMPP_HOME.' }
$app=Join-Path $PSScriptRoot 'приложение'
$runtime=Join-Path $PSScriptRoot '.runtime'
New-Item -ItemType Directory -Force -Path $runtime | Out-Null
function OpenPort([int]$port){$client=[Net.Sockets.TcpClient]::new();try{$client.Connect('127.0.0.1',$port);return $true}catch{return $false}finally{$client.Dispose()}}
if(OpenPort 8081){Write-Host 'Порт 8081 уже занят. Если сайт запущен, откройте http://127.0.0.1:8081';exit 0}
$state=@{xampp=$xampp;phpId=$null;mysqlId=$null;phpStarted=$null;mysqlStarted=$null}
if(!(OpenPort 3306)){
 $mysql=Start-Process -FilePath (Join-Path $xampp 'mysql\bin\mysqld.exe') -ArgumentList @(('--defaults-file="'+$xampp+'\mysql\bin\my.ini"'),'--standalone','--console') -WorkingDirectory $xampp -WindowStyle Hidden -RedirectStandardOutput (Join-Path $runtime 'mysql-out.log') -RedirectStandardError (Join-Path $runtime 'mysql-error.log') -PassThru
 $state.mysqlId=$mysql.Id
 $state.mysqlStarted=$mysql.StartTime.ToUniversalTime().Ticks.ToString()
 $state|ConvertTo-Json|Set-Content (Join-Path $runtime 'processes.json') -Encoding UTF8
 for($i=0;$i -lt 40 -and !(OpenPort 3306);$i++){Start-Sleep -Milliseconds 250}
 if(!(OpenPort 3306)){throw 'MySQL не запустился. См. .runtime/mysql-error.log'}
}
$php=Start-Process -FilePath (Join-Path $xampp 'php\php.exe') -ArgumentList @('-S','127.0.0.1:8081','-t',('"'+$app+'"'),('"'+$app+'\router.php"')) -WorkingDirectory $app -WindowStyle Hidden -RedirectStandardOutput (Join-Path $runtime 'php-out.log') -RedirectStandardError (Join-Path $runtime 'php-error.log') -PassThru
$state.phpId=$php.Id
$state.phpStarted=$php.StartTime.ToUniversalTime().Ticks.ToString()
$state|ConvertTo-Json|Set-Content (Join-Path $runtime 'processes.json') -Encoding UTF8
for($i=0;$i -lt 30 -and !(OpenPort 8081);$i++){Start-Sleep -Milliseconds 200}
if(!(OpenPort 8081)){throw 'PHP-сервер не запустился. См. .runtime/php-error.log'}
Write-Host 'Сайт запущен: http://127.0.0.1:8081 . Администратор: Admin / KorokNET.'
