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

			if(LANG_CHARSET!="UTF-8")
			{
				$res = $GLOBALS["APPLICATION"]->ConvertCharset($res, "UTF-8", LANG_CHARSET);

				/* if we recieved the mash from utf-8 and other encodings, lets prevent utf-8 text to be wrong decoded
				(sender: /components/bitrix/wiki.edit/templates/.default/script.php:599
				function insertSanitized())
				for example user could copy and insert url from it's browser.
				http://work.localhost/services/wiki/%C3%EB%E0%E2%ED%E0%FF+%F1%F2%F0%E0%ED%E8%F6%E0/edit/	*/
				$res =str_replace("##%##", "%", $res);
			}

			$CWikiParser = new CWikiParser();
			$res = $CWikiParser->Clear($res);
		}

		break;
}

echo $res;
?>
