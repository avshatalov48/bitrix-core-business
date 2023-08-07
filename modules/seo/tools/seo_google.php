<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

if (!$USER->CanDoOperation('seo_tools'))
{
	die(GetMessage("ACCESS_DENIED"));
}

use Bitrix\Main\Loader;
use Bitrix\Seo\Engine;
use Bitrix\Main\IO\Path;
use Bitrix\Seo\Webmaster;

IncludeModuleLangFile(__FILE__);
Loader::includeModule('seo');
Loader::includeModule('socialservices');

CUtil::JSPostUnescape();

if (isset($_REQUEST['action']) && check_bitrix_sessid())
{
	$res = [];

	$arDomain = null;
	if (isset($_REQUEST['domain']) && $_REQUEST['domain'] <> '')
	{
		$bFound = false;
		$arDomains = \CSeoUtils::getDomainsList();
		foreach ($arDomains as $arDomain)
		{
			if ($arDomain['DOMAIN'] == $_REQUEST['domain']
				&& (rtrim($arDomain['SITE_DIR'], '/') == rtrim($_REQUEST['dir'], '/'))
			)
			{
				$bFound = true;
				break;
			}
		}

		if (!$bFound)
		{
			$res = ['error' => 'Unknown site!'];
		}
	}

	if (!$res['error'])
	{
		try
		{
			switch ($_REQUEST['action'])
			{
				case 'nullify_auth':
					Webmaster\Service::getAuthAdapter(Webmaster\Service::TYPE_GOOGLE)
						->setService(Webmaster\Service::getInstance())
						->removeAuth()
					;
					$res = ["result" => true];

					break;

				case 'sites_feed':
					$res = Webmaster\Service::getSites();
					if ($res['errors'])
					{
						$res = ['error' => $res['error']];
					}

					break;

				case 'site_add':
					$domain = $arDomain['DOMAIN'];
					$dir = $arDomain['SITE_DIR'] ?? '/';
					$resAdd = Webmaster\Service::addSite($domain, $dir);
					if ($resAdd['error'])
					{
						$res = ['error' => $resAdd['error']];
						break;
					}
					$res = Webmaster\Service::getSites();
					$res['_domain'] = $domain;

					break;

				case 'site_verify':
					$res = array('error' => 'Unknown domain');

					if (is_array($arDomain))
					{
						$sitesInfo = Webmaster\Service::getSites();
						if ($sitesInfo['error'])
						{
							$res = ['error' => $res['error']];
							break;
						}
						$verified = $sitesInfo[$arDomain['DOMAIN']]['verified'];
						if (!$verified)
						{
							$domain = $arDomain['DOMAIN'];
							$dir = $arDomain['SITE_DIR'] ?? '/';
							$filename = Webmaster\Service::getVerifyToken($domain, $dir);
							if ($filename['error'])
							{
								$res = ['error' => $filename['error']];
								break;
							}
							$filename = $filename['token'];
							// paranoia?
							$filename = preg_replace("/^(.*?)\..*$/", "\\1.html", $filename);

							if ($filename <> '')
							{
								$path = Path::combine(
									(
										$arDomain['SITE_DOC_ROOT'] <> ''
											? $arDomain['SITE_DOC_ROOT']
											: $_SERVER['DOCUMENT_ROOT']
									),
									$arDomain['SITE_DIR'],
									$filename
								);

								$obFile = new \Bitrix\Main\IO\File($path);
								if ($obFile->isExists())
								{
									$obFile->delete();
								}

								$obFile->putContents('google-site-verification: ' . $filename);

								$resVerify = !Webmaster\Service::verifySite($domain, $dir);
								if ($resVerify['errors'])
								{
									$res = ['error' => $resVerify['error']];
									break;
								}
							}

							$res = Webmaster\Service::getSites();
							if ($res['errors'])
							{
								$res = ['error' => $res['error']];
								break;
							}

							$res['_domain'] = $arDomain['DOMAIN'];
						}
						elseif ($verified == 'true')
						{
							$res = $sitesInfo;
							$res['_domain'] = $arDomain['DOMAIN'];
						}
					}
					else
					{
						$res = ['error' => 'No domain'];
					}
					break;

				default:
					$res = ['error' => 'unknown action'];
					break;
			}
		}
		catch (Exception $e)
		{
			$res = [
				'error' => $e->getMessage(),
			];
		}
	}

	Header('Content-type: application/json');
	echo \Bitrix\Main\Web\Json::encode($res);
}
?>