<?php
/**
 * Created by PhpStorm.
 * User: sigurd
 * Date: 26.09.17
 * Time: 19:26
 */

namespace Bitrix\Rest\UserField;


use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\TypeBase;
use Bitrix\Rest\Api\UserFieldType;
use Bitrix\Rest\PlacementTable;

class Type extends TypeBase
{
	const USER_TYPE_ID = 'rest';

	public static function getDBColumnType()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		return $helper->getColumnTypeByField(new \Bitrix\Main\ORM\Fields\TextField('x'));
	}

	public static function getPublicView($arUserField, $arAdditionalParameters = array())
	{
		$value = static::getFieldValue($arUserField, $arAdditionalParameters);

		$arUserField['VALUE'] = $value;

		return static::getApplication($arUserField, array(), 'view');
	}

	public static function getPublicEdit($arUserField, $arAdditionalParameters = array())
	{
		$fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
		$value = static::getFieldValue($arUserField, $arAdditionalParameters);

		$arUserField['VALUE'] = $value;

		return static::getApplication($arUserField, array(
			'NAME' => $fieldName
		), 'edit');
	}

	function getEditFormHTML($arUserField, $arHtmlControl)
	{
		return static::getApplication($arUserField, $arHtmlControl, 'edit');
	}

	function getEditFormHTMLMulty($arUserField, $arHtmlControl)
	{
		return static::getApplication($arUserField, $arHtmlControl, 'edit');
	}

	protected static function getApplication($arUserField, $arHtmlControl, $mode = 'edit')
	{
		global $APPLICATION;

		$fieldName = $arHtmlControl['NAME'];
		$value = static::normalizeFieldValue($arUserField['VALUE']);

		$placementHandlerList = PlacementTable::getHandlersList(UserFieldType::PLACEMENT_UF_TYPE);
		$currentPlacementHandler = null;
		foreach($placementHandlerList as $placementInfo)
		{
			if($arUserField['USER_TYPE_ID'] === Callback::getUserTypeId($placementInfo))
			{
				$currentPlacementHandler = $placementInfo;
				break;
			}
		}

		$placementOptions = [
			'MODE' => $mode,
			'ENTITY_ID' => $arUserField['ENTITY_ID'],
			'FIELD_NAME' => $arUserField['FIELD_NAME'],
			'ENTITY_VALUE_ID' => $arUserField['ENTITY_VALUE_ID'],
			'VALUE' => $arUserField['MULTIPLE'] === 'N' ? $value[0] : $value,
			'MULTIPLE' => $arUserField['MULTIPLE'],
			'MANDATORY' => $arUserField['MANDATORY'],
			'XML_ID' => $arUserField['XML_ID'],
		];

		$event = new \Bitrix\Main\Event('rest', 'OnUserFieldPlacementPrepareParams', [
			$arUserField,
			&$placementOptions,
		]);
		$event->send();

		$html = '';
		if($currentPlacementHandler !== null)
		{
			ob_start();

			if($mode === 'edit'):
?>
<div style="display: none;" id="uf_rest_value_<?=$arUserField['FIELD_NAME']?>">
<?
				foreach($value as $res):
?>
	<input type="hidden" name="<?=$fieldName?>" value="<?=HtmlFilter::encode($res)?>" />
<?
				endforeach;
			endif;
?>
</div>
<?
			$placementSid = $APPLICATION->includeComponent(
				'bitrix:app.layout',
				'',
				array(
					'ID' => $currentPlacementHandler['APP_ID'],
					'PLACEMENT_ID' => $currentPlacementHandler['ID'],
					'PLACEMENT' => UserFieldType::PLACEMENT_UF_TYPE,
					'SHOW_LOADER' => 'N',
					'SET_TITLE' => 'N',
					'PLACEMENT_OPTIONS' => $placementOptions,
					'PARAM' => array(
						'FRAME_HEIGHT' => '200px',
					)
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);
?>
<script>
	(function(){
		'use strict';

		new BX.rest.UserField('<?=$placementSid?>', {
			value: <?=\CUtil::phpToJsObject($arUserField['VALUE'])?>,
			callback: function(value)
			{
<?
			if($mode === 'edit'):
?>
				if(!BX.type.isArray(value))
				{
					value = [value];
				}

				var html = '';
				for(var i = 0; i < value.length; i++)
				{
					html += '<input type="hidden" name="<?=$fieldName?>" value="'+BX.util.htmlspecialchars(value[i])+'" />';
				}

				BX('uf_rest_value_<?=$arUserField['FIELD_NAME']?>').innerHTML = html;

				var input = BX('uf_rest_value_<?=$arUserField['FIELD_NAME']?>').firstChild;
				if(input)
				{
					BX.fireEvent(input, 'change');
				}
<?
			endif;
?>
			}
		});

	})();
</script>

<?
			$html = ob_get_clean();
		}

		\CJSCore::init(array('rest_userfield'));

		return static::getHelper()->wrapDisplayResult($html);

	}

	function prepareSettings($arUserField){}
	function getSettingsHTML($arUserField, $arHtmlControl, $bVarsFromForm){}
	function getFilterHTML($arUserField, $arHtmlControl){}
	function getFilterData($arUserField, $arHtmlControl){}
	function getAdminListViewHTML($arUserField, $arHtmlControl){}
	function getAdminListViewHTMLMulty($arUserField, $arHtmlControl){}
	function getAdminListEditHTML($arUserField, $arHtmlControl){}
	function getAdminListEditHTMLMulty($arUserField, $arHtmlControl){}
}