<?php

if(!isset($_GET['canal']) || !in_array($_GET['canal'], ['stable', 'beta']))
	$_GET['canal'] = 'stable';

$canal = @$_GET['canal'];

$dirs = glob("{$canal}/*", GLOB_ONLYDIR);

$versions = [];
foreach($dirs as $v)
{
	$v = str_replace("{$canal}/", "", $v);
	$versions[] = $v;
}

usort($versions, 'version_compare');
$versions = array_reverse($versions);

die(json_encode($versions));



