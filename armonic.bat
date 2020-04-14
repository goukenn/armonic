@echo OFF
REM ECO OFF
set s=%*
set pwd=%~dp0
REM goto end
REM echo "value : " %s%
php -d display_errors=1 %pwd%\armonic.php %s%

:end