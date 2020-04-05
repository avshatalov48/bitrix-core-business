<?
namespace Bitrix\Translate;

\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);

const TRANSLATE_DEFAULT_PATH = '/bitrix/';

\CModule::AddAutoloadClasses(
	'translate',
	array(
		'CTranslateEventHandlers' => 'translate_tools.php',
		'CTranslateUtils' => 'translate_tools.php',
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
	'.idea',
	'.access.php',
	'.htaccess',
);

const IGNORE_BX_NAMES = array(
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
);

const SUPD_LANG_DATE_MARK = '/main/lang/#LANG_ID#/supd_lang_date.dat';

const WORKING_DIR = '/bitrix/updates/_langs/';

const COLLECT_CUSTOM_LIST = '/bitrix/modules/langs.txt';

const BACKUP_PATH = '/bitrix/tmp/translate/_backup/';

