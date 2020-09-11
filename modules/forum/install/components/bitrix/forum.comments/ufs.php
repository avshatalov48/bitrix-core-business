<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
class CCommentUFs
{
	var $component = null;

	function __construct(&$component)
	{
		global $APPLICATION;
		$this->component = &$component;
		$arResult =& $component->arResult;
		$arParams =& $component->arParams;

		AddEventHandler("forum", "OnCommentsInit", Array(&$this, "OnCommentsInit"));
		AddEventHandler("forum", "OnPrepareComments", Array(&$this, "OnPrepareComments"));
	}

	function OnCommentsInit()
	{
		$arResult =& $this->component->arResult;
		$arParams =& $this->component->arParams;
		$arParams["USER_FIELDS_SETTINGS"] = (is_array($arParams["USER_FIELDS_SETTINGS"]) ? $arParams["USER_FIELDS_SETTINGS"] : array());
		$arParams["USER_FIELDS"] = (is_array($arParams["USER_FIELDS"]) ? $arParams["USER_FIELDS"] : array("UF_FORUM_MESSAGE_DOC", "UF_FORUM_MESSAGE_VER", "UF_FORUM_MES_URL_PRV"));
		$arResult["~USER_FIELDS"] = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("FORUM_MESSAGE", 0, LANGUAGE_ID);
		foreach($arResult["~USER_FIELDS"] as $key => $val)
		{
			if ($val["MANDATORY"] == "Y" && !in_array($key, $arParams["USER_FIELDS"]))
			{
				$arParams["USER_FIELDS"][] = $key;
			}
		}
		$arResult["USER_FIELDS"] = array_intersect_key($arResult["~USER_FIELDS"], array_flip($arParams["USER_FIELDS"]));
		$arResult['UFS'] = array();
	}

	function OnPrepareComments()
	{
		$arResult =& $this->component->arResult;
		$arParams =& $this->component->arParams;

		$arMessages = &$arResult['MESSAGES'];
		$arResult['UFS'] = array();
		if (!empty($arMessages) && !empty($arResult["USER_FIELDS"]))
		{
			$res = array_keys($arMessages);
			$arFilter = array(
				"FORUM_ID" => $arParams["FORUM_ID"],
				"TOPIC_ID" => $arResult["FORUM_TOPIC_ID"],
				"APPROVED_AND_MINE" => $GLOBALS["USER"]->GetId(),
				">ID" => intval(min($res)) - 1,
				"<ID" => intval(max($res)) + 1);
			if ($arFilter[">ID"] <= 0)
				unset($arFilter[">ID"]);
			if ($arResult["USER"]["RIGHTS"]["MODERATE"] == "Y")
				unset($arFilter["APPROVED_AND_MINE"]);

			$db_res = CForumMessage::GetList(array("ID" => "ASC"), $arFilter, false, 0, array("SELECT" => array_keys($arResult["USER_FIELDS"])));
			if ($db_res && ($res = $db_res->Fetch()))
			{
				do {
					$arResult['UFS'][$res["ID"]] = array_intersect_key($res, $arResult["USER_FIELDS"]);
				} while ($res = $db_res->Fetch());
			}
		}
	}

	function OnCommentPreviewDisplay()
	{
		$arResult =& $this->component->arResult;
		$arParams =& $this->component->arParams;
		if (empty($arResult["USER_FIELDS"]))
			return null;

		ob_start();
		foreach ($arResult["USER_FIELDS"] as $k => $arPostField)
		{
			if(!empty($_REQUEST[$k]))
			{
				$GLOBALS["APPLICATION"]->IncludeComponent(
					"bitrix:system.field.view",
					$arPostField["USER_TYPE"]["USER_TYPE_ID"],
					array("arUserField" => array_merge($arPostField, array("VALUE" => $_REQUEST[$k]))),
					null,
					array("HIDE_ICONS"=>"Y")
				);
			}
		}
		return array(array('DISPLAY' => 'AFTER', 'SORT' => '50', 'TEXT' => ob_get_clean()));
	}

	function OnCommentDisplay($arComment)
	{
		$arResult =& $this->component->arResult;
		$arParams =& $this->component->arParams;

		if (empty($arComment["PROPS"]))
			return null;

		ob_start();
		if (is_array($arComment["PROPS"]))
		{
			foreach ($arComment["PROPS"] as $arPostField)
			{
				if(!empty($arPostField["VALUE"]))
				{
					$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:system.field.view", $arPostField["USER_TYPE"]["USER_TYPE_ID"],
						array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));
				}
			}
		}
		return array(array('DISPLAY' => 'AFTER', 'SORT' => '50', 'TEXT' => ob_get_clean()));
	}

	function OnCommentFormDisplay()
	{
		$arResult =& $this->component->arResult;
		$arParams =& $this->component->arParams;
		if (empty($arResult["USER_FIELDS"]))
			return null;

		ob_start();
		foreach ($arResult["USER_FIELDS"] as $k => $v)
		{
			if ($k != "UF_FORUM_MESSAGE_DOC")
			{
				$v["VALUE"] = (!empty($_REQUEST[$k]) ? $_REQUEST[$k] : $v["VALUE"]);

				?><dt><?=$v["EDIT_FORM_LABEL"]?></dt><dd><?
					$GLOBALS["APPLICATION"]->IncludeComponent(
					"bitrix:system.field.edit",
					$v["USER_TYPE"]["USER_TYPE_ID"],
					array("arUserField" => $v, "bVarsFromForm" => true),
					null,
					array("HIDE_ICONS" => "Y")
				);?></dd><?
			}
		}
		$res = ob_get_clean();
		if (!empty($res))
			$res = "<dl>".$res."</dl>";
		return array(array('DISPLAY' => 'AFTER', 'SORT' => '50', 'TEXT' => $res));
	}
}
?>
