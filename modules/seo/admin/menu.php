<?
/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CAdminMenu $this
 */

use \Bitrix\Seo\Adv;
use \Bitrix\Seo\Engine;
use \Bitrix\Main\Localization\Loc;

if($APPLICATION->GetGroupRight("seo") > "D")
{
	if(\Bitrix\Main\ModuleManager::isModuleInstalled('seo'))
	{
		IncludeModuleLangFile(__FILE__);

		$bShowYandexServices =
			COption::GetOptionString('main', 'vendor', '') == '1c_bitrix'
			&& Loc::getDefaultLang(LANGUAGE_ID) == 'ru';
		$bShowGoogleServices = $bShowYandexServices;
		
		$aMenu = array(
			array(
				"parent_menu" => "global_menu_marketing",
				"section" => "seo",
				"sort" => 900,
				"text" => Loc::getMessage("SEO_MENU_MAIN"),
				"title" => Loc::getMessage("SEO_MENU_MAIN_TITLE"),
				"icon" => "seo_menu_icon",
				"page_icon" => "seo_page_icon",
				"module_id" => "seo",
				"items_id" => "menu_seo",
				"items" => array(),
			)
		);

		$arEngineList = array();
		$arAdvList = array();
		
//		not show Yandex services on portal
		if ($bShowYandexServices)
		{
			$arEngineList[] = array(
				'url' => 'seo_search_yandex.php?lang='.LANGUAGE_ID,
				'more_url' => array('seo_search_yandex_detail.php?lang='.LANGUAGE_ID),
				'text' => Loc::getMessage("SEO_MENU_YANDEX"),
			);

			$yandexAdvItem = array(
				'url' => 'seo_search_yandex_direct.php?lang='.LANGUAGE_ID,
				'more_url' => array(
					'seo_search_yandex_direct_edit.php?lang='.LANGUAGE_ID,
					'seo_search_yandex_direct_banner.php?lang='.LANGUAGE_ID,
					'seo_search_yandex_direct_banner_edit.php?lang='.LANGUAGE_ID,
				),
				'text' => Loc::getMessage("SEO_MENU_YANDEX_DIRECT"),
				"module_id" => "seo",
				'items_id' => "seo_search_adv_items",
				"dynamic" => true,
				'items' => array(),
			);

			if(
				(
					method_exists($this, "IsSectionActive")
					&& $this->IsSectionActive("seo_search_adv_items")
				)
				|| (
					defined('BX_ADMIN_SEO_ADV_MENU_OPEN')
					&& BX_ADMIN_SEO_ADV_MENU_OPEN == 1
				)
			)
			{
				if(\Bitrix\Main\Loader::includeModule('seo'))
				{
					$yandexAdvCampaigns = array();

					$engine = new Engine\YandexDirect();

					$dbRes = Adv\YandexCampaignTable::getList(array(
						'order' => array('NAME' => 'ASC'),
						'filter' => array(
							'=ENGINE_ID' => $engine->getId(),
							'=ACTIVE' => Adv\YandexCampaignTable::ACTIVE
						),
						'select' => array('ID', 'NAME'),
					));

					while($campaign = $dbRes->fetch())
					{
						if(
						(
							method_exists($this, "IsSectionActive")
							&& $this->IsSectionActive("seo_search_adv_items/".$campaign['ID'])
						)
						|| (
							$GLOBALS["APPLICATION"]->GetCurPage() == '/bitrix/admin/seo_search_yandex_direct_banner_edit.php'
							&& $_REQUEST['campaign'] == $campaign['ID']
						))
						{
							$yandexAdvCampaigns[$campaign['ID']] = count($yandexAdvItem['items']);
						}

						$yandexAdvItem['items'][] = array(
							'url' => 'seo_search_yandex_direct_edit.php?lang='.LANGUAGE_ID.'&ID='.$campaign['ID'],
							'more_url' => array(
								'seo_search_yandex_direct_banner.php?lang='.LANGUAGE_ID.'&campaign='.$campaign['ID'],
								'seo_search_yandex_direct_banner_edit.php?lang='.LANGUAGE_ID.'&campaign='.$campaign['ID'],
							),
							'text' => $campaign['NAME'],
							'module_id' => 'seo',
							'dynamic' => true,
							'items_id' => "seo_search_adv_items/".$campaign['ID'],
							'items' => array(),
						);
					}

					if(count($yandexAdvCampaigns) > 0)
					{
						$dbRes = Adv\YandexBannerTable::getList(array(
							'order' => array('NAME' => 'ASC'),
							'filter' => array(
								'=ENGINE_ID' => $engine->getId(),
								'=CAMPAIGN_ID' => array_keys($yandexAdvCampaigns),
								'=ACTIVE' => Adv\YandexBannerTable::ACTIVE,
							),
							'select' => array('ID', 'CAMPAIGN_ID', 'NAME'),
						));
						while($banner = $dbRes->fetch())
						{
							$yandexAdvItem['items'][$yandexAdvCampaigns[$banner['CAMPAIGN_ID']]]['items'][] = array(
								'url' => 'seo_search_yandex_direct_banner_edit.php?lang='.LANGUAGE_ID.'&campaign='.$banner['CAMPAIGN_ID'].'&ID='.$banner['ID'],
								'text' => $banner['NAME'],
								'parent_menu' => "seo_search_adv_items/".$banner['CAMPAIGN_ID'],
								'items_id' => "seo_search_adv_items/".$banner['CAMPAIGN_ID']."/".$banner['ID']
							);
						}
					}
				}
			}

			$arAdvList[] = $yandexAdvItem;
		}
		
//		not show Yandex and Google on portal
		if($bShowGoogleServices)
			$arEngineList[] = array(
				'url' => 'seo_search_google.php?lang='.LANGUAGE_ID,
				'text' => Loc::getMessage("SEO_MENU_GOOGLE"),
			);

		if(count($arEngineList) > 0)
		{
			$aMenu[0]["items"][] = array(
				"text" => Loc::getMessage("SEO_MENU_SEARCH_ENGINES"),
				"title" => Loc::getMessage("SEO_MENU_SEARCH_ENGINES_ALT"),
				"items_id" => "seo_search_engine",
				"items" => $arEngineList
			);
		}

		$aMenu[0]['items'][] = array(
			"url" => "seo_robots.php?lang=".LANGUAGE_ID,
			"text" => Loc::getMessage("SEO_MENU_ROBOTS_ALT"),
			//"title" => Loc::getMessage("SEO_MENU_ROBOTS_ALT"),
		);
		$aMenu[0]['items'][] = array(
			"url" => "seo_sitemap.php?lang=".LANGUAGE_ID,
			"more_url" => array("seo_sitemap_edit.php?lang=".LANGUAGE_ID),
			"text" => Loc::getMessage("SEO_MENU_SITEMAP_ALT"),
			//"title" => Loc::getMessage("SEO_MENU_SITEMAP_ALT"),
		);

		if(count($arAdvList) > 0)
		{
			$arAdvList[] = array(
				"sort" => 4000,
				"text" => Loc::getMessage("SEO_MENU_ADV_AUTOLOG"),
				"title" => Loc::getMessage("SEO_MENU_ADV_AUTOLOG_ALT"),
				"url" => "seo_search_yandex_direct_autolog.php?lang=".LANGUAGE_ID,
			);


			$aMenu[] = array(
				"parent_menu" => "global_menu_marketing",
				"section" => "seo_adv",
				"sort" => 400,
				"text" => Loc::getMessage("SEO_MENU_ADV_ENGINES"),
				"title" => Loc::getMessage("SEO_MENU_ADV_ENGINES_ALT"),
				"icon" => "seo_adv_menu_icon",
				"page_icon" => "seo_page_icon",
				"module_id" => "seo",
				"items_id" => "seo_search_adv",
				"items" => $arAdvList,
			);
		}

		return $aMenu;
	}
}
return false;
