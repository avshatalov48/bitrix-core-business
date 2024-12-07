<?php

use Bitrix\Main\Web\Json;

IncludeModuleLangFile(__FILE__);

class CIBlockPropertyUserID
{
	static array $cache = [];
	public const USER_TYPE = 'UserID';

	public static function GetUserTypeDescription()
	{
		return [
			"PROPERTY_TYPE" => "S",
			"USER_TYPE" => self::USER_TYPE,
			"DESCRIPTION" => GetMessage("IBLOCK_PROP_USERID_DESC"),
			"GetAdminListViewHTML" => [__CLASS__, "getAdminListViewHTMLExtended"],
			"GetPropertyFieldHtml" => [__CLASS__, "GetPropertyFieldHtml"],
			"ConvertToDB" => [__CLASS__, "ConvertToDB"],
			"ConvertFromDB" => [__CLASS__, "ConvertFromDB"],
			"GetSettingsHTML" => [__CLASS__, "GetSettingsHTML"],
			"GetPublicViewHTML" => [__CLASS__, "getPublicViewHTML"],
			"AddFilterFields" => [__CLASS__,'AddFilterFields'],
			"GetAdminFilterHTML" => [__CLASS__, "GetAdminFilterHTML"],
			"GetUIFilterProperty" => [__CLASS__, 'GetUIFilterProperty'],
			"GetUIEntityEditorProperty" => [__CLASS__, 'GetUIEntityEditorProperty'],
			"GetUIEntityEditorPropertyEditHtml" => [__CLASS__, 'GetUIEntityEditorPropertyEditHtml'],
			"GetUIEntityEditorPropertyViewHtml" => [__CLASS__, 'GetUIEntityEditorPropertyViewHtml'],
		];
	}

	public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
	{
		static $cache = array();
		$value = intval($value["VALUE"]);
		if(!array_key_exists($value, $cache))
		{
			$rsUsers = CUser::GetList('', '', array("ID" => $value));
			$cache[$value] = $rsUsers->Fetch();
		}
		$arUser = $cache[$value];
		if($arUser)
		{
			return "[<a title='".GetMessage("MAIN_EDIT_USER_PROFILE")."' href='user_edit.php?ID=".$arUser["ID"]."&lang=".LANGUAGE_ID."'>".$arUser["ID"]."</a>]&nbsp;(".htmlspecialcharsbx($arUser["LOGIN"]).") ".htmlspecialcharsbx($arUser["NAME"])." ".htmlspecialcharsbx($arUser["LAST_NAME"]);
		}
		else
			return "&nbsp;";
	}

	/**
	 * Returns HTML of property in view mode depending on public/admin mode. Useful for admin grids used in public mode
	 * @param array $property
	 * @param array $value
	 * @param $control
	 * @return string
	 */
	public static function getAdminListViewHTMLExtended(array $property, array $value, $control): string
	{
		$result = '';
		if ($value['VALUE'])
		{
			$isPublicMode = (defined("PUBLIC_MODE") && (int)PUBLIC_MODE === 1);

			if ($isPublicMode)
			{
				$result .= self::getPublicViewHTML($property, $value, $control);
			}
			else
			{
				$result .= self::GetAdminListViewHTML($property, $value, $control);
			}
		}

		return $result;
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE","DESCRIPTION") -- here comes HTML form value
	//strHTMLControlName - array("VALUE","DESCRIPTION")
	//return:
	//safe html

	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		global $USER;
		$default_value = intval($value["VALUE"]);
		$res = "";
		if ($default_value == $USER->GetID())
		{
			$select = "CU";
			$res = "[<a title='".GetMessage("MAIN_EDIT_USER_PROFILE")."'  href='/bitrix/admin/user_edit.php?ID=".$USER->GetID()."&lang=".LANG."'>".$USER->GetID()."</a>] (".htmlspecialcharsbx($USER->GetLogin()).") ".htmlspecialcharsbx($USER->GetFirstName())." ".htmlspecialcharsbx($USER->GetLastName());
		}
		elseif ($default_value > 0)
		{
			$select = "SU";
			$rsUsers = CUser::GetList('', '', array("ID" => $default_value));
			if ($arUser = $rsUsers->Fetch())
				$res = "[<a title='".GetMessage("MAIN_EDIT_USER_PROFILE")."'  href='/bitrix/admin/user_edit.php?ID=".$arUser["ID"]."&lang=".LANG."'>".$arUser["ID"]."</a>] (".htmlspecialcharsbx($arUser["LOGIN"]).") ".htmlspecialcharsbx($arUser["NAME"])." ".htmlspecialcharsbx($arUser["LAST_NAME"]);
			else
				$res = "&nbsp;".GetMessage("MAIN_NOT_FOUND");
		}
		else
		{
			$select = "none";
			$default_value = "";
		}
		$name_x = preg_replace("/([^a-z0-9])/is", "x", $strHTMLControlName["VALUE"]);
		if (trim($strHTMLControlName["FORM_NAME"]) == '')
			$strHTMLControlName["FORM_NAME"] = "form_element";

		$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");

		ob_start();
		?><select id="SELECT<?=htmlspecialcharsbx($strHTMLControlName["VALUE"])?>" name="SELECT<?=htmlspecialcharsbx($strHTMLControlName["VALUE"])?>" onchange="if(this.value == 'none')
						{
							var v=document.getElementById('<?=htmlspecialcharsbx($strHTMLControlName["VALUE"])?>');
							v.value = '';
							v.readOnly = true;
							document.getElementById('FindUser<?=$name_x?>').disabled = true;
						}
						else
						{
							var v=document.getElementById('<?=htmlspecialcharsbx($strHTMLControlName["VALUE"])?>');
							v.value = this.value == 'CU'?'<?=$USER->GetID()?>':'';
							v.readOnly = false;
							document.getElementById('FindUser<?=$name_x?>').disabled = false;
						}">
					<option value="none"<?if($select=="none")echo " selected"?>><?=GetMessage("IBLOCK_PROP_USERID_NONE")?></option>
					<option value="CU"<?if($select=="CU")echo " selected"?>><?=GetMessage("IBLOCK_PROP_USERID_CURR")?></option>
					<option value="SU"<?if($select=="SU")echo " selected"?>><?=GetMessage("IBLOCK_PROP_USERID_OTHR")?></option>
				</select>&nbsp;
				<?echo FindUserIDNew(
					htmlspecialcharsbx($strHTMLControlName["VALUE"]),
					$value["VALUE"],
					$res,
					htmlspecialcharsEx($strHTMLControlName["FORM_NAME"]),
					$select,
					"3",
					"",
					"...",
					"typeinput",
					"tablebodybutton",
			$selfFolderUrl."user_search.php"
	);
			$return = ob_get_contents();
			ob_end_clean();
		return  $return;
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//array of error messages
	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//DB form of the value
	public static function ConvertToDB($arProperty, $value)
	{
		$value["VALUE"] = intval($value["VALUE"]);
		if($value["VALUE"] <= 0)
			$value["VALUE"] = "";
		return $value;
	}

	public static function ConvertFromDB($arProperty, $value)
	{
		$value["VALUE"] = intval($value["VALUE"]);
		if($value["VALUE"] <= 0)
			$value["VALUE"] = "";
		return $value;
	}

	public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = array(
			"HIDE" => array("WITH_DESCRIPTION"),
		);
		return '';
	}

	public static function AddFilterFields($arProperty, $control, &$arFilter, &$filtered)
	{
		$filtered = false;
		$filterValue = self::getFilterValue($control);

		if ($filterValue !== null)
		{
			$arFilter["=PROPERTY_".$arProperty["ID"]] = $filterValue;
			$filtered = true;
		}
	}

	public static function GetAdminFilterHTML($property, $control)
	{
		$controlName = $control["VALUE"];

		$value = (string)self::getFilterValue($control);
		return '<input type="text" name="'.$controlName.'" value="'.htmlspecialcharsbx($value).'" size="30">';
	}

	/**
	 * @param array $property
	 * @param array $strHTMLControlName
	 * @param array &$fields
	 * @return void
	 */
	public static function GetUIFilterProperty($property, $strHTMLControlName, &$fields)
	{
		$fields["type"] = "custom_entity";
		$fields["filterable"] = "";
		$fields["selector"] = array("type" => "user");
		$fields["operators"] = array("default" => "=");
	}

	private static function getFilterValue($control)
	{
		$filterValue = null;

		$controlName = $control["VALUE"];

		if (isset($GLOBALS[$controlName]) && !is_array($GLOBALS[$controlName]))
		{
			if (is_int($GLOBALS[$controlName]))
			{
				$filterValue = $GLOBALS[$controlName];
			}
			elseif (is_string($GLOBALS[$controlName]))
			{
				$rawValue = trim($GLOBALS[$controlName]);
				if ($rawValue !== '')
					$filterValue = (int)$rawValue;
			}
		}

		return $filterValue;
	}

	/**
	 * Returns property description for ui.entity-editor extension
	 * @param $property
	 * @param $value
	 * @return string[]
	 */
	public static function GetUIEntityEditorProperty($property, $value): array
	{
		return [
			'type' => 'custom',
		];
	}

	/**
	 * Returns HTML of property in Edit mode in entity editors - ui.entity-selector extension is used
	 * @param array $params
	 * @return string
	 */
	public static function GetUIEntityEditorPropertyEditHtml(array $params = []): string
	{
		\Bitrix\Main\UI\Extension::load(['ui.entity-selector', 'ui.buttons', 'ui.forms']);
		$fieldName = $params['FIELD_NAME'];

		$containerId = $fieldName . '_container';
		$inputsContainerId = $fieldName . '_inputs_container';

		$isMultiple = $params['SETTINGS']['MULTIPLE'] === 'Y' ? 'true' : 'false';

		if (!is_array($params['VALUE']))
		{
			if (empty($params['VALUE']))
			{
				$params['VALUE'] = [];
			}
			else
			{
				$params['VALUE'] = [$params['VALUE']];
			}
		}

		$preselectedItems = [];
		foreach ($params['VALUE'] as $value)
		{
			$preselectedItems[] = ['user', $value];
		}
		$dialogItems = \Bitrix\UI\EntitySelector\Dialog::getPreselectedItems($preselectedItems)->toArray();

		foreach ($dialogItems as $index => $item)
		{
			/** @var \Bitrix\UI\EntitySelector\Item $item */
			if (isset($item['hidden'], $item['id']))
			{
				$userId = (int)($item['id']);
				$user = self::getUserArray($userId);
				unset(
					$dialogItems[$index]['searchable'],
					$dialogItems[$index]['saveable'],
					$dialogItems[$index]['hidden'],
					$dialogItems[$index]['deselectable'],
					$dialogItems[$index]['avatar'],
					$dialogItems[$index]['link']
				);
				if ($user)
				{
					$dialogItems[$index]['title'] = $user['NAME'] . ' ' . $user['LAST_NAME'];
				}
			}
		}
		$selectedItems = Json::encode($dialogItems);

		return <<<HTML
			<div id="{$containerId}" name="{$containerId}"></div>
			<div id="{$inputsContainerId}" name="{$inputsContainerId}"></div>
			<script>
				(function() {
					var selector = new BX.UI.EntitySelector.TagSelector({
						id: '{$containerId}',
						multiple: {$isMultiple},
						
						dialogOptions: {
							height: 300,
							id: '{$containerId}',
							multiple: {$isMultiple},
							selectedItems: {$selectedItems},
							
							events: {
								'Item:onSelect': setSelectedInputs.bind(this, 'Item:onSelect'),
								'Item:onDeselect': setSelectedInputs.bind(this, 'Item:onDeselect'),
							},
							
							entities: [
								{
									id: 'user',
									options: {
										'inviteEmployeeLink': false,
									},
								},
							],
					},
					})
					
					function setSelectedInputs(eventName, event)
					{
						var dialog = event.getData().item.getDialog();
						if (!dialog.isMultiple())
						{
							dialog.hide();
						}
						var selectedItems = dialog.getSelectedItems();
						if (Array.isArray(selectedItems))
						{
							var htmlInputs = '';
							selectedItems.forEach(function(item)
							{
								htmlInputs +=
									'<input type="hidden" name="{$fieldName}[]" value="' + item['id'] + '" />'
								;
							});
							if (htmlInputs === '')
							{
								htmlInputs =
									'<input type="hidden" name="{$fieldName}[]" value="" />'
								;
							}
							document.getElementById('{$inputsContainerId}').innerHTML = htmlInputs;
							BX.Event.EventEmitter.emit('onChangeUser');
						}
					}
					
					selector.renderTo(document.getElementById('{$containerId}'));
				})();
			
			</script>
HTML;
	}

	/**
	 * Returns HTML of property in View mode in entity editors
	 * @param array $params
	 * @return string
	 */
	public static function GetUIEntityEditorPropertyViewHtml(array $params = []): string
	{
		$result = [];
		if (!is_array($params['VALUE']))
		{
			$params['VALUE'] = [$params['VALUE']];
		}

		$nameFormat = CSite::GetNameFormat();
		foreach ($params['VALUE'] as $userId)
		{
			$userId = (int)$userId;
			$user = static::getUserArray($userId);
			if ($user)
			{
				$result[] = '<a href="/company/personal/user/' . $userId . '/">' . CUser::FormatName($nameFormat, $user) . '</a>';
			}
		}

		return implode(', ', $result);
	}

	/**
	 * Returns user data with keys from b_user table
	 * @param int $userId
	 * @return array|false|mixed
	 */
	private static function getUserArray(int $userId)
	{
		if (!isset(self::$cache[$userId]))
		{
			$userResult = \Bitrix\Main\UserTable::getRow([
				'select' => [
					'ID',
					'NAME',
					'LAST_NAME',
					'SECOND_NAME',
					'TITLE',
					'LOGIN',
					'EMAIL',
				],
				'filter' => ['ID' => $userId],
			]);
			if ($userResult)
			{
				self::$cache[$userId] = $userResult;
			}
			else
			{
				self::$cache[$userId] = [];
			}
		}

		return self::$cache[$userId];
	}

	/**
	 * Returns HTML of property in View mode in public section
	 * @param array $property
	 * @param array $value
	 * @param $control
	 * @return string
	 */
	public static function getPublicViewHTML(array $property, array $value, $control): string
	{
		$userId = (int)$value['VALUE'];
		$user = static::getUserArray($userId);
		if ($user)
		{
			$link = '[<a href="/company/personal/user/' . $userId . '/">' . $userId . '</a>] ';
			$login = '(' . htmlspecialcharsbx($user['LOGIN']) . ') ';
			$nameFormat = CSite::GetNameFormat();
			$name = CUser::FormatName($nameFormat, $user);

			return $link . $login . $name;
		}

		return '';
	}
}

function FindUserIDNew($tag_name, $tag_value, $user_name="", $form_name = "form1", $select="none", $tag_size = "3", $tag_maxlength="", $button_value = "...", $tag_class="typeinput", $button_class="tablebodybutton", $search_page="/bitrix/admin/user_search.php")
{
	global $APPLICATION, $USER;
	$tag_name_x = preg_replace("/([^a-z0-9])/is", "x", $tag_name);
	$tag_name_escaped = CUtil::JSEscape($tag_name);

	if($APPLICATION->GetGroupRight("main") >= "R")
	{
		$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");

		$strReturn = "
<input type=\"text\" name=\"".$tag_name."\" id=\"".$tag_name."\" value=\"".($select=="none"?"":$tag_value)."\" size=\"".$tag_size."\" maxlength=\"".$tag_maxlength."\" class=\"".$tag_class."\">
<IFRAME style=\"width:0px; height:0px; border: 0px\" src=\"javascript:void(0)\" name=\"hiddenframe".$tag_name."\" id=\"hiddenframe".$tag_name."\"></IFRAME>
<input class=\"".$button_class."\" type=\"button\" name=\"FindUser".$tag_name_x."\" id=\"FindUser".$tag_name_x."\" OnClick=\"window.open('".$search_page."?lang=".LANGUAGE_ID."&FN=".$form_name."&FC=".$tag_name_escaped."', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));\" value=\"".$button_value."\" ".($select=="none"?"disabled":"").">
<span id=\"div_".$tag_name."\">".$user_name."</span>
<script>
";
		if($user_name=="")
			$strReturn.= "var tv".$tag_name_x."='';\n";
		else
			$strReturn.= "var tv".$tag_name_x."='".CUtil::JSEscape($tag_value)."';\n";

		$strReturn.= "
function Ch".$tag_name_x."()
{
	var DV_".$tag_name_x.";
	DV_".$tag_name_x." = document.getElementById(\"div_".$tag_name_escaped."\");
	if (!!DV_".$tag_name_x.")
	{
		if (
			document.".$form_name."
			&& document.".$form_name."['".$tag_name_escaped."']
			&& typeof tv".$tag_name_x." != 'undefined'
			&& tv".$tag_name_x." != document.".$form_name."['".$tag_name_escaped."'].value
		)
		{
			tv".$tag_name_x."=document.".$form_name."['".$tag_name_escaped."'].value;
			if (tv".$tag_name_x."!='')
			{
				DV_".$tag_name_x.".innerHTML = '<i>".GetMessage("MAIN_WAIT")."</i>';

				if (tv".$tag_name_x."!=".intval($USER->GetID()).")
				{
					document.getElementById(\"hiddenframe".$tag_name_escaped."\").src='".$selfFolderUrl."get_user.php?ID=' + tv".$tag_name_x."+'&strName=".$tag_name_escaped."&lang=".LANGUAGE_ID.(defined("ADMIN_SECTION") && ADMIN_SECTION===true?"&admin_section=Y":"")."';
					document.getElementById('SELECT".$tag_name_escaped."').value = 'SU';
				}
				else
				{
					DV_".$tag_name_x.".innerHTML = '".CUtil::JSEscape("[<a title=\"".GetMessage("MAIN_EDIT_USER_PROFILE")."\" class=\"tablebodylink\" href=\"/bitrix/admin/user_edit.php?ID=".$USER->GetID()."&lang=".LANGUAGE_ID."\">".$USER->GetID()."</a>] (".htmlspecialcharsbx($USER->GetLogin()).") ".htmlspecialcharsbx($USER->GetFirstName())." ".htmlspecialcharsbx($USER->GetLastName()))."';
					document.getElementById('SELECT".$tag_name_escaped."').value = 'CU';
				}
			}
			else
			{
				DV_".$tag_name_x.".innerHTML = '';
			}
		}
		else if (
			DV_".$tag_name_x."
			&& DV_".$tag_name_x.".innerHTML.length > 0
			&& document.".$form_name."
			&& document.".$form_name."['".$tag_name_escaped."']
			&& document.".$form_name."['".$tag_name_escaped."'].value == ''
		)
		{
			document.getElementById('div_".$tag_name."').innerHTML = '';
		}
	}
	setTimeout(function(){Ch".$tag_name_x."()},1000);
}
Ch".$tag_name_x."();
//-->
</script>
";
	}
	else
	{
		$strReturn = "
			<input type=\"text\" name=\"$tag_name\" id=\"$tag_name\" value=\"$tag_value\" size=\"$tag_size\" maxlength=\"strMaxLenght\">
			<input type=\"button\" name=\"FindUser".$tag_name_x."\" id=\"FindUser".$tag_name_x."\" OnClick=\"window.open('".$search_page."?lang=".LANGUAGE_ID."&FN=$form_name&FC=$tag_name_escaped', '', 'scrollbars=yes,resizable=yes,width=760,height=560,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));\" value=\"$button_value\">
			$user_name
			";
	}
	return $strReturn;
}