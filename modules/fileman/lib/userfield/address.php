<?php

namespace Bitrix\Fileman\UserField;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Fileman\UserField\Types\AddressType;

Loc::loadMessages(__FILE__);

class Address extends \Bitrix\Main\UserField\TypeBase
{
	const USER_TYPE_ID = AddressType::USER_TYPE_ID;

	const BITRIX24_RESTRICTION = AddressType::BITRIX24_RESTRICTION;
	const BITRIX24_RESTRICTION_CODE = AddressType::BITRIX24_RESTRICTION_CODE;

	protected static $restrictionCount = null;

	function getUserTypeDescription()
	{
		return AddressType::getUserTypeDescription();
	}

	public static function getApiKey()
	{
		return AddressType::getApiKey();
	}

	public static function getApiKeyHint()
	{
		return AddressType::getApiKeyHint();
	}

	public static function getTrialHint()
	{
		return AddressType::getTrialHint();
	}

	public static function canUseMap()
	{
		return AddressType::canUseMap();
	}

	public static function checkRestriction()
	{
		return AddressType::checkRestriction();
	}

	public static function useRestriction()
	{
		return AddressType::useRestriction();
	}

	function prepareSettings($userField)
	{
		return AddressType::prepareSettings($userField);
	}

	function getDbColumnType($userField)
	{
		return AddressType::getDbColumnType();
	}

	function checkFields($userField, $value)
	{
		return AddressType::checkFields($userField, $value);
	}

	function onBeforeSave($userField, $value)
	{
		return AddressType::onBeforeSave($userField, $value);
	}

	function getSettingsHtml($userField = false, $additionalParameters, $varsFromForm)
	{
		return AddressType::renderSettings($userField, $additionalParameters, $varsFromForm);
	}

	function getEditFormHtml($userField, $additionalParameters)
	{
		return AddressType::renderEditForm($userField, $additionalParameters);
	}

	function getEditFormHtmlMulty($userField, $additionalParameters)
	{
		return AddressType::renderEditForm($userField, $additionalParameters);
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

	public static function getPublicEdit($userField, $additionalParameters = array())
	{
		return AddressType::RenderEdit($userField, $additionalParameters);
	}

	public static function getPublicView($userField, $additionalParameters = array())
	{
		return AddressType::renderView($userField, $additionalParameters);
	}

	public static function getPublicText($userField)
	{
		return AddressType::renderText($userField);
	}

	public static function getAdminListViewHtml($userField, $additionalParameters){
		return AddressType::renderAdminListView($userField, $additionalParameters);
	}

	public static function getAdminListEditHtml($userField, $additionalParameters){
		return AddressType::renderAdminListEdit($userField, $additionalParameters);
	}

	protected static function parseValue($value)
	{
		return AddressType::parseValue($value);
	}
}