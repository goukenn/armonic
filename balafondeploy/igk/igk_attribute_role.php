<?php
// author: C.A.D. BONDJE DOUE

require_once(dirname(__FILE__)."/igk_framework.php");
$r=IGKHtmlItem::CreateWebNode("inputRole");
$t=$r->add("igk-input-role")->Content="define role to an input text. if an value is expression regex will be used ";
$t->add("value")->Content="text";
$t->add("value")->Content="number";
$t->add("value")->Content="integer";
$t->add("value")->Content="expression";
header("Content-Type: application/xml");
$r->RenderAJX((object)array("Indent"=>true));