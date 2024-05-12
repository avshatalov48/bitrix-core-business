<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Disk\Uf\FileUserType;
use Bitrix\Disk\AttachedObject;
use Bitrix\Disk\File;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class CLists
{
	private static $iblockTypeList = array(
		"lists" => true,
		"bitrix_processes" => true,
		"lists_socnet" => true
	);

	private static $featuresCache = array();

	public static function SetPermission($iblock_type_id, $arGroups)
	{
		global $DB, $CACHE_MANAGER;

		$grp = array();
		foreach($arGroups as $group_id)
		{
			$group_id = intval($group_id);
			if($group_id)
				$grp[$group_id] = $group_id;
		}

		$DB->Query("
			delete from b_lists_permission
			where IBLOCK_TYPE_ID = '".$DB->ForSQL($iblock_type_id)."'
		", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if(count($grp))
		{
			$DB->Query("
				insert into b_lists_permission
				select ibt.ID, ug.ID
				from
					b_iblock_type ibt
					,b_group ug
				where
					ibt.ID =  '".$DB->ForSQL($iblock_type_id)."'
					and ug.ID in (".implode(", ", $grp).")
			", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		if(CACHED_b_lists_permission !== false)
			$CACHE_MANAGER->Clean("b_lists_permission");
	}

	public static function GetPermission($iblock_type_id = false)
	{
		global $DB, $CACHE_MANAGER;

		$arResult = false;
		if(CACHED_b_lists_permission !== false)
		{
			if($CACHE_MANAGER->Read(CACHED_b_lists_permission, "b_lists_permission"))
				$arResult = $CACHE_MANAGER->Get("b_lists_permission");
		}

		if($arResult === false)
		{
			$arResult = array();
			$res = $DB->Query("select IBLOCK_TYPE_ID, GROUP_ID from b_lists_permission", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			while($ar = $res->Fetch())
				$arResult[$ar["IBLOCK_TYPE_ID"]][] = $ar["GROUP_ID"];

			if(CACHED_b_lists_permission !== false)
				$CACHE_MANAGER->Set("b_lists_permission", $arResult);
		}

		if ($iblock_type_id === false)
		{
			return $arResult;
		}
		else
		{
			return $arResult[$iblock_type_id] ?? false;
		}
	}

	public static function GetDefaultSocnetPermission()
	{
		return array(
			"A" => "X", //Group owner
			"E" => "W", //Group moderator
			"K" => "W", //Group member
			"L" => "D", //Authorized users
			"N" => "D", //Everyone
			"T" => "D", //Banned
			"Z" => "D", //Request?
		);
	}

	public static function SetSocnetPermission($iblock_id, $arRoles)
	{
		global $DB, $CACHE_MANAGER;
		$iblock_id = intval($iblock_id);

		$arToDB = CLists::GetDefaultSocnetPermission();
		foreach($arToDB as $role => $permission)
			if(isset($arRoles[$role]))
				$arToDB[$role] = mb_substr($arRoles[$role], 0, 1);
		$arToDB["A"] = "X"; //Group owner always in charge
		$arToDB["T"] = "D"; //Banned
		$arToDB["Z"] = "D"; //and Request never get to list

		$DB->Query("
			delete from b_lists_socnet_group
			where IBLOCK_ID = ".$iblock_id."
		", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		foreach($arToDB as $role => $permission)
		{
			$DB->Query("
				insert into b_lists_socnet_group
				(IBLOCK_ID, SOCNET_ROLE, PERMISSION)
				values
				(".$iblock_id.", '".$role."', '".$permission."')
			", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		if(CACHED_b_lists_permission !== false)
			$CACHE_MANAGER->Clean("b_lists_perm".$iblock_id);
	}

	public static function GetSocnetPermission($iblock_id)
	{
		global $DB, $CACHE_MANAGER;
		$iblock_id = (int)$iblock_id;

		$arCache = array();
		if(!array_key_exists($iblock_id, $arCache))
		{
			$arCache[$iblock_id] = CLists::GetDefaultSocnetPermission();

			if(CACHED_b_lists_permission !== false)
			{
				$cache_id = "b_lists_perm".$iblock_id;

				if($CACHE_MANAGER->Read(CACHED_b_lists_permission, $cache_id))
				{
					$arCache[$iblock_id] = $CACHE_MANAGER->Get($cache_id);
				}
				else
				{
					$res = $DB->Query("
						select SOCNET_ROLE, PERMISSION
						from b_lists_socnet_group
						where IBLOCK_ID=".$iblock_id."
					", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					while($ar = $res->Fetch())
						$arCache[$iblock_id][$ar["SOCNET_ROLE"]] = $ar["PERMISSION"];

					$CACHE_MANAGER->Set($cache_id, $arCache[$iblock_id]);
				}
			}
			else
			{
				$res = $DB->Query("
					select SOCNET_ROLE, PERMISSION
					from b_lists_socnet_group
					where IBLOCK_ID=".$iblock_id."
				", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
				while($ar = $res->Fetch())
					$arCache[$iblock_id][$ar["SOCNET_ROLE"]] = $ar["PERMISSION"];
			}

			$arCache[$iblock_id]["A"] = "X"; //Group owner always in charge
			$arCache[$iblock_id]["T"] = "D"; //Banned
			$arCache[$iblock_id]["Z"] = "D"; //and Request never get to list
		}

		return $arCache[$iblock_id];
	}

	public static function GetIBlockPermission($iblock_id, $user_id)
	{
		global $USER;

		//IBlock permissions by default
		$Permission = CIBlock::GetPermission($iblock_id, $user_id);
		if($Permission < "W")
		{
			$arIBlock = CIBlock::GetArrayByID($iblock_id);
			if($arIBlock)
			{
				//Check if iblock is list
				$arListsPerm = CLists::GetPermission($arIBlock["IBLOCK_TYPE_ID"]);
				if (is_array($arListsPerm) && count($arListsPerm) > 0)
				{
					//User groups
					if($user_id == $USER->GetID())
						$arUserGroups = $USER->GetUserGroupArray();
					else
						$arUserGroups = $USER->GetUserGroup($user_id);

					//One of lists admins
					if(count(array_intersect($arListsPerm, $arUserGroups)))
						$Permission = "X";
				}
			}
		}

		if(
			$Permission < "W"
			&& $arIBlock["SOCNET_GROUP_ID"]
			&& CModule::IncludeModule('socialnetwork')
		)
		{
			$arSocnetPerm = CLists::GetSocnetPermission($iblock_id);
			$socnet_role = CSocNetUserToGroup::GetUserRole($USER->GetID(), $arIBlock["SOCNET_GROUP_ID"]);
			$Permission = $arSocnetPerm[$socnet_role];
		}
		return $Permission;
	}

	public static function GetIBlockTypes($language_id = false)
	{
		global $DB;
		$res = $DB->Query("
			SELECT IBLOCK_TYPE_ID, NAME
			FROM b_iblock_type_lang
			WHERE
				LID = '".$DB->ForSQL($language_id===false? LANGUAGE_ID: $language_id)."'
				AND EXISTS (
					SELECT *
					FROM b_lists_permission
					WHERE b_lists_permission.IBLOCK_TYPE_ID = b_iblock_type_lang.IBLOCK_TYPE_ID
				)
		", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		return $res;
	}

	public static function GetIBlocks($iblock_type_id, $check_permissions, $socnet_group_id = false)
	{
		$arOrder = array(
			"SORT" => "ASC",
			"NAME" => "ASC",
		);

		$arFilter = array(
			"ACTIVE" => "Y",
			"SITE_ID" => SITE_ID,
			"TYPE" => $iblock_type_id,
			"CHECK_PERMISSIONS" => ($check_permissions? "Y": "N"), //This cancels iblock permissions for trusted users
		);
		if($socnet_group_id > 0)
			$arFilter["=SOCNET_GROUP_ID"] = $socnet_group_id;

		$arResult = array();
		$rsIBlocks = CIBlock::GetList($arOrder, $arFilter);
		while($ar = $rsIBlocks->Fetch())
		{
			$arResult[$ar["ID"]] = $ar["NAME"];
		}
		return $arResult;
	}

	public static function OnIBlockDelete($iblock_id)
	{
		global $DB, $CACHE_MANAGER;
		$iblock_id = intval($iblock_id);

		$DB->Query("delete from b_lists_url where IBLOCK_ID=".$iblock_id, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$DB->Query("delete from b_lists_socnet_group where IBLOCK_ID=".$iblock_id, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$CACHE_MANAGER->Clean("b_lists_perm".$iblock_id);

		CListFieldList::DeleteFields($iblock_id);

		self::deleteLockFeatureOption($iblock_id);
	}

	public static function OnAfterIBlockUpdate(array &$fields)
	{
		if (!empty($fields["RESULT"]))
		{
			self::deleteListsCache('/lists/crm/attached/');
		}
	}

	public static function OnAfterIBlockDelete($iblock_id)
	{
		if (CModule::includeModule('bizproc'))
		{
			BizProcDocument::deleteDataIblock($iblock_id);
		}
	}

	public static function IsEnabledSocnet()
	{
		$bActive = false;
		foreach (GetModuleEvents("socialnetwork", "OnFillSocNetFeaturesList", true) as $arEvent)
		{
			if(
				$arEvent["TO_MODULE_ID"] == "lists"
				&& $arEvent["TO_CLASS"] == "CListsSocnet"
			)
			{
				$bActive = true;
				break;
			}
		}
		return $bActive;
	}

	public static function EnableSocnet($bActive = false)
	{
		if($bActive)
		{
			if(!CLists::IsEnabledSocnet())
			{
				RegisterModuleDependences("socialnetwork", "OnFillSocNetFeaturesList", "lists", "CListsSocnet", "OnFillSocNetFeaturesList");
				RegisterModuleDependences("socialnetwork", "OnFillSocNetMenu", "lists", "CListsSocnet", "OnFillSocNetMenu");
				RegisterModuleDependences("socialnetwork", "OnParseSocNetComponentPath", "lists", "CListsSocnet", "OnParseSocNetComponentPath");
				RegisterModuleDependences("socialnetwork", "OnInitSocNetComponentVariables", "lists", "CListsSocnet", "OnInitSocNetComponentVariables");
			}
		}
		else
		{
			if(CLists::IsEnabledSocnet())
			{
				UnRegisterModuleDependences("socialnetwork", "OnFillSocNetFeaturesList", "lists", "CListsSocnet", "OnFillSocNetFeaturesList");
				UnRegisterModuleDependences("socialnetwork", "OnFillSocNetMenu", "lists", "CListsSocnet", "OnFillSocNetMenu");
				UnRegisterModuleDependences("socialnetwork", "OnParseSocNetComponentPath", "lists", "CListsSocnet", "OnParseSocNetComponentPath");
				UnRegisterModuleDependences("socialnetwork", "OnInitSocNetComponentVariables", "lists", "CListsSocnet", "OnInitSocNetComponentVariables");
			}
		}
	}

	public static function OnSharepointCreateProperty($arInputFields)
	{
		global $DB;
		$iblock_id = (int)$arInputFields["IBLOCK_ID"];
		if($iblock_id > 0)
		{
			//Check if there is at list one field defined for given iblock
			$rsFields = $DB->Query("
				SELECT * FROM b_lists_field
				WHERE IBLOCK_ID = ".$iblock_id."
				ORDER BY SORT ASC
			", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if($rsFields->Fetch())
			{
				$arNewFields = array(
					"SORT" => 500,
					"NAME" => $arInputFields["SP_FIELD"],
				);

				if(mb_substr($arInputFields["FIELD_ID"], 0, 9) == "PROPERTY_")
				{
					$arNewFields["ID"] = mb_substr($arInputFields["FIELD_ID"], 9);
					$arNewFields["TYPE"] = "S";
				}
				else
					$arNewFields["TYPE"] = $arInputFields["FIELD_ID"];

				//Publish property on the list
				$obList = new CList($iblock_id);
				$obList->AddField($arNewFields);
			}
		}
	}

	public static function OnSharepointCheckAccess($iblock_id)
	{
		global $USER;
		$arIBlock = CIBlock::GetArrayByID($iblock_id);
		if($arIBlock)
		{
			//Check if iblock is list
			$arListsPerm = CLists::GetPermission($arIBlock["IBLOCK_TYPE_ID"]);
			if(
				is_array($arListsPerm)
				&& count($arListsPerm)
			)
			{
				//User groups
				$arUserGroups = $USER->GetUserGroupArray();
				//One of lists admins
				if(count(array_intersect($arListsPerm, $arUserGroups)))
					return true;
				else
					return false;
			}
		}
	}

	public static function setLiveFeed($checked, $iblockId)
	{
		global $DB;
		$iblockId = intval($iblockId);
		$checked = intval($checked);

		$resultQuery = $DB->Query("SELECT LIVE_FEED FROM b_lists_url WHERE IBLOCK_ID = ".$iblockId);
		$resultData = $resultQuery->fetch();

		if($resultData)
		{
			if($resultData["LIVE_FEED"] != $checked)
			{
				$DB->Query("UPDATE b_lists_url SET LIVE_FEED = '".$checked."' WHERE IBLOCK_ID = ".$iblockId);
			}
		}
		else
		{
			$url = '/'.$iblockId.'/element/#section_id#/#element_id#/';
			$DB->Query("INSERT INTO b_lists_url (IBLOCK_ID, URL, LIVE_FEED) values (".$iblockId.", '".$DB->ForSQL($url)."', ".$checked.")");
		}
	}

	public static function getLiveFeed($iblockId)
	{
		global $DB;
		$iblockId = intval($iblockId);

		$resultQuery = $DB->Query("SELECT LIVE_FEED FROM b_lists_url WHERE IBLOCK_ID = ".$iblockId);
		$resultData = $resultQuery->fetch();

		if ($resultData)
			return $resultData["LIVE_FEED"];
		else
			return "";
	}

	public static function getCountProcessesUser($userId, $iblockTypeId)
	{
		$userId = intval($userId);
		return CIBlockElement::getList(
			array(),
			array('CREATED_BY' => $userId, 'IBLOCK_TYPE' => $iblockTypeId),
			true,
			false,
			array('ID')
		);
	}

	public static function generateMnemonicCode($integerCode = 0)
	{
		if(!$integerCode)
			$integerCode = time();

		$code = '';
		for ($i = 1; $integerCode >= 0 && $i < 10; $i++)
		{
			$code = chr(0x41 + ($integerCode % pow(26, $i) / pow(26, $i - 1))) . $code;
			$integerCode -= pow(26, $i);
		}
		return $code;
	}

	public static function OnAfterIBlockElementDelete($fields)
	{
		if (CModule::includeModule('bizproc'))
		{
			$errors = array();

			$iblockType = COption::getOptionString("lists", "livefeed_iblock_type_id");

			$iblockQuery = CIBlock::getList(array(), array('ID' => $fields['IBLOCK_ID']));
			if($iblock = $iblockQuery->fetch())
			{
				$iblockType = $iblock["IBLOCK_TYPE_ID"];
			}

			$states = CBPStateService::getDocumentStates(BizprocDocument::getDocumentComplexId($iblockType, $fields['ID']));
			$listWorkflowId = array();
			foreach ($states as $workflowId => $state)
			{
				$listWorkflowId[] = $workflowId;
			}

			self::deleteSocnetLog($listWorkflowId);

			CBPDocument::onDocumentDelete(BizprocDocument::getDocumentComplexId($iblockType, $fields['ID']), $errors);
		}

		$propertyQuery = CIBlockElement::getProperty(
			$fields['IBLOCK_ID'], $fields['ID'], 'sort', 'asc', array('ACTIVE'=>'Y'));
		while($property = $propertyQuery->fetch())
		{
			$userType = \CIBlockProperty::getUserType($property['USER_TYPE']);
			if (array_key_exists('DeleteAllAttachedFiles', $userType))
			{
				call_user_func_array($userType['DeleteAllAttachedFiles'], array($fields['ID']));
			}
		}
	}

	/**
	 * @param string $workflowId
	 * @param string $iblockType
	 * @param int $elementId
	 * @param int $iblockId
	 * @param string $action Action stop or delete
	 * @return string error
	 */
	public static function completeWorkflow($workflowId, $iblockType, $elementId, $iblockId, $action)
	{
		if (!Loader::includeModule('bizproc'))
		{
			return Loc::getMessage('LISTS_MODULE_BIZPROC_NOT_INSTALLED_MSGVER_1');
		}

		global $USER;
		$userId = $USER->getID();

		$documentType = BizprocDocument::generateDocumentComplexType($iblockType, $iblockId);
		$documentId = BizprocDocument::getDocumentComplexId($iblockType, $elementId);
		$documentStates = CBPDocument::getDocumentStates($documentType, $documentId);

		$permission = CBPDocument::canUserOperateDocument(
			($action == 'stop') ? CBPCanUserOperateOperation::StartWorkflow :
				CBPCanUserOperateOperation::CreateWorkflow,
			$userId,
			$documentId,
			array("DocumentStates" => $documentStates)
		);

		if(!$permission)
		{
			return Loc::getMessage('LISTS_ACCESS_DENIED');
		}

		$stringError = '';

		if($action == 'stop')
		{
			$errors = array();
			CBPDocument::terminateWorkflow(
				$workflowId,
				$documentId,
				$errors
			);

			if (!empty($errors))
			{
				$stringError = '';
				foreach ($errors as $error)
					$stringError .= $error['message'];
				$listError[] = array('id' => 'stopBizproc', 'text' => $stringError);
			}
		}
		else
		{
			$errors = CBPDocument::killWorkflow($workflowId);
			foreach ($errors as $error)
			{
				$stringError .= $error['message'];
			}

			if ($errors)
			{
				$listError[] = [
					'id' => 'stopBizproc',
					'text' => $stringError
				];
			}
		}

		if(empty($listError) && Loader::includeModule('socialnetwork') &&
			$iblockType == COption::getOptionString("lists", "livefeed_iblock_type_id"))
		{
			$sourceId = CBPStateService::getWorkflowIntegerId($workflowId);
			$resultQuery = CSocNetLog::getList(
				array(),
				array('EVENT_ID' => 'lists_new_element', 'SOURCE_ID' => $sourceId),
				false,
				false,
				array('ID')
			);
			while ($log = $resultQuery->fetch())
			{
				CSocNetLog::delete($log['ID']);
			}
		}

		if (!empty($listError))
		{
			$errorObject = new CAdminException($listError);
			$stringError = $errorObject->getString();
		}

		return $stringError;
	}

	public static function deleteSocnetLog(array $listWorkflowId)
	{
		if(CModule::includeModule('socialnetwork'))
		{
			foreach ($listWorkflowId as $workflowId)
			{
				$sourceId = CBPStateService::getWorkflowIntegerId($workflowId);
				$resultQuery = CSocNetLog::getList(
					array(),
					array('EVENT_ID' => 'lists_new_element', 'SOURCE_ID' => $sourceId),
					false,
					false,
					array('ID')
				);
				while ($log = $resultQuery->fetch())
				{
					CSocNetLog::delete($log['ID']);
				}
			}
		}
	}

	/**
	 * @param $iblockId
	 * @param array $errors - an array of errors that occurred array(0 => 'error message')
	 * @return bool or int
	 * @deprecated
	 */
	public static function copyIblock($iblockId, array &$errors)
	{
		$iblockId = (int)$iblockId;
		if(!$iblockId)
		{
			$errors[] = Loc::getMessage('LISTS_REQUIRED_PARAMETER', array('#parameter#' => 'iblockId'));
			return false;
		}

		/* We obtain data on old iblock and add a new iblock */
		$query = CIBlock::getList(array(), array('ID' => $iblockId), true);
		$iblock = $query->fetch();
		if(!$iblock)
		{
			$errors[] = Loc::getMessage('LISTS_COPY_IBLOCK_ERROR_GET_DATA');
			return false;
		}
		$iblockMessage = CIBlock::getMessages($iblockId);
		$iblock = array_merge($iblock, $iblockMessage);

		$iblock['NAME'] = $iblock['NAME'].Loc::getMessage('LISTS_COPY_IBLOCK_NAME_TITLE');
		if(!empty($iblock['PICTURE']))
		{
			$iblock['PICTURE'] = CFile::makeFileArray($iblock['PICTURE']);
		}
		if(!empty($iblock['CODE']))
		{
			$iblock['CODE'] = $iblock['CODE'].'_copy';
		}
		$iblockObject = new CIBlock;
		if(!$iblockObject)
		{
			$errors[] = Loc::getMessage('LISTS_COPY_IBLOCK_ERROR_GET_DATA');
			return false;
		}
		$copyIblockId = $iblockObject->add($iblock);
		if($copyIblockId)
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->ClearByTag('lists_list_'.$copyIblockId);
			$CACHE_MANAGER->ClearByTag('lists_list_any');
			$CACHE_MANAGER->CleanDir('menu');
		}
		else
		{
			$errors[] = Loc::getMessage('LISTS_COPY_IBLOCK_ERROR_GET_DATA');
			return false;
		}

		/* Set right */
		$rights = array();
		if($iblock['RIGHTS_MODE'] == 'E')
		{
			$rightObject = new CIBlockRights($iblockId);
			$i = 0;
			foreach($rightObject->getRights() as $right)
			{
				$rights['n'.($i++)] = array(
					'GROUP_CODE' => $right['GROUP_CODE'],
					'DO_CLEAN' => 'N',
					'TASK_ID' => $right['TASK_ID'],
				);
			}
		}
		else
		{
			$i = 0;
			if(!empty($iblock['SOCNET_GROUP_ID']))
			{
				$socnetPerm = self::getSocnetPermission($iblockId);
				foreach($socnetPerm as $role => $permission)
				{
					if($permission > "W")
						$permission = "W";
					switch($role)
					{
						case "A":
						case "E":
						case "K":
							$rights['n'.($i++)] = array(
								"GROUP_CODE" => "SG".$iblock['SOCNET_GROUP_ID']."_".$role,
								"IS_INHERITED" => "N",
								"TASK_ID" => CIBlockRights::letterToTask($permission),
							);
							break;
						case "L":
							$rights['n'.($i++)] = array(
								"GROUP_CODE" => "AU",
								"IS_INHERITED" => "N",
								"TASK_ID" => CIBlockRights::letterToTask($permission),
							);
							break;
						case "N":
							$rights['n'.($i++)] = array(
								"GROUP_CODE" => "G2",
								"IS_INHERITED" => "N",
								"TASK_ID" => CIBlockRights::letterToTask($permission),
							);
							break;
					}
				}
			}
			else
			{
				$groupPermissions = CIBlock::getGroupPermissions($iblockId);
				foreach($groupPermissions as $groupId => $permission)
				{
					if($permission > 'W')
						$rights['n'.($i++)] = array(
							'GROUP_CODE' => 'G'.$groupId,
							'IS_INHERITED' => 'N',
							'TASK_ID' => CIBlockRights::letterToTask($permission),
						);
				}
			}

		}
		$iblock['RIGHTS'] = $rights;
		$resultIblock = $iblockObject->update($copyIblockId, $iblock);
		if(!$resultIblock)
			$errors[] = Loc::getMessage('LISTS_COPY_IBLOCK_ERROR_SET_RIGHT');

		/* Add fields */
		$listObject = new CList($iblockId);
		$fields = $listObject->getFields();
		$copyListObject = new CList($copyIblockId);
		foreach($fields as $fieldId => $field)
		{
			$copyFields = array(
				'NAME' => $field['NAME'],
				'SORT' => $field['SORT'],
				'MULTIPLE' => $field['MULTIPLE'],
				'IS_REQUIRED' => $field['IS_REQUIRED'],
				'IBLOCK_ID' => $copyIblockId,
				'SETTINGS' => $field['SETTINGS'],
				'DEFAULT_VALUE' => $field['DEFAULT_VALUE'],
				'TYPE' => $field['TYPE'],
				'PROPERTY_TYPE' => $field['PROPERTY_TYPE'],
			);

			if(!$listObject->is_field($fieldId))
			{
				if($field['TYPE'] == 'L')
				{
					$enum = CIBlockPropertyEnum::getList(array(), array('PROPERTY_ID' => $field['ID']));
					while($listData = $enum->fetch())
					{
						$copyFields['VALUES'][] = array(
							'XML_ID' => $listData['XML_ID'],
							'VALUE' => $listData['VALUE'],
							'DEF' => $listData['DEF'],
							'SORT' => $listData['SORT']
						);
					}
				}

				$copyFields['CODE'] = $field['CODE'];
				$copyFields['LINK_IBLOCK_ID'] = $field['LINK_IBLOCK_ID'];
				if(!empty($field['PROPERTY_USER_TYPE']['USER_TYPE']))
					$copyFields['USER_TYPE'] = $field['PROPERTY_USER_TYPE']['USER_TYPE'];
				if(!empty($field['ROW_COUNT']))
					$copyFields['ROW_COUNT'] = $field['ROW_COUNT'];
				if(!empty($field['COL_COUNT']))
					$copyFields['COL_COUNT'] = $field['COL_COUNT'];
				if(!empty($field['USER_TYPE_SETTINGS']))
					$copyFields['USER_TYPE_SETTINGS'] = $field['USER_TYPE_SETTINGS'];
			}

			if($fieldId == 'NAME')
			{
				$resultUpdateField = $copyListObject->updateField("NAME", $copyFields);
				if($resultUpdateField)
					$copyListObject->save();
				else
					$errors[] = Loc::getMessage('LISTS_COPY_IBLOCK_ERROR_ADD_FIELD',
						array('#field#' => $field['NAME']));

				continue;
			}

			$copyFieldId = $copyListObject->addField($copyFields);
			if($copyFieldId)
				$copyListObject->save();
			else
				$errors[] = Loc::getMessage('LISTS_COPY_IBLOCK_ERROR_ADD_FIELD',
					array('#field#' => $field['NAME']));
		}

		/* Copy Workflow Template */
		// Make a copy workflow templates

		return $copyIblockId;
	}

	public static function checkChangedFields($iblockId, $elementId, array $select, array $elementFields, array $elementProperty)
	{
		$changedFields = array();
		/* We get the new data element. */
		$elementNewData = array();
		$elementQuery = CIBlockElement::getList(
			array(), array('IBLOCK_ID' => $iblockId, '=ID' => $elementId), false, false, $select);
		$elementObject = $elementQuery->getNextElement();

		if(is_object($elementObject))
			$elementNewData = $elementObject->getFields();

		$elementOldData = $elementFields;
		unset($elementNewData["TIMESTAMP_X"]);
		unset($elementOldData["TIMESTAMP_X"]);

		$elementNewData["PROPERTY_VALUES"] = array();
		if(is_object($elementObject))
		{
			$propertyQuery = CIBlockElement::getProperty(
				$iblockId,
				$elementId,
				array("sort"=>"asc", "id"=>"asc", "enum_sort"=>"asc", "value_id"=>"asc"),
				array("ACTIVE"=>"Y", "EMPTY"=>"N")
			);
			while($property = $propertyQuery->fetch())
			{
				$propertyId = $property["ID"];
				if(!array_key_exists($propertyId, $elementNewData["PROPERTY_VALUES"]))
				{
					$elementNewData["PROPERTY_VALUES"][$propertyId] = $property;
					unset($elementNewData["PROPERTY_VALUES"][$propertyId]["DESCRIPTION"]);
					unset($elementNewData["PROPERTY_VALUES"][$propertyId]["VALUE_ENUM_ID"]);
					unset($elementNewData["PROPERTY_VALUES"][$propertyId]["VALUE_ENUM"]);
					unset($elementNewData["PROPERTY_VALUES"][$propertyId]["VALUE_XML_ID"]);
					$elementNewData["PROPERTY_VALUES"][$propertyId]["FULL_VALUES"] = array();
					$elementNewData["PROPERTY_VALUES"][$propertyId]["VALUES_LIST"] = array();
				}

				$elementNewData["PROPERTY_VALUES"][$propertyId]["FULL_VALUES"][$property["PROPERTY_VALUE_ID"]] = array(
					"VALUE" => $property["VALUE"],
					"DESCRIPTION" => $property["DESCRIPTION"],
				);
				$elementNewData["PROPERTY_VALUES"][$propertyId]["VALUES_LIST"][$property["PROPERTY_VALUE_ID"]] = $property["VALUE"];
			}
		}

		$elementOldData["PROPERTY_VALUES"] = $elementProperty;

		/* Check added or deleted fields. */
		$listNewFieldIdToDelete = array();
		$listOldFieldIdToDelete = array();
		$differences = array_diff_key($elementNewData, $elementOldData);
		foreach(array_keys($differences) as $fieldId)
		{
			if($fieldId[0] === '~')
				continue;
			$changedFields[] = $fieldId;
			$listNewFieldIdToDelete["FIELD"][] = $fieldId;
		}
		$differences = array_diff_key($elementOldData, $elementNewData);
		foreach(array_keys($differences) as $fieldId)
		{
			if($fieldId[0] === '~')
				continue;
			$changedFields[] = $fieldId;
			$listOldFieldIdToDelete["FIELD"][] = $fieldId;
		}

		$differences = array_diff_key(
			$elementNewData["PROPERTY_VALUES"],
			$elementOldData["PROPERTY_VALUES"]
		);
		foreach(array_keys($differences) as $fieldId)
		{
			$listNewFieldIdToDelete["PROPERTY"][] = $fieldId;

			if(!empty($elementNewData["PROPERTY_VALUES"][$fieldId]["CODE"]))
				$fieldId = "PROPERTY_".$elementNewData["PROPERTY_VALUES"][$fieldId]["CODE"];
			else
				$fieldId = "PROPERTY_".$fieldId;
			$changedFields[] = $fieldId;
		}
		$differences = array_diff_key(
			$elementOldData["PROPERTY_VALUES"],
			$elementNewData["PROPERTY_VALUES"]
		);
		foreach(array_keys($differences) as $fieldId)
		{
			$listOldFieldIdToDelete["PROPERTY"][] = $fieldId;

			if(!empty($elementOldData["PROPERTY_VALUES"][$fieldId]["CODE"]))
				$fieldId = "PROPERTY_".$elementOldData["PROPERTY_VALUES"][$fieldId]["CODE"];
			else
				$fieldId = "PROPERTY_".$fieldId;
			$changedFields[] = $fieldId;
		}

		foreach($listNewFieldIdToDelete as $typeField => $listField)
		{
			if($typeField == "FIELD")
				foreach($listField as $fieldId)
					unset($elementNewData[$fieldId]);
			elseif($typeField == "PROPERTY")
				foreach($listField as $fieldId)
					unset($elementNewData["PROPERTY_VALUES"][$fieldId]);
		}
		foreach($listOldFieldIdToDelete as $typeField => $listField)
		{
			if($typeField == "FIELD")
				foreach($listField as $fieldId)
					unset($elementOldData[$fieldId]);
			elseif($typeField == "PROPERTY")
				foreach($listField as $fieldId)
					unset($elementOldData["PROPERTY_VALUES"][$fieldId]);
		}

		/* Preparing arrays to compare */
		$listObject = new CList($iblockId);
		foreach($elementNewData as $fieldId => $fieldValue)
		{
			if(!$listObject->is_field($fieldId) && $fieldId != "PROPERTY_VALUES")
			{
				unset($elementNewData[$fieldId]);
			}
			elseif($fieldId == "PROPERTY_VALUES")
			{
				foreach($fieldValue as $propertyId => $propertyData)
				{
					if(!empty($propertyData["CODE"]))
						$elementNewData["PROPERTY_".$propertyData["CODE"]] = $propertyData["VALUES_LIST"];
					else
						$elementNewData["PROPERTY_".$propertyData["ID"]] = $propertyData["VALUES_LIST"];

					unset($elementNewData["PROPERTY_VALUES"][$propertyId]);
				}
				unset($elementNewData["PROPERTY_VALUES"]);
			}
		}
		foreach($elementOldData as $fieldId => $fieldValue)
		{
			if(!$listObject->is_field($fieldId) && $fieldId != "PROPERTY_VALUES")
			{
				unset($elementOldData[$fieldId]);
			}
			elseif($fieldId == "PROPERTY_VALUES")
			{
				foreach($fieldValue as $propertyId => $propertyData)
				{
					if(!empty($propertyData["CODE"]))
						$elementOldData["PROPERTY_".$propertyData["CODE"]] = $propertyData["VALUES_LIST"];
					else
						$elementOldData["PROPERTY_".$propertyData["ID"]] = $propertyData["VALUES_LIST"];

					unset($elementOldData["PROPERTY_VALUES"][$propertyId]);
				}
				unset($elementOldData["PROPERTY_VALUES"]);
			}
		}

		/* Compares the value */
		foreach($elementNewData as $fieldName => $fieldValue)
		{
			if(is_array($fieldValue))
			{
				if(is_array(current($fieldValue)))
				{
					$firstValues = array();
					$secondValues = array();
					foreach ($fieldValue as $values)
					{
						$firstValues = is_array($values) ? $values : [$values];
					}
					foreach ($elementOldData[$fieldName] as $values)
					{
						$secondValues = is_array($values) ? $values : [$values];
					}

					if(array_key_exists("TEXT", $firstValues))
					{
						$differences = array_diff($firstValues, $secondValues);
						if(!empty($differences))
							$changedFields[] = $fieldName;
					}
					else
					{
						if(count($firstValues) != count($secondValues))
							$changedFields[] = $fieldName;
					}
				}
				else
				{
					$differences = array_diff($fieldValue, $elementOldData[$fieldName]);
					if(!empty($differences))
						$changedFields[] = $fieldName;
				}
			}
			else
			{
				if(strcmp((string)$fieldValue, (string)$elementOldData[$fieldName]) !== 0)
					$changedFields[] = $fieldName;
			}
		}

		return $changedFields;
	}

	public static function deleteListsUrl($iblockId)
	{
		global $DB;
		$iblockId = intval($iblockId);
		$DB->Query(
			"delete from b_lists_url where IBLOCK_ID=" . $iblockId,
			false,
			"FILE: ".__FILE__."<br> LINE: ".__LINE__
		);
	}

	/**
	 * Method get iblock attached crm.
	 *
	 * @param string $entityType Type entity.
	 * @return array List iblock data array(iblockId => IblockName).
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getIblockAttachedCrm($entityType)
	{
		if (
			(!self::isFeatureEnabled("lists")) &&
			(!self::isFeatureEnabled("lists_processes"))
		)
		{
			return [];
		}

		$cacheTime = defined('BX_COMP_MANAGED_CACHE') ? 3153600 : 3600*4;
		$cacheId = 'lists-crm-attached-'.mb_strtolower($entityType);
		$cacheDir = '/lists/crm/attached/'.mb_strtolower($entityType).'/';
		$cache = new CPHPCache;
		if($cache->initCache($cacheTime, $cacheId, $cacheDir))
		{
			$listIblock = $cache->getVars();
		}
		else
		{
			$cache->startDataCache();
			$listIblock = array();
			$listProperty = array();
			$propertyObject = Bitrix\Iblock\PropertyTable::getList(array(
				'select' => array('ID', 'IBLOCK_ID', 'USER_TYPE_SETTINGS'),
				'filter' => array(
					'=ACTIVE' => 'Y',
					'=USER_TYPE' => 'ECrm',
				)
			));
			while($property = $propertyObject->fetch())
			{
				$property['USER_TYPE_SETTINGS'] = unserialize(
					$property['USER_TYPE_SETTINGS'],
					['allowed_classes' => false]
				);
				if(empty($property['USER_TYPE_SETTINGS']['VISIBLE']))
					$property['USER_TYPE_SETTINGS']['VISIBLE'] = 'Y';
				if($property['USER_TYPE_SETTINGS']['VISIBLE'] == 'Y'
					&& !empty($property['USER_TYPE_SETTINGS'][$entityType]))
				{
					if($property['USER_TYPE_SETTINGS'][$entityType] == 'Y')
					{
						$listProperty[$property['IBLOCK_ID']][] = $property['ID'];
					}
				}
			}
			$isListsFeatureEnabled = self::isFeatureEnabled("lists");
			$isProcessesFeatureEnabled = self::isFeatureEnabled("lists_processes");
			foreach ($listProperty as $iblockId => $listPropertyId)
			{
				$iblockObject = Bitrix\Iblock\IblockTable::getList(array(
					'select' => array('ID', 'NAME', 'IBLOCK_TYPE_ID'),
					'filter' => array('=ACTIVE' => 'Y', '=ID' => $iblockId)
				));
				if ($iblock = $iblockObject->fetch())
				{
					switch ($iblock['IBLOCK_TYPE_ID'])
					{
						case "CRM_PRODUCT_CATALOG":
						{
							continue 2;
						}
						case "bitrix_processes":
						{
							if (!$isProcessesFeatureEnabled)
							{
								continue 2;
							}
							break;
						}
						default:
							if (!$isListsFeatureEnabled)
							{
								continue 2;
							}
					}
					$listIblock[$iblockId] = $iblock['NAME'];
				}
			}
			$cache->endDataCache($listIblock);
		}

		return $listIblock;
	}

	protected static function deleteListsCache($cacheDir)
	{
		$cache = new CPHPCache;
		$cache->cleanDir($cacheDir);
	}

	public static function OnAfterIBlockPropertyAdd($fields)
	{
		self::deleteCacheToECrmProperty($fields);
	}

	public static function OnAfterIBlockPropertyUpdate($fields)
	{
		self::deleteCacheToECrmProperty($fields);
	}

	public static function OnAfterIBlockPropertyDelete($fields)
	{
		self::deleteCacheToECrmProperty($fields);
	}

	public static function getChildSection($baseSectionId, array $listSection, array &$listChildSection)
	{
		$baseSectionId = intval($baseSectionId);
		if (!$baseSectionId)
			return;
		if(!in_array($baseSectionId, $listChildSection))
			$listChildSection[] = $baseSectionId;

		foreach($listSection as $sectionId => $section)
		{
			if(($section['PARENT_ID'] ?? null) == $baseSectionId)
			{
				$listChildSection[] = $sectionId;
				self::getChildSection($sectionId, $listSection, $listChildSection);
			}
		}
	}

	public static function isAssociativeArray($array)
	{
		if (!is_array($array) || empty($array))
			return false;
		return array_keys($array) !== range(0, count($array) - 1);
	}

	/**
	 * Handler OnBeforeIBlockElementAdd.
	 *
	 * @param array $fields Current values of the elements.
	 * @return bool
	 */
	public static function OnBeforeIBlockElementAdd(&$fields)
	{
		$iblockTypeId = (string)CIBlock::GetArrayByID($fields["IBLOCK_ID"], 'IBLOCK_TYPE_ID');
		if (isset(self::$iblockTypeList[$iblockTypeId]))
		{
			$fields["SEARCHABLE_CONTENT"] = self::createSeachableContent($fields);
		}
		unset($iblockTypeId);
		return true;
	}

	/**
	 * Handler OnBeforeIBlockElementUpdate.
	 *
	 * @param array $fields Current values of the elements.
	 * @return bool
	 */
	public static function OnBeforeIBlockElementUpdate(&$fields)
	{
		$iblockTypeId = (string)CIBlock::GetArrayByID($fields["IBLOCK_ID"], 'IBLOCK_TYPE_ID');
		if (isset(self::$iblockTypeList[$iblockTypeId]))
		{
			$fields["SEARCHABLE_CONTENT"] = self::createSeachableContent($fields);
		}
		unset($iblockTypeId);
		return true;
	}

	/**
	 * Agent function. Run rebuild seachable content.
	 *
	 * @param $iblockId
	 * @return bool
	 */
	public static function runRebuildSeachableContent($iblockId)
	{
		$rebuildedData = Option::get("lists", "rebuild_seachable_content");
		$rebuildedData = unserialize($rebuildedData, ['allowed_classes' => false]);
		if(!is_array($rebuildedData))
			$rebuildedData = array();
		if(!isset($rebuildedData[$iblockId]))
		{
			return '';
		}

		$limit = 50;
		$offset = $rebuildedData[$iblockId];
		$rebuildedElementCount = CLists::rebuildSeachableContent($iblockId, $limit, $offset);

		if($rebuildedElementCount < $limit)
		{
			unset($rebuildedData[$iblockId]);
			Option::set("lists", "rebuild_seachable_content", serialize($rebuildedData));
			return '';
		}
		else
		{
			$rebuildedData[$iblockId] = $offset + $rebuildedElementCount;
			Option::set("lists", "rebuild_seachable_content", serialize($rebuildedData));
			return 'CLists::runRebuildSeachableContent('.$iblockId.');';
		}
	}

	/**
	 * Method rebuild seachable content taking into account the current values of the elements.
	 *
	 * @param int $iblockId Iblock id.
	 * @param int $limit Restricts the number of results.
	 * @param int $offset Specifies the number of rows to skip, before starting to return rows from the query expression.
	 * @return int Number of processed items.
	 * @throws ArgumentException
	 */
	public static function rebuildSeachableContent($iblockId, $limit = 100, $offset = 0)
	{
		$iblockId = intval($iblockId);
		if(!$iblockId)
			throw new ArgumentException(Loc::getMessage("LISTS_REQUIRED_PARAMETER",array("#parameter#" => "iblockId")));

		$connection = Application::getInstance()->getConnection();
		$iblockId = $connection->getSqlHelper()->forSql($iblockId);
		$offset = intval($offset);
		$limit = intval($limit);
		$sqlString = "SELECT ID FROM b_iblock_element WHERE IBLOCK_ID=".$iblockId." ORDER BY ID ASC LIMIT ".$limit." OFFSET ".$offset;
		$queryObject = $connection->query($sqlString);
		$listElement = $queryObject->fetchAll();
		$rebuildedElementCount = $queryObject->getSelectedRowsCount();
		$listElementId = array();
		foreach($listElement as $element)
			$listElementId[] = $element['ID'];

		$listElementValue = !empty($listElementId) ? self::getListElementValue($iblockId, $listElementId) : array();

		$listSeachableContent = array();
		foreach($listElementValue as $elementId => $elementData)
		{
			$listSeachableContent[$elementId] = self::createSeachableContent($elementData);
		}

		global $DB;
		foreach($listSeachableContent as $elementId => $seachableContent)
		{
			$strUpdate = $DB->prepareUpdate("b_iblock_element", array("SEARCHABLE_CONTENT" => $seachableContent));
			$strSql = "UPDATE b_iblock_element SET ".$strUpdate." WHERE ID=".intval($elementId);
			$DB->query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $rebuildedElementCount;
	}

	/**
	 * The method rebuild seachable content taking into account the current values of the element.
	 *
	 * @param $iblockId
	 * @param $elementId
	 * @throws ArgumentException
	 */
	public static function rebuildSeachableContentForElement($iblockId, $elementId)
	{
		$iblockId = intval($iblockId);
		if(!$iblockId)
		{
			throw new ArgumentException(Loc::getMessage("LISTS_REQUIRED_PARAMETER",array("#parameter#" => "iblockId")));
		}
		$elementId = intval($elementId);
		if(!$elementId)
		{
			throw new ArgumentException(Loc::getMessage("LISTS_REQUIRED_PARAMETER",array("#parameter#" => "elementId")));
		}

		$elementValue = self::getListElementValue($iblockId, $elementId);
		$listSeachableContent = array();
		foreach($elementValue as $elementData)
		{
			$listSeachableContent[$elementId] = self::createSeachableContent($elementData);
		}
		global $DB;
		foreach($listSeachableContent as $seachableContent)
		{
			$strUpdate = $DB->prepareUpdate("b_iblock_element", array("SEARCHABLE_CONTENT" => $seachableContent));
			$strSql = "UPDATE b_iblock_element SET ".$strUpdate." WHERE ID=".intval($elementId);
			$DB->query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
	}

	private static function getListElementValue($iblockId, $listElementId)
	{
		$iblockId = intval($iblockId);
		$listElementValue = array();
		$listObject = new CList($iblockId);
		$queryObject = CIBlockElement::getList(array(), array("IBLOCK_ID" => $iblockId,
			"=ID" => $listElementId), false, false, array('*'));
		while($queryResult = $queryObject->getNextElement())
		{
			$element = $queryResult->getFields();
			if(is_array($element))
			{
				foreach($element as $fieldId => $fieldValue)
				{
					if(!$listObject->is_field($fieldId))
						continue;
					$listElementValue[$element["ID"]][$fieldId] = $element[$fieldId];
				}
				$query = CIblockElement::getPropertyValues($iblockId, array("ID" => $element["ID"]));
				if($propertyValues = $query->fetch())
				{
					$listElementValue[$element["ID"]]["PROPERTY_VALUES"] = array();
					foreach($propertyValues as $id => $values)
					{
						if($id == "IBLOCK_ELEMENT_ID")
							continue;
						$listElementValue[$element["ID"]]["PROPERTY_VALUES"][$id] = $values;
					}
				}
			}
		}
		return $listElementValue;
	}

	private static function createSeachableContent(array $fields)
	{
		$searchableContent = $fields["NAME"] ?? '';

		if(!empty($fields["DATE_CREATE"]))
		{
			$searchableContent .= "\r\n".$fields["DATE_CREATE"];
		}
		if(!empty($fields["TIMESTAMP_X"]))
		{
			$searchableContent .= "\r\n".$fields["TIMESTAMP_X"];
		}
		if(!empty($fields["ACTIVE_FROM"]))
		{
			$searchableContent .= "\r\n".$fields["ACTIVE_FROM"];
		}
		if(!empty($fields["ACTIVE_TO"]))
		{
			$searchableContent .= "\r\n".$fields["ACTIVE_TO"];
		}
		if(!empty($fields["PREVIEW_PICTURE"]))
		{
			$fileData = CFile::getFileArray($fields["PREVIEW_PICTURE"]);
			if($fileData)
			{
				$searchableContent .= "\r\n".$fileData["FILE_NAME"];
			}
		}
		if(!empty($fields["DETAIL_PICTURE"]))
		{
			$fileData = CFile::getFileArray($fields["DETAIL_PICTURE"]);
			if($fileData)
			{
				$searchableContent .= "\r\n".$fileData["FILE_NAME"];
			}
		}
		if(!empty($fields["CREATED_BY"]))
		{
			$user = new CUser();
			$userDetails = $user->getByID($fields["CREATED_BY"])->fetch();
			if(is_array($userDetails))
			{
				$siteNameFormat = CSite::getNameFormat(false);
				$searchableContent .= "\r\n".CUser::formatName($siteNameFormat, $userDetails, true, false);
			}
		}
		if(!empty($fields["MODIFIED_BY"]))
		{
			$user = new CUser();
			$userDetails = $user->getByID($fields["MODIFIED_BY"])->fetch();
			if(is_array($userDetails))
			{
				$siteNameFormat = CSite::getNameFormat(false);
				$searchableContent .= "\r\n".CUser::formatName($siteNameFormat, $userDetails, true, false);
			}
		}
		if(!empty($fields["PREVIEW_TEXT"]))
		{
			if(isset($fields["PREVIEW_TEXT_TYPE"]) && $fields["PREVIEW_TEXT_TYPE"] == "html")
				$searchableContent .= "\r\n".HTMLToTxt($fields["PREVIEW_TEXT"]);
			else
				$searchableContent .= "\r\n".$fields["PREVIEW_TEXT"];
		}
		if(!empty($fields["DETAIL_TEXT"]))
		{
			if(isset($fields["DETAIL_TEXT_TYPE"]) && $fields["DETAIL_TEXT_TYPE"] == "html")
				$searchableContent .= "\r\n".HTMLToTxt($fields["DETAIL_TEXT"]);
			else
				$searchableContent .= "\r\n".$fields["DETAIL_TEXT"];
		}
		if(!empty($fields["PROPERTY_VALUES"]) && is_array($fields["PROPERTY_VALUES"]))
		{
			$searchableContent .= self::createSeachableContentForProperty($fields);
		}

		$searchableContent = mb_strtoupper($searchableContent);

		return $searchableContent;
	}

	private static function createSeachableContentForProperty($fields)
	{
		$searchableContent = '';

		global $DB;
		$properties = array();
		foreach($fields["PROPERTY_VALUES"] as $propertyId => $valueData)
		{
			if(!$valueData)
				continue;
			$properties[$propertyId] = array();
			if(is_array($valueData))
			{
				foreach($valueData as $valueId => $value)
				{
					if(is_object($value))
						continue;
					if(isset($value["VALUE"]))
					{
						if(is_array($value["VALUE"]))
						{
							if(!empty($value["VALUE"]))
								$properties[$propertyId][] = $value["VALUE"];
						}
						else
						{
							if($value["VALUE"] <> '')
								$properties[$propertyId][] = $value["VALUE"];
						}
					}
					else
					{
						if(is_array($value))
						{
							foreach($value as $v)
							{
								if($v <> '')
									$properties[$propertyId][] = $v;
							}
						}
						else
						{
							if($value <> '')
								$properties[$propertyId][] = $value;
						}
					}
				}
			}
			else
			{
				$properties[$propertyId][] = $valueData;
			}

			$queryObject = CIBlockProperty::getById($propertyId);
			if($property = $queryObject->fetch())
			{
				$propertyValues = array();
				if(!empty($property["USER_TYPE"]))
				{
					switch($property["USER_TYPE"])
					{
						case "Date":
						case "DateTime":
						{
							$format = "FULL";
							if($property["USER_TYPE"] == "Date")
								$format = "SHORT";
							foreach($properties[$propertyId] as $value)
							{
								try
								{
									$date = new Bitrix\Main\Type\DateTime($value);
									$propertyValues[] = $date->format($DB->dateFormatToPHP(CSite::getDateFormat($format)));
								}
								catch (Exception $ex)
								{
									$propertyValues[] = $value;
								}
							}
							break;
						}
						case "HTML":
						{
							foreach($properties[$propertyId] as $value)
							{
								if (is_string($value))
								{
									$unserialize = unserialize($value, ['allowed_classes' => false]);
									if($unserialize)
										$value = $unserialize;
								}
								if (
									is_array($value)
									&& !empty($value['TEXT'])
									&& is_string($value['TEXT'])
								)
								{
									if (isset($value["TYPE"]))
									{
										$value["TYPE"] = mb_strtoupper($value["TYPE"]);
										if ($value["TYPE"] == "HTML")
											$propertyValues[] = HTMLToTxt($value["TEXT"]);
									}
								}
							}
							break;
						}
						case "EList":
						{
							if(!empty($properties[$propertyId]))
							{
								$queryObject = CIBlockElement::getList(array(), array("ID" => $properties[$propertyId]),
									false, false, array('NAME'));
								while($element = $queryObject->getNext())
									$propertyValues[] = $element["~NAME"];
							}
							break;
						}
						case "Money":
						case "map_yandex":
						{
							$propertyValues = $properties[$propertyId];
							break;
						}
						case "Sequence":
						{
							foreach($properties[$propertyId] as $value)
								$propertyValues[] = intval($value);
							break;
						}
						case "ECrm":
						{
							if(Loader::includeModule("crm"))
							{
								foreach($properties[$propertyId] as $value)
								{
									if (intval($value))
									{
										foreach($property["USER_TYPE_SETTINGS"] as $entityType => $marker)
										{
											if ($entityType != "VISIBLE" && $marker == "Y")
											{
												$typeId = CCrmOwnerType::resolveID($entityType);
												$propertyValues[] = CCrmOwnerType::getCaption($typeId, $value, false);
											}
										}
									}
									else
									{
										$explode = explode('_', $value);
										$type = $explode[0];
										$typeId = CCrmOwnerType::resolveID(CCrmOwnerTypeAbbr::resolveName($type));
										$propertyValues[] = CCrmOwnerType::getCaption($typeId, $explode[1], false);
									}
								}
							}
							break;
						}
						case "employee":
						{
							$siteNameFormat = CSite::getNameFormat(false);
							foreach($properties[$propertyId] as $value)
							{
								$user = new CUser();
								$userDetails = $user->getByID($value)->fetch();
								if(is_array($userDetails))
									$propertyValues[] = CUser::formatName($siteNameFormat, $userDetails,true,false);
							}
							break;
						}
						case "DiskFile":
						{
							if(Loader::includeModule("disk"))
							{
								foreach($properties[$propertyId] as $value)
								{
									if(!is_array($value))
										$value = array($value);
									foreach($value as $v)
									{
										if(empty($v))
											continue;
										list($type, $realId) = FileUserType::detectType($v);
										if($type == FileUserType::TYPE_ALREADY_ATTACHED)
										{
											$attachedModel = AttachedObject::loadById($realId);
											if($attachedModel)
											{
												$file = $attachedModel->getFile();
												if($file)
													$propertyValues[] = $file->getName();
											}
										}
										else
										{
											$fileModel = File::loadById($realId, array('STORAGE'));
											if($fileModel)
												$propertyValues[] = $fileModel->getName();
										}
									}
								}
							}
							break;
						}
					}
				}
				else
				{
					switch($property["PROPERTY_TYPE"])
					{
						case "S":
						{
							$propertyValues = $properties[$propertyId];
							break;
						}
						case "N":
						{
							$propertyValues = $properties[$propertyId];
							break;
						}
						case "L":
						{
							$queryObject = CIBlockProperty::getPropertyEnum($propertyId);
							while($propertyEnum = $queryObject->fetch())
							{
								if(in_array($propertyEnum["ID"], $properties[$propertyId]))
									$propertyValues[] = $propertyEnum["VALUE"];
							}
							break;
						}
						case "F":
						{
							$listPropertyIdForGetExtraValue = array();
							foreach($properties[$propertyId] as $value)
							{
								if(isset($value["name"]))
								{
									if(!empty($value["name"]))
									{
										$propertyValues[] = $value["name"];
									}
									else
									{
										$listPropertyIdForGetExtraValue[] = $propertyId;
									}
								}
								else
								{
									$fileData = CFile::getFileArray($value);
									if($fileData)
										$propertyValues[] = $fileData["FILE_NAME"];
								}
							}
							if(!empty($fields["ID"]) && !empty($listPropertyIdForGetExtraValue))
							{
								$query = CIblockElement::getPropertyValues($property["IBLOCK_ID"],
									array("ID" => $fields["ID"]), false, array("ID" => $listPropertyIdForGetExtraValue));
								if($listExtraPropertyValues = $query->fetch())
								{
									foreach($listExtraPropertyValues as $id => $extraPropertyValues)
									{
										if($id == "IBLOCK_ELEMENT_ID")
											continue;
										if(is_array($extraPropertyValues))
										{
											foreach($extraPropertyValues as $extraPropertyValue)
											{
												$fileData = CFile::getFileArray($extraPropertyValue);
												if($fileData)
													$propertyValues[] = $fileData["FILE_NAME"];
											}
										}
										else
										{
											$fileData = CFile::getFileArray($extraPropertyValues);
											if($fileData)
												$propertyValues[] = $fileData["FILE_NAME"];
										}
									}
								}
							}
							break;
						}
						case "G":
						{
							if(!empty($properties[$propertyId]))
							{
								$queryObject = CIBlockSection::getList(array(),
									array("=ID" => $properties[$propertyId]), false, array("NAME"));
								while($section = $queryObject->getNext())
									$propertyValues[] = $section["~NAME"];
							}
							break;
						}
						case "E":
						{
							if(!empty($properties[$propertyId]))
							{
								$queryObject = CIBlockElement::getList(array(), array("ID" => $properties[$propertyId]),
									false, false, array('NAME'));
								while($element = $queryObject->getNext())
									$propertyValues[] = $element["~NAME"];
							}
							break;
						}
					}
				}
				foreach($propertyValues as $propertyValue)
					$searchableContent .= "\r\n".$propertyValue;
			}
		}

		return $searchableContent;
	}

	/**
	 * The method return number of elements by iblock id.
	 *
	 * @param $iblockId Iblock id.
	 * @return int
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getNumberOfElements($iblockId)
	{
		$iblockId = intval($iblockId);
		if(!$iblockId)
		{
			throw new ArgumentException(Loc::getMessage("LISTS_REQUIRED_PARAMETER",array("#parameter#" => "iblockId")));
		}

		$connection = Application::getInstance()->getConnection();
		$sqlString = "SELECT COUNT(ID) as COUNT FROM b_iblock_element WHERE IBLOCK_ID=".$iblockId;
		$queryObject = $connection->query($sqlString);
		$result = $queryObject->fetch();

		return intval($result["COUNT"]);
	}

	public static function isListProcesses(string $iblockTypeId): bool
	{
		return $iblockTypeId == COption::GetOptionString('lists', 'livefeed_iblock_type_id');
	}

	public static function isBpFeatureEnabled(string $iblockTypeId): bool
	{
		$processes = CLists::isListProcesses($iblockTypeId);

		return ($processes ? CLists::isFeatureEnabled() : CBPRuntime::isFeatureEnabled());
	}

	public static function isListFeatureEnabled(string $iblockTypeId): bool
	{
		$processes = CLists::isListProcesses($iblockTypeId);

		return ($processes ? CLists::isFeatureEnabled('lists_processes') : CLists::isFeatureEnabled('lists'));
	}

	public static function isFeatureEnabled($featureName = '')
	{
		if (!CModule::IncludeModule("bitrix24"))
			return true;

		$featureName = (string)$featureName;

		if ($featureName === "")
			$featureName = "lists_processes";

		if (!isset(static::$featuresCache[$featureName]))
			static::$featuresCache[$featureName] = \Bitrix\Bitrix24\Feature::isFeatureEnabled($featureName);

		return static::$featuresCache[$featureName];
	}

	public static function isWorkflowParticipant($workflowId)
	{
		global $USER;

		if ($USER->isAdmin() || $USER->canDoOperation("bitrix24_config"))
		{
			return true;
		}

		$userId = (int) $USER->getID();
		$participants = \CBPTaskService::getWorkflowParticipants($workflowId);
		if (in_array($userId, $participants))
		{
			return true;
		}
		else
		{
			$state = \CBPStateService::getWorkflowStateInfo($workflowId);
			if ($state && $userId === (int) $state['STARTED_BY'])
			{
				return true;
			}
		}

		return false;
	}

	public static function isEnabledLockFeature($iblockId)
	{
		$optionData = Option::get("lists", "iblock_lock_feature");
		$iblockIdsWithLockFeature = unserialize($optionData, ['allowed_classes' => false]);
		if (!is_array($iblockIdsWithLockFeature))
		{
			$iblockIdsWithLockFeature = [];
		}
		return isset($iblockIdsWithLockFeature[$iblockId]);
	}

	public static function deleteLockFeatureOption(int $iblockId)
	{
		$option = Option::get("lists", "iblock_lock_feature");
		$iblockIdsWithLockFeature = ($option !== "" ? unserialize($option, ['allowed_classes' => false]) : []);
		if (isset($iblockIdsWithLockFeature[$iblockId]))
		{
			unset($iblockIdsWithLockFeature[$iblockId]);
			Option::set("lists", "iblock_lock_feature", serialize($iblockIdsWithLockFeature));
		}
	}

	private static function deleteCacheToECrmProperty($fields): void
	{
		if (!empty($fields['USER_TYPE']) && $fields['USER_TYPE'] == 'ECrm')
		{
			if (!empty($fields['USER_TYPE_SETTINGS']))
			{
				if (!is_array($fields['USER_TYPE_SETTINGS']))
				{
					$fields['USER_TYPE_SETTINGS'] = unserialize(
						$fields['USER_TYPE_SETTINGS'],
						['allowed_classes' => false]
					);
				}
				if (is_array($fields['USER_TYPE_SETTINGS']))
				{
					foreach ($fields['USER_TYPE_SETTINGS'] as $entityType => $marker)
					{
						if ($marker == 'Y')
						{
							self::deleteListsCache('/lists/crm/attached/'.mb_strtolower($entityType).'/');
						}
					}
				}
			}
			else
			{
				self::deleteListsCache('/lists/crm/attached/');
			}
		}
	}
}
