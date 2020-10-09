<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

use Bitrix\Main\Localization\CultureTable;

IncludeModuleLangFile(__FILE__);

class CWizardSolPanel
{
	public static function ShowPanel()
	{
		global $USER, $APPLICATION;

		if(defined("ADMIN_SECTION") && ADMIN_SECTION == true)
			return;

		if($USER->IsAdmin())
		{
			if($_REQUEST['add_new_site_sol']=='sol' && check_bitrix_sessid())
			{
				$dbrSites = CSite::GetList($by, $ord);
				$arSitesID = Array();
				$arSitesPath = Array();
				$siteCnt = 0;
				while($arSite = $dbrSites->Fetch())
				{
					if($arSite["ACTIVE"]=="Y")
						$siteCnt++;

					$arSitesID[] = mb_strtolower($arSite["ID"]);
					$arSitesPath[] = mb_strtolower($arSite["PATH"]);
				}

				$newSiteID = "";
				while(true)
				{
					$newSiteID = chr(rand(ord("a"), ord("z"))).chr(rand(ord("a"), ord("z")));
					if(!in_array($newSiteID, $arSitesID) && !in_array("/site".$newSiteID."/", $arSitesPath) && !file_exists($_SERVER['DOCUMENT_ROOT']."/site".$newSiteID))
						break;
				}
				
				$culture = CultureTable::getRow(array('filter'=>array(
					"=FORMAT_DATE" => FORMAT_DATE,
					"=FORMAT_DATETIME" => FORMAT_DATETIME,
					"=FORMAT_NAME" => CSite::GetDefaultNameFormat(),
					"=CHARSET" => SITE_CHARSET,
				)));

				if($culture)
				{
					$cultureId = $culture["ID"];
				}
				else
				{
					$addResult = CultureTable::add(array(
						"NAME" => $newSiteID,
						"CODE" => $newSiteID,
						"FORMAT_DATE" => FORMAT_DATE,
						"FORMAT_DATETIME" => FORMAT_DATETIME,
						"FORMAT_NAME" => CSite::GetDefaultNameFormat(),
						"CHARSET" => SITE_CHARSET,
					));
					$cultureId = $addResult->getId();
				}

				$arFields = array(
					"LID" => $newSiteID,
					"ACTIVE" => "Y",
					"SORT" => 100,
					"DEF" => "N",
					"NAME" => $newSiteID,
					"DIR" => "/site_".$newSiteID."/",
					"SITE_NAME" => $newSiteID,
					"SERVER_NAME" => $_SERVER["SERVER_NAME"],
					"EMAIL" => COption::GetOptionString("main", "email_from"),
					"LANGUAGE_ID" => LANGUAGE_ID,
					"DOC_ROOT" => "",
					"CULTURE_ID" => $cultureId,
				);

				$obSite = new CSite;
				$result = $obSite->Add($arFields);
				if ($result)
				{
					CheckDirPath($_SERVER["DOCUMENT_ROOT"]."/site_".$newSiteID."/");
					$indexContent = '<'.'?'.
						'define("B_PROLOG_INCLUDED", true);'.
						'define("WIZARD_DEFAULT_SITE_ID", "'.$newSiteID.'");'.
						'define("WIZARD_DEFAULT_TONLY", true);'.
						'define("PRE_LANGUAGE_ID","'.LANGUAGE_ID.'");'.
						'define("PRE_INSTALL_CHARSET","'.SITE_CHARSET.'");'.
						'include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard/wizard.php");'.
						'?'.'>';

					$handler = fopen($_SERVER["DOCUMENT_ROOT"]."/site_".$newSiteID."/index.php","wb");
					fwrite($handler, $indexContent);
					fclose($handler);
					
					LocalRedirect("/site_".$newSiteID."/");
				}
				else 
				{
					echo $obSite->LAST_ERROR; 
				}
			}

			if($_REQUEST['delete_button_sol']=='sol' && check_bitrix_sessid())
			{
				UnRegisterModuleDependences('main', 'OnBeforeProlog', 'main', 'CWizardSolPanel', 'ShowPanel', '/modules/main/install/wizard_sol/panel_button.php');
				LocalRedirect($APPLICATION->GetCurPage());
			}

			$arMenu = Array(
				Array(		
					"ACTION" => "jsUtils.Redirect([], '".CUtil::JSEscape(SITE_DIR)."?add_new_site_sol=sol&".bitrix_sessid_get()."')",
					"HTML" => "<b>".GetMessage("SOL_BUTTON_TEST_TEXT", Array("#BR#" => " "))."</b>",
					"TITLE" => GetMessage("SOL_BUTTON_TEST_TITLE"),
				),
			);

			$arMenu[] = Array(		
				"ACTION" => "if(confirm('".AddSlashes(GetMessage("SOL_BUTTON_CONFIRM_W2"))."')) jsUtils.Redirect([], '".CUtil::JSEscape(SITE_DIR)."?delete_button_sol=sol&".bitrix_sessid_get()."');",
				"TEXT" => GetMessage("SOL_BUTTON_DEL_TEXT"),
				"TITLE" => GetMessage("SOL_BUTTON_DEL_TITLE"),
			);

			$protocol = (CMain::IsHTTPS()? 'https://' : 'http://');
			$arSites = array();	
			$dbrSites = CSite::GetList($by, $ord, Array("ACTIVE"=>"Y"));
			while($arSite = $dbrSites->GetNext())
			{
				$url = $arSite["DIR"];
				if($arSite["DOMAIN_LIMITED"] == "Y" && $arSite["LID"] <> SITE_ID)
				{
					$site = trim(preg_replace("#^(http://|https://)#i", "", $arSite["SERVER_NAME"]), " \t\n\r\0\x0B/");
					if($site <> '')
						$url = $protocol.$site.'/'.ltrim($url, '/');
				}
				$arSites[] = Array(		
					"ACTION" => "jsUtils.Redirect([], '".CUtil::JSEscape($url)."');",
					"ICON" => ($arSite["LID"] == SITE_ID? "checked":""),
					"TEXT" => $arSite["NAME"],
					"TITLE" => GetMessage("SOL_BUTTON_GOTOSITE")." ".$arSite["NAME"],
				);
			}
			$arMenu[] = Array("SEPARATOR"=>true);
			$arMenu[] = Array(		
				"TEXT" => GetMessage("SOL_BUTTON_GOTOSITE"),
				"MENU" => $arSites,
			);

			$APPLICATION->AddPanelButton(array(
				"HREF" => SITE_DIR."?add_new_site_sol=sol&".bitrix_sessid_get(),
				"ID" => "solutions_wizard",
				"ICON" => "bx-panel-install-solution-icon",
				"TYPE" => "BIG",
				"ALT" => GetMessage("SOL_BUTTON_TEST_TITLE"),
				"TEXT" => GetMessage("SOL_BUTTON_TEST_TEXT"),
				"MAIN_SORT" => 2520,
				"SORT" => 20,
				"MENU" => $arMenu,
				'HINT' => array(
					'TITLE' => str_replace('#BR#', ' ', GetMessage("SOL_BUTTON_TEST_TEXT")),
					'TEXT' => GetMessage('SOL_BUTTON_TEST_TEXT_HINT')
				),
				'HINT_MENU' => array(
					'TITLE' => str_replace('#BR#', ' ', GetMessage("SOL_BUTTON_TEST_TEXT")),
					'TEXT' => GetMessage('SOL_BUTTON_TEST_MENU_HINT')
				)
			));
		}

	}
}
