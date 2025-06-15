@echo off
setlocal enabledelayedexpansion

set "backupDir=D:\backup"
set "mysqlDir=D:\xampp\mysql\bin"

:: Extract date parts
set "year=%date:~6,4%"
set "month=%date:~3,2%"
set "day=%date:~0,2%"

:: Extract time parts and ensure two-digit hour
set "hour=%time:~0,2%"
set "minute=%time:~3,2%"

:: Replace leading space with zero for hours less than 10
if "!hour:~0,1!"==" " set "hour=0!hour:~1,1!"

:: Construct timestamp
set "timestamp=!year!-!month!-!day!_!hour!-!minute!"

"%mysqlDir%\mysqldump" -u adm_backup -padmin123 sisri_db > "%backupDir%\backup_%timestamp%.sql"

endlocal
