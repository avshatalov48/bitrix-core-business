<?php

CModule::AddAutoloadClasses('highloadblock', array(
	'Bitrix\Highloadblock\HighloadBlockTable' => 'lib/highloadblock.php',
	'\Bitrix\Highloadblock\HighloadBlockTable' => 'lib/highloadblock.php',
	'CIBlockPropertyDirectory' => 'classes/general/prop_directory.php',
	'CUserTypeHlblock' => 'classes/general/cusertypehlblock.php'
));