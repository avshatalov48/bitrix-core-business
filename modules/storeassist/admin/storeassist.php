<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2014 Bitrix             #
# https://www.bitrixsoft.com                 #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/storeassist/include.php");

IncludeModuleLangFile(__FILE__);
\Bitrix\Main\Loader::includeModule('storeassist');

if (!($APPLICATION->GetGroupRight("storeassist") >= "R"))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/storeassist/prolog.php");

$APPLICATION->SetTitle(GetMessage("STOREAS_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$APPLICATION->SetAdditionalCSS('/bitrix/panel/storeassist/storeassist.css');

CUtil::InitJSCore(array("fx", "storeassist"));

$catalogIblockId = "";
if (Bitrix\Main\Loader::includeModule("catalog"))
{
	$dbCatalog = CCatalog::getList(array(), array(
		"IBLOCK_ACTIVE" => "Y",
		"IBLOCK_TYPE_ID" => "catalog"
	));
	if ($arCatalog = $dbCatalog->Fetch())
	{
		$catalogIblockId = $arCatalog["IBLOCK_ID"];
	}
}

$partnerUrl = Bitrix\Main\Config\Option::get("storeassist", "partner_url", "");

$arAssistSteps = array(
	"MAIN" => array(
		"BLOCKS" => array(
			"BLOCK_1" => array(
				"MAIN_ITEMS" => array(
					"currencies" => array(
						"path" => "/bitrix/admin/currencies.php?lang=".LANGUAGE_ID."&back=main_block_1#showtask",
						"available" => IsModuleInstalled("currency")
					),
					"cat_group_admin" => array(
						"path" => "/bitrix/admin/cat_group_admin.php?lang=".LANGUAGE_ID."&back=main_block_1#showtask",
						"available" => IsModuleInstalled("catalog")
					),
					"cat_measure_list" => array(
						"path" => "/bitrix/admin/cat_measure_list.php?lang=".LANGUAGE_ID."&back=main_block_1#showtask",
						"available" => IsModuleInstalled("catalog")
					),
					"sale_report_edit" => array(
						"path" => "/bitrix/admin/sale_report_edit.php?lang=".LANGUAGE_ID."&back=main_block_1#showtask",
						"available" => IsModuleInstalled("sale")
					),
					"sale_person_type" => array(
						"path" => "/bitrix/admin/sale_person_type.php?lang=".LANGUAGE_ID."&back=main_block_1#showtask",
						"available" => IsModuleInstalled("sale")
					),
					//"locations" => "/bitrix/admin/sale_location_admin.php?lang=".LANGUAGE_ID."#showtask", //TODO pageId
					"sale_buyers" => array(
						"path" => "/bitrix/admin/sale_buyers.php?lang=".LANGUAGE_ID."&pageid=sale_buyers&back=main_block_1#showtask",
						"available" => IsModuleInstalled("sale")
					),
					"sale_status" => array(
						"path" => "/bitrix/admin/sale_status.php?lang=".LANGUAGE_ID."&back=main_block_1#showtask",
						"available" => IsModuleInstalled("sale")
					),
					"cat_store_list" => array(
						"path" => "/bitrix/admin/cat_store_list.php?lang=".LANGUAGE_ID."&back=main_block_1#showtask",
						"available" => IsModuleInstalled("catalog")
					),
					"storeassist_social" => array(
						"path" => "/bitrix/admin/storeassist_social.php?lang=".LANGUAGE_ID."&back=main_block_1#showtask",
						"available" => true
					)
				),
				"ADDITIONAL_ITEMS" => array()
			),
			"BLOCK_2" => array(
				"MAIN_ITEMS" => array(
					"cat_product_list" =>  array(
						"path" => (intval($catalogIblockId)) ? "/bitrix/admin/cat_product_list.php?lang=".LANGUAGE_ID."&IBLOCK_ID=".$catalogIblockId."&type=catalog&find_section_section=-1" : "/bitrix/admin/storeassist_new_items.php?lang=".LANGUAGE_ID."&pageid=cat_product_list&back=main_block_2#showtask",
						"available" => IsModuleInstalled("catalog")
					),
					"quantity" => array(
						"path" => "/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=catalog&pageid=quantity&back=main_block_2#showtask",
						"available" => IsModuleInstalled("catalog")
					),
					"cat_store_document_list" => array(
						"path" => "/bitrix/admin/cat_store_document_list.php?lang=".LANGUAGE_ID."&back=main_block_2#showtask",
						"available" => IsModuleInstalled("catalog")
					),
					"order_setting" => array(
						"path" => "/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=sale&pageid=order_setting&back=main_block_2#showtask",
						"available" => IsModuleInstalled("sale")
					),
					"reserve_setting" => array(
						"path" => "/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=catalog&pageid=reserve_setting&back=main_block_2#showtask",
						"available" => IsModuleInstalled("catalog")
					)
				),
				"ADDITIONAL_ITEMS" => array(),
				"TYPE" => "ONE"
			),
			"BLOCK_3" => array(
				"MAIN_ITEMS" => array(
					"storeassist_1c_catalog_fill" => array(
						"path" => "/bitrix/admin/storeassist_1c_catalog_fill.php?lang=".LANGUAGE_ID."&back=main_block_3#showtask",
						"available" => true
					),
					"1c_integration" => array(
						"path" => "/bitrix/admin/1c_admin.php?lang=".LANGUAGE_ID."&pageid=1c_integration&back=main_block_3#showtask",
						"available" => IsModuleInstalled("sale")
					),
					"storeassist_1c_unloading" => array(
						"path" => "/bitrix/admin/storeassist_1c_unloading.php?lang=".LANGUAGE_ID."&back=main_block_3#showtask",
						"available" => true
					),
					"1c_exchange" => array(
						"path" => "/bitrix/admin/1c_admin.php?lang=".LANGUAGE_ID."&pageid=1c_exchange&back=main_block_3#showtask",
						"available" => IsModuleInstalled("sale")
					),
					"storeassist_1c_exchange_realtime" => array(
						"path" => "/bitrix/admin/storeassist_1c_exchange_realtime.php?lang=".LANGUAGE_ID."&back=main_block_3#showtask",
						"available" => true
					),
					"storeassist_1c_small_firm" => array(
						"path" => "/bitrix/admin/storeassist_1c_small_firm.php?lang=".LANGUAGE_ID."&back=main_block_3#showtask",
						"available" => true
					)
				),
				"ADDITIONAL_ITEMS" => array(),
				"TYPE" => "TWO"
			),
			"BLOCK_4" => array(
				"MAIN_ITEMS" => array(
					"sale_pay_system" => array(
						"path" => "/bitrix/admin/sale_pay_system.php?lang=".LANGUAGE_ID."&back=main_block_4#showtask",
						"available" => IsModuleInstalled("sale")
					),
					(COption::GetOptionString("main", "~sale_converted_15", "") == "Y" ? "sale_delivery_service_list" : "sale_delivery") => array(
						"path" => "/bitrix/admin/".(COption::GetOptionString("main", "~sale_converted_15", "") == "Y" ? "sale_delivery_service_list" : "sale_delivery").".php?lang=".LANGUAGE_ID."&back=main_block_4#showtask",
						"available" => IsModuleInstalled("sale")
					)
				),
				"ADDITIONAL_ITEMS" => array()
			),
			"BLOCK_5" => array(
				"MAIN_ITEMS" => array(
					"storeassist_seo_settings" => array(
						"path" => "/bitrix/admin/storeassist_seo_settings.php?lang=".LANGUAGE_ID."&back=main_block_5#showtask",
						"available" => true
					),
					"seo_robots" => array(
						"path" => "/bitrix/admin/seo_robots.php?lang=".LANGUAGE_ID."&back=main_block_5#showtask",
						"available" => IsModuleInstalled("seo")
					),
					"seo_sitemap" => array(
						"path" => "/bitrix/admin/seo_sitemap.php?lang=".LANGUAGE_ID."&back=main_block_5#showtask",
						"available" => IsModuleInstalled("seo")
					),
					"seo_search_yandex" => array(
						"path" => "/bitrix/admin/seo_search_yandex.php?lang=".LANGUAGE_ID."&back=main_block_5#showtask",
						"available" => IsModuleInstalled("seo")
					),
					"seo_search_google" => array(
						"path" => "/bitrix/admin/seo_search_google.php?lang=".LANGUAGE_ID."&back=main_block_5#showtask",
						"available" => IsModuleInstalled("seo")
					),
					"search_reindex" => array(
						"path" => "/bitrix/admin/search_reindex.php?lang=".LANGUAGE_ID."&back=main_block_5#showtask",
						"available" => IsModuleInstalled("search")
					)
				),
				"ADDITIONAL_ITEMS" => array()
			)
		)
	),
	"WORK" => array(
		"BLOCKS" => array(
			"BLOCK_1" => array(
				"MAIN_ITEMS" => array(
					"storeassist_adaptive" => array(
						"path" => "/bitrix/admin/storeassist_adaptive.php?lang=".LANGUAGE_ID."&back=work_block_1#showtask",
						"available" => true
					),
					"opening" => array(
						"path" => "/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=main&pageid=opening&back=work_block_1#showtask",
						"available" => IsModuleInstalled("main")
					),
					"checklist" => array(
						"path" => "/bitrix/admin/checklist.php?lang=".LANGUAGE_ID."&back=work_block_1#showtask",
						"available" => IsModuleInstalled("main")
					),
				),
				"ADDITIONAL_ITEMS" => array()
			),
			"BLOCK_2" => array(
				"MAIN_ITEMS" => array(
				//	"storeassist_context_adv" => "/bitrix/admin/storeassist_context_adv.php?lang=".LANGUAGE_ID."&back=work_block_2#showtask",
					"cat_discount_admin" => array(
						"path" => "/bitrix/admin/cat_discount_admin.php?lang=".LANGUAGE_ID."&back=work_block_2#showtask",
						"available" => IsModuleInstalled("catalog")
					),
				//	"storeassist_marketing" => "/bitrix/admin/storeassist_marketing.php?lang=".LANGUAGE_ID."&back=work_block_2#showtask",
					"posting_admin" => array(
						"path" => "/bitrix/admin/posting_admin.php?lang=".LANGUAGE_ID."&back=work_block_2#showtask",
						"available" => IsModuleInstalled("subscribe")
					)
				),
				"ADDITIONAL_ITEMS" => array()
			),
			"BLOCK_3" => array(
				"MAIN_ITEMS" => array(
					"cat_export_setup" => array(
						"path" => "/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID."&back=work_block_3#showtask",
						"available" => IsModuleInstalled("catalog")
					),
					"sale_ymarket" => array(
						"path" => "/bitrix/admin/sale_ymarket.php?lang=".LANGUAGE_ID."&back=work_block_3#showtask",
						"available" => IsModuleInstalled("sale")
					),
					//"ebay" => ""//TODO pageId
				),
				"ADDITIONAL_ITEMS" => array()
			),
			"BLOCK_4" => array(
				"MAIN_ITEMS" => array(
					"sale_order" => array(
						"path" => "/bitrix/admin/sale_order.php?lang=".LANGUAGE_ID."&back=work_block_4#showtask",
						"available" => IsModuleInstalled("sale")
					),
					"sale_report" => array(
						"path" => "/bitrix/admin/sale_report.php?lang=".LANGUAGE_ID."&back=work_block_4#showtask",
						"available" => IsModuleInstalled("sale")
					),
					//"storeassist_print" => "/bitrix/admin/storeassist_print.php?lang=".LANGUAGE_ID."&back=work_block_4#showtask",
					"client" => array(
						"path" => "/bitrix/admin/sale_buyers.php?lang=".LANGUAGE_ID."&pageid=client&back=work_block_4",
						"available" => IsModuleInstalled("sale")
					),
					"sale_account_admin" => array(
						"path" => "/bitrix/admin/sale_account_admin.php?lang=".LANGUAGE_ID."&back=work_block_4#showtask",
						"available" => IsModuleInstalled("sale")
					),
					"sale_basket" => array(
						"path" => "/bitrix/admin/sale_basket.php?lang=".LANGUAGE_ID."&back=work_block_4#showtask",
						"available" => IsModuleInstalled("sale")
					)
				),
				"ADDITIONAL_ITEMS" => array()
			),
			"BLOCK_5" => array(
				"MAIN_ITEMS" => array(
					"sale_personalization" => array(
						"path" => "/bitrix/admin/sale_personalization.php?lang=".LANGUAGE_ID."&back=work_block_5#showtask",
						"available" => IsModuleInstalled("sale")
					),
				),
				"ADDITIONAL_ITEMS" => array()
			),
			"BLOCK_6" => array(
				"MAIN_ITEMS" => array(
					"blog_comment" => array(
						"path" => "/bitrix/admin/blog_comment.php?lang=".LANGUAGE_ID."&back=work_block_6#showtask",
						"available" => IsModuleInstalled("blog")
					),
					"ticket_desktop" => array(
						"path" => "/bitrix/admin/ticket_desktop.php?lang=".LANGUAGE_ID."&back=work_block_6#showtask",
						"available" => IsModuleInstalled("support")
					)
				),
				"ADDITIONAL_ITEMS" => array()
			),
			"BLOCK_7" => array(
				"MAIN_ITEMS" => array(
					"sale_crm" => array(
						"path" => "/bitrix/admin/sale_crm.php?lang=".LANGUAGE_ID."&back=work_block_6#showtask",
						"available" => IsModuleInstalled("sale")
					),
					"storeassist_crm_client" => array(
						"path" => "/bitrix/admin/storeassist_crm_client.php?lang=".LANGUAGE_ID."&back=work_block_7#showtask",
						"available" => true
					),
					"storeassist_crm_calls" => array(
						"path" => "/bitrix/admin/storeassist_crm_calls.php?lang=".LANGUAGE_ID."&back=work_block_7#showtask",
						"available" => true
					)
				),
				"ADDITIONAL_ITEMS" => array()
			)
		)
	),
	"HEALTH" => array(
		"BLOCKS" => array(
			"BLOCK_1" => array(
				"MAIN_ITEMS" => array(
					"site_speed" => array(
						"path" => "/bitrix/admin/site_speed.php?lang=".LANGUAGE_ID."&back=health_block_1#showtask",
						"available" => IsModuleInstalled("main")
					),
					"bitrixcloud_cdn" => array(
						"path" => "/bitrix/admin/bitrixcloud_cdn.php?lang=".LANGUAGE_ID."&back=health_block_1#showtask",
						"available" => IsModuleInstalled("bitrixcloud")
					),
					"composite" => array(
						"path" => "/bitrix/admin/composite.php?lang=".LANGUAGE_ID."&back=health_block_1#showtask",
						"available" => IsModuleInstalled("main"),
						"subItems" => array(
							"composite_dev" => "/bitrix/admin/composite.php?lang=".LANGUAGE_ID."&back=health_block_1&subId=composite_dev#showtask",
							"composite_auto" => "/bitrix/admin/composite.php?lang=".LANGUAGE_ID."&back=health_block_1&subId=composite_auto&tabControl_active_tab=composite#showtask"
						)
					),
					"perfmon_panel" => array(
						"path" => "/bitrix/admin/perfmon_panel.php?lang=".LANGUAGE_ID."&back=health_block_1#showtask",
						"available" => IsModuleInstalled("perfmon")
					)
				),
				"ADDITIONAL_ITEMS" => array()
			),
			"BLOCK_2" => array(
				"MAIN_ITEMS" => array(
					"security_filter" => array(
						"path" => "/bitrix/admin/security_filter.php?lang=".LANGUAGE_ID."&back=health_block_2#showtask",
						"available" => IsModuleInstalled("security")
					),
					"dump_auto" => array(
						"path" => "/bitrix/admin/dump_auto.php?lang=".LANGUAGE_ID."&back=health_block_2#showtask",
						"available" => IsModuleInstalled("main")
					),
					"security_scanner" => array(
						"path" => "/bitrix/admin/security_scanner.php?lang=".LANGUAGE_ID."&back=health_block_2#showtask",
						"available" => IsModuleInstalled("security")
					),
					"bitrixcloud_monitoring_admin" => array(
						"path" => "/bitrix/admin/bitrixcloud_monitoring_admin.php?lang=".LANGUAGE_ID."&back=health_block_2#showtask",
						"available" => IsModuleInstalled("bitrixcloud")
					),
					"security_otp" => array(
						"path" => "/bitrix/admin/security_otp.php?lang=".LANGUAGE_ID."&back=health_block_2#showtask",
						"available" => IsModuleInstalled("security")
					)
				),
				"ADDITIONAL_ITEMS" => array()
			),
			"BLOCK_3" => array(
				"MAIN_ITEMS" => array(
					"scale_graph" => array(
						"path" => "/bitrix/admin/scale_graph.php?lang=".LANGUAGE_ID."&back=health_block_3#showtask",
						"available" => IsModuleInstalled("scale")
					),
					"cluster_index" => array(
						"path" => "/bitrix/admin/cluster_index.php?lang=".LANGUAGE_ID."&back=health_block_3#showtask",
						"available" => IsModuleInstalled("cluster")
					),
					"storeassist_virtual" => array(
						"path" => "/bitrix/admin/storeassist_virtual.php?lang=".LANGUAGE_ID."&back=health_block_3#showtask",
						"available" => true
					)
				),
				"ADDITIONAL_ITEMS" => array()
			),
			"BLOCK_4" => array(
				"MAIN_ITEMS" => array(
					"site_checker" => array(
						"path" => "/bitrix/admin/site_checker.php?lang=".LANGUAGE_ID."&back=health_block_4#showtask",
						"available" => IsModuleInstalled("main")
					),
					"info_vk" => array(
						"path" => "https://vk.com/topic-23581648_24910930",
						"available" => true
					),
					"info_blog" => array(
						"path" => "https://dev.1c-bitrix.ru/community/blogs/product_features/",
						"available" => true
					),
					"info_forum_guest" => array(
						"path" => "https://dev.1c-bitrix.ru/community/forums/forum6/",
						"available" => true
					),
					"info_forum_client" => array(
						"path" => "https://dev.1c-bitrix.ru/community/forums/forum7/",
						"available" => true
					),
					"info_idea" => array(
						"path" => "https://idea.1c-bitrix.ru/category/bitrix/",
						"available" => true
					),
					"info_user_doc" => array(
						"path" => "https://dev.1c-bitrix.ru/user_help/",
						"available" => true
					),
					"info_api_doc" => array(
						"path" => "https://dev.1c-bitrix.ru/api_help/",
						"available" => true
					),
					"support_bitrix" => array(
						"path" => "https://www.1c-bitrix.ru/support/",
						"available" => true
					),
					"info_courses" => array(
						"path" => "https://dev.1c-bitrix.ru/learning/index.php",
						"available" => true
					),
					"support_developer" => array(
						"path" => htmlspecialcharsbx($partnerUrl),
						"available" => ($partnerUrl ? true : false)
					)
				),
				"ADDITIONAL_ITEMS" => array()
			)
		)
	)
);

//option of checked items
$arStoreAssistOption = CStoreAssist::getSettingOption();

//check Opening item
if (!in_array("opening", $arStoreAssistOption))
{
	if(Bitrix\Main\Config\Option::get("main", "site_stopped", "N")=="N")
	{
		CStoreAssist::setSettingOption("opening", "Y");
		$arStoreAssistOption[] = "opening";
	}
}

//count checked percent and number of items
$assistTotalCount = 0;
$assistDoneCount = 0;
foreach($arAssistSteps as $stepCode => $arStep)
{
	$curStepTotalCount = 0;
	$curStepDoneCount = 0;
	foreach($arStep["BLOCKS"] as $block => $arBlock)
	{
		$curBlockTotalCount = 0;
		$curBlockDoneCount = 0;

		foreach($arBlock["MAIN_ITEMS"] as $itemCode => $arItem)
		{
			if (!$arItem["available"])
				continue;

			$curBlockTotalCount++;
			if (in_array($itemCode, $arStoreAssistOption))
				$curBlockDoneCount++;
		}

		$arAssistSteps[$stepCode]["BLOCKS"][$block]["TOTAL_COUNT"] = $curBlockTotalCount;
		$curStepTotalCount+= $curBlockTotalCount;
		$arAssistSteps[$stepCode]["BLOCKS"][$block]["DONE_COUNT"] = $curBlockDoneCount;
		$arAssistSteps[$stepCode]["BLOCKS"][$block]["PERCENT"] = ($curBlockDoneCount > 0) ? round(($curBlockDoneCount*100)/$curBlockTotalCount) : 0;
		$curStepDoneCount+= $arAssistSteps[$stepCode]["BLOCKS"][$block]["DONE_COUNT"];
	}
	$arAssistSteps[$stepCode]["TOTAL_COUNT"] = $curStepTotalCount;
	$arAssistSteps[$stepCode]["DONE_COUNT"] = $curStepDoneCount;
	$arAssistSteps[$stepCode]["PERCENT"] = ($curStepDoneCount > 0) ? round(($curStepDoneCount*100)/$curStepTotalCount) : 0;

	$assistTotalCount += $curStepTotalCount;
	$assistDoneCount += $curStepDoneCount;
}

$assistPersent = ($assistTotalCount > 0) ? round(($assistDoneCount*90)/$assistTotalCount) : 0; //90% - maximum for tasks

//get order progress percent
$orderPercent = intval(CStoreAssist::getProgressPercent());

if ($orderPercent > 0)
	$assistPersent += $orderPercent;
?>

<!-- GLOBAL PROGRESS -->
<div class="adm-s-thermometer-container">
	<div class="adm-s-thermometer-title"><?=GetMessage("STOREAS_PROGRESS")?></div>
	<div class="adm-s-thermometer-block">
		<div class="adm-s-thermometer-block-status red <?if ($assistPersent <= 18):?>active<?endif?>"><div class="adm-s-thermometer-point-desc"><?=GetMessage("STOREAS_FIRST_SETTINGS")?></div></div>
		<div class="adm-s-thermometer-block-status orange <?if ($assistPersent > 18 && $assistPersent <= 36):?>active<?endif?>"><div class="adm-s-thermometer-point-desc"><?=GetMessage("STOREAS_SECOND_SETTINGS")?></div></div>
		<div class="adm-s-thermometer-block-status yellow <?if ($assistPersent > 36 && $assistPersent <= 54):?>active<?endif?>"><div class="adm-s-thermometer-point-desc"><?=GetMessage("STOREAS_THIRD_SETTINGS")?></div></div>
		<div class="adm-s-thermometer-block-status green <?if ($assistPersent > 54 && $assistPersent <= 72):?>active<?endif?>"><div class="adm-s-thermometer-point-desc"><?=GetMessage("STOREAS_FORTH_SETTINGS")?></div></div>
		<div class="adm-s-thermometer-block-status lightgreen <?if ($assistPersent > 72 && $assistPersent <= 90):?>active<?endif?>"><div class="adm-s-thermometer-point-desc"><?=GetMessage("STOREAS_FIFTH_SETTINGS")?></div></div>
		<div class="adm-s-thermometer-block-status blue <?if ($assistPersent > 90):?>active<?endif?>"><div class="adm-s-thermometer-point-desc"><?=GetMessage("STOREAS_SIXTH_SETTINGS")?></div></div>
		<div class="adm-s-thermometer-track">
			<div class="adm-s-thermometer-track-shadow">
				<div class="adm-s-thermometer-point" style="left: <?=$assistPersent?>%;" data-role="percentRuleSlider">
					<div class="adm-s-thermometer-point-tablet"><?=$assistPersent?>%</div>
				</div>
			</div>
		</div>
		<div class="adm-s-thermometer-block-shadow"></div>
	</div>
</div>

<?
//get option of toggled sections
$step_toggle = CUserOptions::GetOption("storeassist", "step_toggle", array());
if (empty($step_toggle))
{
	if ($arAssistSteps["MAIN"]["PERCENT"] >= 80)
		$step_toggle["MAIN"] = "N";
	else
		$step_toggle["WORK"] = "N";

	$step_toggle["HEALTH"] = "N";
}

$i = 1;
$numSteps = count($arAssistSteps);
foreach($arAssistSteps as $stepCode => $arStep)
{
?>
	<div class="adm-s-setting-container <?=mb_strtolower($stepCode)?> <?=(isset($step_toggle[$stepCode]) && $step_toggle[$stepCode] == "N" ? "close" : "open")?> <?if ($i == $numSteps) echo "last"?>" data-role="step<?=$stepCode?>">
		<div class="adm-s-setting-title-container" onclick="BX.Storeassist.Admin.toggleStep('<?=CUtil::JSEscape($stepCode)?>')">
			<div class="adm-s-setting-action"><span data-role="toggle<?=$stepCode?>"><?=GetMessage("STOREAS_".(isset($step_toggle[$stepCode]) && $step_toggle[$stepCode] == "N" ? "SHOW" : "HIDE"))?></span><span class="arrow"></span></div>
			<div class="adm-s-setting-title-icon"></div>
			<div class="adm-s-setting-title-line"></div>
			<h2  class="adm-s-setting-title"><?=GetMessage("STOREAS_STEPS_".$stepCode)?></h2>
		</div>

		<div class="adm-s-setting-content-container" data-role="container<?=$stepCode?>">
			<!-- BLOCK CONTENT Progress  -->
			<div class="adm-s-setting-progress-block">
				<div class="adm-s-setting-progress-line-h"></div>
				<div class="adm-s-setting-progress-cyrcle-container"><?=$arStep["PERCENT"]?>%</div>
				<div class="adm-s-setting-progress-cyrcle-container-desc"><?=GetMessage("STOREAS_TASKS_READY")?></div>
			</div>
			<!--  -->

			<div class="adm-s-setting-content-container-line"><span></span></div>
			<?foreach($arStep["BLOCKS"] as $block => $arBlock):?>
				<?if (!empty($arBlock["TYPE"]) && $arBlock["TYPE"] == "ONE"):?>
				<div class="adm-s-setting-content-block">
					<div class="posr">
				<?endif?>
				<div class="adm-s-setting-content-block <?if (!empty($arBlock["TYPE"])) echo ($arBlock["TYPE"] == "ONE" ? "one" : "one two");?>" id="<?=mb_strtolower($stepCode."_".$block)?>">
					<!-- BLOCK CONTENT container title -->
					<div class="adm-s-setting-content-block-title-container">
						<div class="adm-s-setting-content-block-line"></div>
						<div class="adm-s-setting-content-block-point"></div>
						<div class="adm-s-setting-content-block-title"><?=GetMessage("STOREAS_STEPS_".$stepCode."_".mb_strtoupper($block))?></div>
						<!-- BLOCK CONTENT status -->
						<div class="adm-s-setting-content-block-status-container">
							<div class="adm-s-setting-content-block-status red"></div>
							<div class="adm-s-setting-content-block-status orange"></div>
							<div class="adm-s-setting-content-block-status yellow"></div>
							<div class="adm-s-setting-content-block-status green"></div>
							<div class="adm-s-setting-content-block-status lightgreen"></div>
							<div class="adm-s-setting-content-block-status-track">
								<div class="adm-s-setting-content-block-status-track-point" style="left: <?=$arBlock["PERCENT"]?>%;"><?=$arBlock["DONE_COUNT"]?>/<?=$arBlock["TOTAL_COUNT"]?></div>
							</div>
						</div>
						<!--  -->
					</div>
					<!--  -->
					<!-- BLOCK CONTENT container body -->
					<div class="adm-s-setting-content-block-body-container">
						<!--<p><?=GetMessage("STOREAS_STEPS_".$stepCode."_".$block."_DESCR")?></p>-->
						<ul class="adm-s-setting-tasklist">
							<?foreach($arBlock["MAIN_ITEMS"] as $itemCode => $arItem):
								if (!$arItem["available"])
									continue;
							?>
								<li class="adm-s-setting-task <?if (in_array($itemCode, $arStoreAssistOption)):?>complited<?endif?>">
									<?
									switch ($itemCode)
									{
										case "support_developer":
											$partnerName = Bitrix\Main\Config\Option::get("storeassist", "partner_name", "");
											$message = htmlspecialcharsbx(GetMessage("STOREAS_ITEMS_".$itemCode, array("#NAME#" => ($partnerName ? "\"".$partnerName."\"" : ""))));
											?>
											<span class="adm-s-setting-task-item">
												<a href="<?=$arItem["path"]?>" title="<?=$message?>" onclick="BX.Storeassist.Admin.setOption('<?=CUtil::JSEscape($itemCode)?>', 'Y')" target="_blank">
													<span><?=$message?></span>
												</a>
											</span>
											<?
											break;
										case "support_bitrix":
										case "info_vk":
										case "info_blog":
										case "info_forum_guest":
										case "info_forum_client":
										case "info_idea":
										case "info_user_doc":
										case "info_api_doc":
										case "info_courses":
											?>
											<span class="adm-s-setting-task-item">
												<a href="<?=$arItem["path"]?>" title="<?=GetMessage("STOREAS_ITEMS_".$itemCode)?>" onclick="BX.Storeassist.Admin.setOption('<?=CUtil::JSEscape($itemCode)?>', 'Y')" target="_blank">
													<span><?=GetMessage("STOREAS_ITEMS_".$itemCode)?></span>
												</a>
											</span>
											<?
											break;
										default:
											if (isset($arItem["subItems"]))
											{
												?>
												<div title="<?=GetMessage("STOREAS_ITEMS_".$itemCode)?>">
													<span class="adm-s-setting-task-item"><?=GetMessage("STOREAS_ITEMS_".$itemCode)?>

												<?
												foreach ($arItem["subItems"] as $subCode => $subPath)
												{
													?>
														<span class="adm-s-setting-task-sub-item">
															<a href="<?=$subPath?>" title="<?=GetMessage("STOREAS_ITEMS_".$subCode)?>">- <?=GetMessage("STOREAS_ITEMS_".$subCode)?></a>
														</span>
													<?
												}
												?>
												</span></div>
												<?
											}
											else
											{
												?>
												<span class="adm-s-setting-task-item"><a href="<?=$arItem["path"]?>" title="<?=GetMessage("STOREAS_ITEMS_".$itemCode)?>"><span><?=GetMessage("STOREAS_ITEMS_".$itemCode)?></span></a></span>
												<?
											}
									}
									?>
								</li>
							<?endforeach?>

							<?if (!empty($arBlock["ADDITIONAL_ITEMS"])):?>
								<li class="adm-s-setting-task add "><span class="adm-s-setting-task-item"><?=GetMessage("STOREAS_ADDITIONAL_TASKS")?></span></li>
							<?endif?>
						</ul>
						<div class="clb"></div>
					</div>
					<!--  -->
				</div>
				<?if (!empty($arBlock["TYPE"]) && $arBlock["TYPE"] == "TWO"):?>

						<div class="clb"></div>

						<div class="adm-s-setting-content-block-body-line-two-h-t"></div>
						<div class="adm-s-setting-content-block-body-line-two-h-b"></div>
					</div>
				</div>
				<?endif?>
			<?endforeach?>

		</div>
	</div>
<?
	$i++;
}
?>
<script>
	BX.ready(function(){
		var percentRuleSlider = document.querySelector('[data-role="percentRuleSlider"]');
		BX.Storeassist.Admin.percentMoveInit(percentRuleSlider, '<?=CUtil::JSEscape($assistPersent)?>');
	});
</script>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>