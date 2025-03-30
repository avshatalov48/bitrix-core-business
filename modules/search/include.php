<?php
if (!defined('CACHED_b_search_tags'))
{
	define('CACHED_b_search_tags', 3600);
}
if (!defined('CACHED_b_search_tags_len'))
{
	define('CACHED_b_search_tags_len', 2);
}
if (!defined('CACHED_opensearch_template'))
{
	define('CACHED_opensearch_template', 36000);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/tools/stemming.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/tools/tags.php';

CModule::AddAutoloadClasses(
	'search',
	[
		'CSearchCallback' => 'classes/general/search.php',
		'CSearch' => 'classes/mysql/search.php',
		'CSearchItem' => 'classes/general/item.php',
		'CSearchQuery' => 'classes/mysql/search.php',
		'CAllSiteMap' => 'classes/general/sitemap.php',
		'CSiteMap' => 'classes/mysql/sitemap.php',
		'CSearchCustomRank' => 'classes/general/customrank.php',
		'CSearchTags' => 'classes/general/tags.php',
		'CSearchSuggest' => 'classes/mysql/suggest.php',
		'CSearchStatistic' => 'classes/general/statistic.php',
		'CSearchTitle' => 'classes/general/title.php',
		'CSearchLanguage' => 'tools/language.php',
		'CSearchUser' => 'classes/general/user.php',
		'CSearchParameters' => 'classes/general/comp_parameters.php',
		'search' => 'install/index.php',
		'CSearchFullText' => 'classes/general/full_text.php',
		'CSearchSphinx' => 'tools/sphinx.php',
		'CSearchOpenSearch' => 'tools/opensearch.php',
		'CSearchStemTable' => 'tools/stemtable.php',
		'CSearchMysql' => 'tools/mysql.php',
		'CSearchPgsql' => 'tools/pgsql.php',
	]
);

CJSCore::RegisterExt('search_tags', [
	'js' => '/bitrix/js/search/tags.js',
]);

/**
 * Returns filtered sName concatenated with random number.
 *
 * @param string $sName
 * @return string
 * @deprecated
 */
function GenerateUniqId($sName)
{
	static $arPostfix = [];

	$sPostfix = rand();
	while (isset($arPostfix[$sPostfix]))
	{
		$sPostfix = rand();
	}

	$arPostfix[$sPostfix] = 1;

	return preg_replace('/\\W/', '_', $sName) . $sPostfix;
}

$DB_test = CDatabase::GetModuleConnection('search', true);
if (!is_object($DB_test))
{
	return false;
}
