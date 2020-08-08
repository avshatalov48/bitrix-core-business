<?php
namespace Bitrix\Fileman\UserField;

use Bitrix\Main\Security\Random;
use Bitrix\Main\Text\GeoHash;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);

/**
 * Class Geo
 * @deprecated
 */
class Geo extends \Bitrix\Main\UserField\TypeBase
{
	const USER_TYPE_ID = 'geo';

	function getUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_GEO_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_STRING,
			"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
			"VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
		);
	}

	function getDBColumnType($arUserField)
	{
		global $DB;
		switch($DB->type)
		{
			case "MYSQL":
				return "varchar(100)";
		}
	}

	function prepareSettings($arUserField)
	{
		$scale = intval($arUserField["SETTINGS"]["INIT_MAP_SCALE"]);
		$lat = doubleval($arUserField["SETTINGS"]["INIT_MAP_LAT"]);
		$lon = doubleval($arUserField["SETTINGS"]["INIT_MAP_LON"]);

		return array(
			'INIT_MAP_SCALE' => $scale,
			'INIT_MAP_LAT' => $lat,
			'INIT_MAP_LON' => $lon,
		);
	}

	function onBeforeSave($userField, $value)
	{
		if($value <> '')
		{
			$encodedValue = GeoHash::encode(explode(';', $value));
			$value = $encodedValue;
		}

		return $value;
	}

	public function onAfterFetch($userfield, $fetched)
	{
		if($fetched['VALUE'] <> '')
		{
			$decodedValue = implode(';', GeoHash::decode($fetched['VALUE']));
			$fetched['VALUE'] = $decodedValue;
		}

		return $fetched['VALUE'];
	}

	function getSettingsHtml($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		global $APPLICATION;

		$mapId = (is_array($arUserField) ? $arUserField['FIELD_NAME'] : '_new') .'_settings';

		$result = '';
		if($bVarsFromForm)
		{
			$scale = intval($GLOBALS[$arHtmlControl["NAME"]]["INIT_MAP_SCALE"]);
			$lat = doubleval($GLOBALS[$arHtmlControl["NAME"]]["INIT_MAP_LAT"]);
			$lon = doubleval($GLOBALS[$arHtmlControl["NAME"]]["INIT_MAP_LON"]);
		}
		elseif(is_array($arUserField))
		{
			$scale = intval($arUserField["SETTINGS"]["INIT_MAP_SCALE"]);
			$lat = doubleval($arUserField["SETTINGS"]["INIT_MAP_LAT"]);
			$lon = doubleval($arUserField["SETTINGS"]["INIT_MAP_LON"]);
		}
		else
		{
			$scale = 0;
			$lat = 0;
			$lon = 0;
		}

		$result .= '<tr>
			<td valign="top">'.GetMessage("USER_TYPE_GEO_INIT_MAP").':</td>
			<td>';

		ob_start();

		$APPLICATION->IncludeComponent('bitrix:map.google.system', '', array(
			'MAP_ID' => $mapId,
			'INIT_MAP_SCALE' => $scale,
			'INIT_MAP_LAT' => $lat,
			'INIT_MAP_LON' => $lon,
		));
?>
		<script>
			var mapId = '<?=$mapId?>';

			function waitForMap()
			{
				if(window.GLOBAL_arMapObjects === null)
				{
					return;
				}

				if(window.GLOBAL_arMapObjects[mapId] && window.google && window.google.maps && window.google.maps.event)
				{
					var map = window.GLOBAL_arMapObjects[mapId];

					var getDataFromMap = function()
					{
						BX('init_scale').value = map.getZoom();
						BX('init_lat').value = map.getCenter().lat();
						BX('init_lng').value = map.getCenter().lng();
					};

					map.addListener('bounds_changed', getDataFromMap);

					getDataFromMap();
				}
				else
				{
					setTimeout(waitForMap, 300);
				}
			}

			waitForMap();
		</script>
<?
		$result .= ob_get_clean();

		$result .= '
			</td>
		</tr>';


		$result .= '<tr>
			<td>'.GetMessage("USER_TYPE_GEO_INIT_MAP_SCALE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[INIT_MAP_SCALE]" size="5"  maxlength="5" readonly="readonly" value="'.$scale.'" id="init_scale" />
			</td>
		</tr>';
		$result .= '<tr>
			<td>'.GetMessage("USER_TYPE_GEO_INIT_MAP_LAT").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[INIT_MAP_LAT]" size="20"  maxlength="50" readonly="readonly" value="'.$lat.'" id="init_lat" />
			</td>
		</tr>';
		$result .= '<tr>
			<td>'.GetMessage("USER_TYPE_GEO_INIT_MAP_LON").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[INIT_MAP_LON]" size="20"  maxlength="50" readonly="readonly" value="'.$lon.'" id="init_lng" />
			</td>
		</tr>';



		return $result;
	}

	function getEditFormHTML($arUserField, $arHtmlControl)
	{
		return static::showAdminEdit($arUserField, $arHtmlControl, false);
	}

	function getEditFormHTMLMulty($arUserField, $arHtmlControl)
	{
		return static::showAdminEdit($arUserField, $arHtmlControl, true);
	}

	protected function showAdminEdit($arUserField, $arHtmlControl, $multiple = false)
	{
		global $APPLICATION;

		ob_start();

		$value = $arHtmlControl['VALUE'];
		if(!is_array($value))
		{
			$value = array($value);
		}

		$pointList = array();
		foreach($value as $point)
		{
			if($point <> '')
			{
				$pointList[] = explode(';', $point);
			}
		}

		$center = static::getCenter($arUserField, $pointList);

		$APPLICATION->IncludeComponent('bitrix:map.google.edit', '', array(
			'MAP_ID' => $arUserField['FIELD_NAME'],
			'MULTIPLE' => $multiple ? 'Y' : 'N',
			'POINTS' => $pointList,
			'INIT_MAP_SCALE' => $arUserField['SETTINGS']['INIT_MAP_SCALE'],
			'INIT_MAP_LAT' => $center[0],
			'INIT_MAP_LON' => $center[1],
		), null, array('HIDE_ICONS' => 'Y'));

		if(!is_array($arHtmlControl['VALUE']))
		{
			$arHtmlControl['VALUE'] = array($arHtmlControl['VALUE']);
		}
?>
		<span style="display: none;" id="<?=$arUserField['FIELD_NAME']?>_valuewrap">
<?
		foreach($arHtmlControl['VALUE'] as $value)
		{
?>
			<input type="hidden" name="<?=$arHtmlControl['NAME']?>" value="<?=$value?>" />
<?
		}
?>
		</span>
		<script>
			BX.Fileman.Map.instance('<?=\CUtil::JSEscape($arUserField['FIELD_NAME'])?>').addListener(function(map, points)
			{
				var str = '';

				for(var i = 0; i < points.length; i++)
				{
					str += '<input type="hidden" name="<?=$arHtmlControl['NAME']?>" value="' + points[i].getPosition().join(';') + '" />';
				}

				BX('<?=$arUserField['FIELD_NAME']?>_valuewrap').innerHTML = str;

			});
		</script>
		<?

		return ob_get_clean();
	}

	public static function getPublicEdit($arUserField, $arAdditionalParameters = array())
	{
		global $APPLICATION;

		$mapId = $arUserField['FIELD_NAME'].'_edit_'.Random::getString(5);

		$fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
		$value = static::getFieldValue($arUserField, $arAdditionalParameters);

		$pointList = array();

		foreach($value as $point)
		{
			if($point <> '')
			{
				$pointList[] = explode(';', $point);
			}
		}

		$html = '';

		$center = static::getCenter($arUserField, $pointList);

		ob_start();
		$APPLICATION->IncludeComponent('bitrix:map.google.edit', '', array(
			'MAP_ID' => $mapId,
			'MAP_WIDTH' => '100%',
			'MULTIPLE' => $arUserField['MULTIPLE'] == 'Y' ? 'Y' : 'N',
			'POINTS' => $pointList,
			'INIT_MAP_SCALE' => $arUserField['SETTINGS']['INIT_MAP_SCALE'],
			'INIT_MAP_LAT' => $center[0],
			'INIT_MAP_LON' => $center[1],
		), null, array('HIDE_ICONS' => 'Y'));

?>
<span style="display: none;" id="<?=$arUserField['FIELD_NAME']?>_valuewrap">
	<input type="hidden" name="<?=HtmlFilter::encode($fieldName)?>" value=""/>
<?
foreach($value as $point)
{
	if($point <> '')
	{
?>
	<input type="hidden" name="<?=HtmlFilter::encode($fieldName)?>" value="<?=$point?>"/>
<?
	}
}
?>
</span>
<script>
	BX.Fileman.Map.instance('<?=\CUtil::JSEscape($mapId)?>').addListener(function(map, points)
	{
		var str = '<input type="hidden" name="<?=\CUtil::JSEscape(HtmlFilter::encode($fieldName))?>" value=""/>';

		for(var i = 0; i < points.length; i++)
		{
			str += '<input type="hidden" name="<?=\CUtil::JSEscape(HtmlFilter::encode($fieldName))?>" value="' + points[i].getPosition().join(';') + '" />';
		}

		BX('<?=HtmlFilter::encode($arUserField['FIELD_NAME'])?>_valuewrap').innerHTML = str;
		var inputList = BX.Main.UF.Factory.get(BX.Main.UF.TypeGeo.USER_TYPE_ID).findInput(BX('<?=\CUtil::JSEscape($arUserField['FIELD_NAME'])?>_valuewrap'), '<?=\CUtil::JSEscape($fieldName)?>');

		if(inputList.length > 0)
		{
			BX.fireEvent(inputList[0], 'change');
		}
	});
</script>
<?
		$html .= ob_get_clean();

		return $html;
	}

	public static function getPublicView($arUserField, $arAdditionalParameters = array())
	{
		global $APPLICATION;

		$mapId = $arUserField['FIELD_NAME'].'_view_'.Random::getString(5);

		$value = static::normalizeFieldValue($arUserField["VALUE"]);

		$placemarkList = array();
		$pointList = array();

		if(count($value) > 0)
		{
			foreach($value as $point)
			{
				if($point <> '')
				{
					$c = explode(';', $point);
					$placemarkList[] = array(
						'TEXT' => '',
						'LAT' => $c[0],
						'LON' => $c[1]
					);

					$pointList[] = $c;
				}
			}
		}

		$center = static::getCenter($arUserField, $pointList);
		ob_start();

		$APPLICATION->IncludeComponent('bitrix:map.google.view', '', array(
			'MAP_ID' => $mapId,
			'MAP_WIDTH' => '100%',
			'MAP_DATA' => serialize(array(
				'google_scale' => $arUserField['SETTINGS']['INIT_MAP_SCALE'],
				'google_lat' => $center[0],
				'google_lon' => $center[1],
				'PLACEMARKS' => $placemarkList
			)),
		), null, array('HIDE_ICONS' => 'Y'));

		return ob_get_clean();
	}

	protected function getCenter($arUserField, $pointList)
	{
		$center = array(0, 0);
		$pointCount = 0;

		if(count($pointList) > 0)
		{
			foreach($pointList as $point)
			{
				if(is_array($point))
				{
					$center[0] += $point[0];
					$center[1] += $point[1];

					$pointCount++;
				}
			}
		}

		if($pointCount <= 0)
		{
			$center = array($arUserField['SETTINGS']['INIT_MAP_LAT'], $arUserField['SETTINGS']['INIT_MAP_LON']);
		}
		else
		{
			$center[0] = $center[0] / $pointCount;
			$center[1] = $center[1] / $pointCount;
		}

		return $center;
	}
}