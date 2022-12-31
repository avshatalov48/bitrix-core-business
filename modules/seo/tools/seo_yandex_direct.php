<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 */

if (!$USER->CanDoOperation('seo_tools') || !check_bitrix_sessid())
	die(GetMessage("ACCESS_DENIED"));

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Engine;
use Bitrix\Seo\Adv;
use Bitrix\Main\Type\Date;
use Bitrix\Seo\Service;

Loader::includeModule('seo');
Loader::includeModule('socialservices');

CUtil::JSPostUnescape();

Loc::loadMessages(__DIR__.'/../include.php');
Loc::loadMessages(__DIR__.'/../admin/seo_adv.php');

$action = isset($_REQUEST['action']) ? $_REQUEST["action"] : null;

if($action !== "register")
{
	$bNeedAuth = !Service::isRegistered();
	if(!$bNeedAuth && $action != "authorize")
	{
		$engine = new Engine\YandexDirect();
		$currentUser = null;

		try
		{
			$currentAuth = Service::getAuth($engine->getCode());
			if($currentAuth)
			{
				$currentUser = $currentAuth["user"];
				$bNeedAuth = false;
			}
		}
		catch(Exception $e)
		{
			$bNeedAuth = true;
		}
	}

}
else
{
	$bNeedAuth = false;
}

if(isset($action) && !$bNeedAuth)
{
	try
	{
		switch($_REQUEST['action'])
		{
			case 'register':
				if(!Service::isRegistered())
				{
					try
					{
						Service::register();
						$res = array("result" => true);
					}
					catch(\Bitrix\Main\SystemException $e)
					{
						$res = array(
							'error' => array(
								'message' => $e->getMessage(),
								'code' => $e->getCode(),
							)
						);
					}
				}

			break;

			case 'unregister':
				if(Service::isRegistered())
				{
					try
					{
						Service::clearAuth($engine->getCode());
						Service::unregister();
						$res = array("result" => true);
					}
					catch(\Bitrix\Main\SystemException $e)
					{
						$res = array(
								'error' => array(
										'message' => $e->getMessage(),
										'code' => $e->getCode(),
								)
						);
					}
				}

			break;

			case 'nullify_auth':
				Service::clearAuth($engine->getCode());
				$res = array("result" => true);
			break;

			case 'wordstat_report_clear':
			case 'wordstat_report':

				if($_REQUEST['action'] == 'wordstat_report_clear')
				{
					$reportList = $engine->getWordstatReportList();

					if(count($reportList) >= Engine\YandexDirect::MAX_WORDSTAT_REPORTS)
					{
						foreach($reportList as $firstReport)
						{
							$engine->deleteWordstatReport($firstReport["ReportID"]);
							break;
						}
					}
				}

				$phraseList = $_REQUEST['phrase'];
				$geo = trim($_REQUEST['geo']);

				if(is_array($phraseList))
				{
					$phraseList = array_values(array_unique($phraseList));
					$geoList = $geo <> '' ? preg_split("/[^0-9\\-]+\\s*/", $geo) : array();

					$phraseHash = md5(implode('|', $phraseList).'|||'.$geo);

					if(!isset($_SESSION["SEO_REPORTS"]))
					{
						$_SESSION["SEO_REPORTS"] = array();
					}

					sortByColumn($_SESSION["SEO_REPORTS"], "TS");

					foreach($_SESSION["SEO_REPORTS"] as $k => $report)
					{
						$lifeTime = time() - $report["TS"];
						if(
							$lifeTime > Engine\YandexDirect::TTL_WORDSTAT_REPORT
							&& $lifeTime < Engine\YandexDirect::TTL_WORDSTAT_REPORT_EXT
							|| count($_SESSION["SEO_REPORTS"]) >= Engine\YandexDirect::MAX_WORDSTAT_REPORTS
						)
						{
							$reportId = $report["REPORT_ID"];
							$engine->deleteWordstatReport($reportId);
							unset($_SESSION["SEO_REPORTS"][$k]);
						}
					}

					if(!isset($_SESSION["SEO_REPORTS"][$phraseHash]))
					{
						$reportId = $engine->createWordstatReport($phraseList, $geoList);

						$_SESSION["SEO_REPORTS"][$phraseHash] = array(
							"REPORT_ID" => intval($reportId),
							"PHRASE" => $phraseList,
							"GEO" => $geoList,
							"TS" => time(),
						);

						$res = $_SESSION["SEO_REPORTS"][$phraseHash];
					}
					else
					{
						$reportId = $_SESSION["SEO_REPORTS"][$phraseHash]["REPORT_ID"];
						$res = $engine->getWordstatReport($reportId);
					}
				}

			break;


			case 'forecast_report_clear':
			case 'forecast_report':

				if($_REQUEST['action'] == 'forecast_report_clear')
				{
					$reportList = $engine->getForecastReportList();

					if(count($reportList) >= Engine\YandexDirect::MAX_FORECAST_REPORTS)
					{
						foreach($reportList as $firstReport)
						{
							$engine->deleteForecastReport($firstReport["ForecastID"]);
							break;
						}
					}
				}

				$phraseList = $_REQUEST['phrase'];
				$geo = trim($_REQUEST['geo']);

				if(is_array($phraseList))
				{
					$phraseList = array_values(array_unique($phraseList));
					$geoList = $geo <> '' ? preg_split("/[^0-9\-]+\\s*/", $geo) : array();

					$phraseHash = md5(implode('|', $phraseList).'|||'.$geo);

					if(!isset($_SESSION["SEO_FORECASTS"]))
					{
						$_SESSION["SEO_FORECASTS"] = array();
					}

					sortByColumn($_SESSION["SEO_FORECASTS"], "TS");

					foreach($_SESSION["SEO_FORECASTS"] as $k => $report)
					{
						$lifeTime = time() - $report["TS"];
						if(
							$lifeTime > Engine\YandexDirect::TTL_FORECAST_REPORT
							&& $lifeTime < Engine\YandexDirect::TTL_FORECAST_REPORT_EXT
							|| count($_SESSION["SEO_FORECASTS"]) >= Engine\YandexDirect::MAX_FORECAST_REPORTS
						)
						{
							$reportId = $report["REPORT_ID"];
							$engine->deleteForecastReport($reportId);
							unset($_SESSION["SEO_FORECASTS"][$k]);
						}
					}

					if(!isset($_SESSION["SEO_FORECASTS"][$phraseHash]))
					{
						$reportId = $engine->createForecastReport($phraseList, $geoList);

						$_SESSION["SEO_FORECASTS"][$phraseHash] = array(
							"REPORT_ID" => intval($reportId),
							"PHRASE" => $phraseList,
							"GEO" => $geoList,
							"TS" => time(),
						);

						$res = $_SESSION["SEO_FORECASTS"][$phraseHash];
					}
					else
					{
						$reportId = $_SESSION["SEO_FORECASTS"][$phraseHash]["REPORT_ID"];
						$res = $engine->getForecastReport($reportId);
					}
				}

				break;

			case 'campaign_update':
				$campaignId = intval($_REQUEST['campaign']);

				$res = $engine->updateCampaignManual($campaignId);

				break;

			case 'banner_update':

				$bannerId = intval($_REQUEST['banner']);
				$campaignId = intval($_REQUEST['campaign']);

				$res = $engine->updateBannersManual($campaignId, $bannerId);

				break;

			case 'link_delete':
			case 'link_create':

				$res = array('result' => false);

				$bannerId = intval($_REQUEST['banner']);
				$linkId = intval($_REQUEST['link']);
				$linkType = $_REQUEST['link_type'];

				if($linkId > 0 & $bannerId > 0)
				{
					if($_REQUEST['action'] == 'link_delete')
					{
						$result = Adv\LinkTable::delete(array(
							'LINK_TYPE' => $linkType,
							'LINK_ID' => $linkId,
							'BANNER_ID' => $bannerId,
						));

						$res = array('result' => $result->isSuccess());
					}
					elseif($_REQUEST['action'] == 'link_create')
					{
						$dbRes = Adv\LinkTable::getByPrimary(array(
							'LINK_TYPE' => $linkType,
							'LINK_ID' => $linkId,
							'BANNER_ID' => $bannerId,
						));
						if(!$dbRes->fetch())
						{
							$result = Adv\LinkTable::add(array(
								'LINK_TYPE' => $linkType,
								'LINK_ID' => $linkId,
								'BANNER_ID' => $bannerId,
							));

							$res = array('result' => $result->isSuccess());
						}
						else
						{
							$res = array('result' => true);
						}
					}
				}

				if($res['result'] && $_REQUEST['get_list_html'])
				{
					Loader::includeModule('iblock');

					ob_start();

					if($_REQUEST['get_list_html'] == '1')
					{
						$iblockElementInfo = array(
							"ID" => $linkId,
							"IBLOCK" => array(
								"ID" => 0
							)
						);

						$dbRes = Adv\LinkTable::getList(array(
							"filter" => array(
								'=LINK_TYPE' => Adv\LinkTable::TYPE_IBLOCK_ELEMENT,
								'=LINK_ID' => $linkId,
								"=BANNER.ENGINE_ID" => $engine->getId(),
							),
							"select" => array(
								"BANNER_ID",
								"BANNER_NAME" => "BANNER.NAME", "BANNER_XML_ID" => "BANNER.XML_ID",
								"BANNER_CAMPAIGN_ID" => "BANNER.CAMPAIGN_ID",
								"LINK_IBLOCK_ID" => "IBLOCK_ELEMENT.IBLOCK_ID",
							)
						));

						$arBanners = array();
						while($banner = $dbRes->fetch())
						{
							if(!isset($arBanners[$banner['BANNER_CAMPAIGN_ID']]))
							{
								$arBanners[$banner['BANNER_CAMPAIGN_ID']] = array();
							}

							$arBanners[$banner['BANNER_CAMPAIGN_ID']][] = $banner;
							$iblockElementInfo['IBLOCK']['ID'] = $banner['LINK_IBLOCK_ID'];
						}

						$dbRes = Adv\YandexCampaignTable::getList(array(
							"order" => array("NAME" => "asc"),
							"filter" => array(
								"=ID" > array_keys($arBanners),
								'=ACTIVE' => Adv\YandexCampaignTable::ACTIVE,
								'=ENGINE_ID' => $engine->getId(),
							),
							'select' => array(
								"ID", "NAME", "XML_ID"
							)
						));
						$campaignList = array();

						while($campaign = $dbRes->fetch())
						{
							$campaignList[$campaign['ID']] = $campaign;
						}

						require(__DIR__."/../admin/tab/seo_search_yandex_direct_list_link.php");
					}
					elseif($_REQUEST['get_list_html'] == '2')
					{
						$dbRes = Adv\LinkTable::getList(array(
							"filter" => array(
								'=BANNER_ID' => $bannerId,
							),
							"select" => array(
								"LINK_TYPE", "LINK_ID",
								"ELEMENT_NAME" => "IBLOCK_ELEMENT.NAME",
								"ELEMENT_IBLOCK_ID" => "IBLOCK_ELEMENT.IBLOCK_ID",
								"ELEMENT_IBLOCK_TYPE_ID" => "IBLOCK_ELEMENT.IBLOCK.IBLOCK_TYPE_ID",
								'ELEMENT_IBLOCK_SECTION_ID' => 'IBLOCK_ELEMENT.IBLOCK_SECTION_ID',
							)
						));

						$arLinks = array();
						while($link = $dbRes->fetch())
						{
							if(!isset($link['LINK_TYPE']) && $elementId > 0)
							{
								$link['LINK_TYPE'] = Adv\LinkTable::TYPE_IBLOCK_ELEMENT;
							}

							$arLinks[] = $link;
						}

						$ID = $bannerId;

						require(__DIR__."/../admin/tab/seo_search_yandex_direct_list_banner.php");
					}

					$res['list_html'] = ob_get_contents();

					ob_end_clean();
				}

			break;

			case 'banners_get':
				$campaignId = intval($_REQUEST['campaign']);

				if($campaignId > 0)
				{
					$res = array();
					$dbRes = Adv\YandexBannerTable::getList(array(
						'filter' => array(
							'=CAMPAIGN_ID' => $campaignId,
							'=ACTIVE' => Adv\YandexBannerTable::ACTIVE,
							'=ENGINE_ID' => $engine->getId(),
						),
						'order' => array(
							'NAME' => 'ASC',
						),
						'select' => array('ID', 'NAME', 'XML_ID')
					));
					while($banner = $dbRes->fetch())
					{
						$res[] = $banner;
					}
				}

			break;

			case 'banner_stats':
				$res = array();

				$bannerId = intval($_REQUEST['banner']);
				$loadingSession = $_REQUEST['loading_session'];

				$gaps = array();

				if($loadingSession)
				{
					if(
						isset($_SESSION[$loadingSession])
						&& $_SESSION[$loadingSession]['BANNER_ID'] == $bannerId
					)
					{
						$dateStart = new Date($_SESSION[$loadingSession]['DATE_START']);
						$dateFinish = new Date($_SESSION[$loadingSession]['DATE_FINISH']);
						$gaps = $_SESSION[$loadingSession]['GAPS'];
					}
					else
					{
						$res = array('error' => array('message' => 'loading session broken'));
						break;
					}
				}
				else
				{
					if(in_array(
						$_REQUEST["type"],
						array("week_ago", "month_ago", "interval")
					))
					{
						$period = array();
						\CGridOptions::CalcDates("", array(
							"_datesel" => $_REQUEST["type"],
							"_days" => 1,
							"_from" => $_REQUEST["date_from"],
							"_to" => $_REQUEST["date_to"],
						), $period);

						if(Date::isCorrect($period['_from']))
						{
							$dateStart = new Date($period['_from']);
						}
						else
						{
							$res = array('error' => array('message' => Loc::getMessage('SEO_ERROR_INCORRECT_DATE').': '.$period['_from']));
							break;
						}

						if(Date::isCorrect($period['_to']))
						{
							$dateFinish = new Date($period['_to']);
						}
						else
						{
							$res = array('error' => array('message' => Loc::getMessage('SEO_ERROR_INCORRECT_DATE').': '.$period['_to']));
							break;
						}

						$statsData = Adv\YandexStatTable::getBannerStat(
							$bannerId,
							$dateStart,
							$dateFinish
						);

						$gaps = Adv\YandexStatTable::getMissedPeriods($statsData, $dateStart, $dateFinish);

					}
					else
					{
						$res = array('error' => array('message' => 'invalid interval type'));
					}
				}

				$errorMessage = null;
				$finish = true;

				if(count($gaps) > 0)
				{
					$cnt = 0;

					try
					{
						$sessionGaps = $gaps;
						foreach($gaps as $key => $gap)
						{
							Adv\YandexStatTable::loadBannerStat($bannerId, $gap[0], $gap[1]);
							unset($sessionGaps[$key]);

							if(++$cnt > 2)
							{
								if(!$loadingSession)
								{
									$loadingSession = uniqid("YD_LOADING_", true);
									$_SESSION[$loadingSession] = array(
										"BANNER_ID" => $bannerId,
										"DATE_START" => $dateStart->toString(),
										"DATE_FINISH" => $dateFinish->toString(),
										"ORIGINAL_CNT" => count($gaps),
									);
								}

								$_SESSION[$loadingSession]["GAPS"] = $sessionGaps;
								$finish = false;

								break;
							}
						}
					}
					catch(Engine\YandexDirectException $e)
					{
						$res = array('error' => array('message' => $e->getMessage(), "code" => $e->getCode()));
						$finish = true;
					}
				}

				if($finish)
				{
					if($loadingSession)
					{
						unset($_SESSION[$loadingSession]);
					}

					if(!$res['error'] || $res["code"] == Engine\YandexDirect::ERROR_NO_STATS)
					{
						$statsData = Adv\YandexStatTable::getBannerStat(
							$bannerId,
							$dateStart,
							$dateFinish
						);

						$graphData = array();

						foreach($statsData as $date => $dayData)
						{
							unset($dayData['ID']);
							unset($dayData['CAMPAIGN_ID']);
							unset($dayData['BANNER_ID']);
							unset($dayData['DATE_DAY']);

							$dayData['date'] = $date;
							$graphData[] = $dayData;
						}

						$res["data"] = $graphData;
						$res["date_from"] = $dateStart->toString();
						$res["date_to"] = $dateFinish->toString();

						if(
							\Bitrix\Main\ModuleManager::isModuleInstalled('sale')
							&& \Bitrix\Main\ModuleManager::isModuleInstalled('catalog')
							&& Loader::includeModule('currency')
						)
						{
							$orderStats = Adv\OrderTable::getList(array(
								'filter' => array(
									'=BANNER_ID' => $bannerId,
									'=PROCESSED' => Adv\OrderTable::PROCESSED,
									">=TIMESTAMP_X" => $dateStart,
									"<TIMESTAMP_X" => $dateFinish,
								),
								'group' => array(
									'BANNER_ID'
								),
								'select' => array('BANNER_SUM'),
								'runtime' => array(
									new \Bitrix\Main\Entity\ExpressionField('BANNER_SUM', 'SUM(SUM)'),
								),
							));
							if($stat = $orderStats->fetch())
							{
								$res["order_sum"] = $stat['BANNER_SUM'];
							}
							else
							{
								$res["order_sum"] = 0;
							}

							$res["order_sum_format"] = \CCurrencyLang::CurrencyFormat(doubleval($res["order_sum"]), \Bitrix\Currency\CurrencyManager::getBaseCurrency(), true);
						}
					}
				}
				else
				{
					$res = array(
						"session" => $loadingSession,
						"amount" => $_SESSION[$loadingSession]['ORIGINAL_CNT'],
						"left" => count($_SESSION[$loadingSession]["GAPS"]),
					);
				}

			break;

			case 'campaign_stats':
				$res = array();

				$campaignId = intval($_REQUEST['campaign']);
				$loadingSession = $_REQUEST['loading_session'];

				$gaps = array();

				if($loadingSession)
				{
					if(
						isset($_SESSION[$loadingSession])
						&& $_SESSION[$loadingSession]['CAMPAIGN_ID'] == $campaignId
					)
					{
						$dateStart = new Date($_SESSION[$loadingSession]['DATE_START']);
						$dateFinish = new Date($_SESSION[$loadingSession]['DATE_FINISH']);
						$gaps = $_SESSION[$loadingSession]['GAPS'];
					}
					else
					{
						$res = array('error' => array('message' => 'loading session broken'));
						break;
					}
				}
				else
				{
					if(in_array(
						$_REQUEST["type"],
						array("week_ago", "month_ago", "interval")
					))
					{
						$period = array();
						\CGridOptions::CalcDates("", array(
							"_datesel" => $_REQUEST["type"],
							"_days" => 1,
							"_from" => $_REQUEST["date_from"],
							"_to" => $_REQUEST["date_to"],
						), $period);

						if(Date::isCorrect($period['_from']))
						{
							$dateStart = new Date($period['_from']);
						}
						else
						{
							$res = array('error' => array('message' => Loc::getMessage('SEO_ERROR_INCORRECT_DATE').': '.$period['_from']));
							break;
						}

						if(Date::isCorrect($period['_to']))
						{
							$dateFinish = new Date($period['_to']);
						}
						else
						{
							$res = array('error' => array('message' => Loc::getMessage('SEO_ERROR_INCORRECT_DATE').': '.$period['_to']));
							break;
						}

						$statsData = Adv\YandexStatTable::getCampaignStat(
							$campaignId,
							$dateStart,
							$dateFinish
						);

						$gaps = Adv\YandexStatTable::getMissedPeriods($statsData, $dateStart, $dateFinish);
					}
					else
					{
						$res = array('error' => array('message' => 'invalid interval type'));
					}
				}

				$errorMessage = null;
				$finish = true;

				if(count($gaps) > 0)
				{
					$cnt = 0;

					try
					{
						$sessionGaps = $gaps;
						foreach($gaps as $key => $gap)
						{
							Adv\YandexStatTable::loadCampaignStat($campaignId, $gap[0], $gap[1]);
							unset($sessionGaps[$key]);

							if(++$cnt > 2)
							{
								if(!$loadingSession)
								{
									$loadingSession = uniqid("YD_LOADING_", true);
									$_SESSION[$loadingSession] = array(
										"CAMPAIGN_ID" => $campaignId,
										"DATE_START" => $dateStart->toString(),
										"DATE_FINISH" => $dateFinish->toString(),
										"ORIGINAL_CNT" => count($gaps),
									);
								}

								$_SESSION[$loadingSession]["GAPS"] = $sessionGaps;
								$finish = false;

								break;
							}
						}
					}
					catch(Engine\YandexDirectException $e)
					{
						$res = array('error' => array('message' => $e->getMessage(), "code" => $e->getCode()));
						$finish = true;
					}
				}

				if($finish)
				{
					if($loadingSession)
					{
						unset($_SESSION[$loadingSession]);
					}

					if(!$res['error'] || $res["code"] == Engine\YandexDirect::ERROR_NO_STATS)
					{
						$statsData = Adv\YandexStatTable::getCampaignStat(
							$campaignId,
							$dateStart,
							$dateFinish
						);

						$graphData = array();

						foreach($statsData as $date => $dayData)
						{
							$graphData[] = array(
								'date' => $date,
								'CURRENCY' => $dayData['CURRENCY'],
								'SUM' => round($dayData['CAMPAIGN_SUM'], 2),
								'SUM_SEARCH' => round($dayData['CAMPAIGN_SUM_SEARCH'], 2),
								'SUM_CONTEXT' => round($dayData['CAMPAIGN_SUM_CONTEXT'], 2),
								'SHOWS' => $dayData['CAMPAIGN_SHOWS'],
								'SHOWS_SEARCH' => $dayData['CAMPAIGN_SHOWS_SEARCH'],
								'SHOWS_CONTEXT' => $dayData['CAMPAIGN_SHOWS_CONTEXT'],
								'CLICKS' => $dayData['CAMPAIGN_CLICKS'],
								'CLICKS_SEARCH' => $dayData['CAMPAIGN_CLICKS_SEARCH'],
								'CLICKS_CONTEXT' => $dayData['CAMPAIGN_CLICKS_CONTEXT'],
							);
						}

						$res["data"] = $graphData;
						$res["date_from"] = $dateStart->toString();
						$res["date_to"] = $dateFinish->toString();
					}
				}
				else
				{
					$res = array(
						"session" => $loadingSession,
						"amount" => $_SESSION[$loadingSession]['ORIGINAL_CNT'],
						"left" => count($_SESSION[$loadingSession]["GAPS"]),
					);
				}

				break;

			case 'banner_stat_detail':
				$res = array('error' => array('message' => 'Wrong banners list'));

				$bSale = \Bitrix\Main\ModuleManager::isModuleInstalled('sale')
					&& \Bitrix\Main\ModuleManager::isModuleInstalled('catalog')
					&& Loader::includeModule('currency');

				$bannerId = $_REQUEST['banner'];
				if($bannerId)
				{
					$bSingle = !is_array($bannerId);
					if($bSingle)
					{
						$bannerId = array($bannerId);
					}

					array_map('intval', $bannerId);

					if(count($bannerId) > 0)
					{
						$dbBanners = Adv\YandexBannerTable::getList(array(
							'filter' => array(
								'@ID' => $bannerId,
								'=ENGINE_ID' => $engine->getId(),
								'=ACTIVE' => Adv\YandexBannerTable::ACTIVE,
							),
							'select' => array(
								'ID', 'CAMPAIGN_ID', 'SETTINGS'
							),
						));

						$bannerList = array();
						$campaignList = array();

						while($banner = $dbBanners->fetch())
						{
							$campaignList[] = $banner['CAMPAIGN_ID'];
							$bannerList[$banner['ID']] = $banner;
						}

						$campaignList = array_unique($campaignList);

						if(count($campaignList) > 0)
						{
							$dbCampaigns = Adv\YandexCampaignTable::getList(array(
								'filter' => array(
									'@ID' => $campaignList,
									'=ENGINE_ID' => $engine->getId(),
									'=ACTIVE' => Adv\YandexCampaignTable::ACTIVE,
								),
								'select' => array(
									'ID', 'SETTINGS'
								),
							));

							$campaignList = array();
							while($campaign = $dbCampaigns->fetch())
							{
								$campaignList[$campaign['ID']] = $campaign;
							}

							$dateFinish = new Date();

							$bannerListToCheck = array();
							foreach($bannerList as $key => $banner)
							{
								$banner['DATE_START'] = $campaignList[$banner['CAMPAIGN_ID']]['SETTINGS']['StartDate'];

								if($bSale)
								{
									$banner['PROFIT'] = 0;
								}

								if($banner['DATE_START'])
								{
									$banner['DATE_START'] = new Date($banner['DATE_START'], 'Y-m-d');

									$banner['STATS_DATA'] = Adv\YandexStatTable::getBannerStat(
										$banner['ID'],
										$banner['DATE_START'],
										$dateFinish
									);

									$gaps = Adv\YandexStatTable::getMissedPeriods($banner['STATS_DATA'], $banner['DATE_START'], $dateFinish);

									if(count($gaps) > 0)
									{
										$banner['LOADING_NEEDED'] = true;
										$bannerListToCheck[] = $banner['ID'];
									}
									else
									{
										$bannerListToCheck[] = $banner['ID'];
									}
								}

								$bannerList[$key] = $banner;
							}

							if($bSale && count($bannerListToCheck) > 0)
							{
								$orderStats = Adv\OrderTable::getList(array(
									'filter' => array(
										'@BANNER_ID' => $bannerListToCheck,
										'=PROCESSED' => Adv\OrderTable::PROCESSED,
									),
									'group' => array(
										'BANNER_ID'
									),
									'select' => array('BANNER_ID', 'BANNER_SUM'),
									'runtime' => array(
										new \Bitrix\Main\Entity\ExpressionField('BANNER_SUM', 'SUM(SUM)'),
									),
								));
								while($stat = $orderStats->fetch())
								{
									$bannerList[$stat['BANNER_ID']]['PROFIT'] = $stat['BANNER_SUM'];
								}
							}

							ob_start();
							require(__DIR__."/../admin/tab/seo_search_yandex_direct_stat.php");
							$res = array('html' => ob_get_contents());
							ob_end_clean();
						}
					}
				}

			break;

			default:
				$res = array('error' => array('message' => 'unknown action'));
			break;
		}
	}
	catch(Engine\YandexDirectException $e)
	{
		$res = array(
			'error' => array(
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			)
		);
	}

	Header('Content-type: application/json; charset='.LANG_CHARSET);
	echo \Bitrix\Main\Web\Json::encode($res);
}
