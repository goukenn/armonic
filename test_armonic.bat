@echo OFF
REM echo "test framework"
REM armonic -f D:\wamp\www\igkdev\Lib\igk\igk_framework.php -of ./test/ouput.php --allowDocBlocking --verbose
echo "test data.php" 
armonic -f ./test/data.php -of ./test/output.php --verbose --debug
REM armonic -f D:\wwwroot\igkdev\Lib\igk\igk_framework.php -of ./test/output.php --allowDocBlocking