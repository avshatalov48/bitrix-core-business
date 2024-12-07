<?
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if(!check_bitrix_sessid())
	return false;

CModule::AddAutoloadClasses(
	'wiki',
	array(
		'CWiki' => 'classes/general/wiki.php',
		'CWikiUtils'  => 'classes/general/wiki_utils.php',
		'CWikiParser' => 'classes/general/wiki_parser.php',
		'CWikiDiff' => 'classes/general/wiki_diff.php',
		'CWikiSocnet' => 'classes/general/wiki_socnet.php',
		'CWikiDocument' => 'classes/general/wiki_document.php',
		'CWikiSecurity' => 'classes/general/wiki_security.php',
		'CUserTypeWiki' => 'classes/general/wiki_usertypewiki.php',
		'CRatingsComponentsWiki' => 'classes/general/ratings_components.php'
	)
);

$res = "";

switch ($_REQUEST["act"])
{
	case  'sanitize':

		if(isset($_REQUEST['text']))
		{
			$res = $_REQUEST['text'];

			$CWikiParser = new CWikiParser();
			$res = $CWikiParser->Clear($res);
		}

		break;
}

echo $res;
?>
