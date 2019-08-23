<?php

class A{
	var $info_a;
	var $info_b;
	var $info_c;
}

$j="c";

$c = new A();
$c->info_{$j} = 8;


var_dump($c);