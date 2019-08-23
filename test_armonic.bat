@echo OFF
REM echo "test framework"
REM armonic -f D:\wamp\www\igkdev\Lib\igk\igk_framework.php -of ./test/ouput.php --allowDocBlocking --verbose

echo "test define"
REM armonic -f ./test/test_define.php -of ./test/ouput.php --allowDocBlocking --verbose --debug
REM armonic -f ./test/test_multi_arraw_operator.php -of ./test/ouput.php --allowDocBlocking --verbose --debug


armonic -f ./test/test_static_function.php -of ./test/ouput.php --allowDocBlocking --verbose --debug