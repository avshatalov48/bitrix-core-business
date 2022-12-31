<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

if (!$USER->CanDoOperation('seo_tools') || !check_bitrix_sessid())
	die(GetMessage("ACCESS_DENIED"));

use Bitrix\Seo\Engine;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO\Path;

\Bitrix\Main\Loader::includeModule('seo');
\Bitrix\Main\Loader::includeModule('socialservices');

CUtil::JSPostUnescape();

Loc::loadMessages(__DIR__.'/../include.php');

$engine = new Engine\Yandex();

if(isset($_REQUEST['action']))
{
	$res = array();

	$arDomain = null;
	if(isset($_REQUEST['domain']) && $_REQUEST['domain'] <> '')
	{
		$bFound = false;
		$arDomains = \CSeoUtils::getDomainsList();
		foreach($arDomains as $arDomain)
		{
			if($arDomain['DOMAIN'] == $_REQUEST['domain'] && rtrim($arDomain['SITE_DIR'], '/') == rtrim($_REQUEST['dir'], '/'))
			{
				$bFound = true;
				break;
			}
		}

		if(!$bFound)
		{
			$res = array('error' => 'Unknown site!');
		}
	}

	if(!$res['error'])
	{
		try
		{
			switch($_REQUEST['action'])
			{
				case 'nullify_auth':
					$engine->clearAuthSettings();
					$res = array("result" => true);
				break;

				case 'sites_feed':
					$res = $engine->getFeeds();
				break;

				case 'site_add':
					$res = $engine->addSite($arDomain['DOMAIN'], $arDomain['SITE_DIR']);
					$res['_domain'] = $arDomain['DOMAIN'];
				break;

				case 'top-queries':
					$res = $engine->getSiteInfoQueries($arDomain['DOMAIN']);
				break;

				case 'site_verify':
					$res = array('error' => array('message' => 'Unknown domain'));
					
					if(is_array($arDomain))
					{
						$arFeeds = $engine->getFeeds();
						if(isset($arFeeds[$arDomain['DOMAIN']]) && is_array($arFeeds[$arDomain['DOMAIN']]))
						{
//							todo: remove 'VERIFIED' after complete migration to v3
							if(/*$arFeeds[$arDomain['DOMAIN']]['verification'] != 'VERIFIED' || */$arFeeds[$arDomain['DOMAIN']]['verified'] === false)
							{
//								get unnicue string for verification
								$uin = $engine->getVerifySiteUin($arDomain['DOMAIN']);
								if($uin)
								{
									$filename = "yandex_".$uin.".html";

									$path = Path::combine((
										$arDomain['SITE_DOC_ROOT'] <> ''
											? $arDomain['SITE_DOC_ROOT']
											: $_SERVER['DOCUMENT_ROOT']
										), $arDomain['SITE_DIR'], $filename);
									$obFile = new \Bitrix\Main\IO\File($path);
									$obFile->putContents('<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>Verification: '.$uin.'</body></html>');

									$res = $engine->verifySite($arDomain['DOMAIN']);

									//$obFile->delete();
								}
							}
						}

						$res['_domain'] = $arDomain['DOMAIN'];
					}
					else
					{
						$res = array('error' => 'No domain');
					}
				break;

				case 'original_text':
					$textContent = $_POST['original_text'];
					$res = $engine->addOriginalText($textContent, $arDomain['DOMAIN']);
				break;

				case 'original_texts':
					$res = $engine->getOriginalTexts($arDomain['DOMAIN']);
				break;

				default:
					$res = array('error' => 'unknown action');
				break;
			}
		}
		catch(Engine\YandexException $e)
		{
			$res = array(
				'error' => array(
					'message' => $e->getMessage(),
					'status' => $e->getStatus(),
					'code' => $e->getCode(),
				)
			);
		}
	}

	Header('Content-type: application/json; charset='.LANG_CHARSET);
	echo \Bitrix\Main\Web\Json::encode($res);
}
elseif (isset($_REQUEST['get']))
{
	switch($_REQUEST['get'])
	{

		case 'original_text_form':
			$arSettings = $engine->getSettings();
			$arDomains = \CSeoUtils::getDomainsList();
			
//			if empty - save list of webmaster-sites in settings
			if(empty($arSettings['SITES']))
			{
				$engine->getFeeds();
				$arSettings = $engine->getSettings();
			}

			foreach($arDomains as $key => $domain)
			{
				if(!isset($arSettings['SITES'][$domain['DOMAIN']]))
				{
					unset($arDomains[$key]);
				}
			}

			if(count($arDomains) <= 0)
			{
				$msg = new CAdminMessage(array(
					'MESSAGE' => Loc::getMessage('SEO_YANDEX_ERROR'),
					'HTML' => 'Y'
				));
				echo $msg->Show();
			}
			else
			{
?>
<div id="seo_original_text_form_form">
<form name="seo_original_text_form" style="padding:0;margin: 0;">
	<b><?=Loc::getMessage('SEO_YANDEX_DOMAIN')?>: </b><select name="domain">
<?
				foreach($arDomains as $domain)
				{
					$errors = [];
					$domainView = \CBXPunycode::ToUnicode($domain['DOMAIN'], $errors);
					$domainEnc = Converter::getHtmlConverter()->encode($domain['DOMAIN']);
					$domainViewEnc = Converter::getHtmlConverter()->encode($domainView);


					?>
		<option value="<?=$domainEnc?>"><?=$domainViewEnc?></option>
<?
				}
?>
	</select><br /><br />
	<textarea style="width: 700px; height: 450px;" name="original_text"></textarea>
</form>
</div><div id="seo_original_text_form_ok" style="display: none;">
<?
	CAdminMessage::ShowMessage(
		array(
			"MESSAGE" => Loc::getMessage('SEO_YANDEX_ORIGINAL_TEXT_OK'),
			"HTML" => true,
			"DETAILS" => Loc::getMessage('SEO_YANDEX_ORIGINAL_TEXT_OK_DETAILS',
				array('#LANGUAGE_ID#' => LANGUAGE_ID)
			),
			"TYPE" => "OK",
		)
	);
?>
</div>
<?
			}
		break;
	}
}
?>