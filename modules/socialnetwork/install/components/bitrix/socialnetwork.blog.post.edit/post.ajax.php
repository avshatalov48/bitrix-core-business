<?
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

if (!CModule::IncludeModule("socialnetwork"))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'MODULE_NOT_INSTALLED'));
	die();
}
if (check_bitrix_sessid())
{
	if (CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser())
		echo CUtil::PhpToJsObject(Array('ERROR' => 'EXTRANET_USER'));
	else
	{
		if (isset($_POST["nt"]))
		{
			preg_match_all("/(#NAME#)|(#LAST_NAME#)|(#SECOND_NAME#)|(#NAME_SHORT#)|(#SECOND_NAME_SHORT#)|\s|\,/", urldecode($_REQUEST["nt"]), $matches);
			$nameTemplate = implode("", $matches[0]);
		}
		else
			$nameTemplate = CSite::GetNameFormat(false);

		if ($_POST['LD_SEARCH'] == 'Y')
		{
			CUtil::decodeURIComponent($_POST);

			echo CUtil::PhpToJsObject(Array(
				'USERS' => CSocNetLogDestination::SearchUsers($_POST['SEARCH'], $nameTemplate, false, IsModuleInstalled("extranet")), 
			));
		}
		elseif (
			$_POST['LD_DEPARTMENT_RELATION'] == 'Y'
			&& IsModuleInstalled("intranet")
		)
			echo CUtil::PhpToJsObject(Array(
				'USERS' => CSocNetLogDestination::GetUsers(Array(
					'deportament_id' => $_POST['DEPARTMENT_ID'],
					"NAME_TEMPLATE" => $nameTemplate
				),
				false),
			));
		elseif(isset($_POST["bitrix_processes"]))
		{
			if(CModule::IncludeModule('lists'))
			{
				IncludeModuleLangFile(__FILE__);
				global $USER;
				$listsPerm = CListPermissions::CheckAccess($USER, COption::GetOptionString("lists", "livefeed_iblock_type_id"), false);
				if($listsPerm < 0)
				{
					switch($listsPerm)
					{
						case CListPermissions::WRONG_IBLOCK_TYPE:
							echo CUtil::PhpToJsObject(Array('success' => false,'error' => GetMessage("CC_BLL_WRONG_IBLOCK_TYPE")));
							die();
						case CListPermissions::WRONG_IBLOCK:
							echo CUtil::PhpToJsObject(Array('success' => false,'error' => GetMessage("CC_BLL_WRONG_IBLOCK")));
							die();
						case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
							echo CUtil::PhpToJsObject(Array('success' => false,'error' => GetMessage("CC_BLL_LISTS_FOR_SONET_GROUP_DISABLED")));
							die();
						default:
							echo CUtil::PhpToJsObject(Array('success' => false,'error' => GetMessage("CC_BLL_UNKNOWN_ERROR")));
							die();
					}
				}
				elseif($listsPerm <= CListPermissions::ACCESS_DENIED)
				{
					echo CUtil::PhpToJsObject(Array('success' => false,'error' => GetMessage("CC_BLL_ACCESS_DENIED")));
					die();
				}

				$permissions = array();
				$admin = false;
				if($listsPerm >= CListPermissions::IS_ADMIN)
				{
					$permissions['new'] = GetMessage("CC_BLL_TITLE_NEW_LIST");
					$permissions['market'] = GetMessage("CC_BLL_TITLE_MARKETPLACE_NEW");
					$permissions['settings'] = GetMessage("CC_BLL_TITLE_SETTINGS");
					$admin = true;

				}
				elseif($listsPerm >= CListPermissions::CAN_READ)
				{
					$permissions['market'] = GetMessage("CC_BLL_TITLE_MARKETPLACE_NEW");
					$permissions['settings'] = GetMessage("CC_BLL_TITLE_SETTINGS");
				}

				$listData = array();
				$siteId = true;
				if($_POST['siteId'])
					$siteId = $_POST['siteId'];
				$lists = CIBlock::getList(
					array("SORT" => "ASC","NAME" => "ASC"),
					array("ACTIVE" => "Y","TYPE" => COption::GetOptionString("lists", "livefeed_iblock_type_id"), 'SITE_ID' => $siteId)
				);
				while($list = $lists->fetch())
				{
					if(CLists::getLiveFeed($list['ID']))
					{
						$listData[$list['ID']]['ID'] = $list['ID'];

						$shortName = substr($list['NAME'], 0, 50);
						if($shortName == $list['NAME'])
							$listData[$list['ID']]['NAME'] = $list['NAME'];
						else
							$listData[$list['ID']]['NAME'] = $shortName.'...';

						$listData[$list['ID']]['DESCRIPTION'] = $list['DESCRIPTION'];
						$listData[$list['ID']]['CODE'] = $list['CODE'];
						if($list['PICTURE'] > 0)
						{
							$imageFile = CFile::GetFileArray($list['PICTURE']);
							if($imageFile !== false)
							{
								$imageFile = CFile::ResizeImageGet(
									$imageFile,
									array("width" => 36, "height" => 30),
									BX_RESIZE_IMAGE_PROPORTIONAL,
									false
								);
								$listData[$list['ID']]['PICTURE'] = '<img src="'.$imageFile["src"].'" width="36" height="30" border="0" />';
								$listData[$list['ID']]['PICTURE_SMALL'] = '<img src="'.$imageFile["src"].'" width="19" height="16" border="0" />';
							}
						}
						else
						{
							$listData[$list['ID']]['PICTURE'] = '<img src="/bitrix/images/lists/default.png" width="36" height="30" border="0" />';
							$listData[$list['ID']]['PICTURE_SMALL'] = '<img src="/bitrix/images/lists/default.png" width="19" height="16" border="0" />';
						}
					}
				}
				$listData= array_values($listData);
				echo CUtil::PhpToJsObject(
					array(
						'success' => true,
						'lists' => $listData,
						'permissions' => $permissions,
						'admin' => $admin
					)
				);
			}
			else
			{
				echo CUtil::PhpToJsObject(Array('success' => false,'error' => 'Lists module not installed!'));
			}
		}
		else
			echo CUtil::PhpToJsObject(Array(
				'ERROR' => 'UNKNOWN_ERROR'
			));
	}
}
else
	echo CUtil::PhpToJsObject(Array(
		'ERROR' => 'SESSION_ERROR'
	));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>