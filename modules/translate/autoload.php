<?php

namespace Bitrix\Translate;

\Bitrix\Main\Loader::registerAutoLoadClasses(
	'translate',
	array(
		'translate' => 'install/index.php',
		'Bitrix\Translate\Ui\Panel' => 'lib/ui/panel.php',

		'CTranslateUtils' => 'translate_tools.php',
		'CTranslateEventHandlers' => 'translate_tools.php',
	)
);

const ENCODINGS = array(
	'utf-8',
	'windows-1250',
	'windows-1251',
	'windows-1252',
	'windows-1253',
	'windows-1254',
	'windows-1255',
	'windows-1256',
	'windows-1257',
	'windows-1258',
	'iso-8859-1',
	'iso-8859-2',
	'iso-8859-3',
	'iso-8859-4',
	'iso-8859-5',
	'iso-8859-6',
	'iso-8859-7',
	'iso-8859-8',
	'iso-8859-9',
	'iso-8859-10',
	'iso-8859-13',
	'iso-8859-15',
);

const IGNORE_FS_NAMES = array(
	'.',
	'..',
	'.hg',
	'.git',
	'.svn',
	'.vs',
	'.vscode',
	'.idea',
	'.DS_Store',
	'.htaccess',
	'.access.php',
	'.settings.php',
);

const IGNORE_BX_NAMES = array(
	'/bitrix/backup',
	'/bitrix/updates',
	'/bitrix/updates_enc',
	'/bitrix/updates_enc5',
	'/bitrix/help',
	'/bitrix/cache',
	'/bitrix/cache_image',
	'/bitrix/managed_cache',
	'/bitrix/stack_cache',
	'/bitrix/tmp',
	'/bitrix/html_pages',
	'/bitrix/vendor',
	'/bitrix/css',
	'/bitrix/fonts',
	'/bitrix/images',
	'/bitrix/routes',
	'/bitrix/sounds',
	'/upload',
);

const IGNORE_LANG_NAMES = array(
	'exec'
);

const IGNORE_MODULE_NAMES = array(
	'dev',
	'meta',
	'tests',
);

const SUPD_LANG_DATE_MARK = '/main/lang/#LANG_ID#/supd_lang_date.dat';

const ASSIGNMENT_TYPES = array(
	'modules',
	'activities',
	'components',
	'public',
	'templates',
	'wizards',
	'gadgets',
	'js',
	'blocks',
	'mobileapp',
	'themes',
	'admin',
	'public_bitrix24',
	'delivery',
	'paysystem',
);
