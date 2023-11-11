<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
	'highloadblock',
	[
		'CIBlockPropertyDirectory' => 'classes/general/prop_directory.php',
		'CUserTypeHlblock' => 'classes/general/cusertypehlblock.php',
	]
);
