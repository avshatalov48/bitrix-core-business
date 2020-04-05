<?php
namespace Bitrix\Fileman\UserField;


use Bitrix\Bitrix24\RestrictionCounter;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);


class Address extends \Bitrix\Main\UserField\TypeBase
{
	const USER_TYPE_ID = 'address';

	const BITRIX24_RESTRICTION = 100;
	const BITRIX24_RESTRICTION_CODE = 'uf_address';

	protected static $restrictionCount = null;

	function getUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_ADDRESS_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_STRING,
			"EDIT_CALLBACK" => array(__CLASS__, 'getPublicEdit'),
			"VIEW_CALLBACK" => array(__CLASS__, 'getPublicView'),
		);
	}

	public static function getApiKey()
	{
		$apiKey = Option::get('fileman', 'google_map_api_key', '');
		if(Loader::includeModule('bitrix24') && \CBitrix24::isCustomDomain())
		{
			$apiKey = '';

			$key = Option::get('bitrix24', 'google_map_api_key', '');
			$keyHost = Option::get('bitrix24', 'google_map_api_key_host', '');
			if(strlen($keyHost) > 0)
			{
				if($keyHost === BX24_HOST_NAME)
				{
					$apiKey = $key;
				}
			}
		}

		return $apiKey;
	}

	public static function getApiKeyHint()
	{
		$hint = '';
		if(static::getApiKey() === '')
		{
			if(Loader::includeModule('bitrix24'))
			{
				if(\CBitrix24::isCustomDomain())
				{
					$hint = Loc::getMessage(
						'USER_TYPE_ADDRESS_NO_KEY_HINT_B24',
						array(
							'#settings_path#' => \CBitrix24::PATH_CONFIGS
						)
					);
				}
			}
			else
			{
				if(defined('ADMIN_SECTION') && ADMIN_SECTION === true)
				{
					$settingsPath = '/bitrix/admin/settings.php?lang='.LANGUAGE_ID.'&mid=fileman';
				}
				else
				{
					$settingsPath = SITE_DIR.'configs/';
				}

				if(
					!file_exists($_SERVER['DOCUMENT_ROOT'].$settingsPath)
					|| !is_dir($_SERVER['DOCUMENT_ROOT'].$settingsPath)
				)
				{
					$settingsPath = SITE_DIR.'settings/configs/';
				}

				$hint = Loc::getMessage(
					'USER_TYPE_ADDRESS_NO_KEY_HINT',
					array(
						'#settings_path#' => $settingsPath
					)
				);
			}
		}

		return $hint;
	}

	public static function getTrialHint()
	{
		if(static::useRestriction() && !static::checkRestriction())
		{
			\CBitrix24::initLicenseInfoPopupJS(static::BITRIX24_RESTRICTION_CODE);

			return array(
				Loc::getMessage('USER_TYPE_ADDRESS_TRIAL_TITLE'),
				Loc::getMessage('USER_TYPE_ADDRESS_TRIAL'),
			);
		}
		else
		{
			return false;
		}
	}

	public static function canUseMap()
	{
		return static::getApiKey() !== '' && static::checkRestriction();
	}

	public static function checkRestriction()
	{
		if(
			static::useRestriction()
			&& static::$restrictionCount === null
			&& Loader::includeModule('bitrix24')
		)
		{
			static::$restrictionCount = RestrictionCounter::get(static::BITRIX24_RESTRICTION_CODE);
		}

		return static::$restrictionCount < static::BITRIX24_RESTRICTION;
	}

	public static function useRestriction()
	{
		return Loader::includeModule('bitrix24') && !\CBitrix24::IsLicensePaid() && !\CBitrix24::IsNfrLicense();
	}

	function PrepareSettings($arUserField)
	{
		return array(
			"SHOW_MAP" => $arUserField["SETTINGS"]["SHOW_MAP"] === 'N' ? 'N' : 'Y',
		);
	}

	function GetDBColumnType($arUserField)
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql":
				return "text";
		}
	}

	function CheckFields($arUserField, $value)
	{
		return array();
	}

	function OnBeforeSave($arUserField, $value)
	{
		if(static::useRestriction() && static::checkRestriction() && strlen($value) > 0 && strpos($value, '|') >= 0)
		{
			if($arUserField['MULTIPLE'] === 'Y')
			{
				$increment = !is_array($arUserField['VALUE']) || !in_array($value, $arUserField['VALUE']);
			}
			else
			{
				$increment = $arUserField['VALUE'] !== $value;
			}

			if($increment && Loader::includeModule('bitrix24'))
			{
				RestrictionCounter::increment(static::BITRIX24_RESTRICTION_CODE);
			}
		}

		return $value;
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';
		if($bVarsFromForm)
		{
			$value = $GLOBALS[$arHtmlControl["NAME"]]["SHOW_MAP"] === 'N' ? 'N' : 'Y';
		}
		elseif(is_array($arUserField))
		{
			$value = $arUserField["SETTINGS"]["DEFAULT_VALUE"] === 'N' ? 'N' : 'Y';
		}
		else
		{
			$value = "Y";
		}
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_ADDRESS_SHOW_MAP").':</td>
			<td>
				<input type="hidden" name="'.$arHtmlControl["NAME"].'[SHOW_MAP]" value="N">
				<label><input type="checkbox" name="'.$arHtmlControl["NAME"].'[SHOW_MAP]" value="Y" '.($value === 'Y' ? ' checked="checked"' : '').'> '.GetMessage('MAIN_YES').'</label>
			</td>
		</tr>
		';

		/// start position

		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		return static::getEdit($arUserField, $arHtmlControl);
	}

	function GetEditFormHtmlMulty($arUserField, $arHtmlControl)
	{
		return static::getEdit($arUserField, $arHtmlControl);
	}

	protected static function getEdit($arUserField, $arHtmlControl)
	{
		$html = '';
		\CJSCore::Init(array('userfield_address', 'google_map'));

		if(static::canUseMap())
		{
			ob_start();

			$controlId = $arUserField['FIELD_NAME'];
?>
<div id="<?=$controlId?>"></div>
<span style="display: none;" id="<?=HtmlFilter::encode($arUserField['FIELD_NAME'])?>_result"></span>
<script>
	(function(){
		'use strict';

		var control = new BX.Fileman.UserField.Address(BX('<?=$controlId?>'), {
			value: <?=\CUtil::PhpToJsObject(static::normalizeFieldValue($arUserField['VALUE']))?>,
			multiple: <?=$arUserField['MULTIPLE'] === 'Y' ? 'true' : 'false'?>
		});
		BX.addCustomEvent(control, 'UserFieldAddress::Change', function(value)
		{
			var node = BX('<?=\CUtil::JSEscape($arUserField['FIELD_NAME'])?>_result');
			var html = '';
			if(value.length === 0)
			{
				value = [{text:''}];
			}

			for(var i = 0; i < value.length; i++)
			{
				var inputValue = value[i].text;

				if(!!value[i].coords)
				{
					inputValue += '|' + value[i].coords.join(';');
				}

				html += '<input type="hidden" name="<?=$arHtmlControl['NAME']?>" value="'+BX.util.htmlspecialchars(inputValue)+'" />';
			}

			node.innerHTML = html;
		});
	})();
</script>
<?
			$html = ob_get_clean();
		}
		else
		{
			$value = static::normalizeFieldValue($arUserField['VALUE']);

			$first = true;
			foreach($value as $res)
			{
				if(!$first)
				{
					$html .= static::getHelper()->getMultipleValuesSeparator();
				}
				$first = false;

				list($text, $coords) = static::parseValue($res);

				$attrList = array(
					'type' => 'text',
					'class' => static::getHelper()->getCssClassName(),
					'name' => $arHtmlControl['NAME'],
					'value' => $text,
				);

				if(static::useRestriction() && !static::checkRestriction())
				{
					$attrList['onfocus'] = 'BX.Fileman.UserField.addressSearchRestriction.show(this)';
				}
				elseif(static::getApiKey() === '')
				{
					$attrList['onfocus'] = 'BX.Fileman.UserField.addressKeyRestriction.show(this)';
				}

				$html .= static::getHelper()->wrapSingleField('<input '.static::buildTagAttributes($attrList).'/>');
			}

			if($arUserField["MULTIPLE"] == "Y")
			{
				$html .= static::getHelper()->getCloneButton($arHtmlControl['NAME']);
			}
		}

		return $html;
	}

	public static function getPublicEdit($arUserField, $arAdditionalParameters = array())
	{
		$fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
		$arUserField['VALUE'] = static::getFieldValue($arUserField, $arAdditionalParameters);

		$html = static::getEdit($arUserField, array(
			'NAME' => $fieldName,
		));

		static::initDisplay();

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function getPublicView($arUserField, $arAdditionalParameters = array())
	{
		$value = static::normalizeFieldValue($arUserField["VALUE"]);

		$html = '';
		$first = true;

		foreach($value as $res)
		{
			if(strlen($res) > 0)
			{
				if(!$first)
				{
					$html .= static::getHelper()->getMultipleValuesSeparator();
				}

				$first = false;

				list($text, $coords) = static::parseValue($res);

				if(strlen($text) > 0)
				{
					if(!$arAdditionalParameters['printable'] && $coords && static::getApiKey() !== '')
					{
						$res = '<a href="javascript:void(0)" onmouseover="BX.Fileman.UserField.addressSearchResultDisplayMap.showHover(this, '.HtmlFilter::encode(\CUtil::PhpToJSObject(array('text' => $text, 'coords' => $coords))).');" onmouseout="BX.Fileman.UserField.addressSearchResultDisplayMap.closeHover(this)">'.HtmlFilter::encode($text).'</a>';
					}
					else
					{
						$res = HtmlFilter::encode($text);
					}

					$html .= static::getHelper()->wrapSingleField($res);
				}
			}
		}

		static::initDisplay(array('userfield_address', 'google_map'));

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function getPublicText($userField)
	{
		$value = static::normalizeFieldValue($userField['VALUE']);

		$text = '';
		$first = true;
		foreach ($value as $res)
		{
			if ($res == '')
				continue;

			list($descr, $coords) = static::parseValue($res);

			if ($descr == '')
				continue;

			if (!$first)
				$text .= ', ';
			$first = false;

			$text .= $coords != '' ? sprintf('%s (%s)', $descr, join(', ', $coords)) : $descr;
		}

		return $text;
	}

	protected static function parseValue($value)
	{
		$coords = '';
		if(strpos($value, '|') !== false)
		{
			list($value, $coords) = explode('|', $value);
			if(strlen($coords) > 0 && strpos($coords, ';') !== false)
			{
				$coords = explode(';', $coords);
			}
			else
			{
				$coords = '';
			}
		}

		return array($value, $coords);
	}
}