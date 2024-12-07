<?php

namespace Bitrix\Sale\Delivery\Inputs;

require_once __DIR__.'/../internals/input.php';

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;
use	Bitrix\Sale\Internals\Input;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Period extends Input\Base
{
	public static function getViewHtmlSingle(array $input, $values)
	{
		if(!is_array($values))
			throw new ArgumentTypeException('values', 'array');

		self::checkArgs($input, $values);

		return $input["ITEMS"]["FROM"]["NAME"].": ".Input\Manager::getViewHtml($input["ITEMS"]["FROM"], $values["FROM"]).
			$input["ITEMS"]["TO"]["NAME"].": ".Input\Manager::getViewHtml($input["ITEMS"]["TO"], $values["TO"]).
			" ".Input\Manager::getViewHtml($input["ITEMS"]["TYPE"], $values["TYPE"]);
	}

	public static function getEditHtmlSingle($name, array $input, $values)
	{
		if (!isset($input["ITEMS"]))
		{
			$input["ITEMS"] = [
				"FROM" => [
					"TYPE" => "STRING",
					"NAME" => ""
				],
				"TO" => [
					"TYPE" => "STRING",
					"NAME" => "&nbsp;-&nbsp;"
				],
				"TYPE" => [
					"TYPE" => "ENUM",
					"OPTIONS" => [
						"H" => "HOURS", //Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_HOUR"),
						"D" => "DAYS", //Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_DAY"),
						"M" => "MONTHS" ////Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_MONTH")
					]
				]
			];
		}

		return
			$input["ITEMS"]["FROM"]["NAME"]
			. Input\Manager::getEditHtml($name . "[FROM]", $input["ITEMS"]["FROM"], $values["FROM"] ?? null)
			. $input["ITEMS"]["TO"]["NAME"]
			. Input\Manager::getEditHtml($name . "[TO]", $input["ITEMS"]["TO"], $values["TO"] ?? null)
			. ' '
			. Input\Manager::getEditHtml($name . "[TYPE]", $input["ITEMS"]["TYPE"], $values["TYPE"] ?? null)
		;
	}

	public static function getError(array $input, $values)
	{
		if(!is_array($values))
			throw new ArgumentTypeException('values', 'array');

		return self::getErrorSingle($input, $values);
	}

	public static function getErrorSingle(array $input, $values)
	{
		if(!is_array($values))
			throw new ArgumentTypeException('values', 'array');

		self::checkArgs($input, $values);

		$errors = array();

		if ($error = Input\Manager::getError($input["ITEMS"]["FROM"], $values["FROM"]))
			$errors = $error;

		if ($error = Input\Manager::getError($input["ITEMS"]["TO"], $values["TO"]))
			$errors = array_merge($errors, $error);

		if ($error = Input\Manager::getError($input["ITEMS"]["TYPE"], $values["TYPE"]))
			$errors = array_merge($errors, $error);

		return $errors;
	}

	public static function getValueSingle(array $input, $userValue)
	{
		return $userValue;
	}

	public static function getSettings(array $input, $reload)
	{
		return array();
	}

	protected static function checkArgs(array $input, array $values)
	{
		if(!isset($input["ITEMS"]["FROM"]) || !isset($input["ITEMS"]["TO"]) || !isset($input["ITEMS"]["TYPE"]))
			throw new ArgumentException("Wrong argument structure!", "input");

		if(!isset($values["FROM"]) || !isset($values["TO"]) || !isset($values["TYPE"]))
			throw new \Bitrix\Main\ArgumentException("Wrong argument structure!", "values");

		return true;
	}
}

Input\Manager::register('DELIVERY_PERIOD', array(
	'CLASS' => __NAMESPACE__.'\\Period',
	'NAME' => Loc::getMessage('INPUT_DELIVERY_PERIOD')
));

class ReadOnlyField extends Input\Base
{
	public static function getViewHtmlSingle(array $input, $value)
	{
		$result = '<span';

		if(!empty($input['ID']))
			$result .= ' id="'.$input['ID'].'_view"';

		$result .= '>';
		$result .= isset($input["VALUE_VIEW"]) ? $input["VALUE_VIEW"] : $value;
		$result .= '</span>';
		return $result;
	}

	public static function getEditHtmlSingle($name, array $input, $value)
	{
		$value = str_replace('"', "'", $value);
		$res = self::getViewHtml($input, $value).'<input type="hidden" value="'.htmlspecialcharsbx($value).'" name="'.htmlspecialcharsbx($name).'"';

		if(!empty($input['ID']))
			$res .= ' id="'.$input['ID'].'"';

		$res .= '>';
		return $res;
	}


	public static function getError(array $input, $values)
	{
		return self::getErrorSingle($input, $values);
	}

	public static function getErrorSingle(array $input, $values)
	{
		return array();
	}

	public static function getValueSingle(array $input, $userValue)
	{
		return $userValue;
	}

	public static function getSettings(array $input, $reload)
	{
		return array();
	}
}

Input\Manager::register('DELIVERY_READ_ONLY', array(
	'CLASS' => __NAMESPACE__.'\\ReadOnlyField',
	'NAME' => Loc::getMessage('INPUT_DELIVERY_READ_ONLY')
));

class MultiControlString extends Input\Base
{
	protected $items = array();
	protected $myParams = array();
	protected $myKey = array();

	public function addItem($key, array $control)
	{
		$this->items[$key] = $control;
	}

	public function setParams($key, array $params)
	{
		$this->myParams = $params;
		$this->setKey($key);
	}

	public function getParams()
	{
		$result = $this->myParams;
		$result["ITEMS"] = $this->items;
		return $result;
	}

	public function setKey($key)
	{
		$this->myKey = $key;
	}

	public function getKey()
	{
		return $this->myKey;
	}

	public function isClean()
	{
		return empty($this->myParams);
	}

	public function clean()
	{
		$this->myParams = $this->items = $this->myKey = array();
	}

	public static function getViewHtmlSingle(array $input, $values)
	{
		$result = "";

		foreach($input["ITEMS"] as $key => $item)
			$result .=
				isset($item["NAME"]) ? $item["NAME"] : "".
				Input\Manager::getViewHtml($item, isset($values[$key]) ? $values[$key] : null).
				" ";

		return $result;
	}

	public static function getEditHtmlSingle($name, array $input, $values)
	{
		$result = "";

		foreach($input["ITEMS"] as $key => $item)
			$result .=
				isset($item["NAME"]) ? $item["NAME"] : "".
				Input\Manager::getEditHtml($name."[".$key."]", $item, isset($values[$key]) ? $values[$key] : null).
				" ";

		return $result;
	}

	public static function getErrorSingle(array $input, $values)
	{
		if(!is_array($values))
			throw new ArgumentTypeException('values', 'array');

		$errors = array();

		foreach($input["ITEMS"] as $key => $item)
			if ($error = Input\Manager::getError($item, isset($values[$key]) ? $values[$key] : null))
				$errors[$key] = $error;

		return $errors;
	}

	public static function getValueSingle(array $input, $userValue)
	{
		return $userValue;
	}

	public static function getSettings(array $input, $reload)
	{
		return array();
	}

	/** Get single value.
	 * @param $value
	 * @return mixed - if value is multiple, get first meaningful value (which is not null)
	 */
	static function asSingle($value)
	{
		return $value;
	}

	/**
	 * @inherit
	 */

	public static function getError(array $input, $value)
	{
		$errors = [];

		foreach($input["ITEMS"] as $key => $item)
		{
			$errors = array_merge($errors, Input\Manager::getError($item, $value[$key]));
		}

		return $errors;

	}

	/**
	 * @inherit
	 */
	public static function getRequiredError(array $input, $value)
	{
		$errors = [];

		foreach($input["ITEMS"] as $key => $item)
		{
			$errors = array_merge($errors, Input\Manager::getRequiredError($item, $value[$key]));
		}

		return $errors;
	}
}

Input\Manager::register('DELIVERY_MULTI_CONTROL_STRING', array(
	'CLASS' => __NAMESPACE__.'\\MultiControlString',
	'NAME' => Loc::getMessage('INPUT_DELIVERY_MULTI_CONTROL_STRING')
));

class LocationMulti extends Input\Base
{
	protected static $d2LClass = '\Bitrix\Sale\Delivery\DeliveryLocationTable';

	public static function getViewHtml(array $input, $value = null)
	{
		$result = "";
		$class = static::$d2LClass;

		$res = $class::getConnectedLocations(
			$input["DELIVERY_ID"],
			array(
				'select' => array('LNAME' => 'NAME.NAME'),
				'filter' => array('NAME.LANGUAGE_ID' => LANGUAGE_ID)
			)
		);

		while($loc = $res->fetch())
			$result .= htmlspecialcharsbx($loc["LNAME"])."<br>\n";

		$res = $class::getConnectedGroups(
			$input["DELIVERY_ID"],
			array(
				'select' => array('LNAME' => 'NAME.NAME'),
				'filter' => array('NAME.LANGUAGE_ID' => LANGUAGE_ID)
			)
		);

		while($loc = $res->fetch())
			$result .= htmlspecialcharsbx($loc["LNAME"])."<br>\n";

		return $result;
	}

	public static function getEditHtml($name, array $input, $values = null)
	{
		global $APPLICATION;

		ob_start();

		$APPLICATION->IncludeComponent(
			"bitrix:sale.location.selector.system",
			"",
			array(
				"ENTITY_PRIMARY" => $input["DELIVERY_ID"],
				"LINK_ENTITY_NAME" => mb_substr(static::$d2LClass, 0, -5),
				"INPUT_NAME" => $name,
				'FILTER_BY_SITE' => 'N',
			),
			false
		);

		$result = ob_get_contents();
		$result = '
			<script>				
				var bxInputdeliveryLocMultiStep3 = function()
				{				
					BX.loadScript("/bitrix/components/bitrix/sale.location.selector.system/templates/.default/script.js", function(){
						BX.onCustomEvent("deliveryGetRestrictionHtmlScriptsReady");
					});
				};

				var bxInputdeliveryLocMultiStep2 = function()
				{									
					BX.load([
						"/bitrix/js/sale/core_ui_etc.js", 
						"/bitrix/js/sale/core_ui_autocomplete.js", 
						"/bitrix/js/sale/core_ui_itemtree.js"
						], 
						bxInputdeliveryLocMultiStep3
					);
				};

				BX.loadScript("/bitrix/js/sale/core_ui_widget.js", bxInputdeliveryLocMultiStep2);

				//at first we must load some scripts in the right order
				window["deliveryGetRestrictionHtmlScriptsLoadingStarted"] = true;

			</script>

			<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/adminstyles_fixed.css">
			<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin.css">
			<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin-public.css">
			<link rel="stylesheet" type="text/css" href="/bitrix/components/bitrix/sale.location.selector.system/templates/.default/style.css">
		'.
		$result;
		ob_end_clean();
		return $result;
	}

	public static function getError(array $input, $values)
	{
		return array();
	}


	public static function getValueSingle(array $input, $userValue)
	{
		return $userValue;
	}

	public static function getSettings(array $input, $reload)
	{
		return array();
	}
}

Input\Manager::register('LOCATION_MULTI', array(
	'CLASS' => __NAMESPACE__.'\\LocationMulti',
	'NAME' => Loc::getMessage('INPUT_DELIVERY_LOCATION_MULTI')
));

class LocationMultiExclude extends LocationMulti
{
	protected static $d2LClass = '\Bitrix\Sale\Delivery\DeliveryLocationExcludeTable';
}

Input\Manager::register('LOCATION_MULTI_EXCLUDE', array(
	'CLASS' => __NAMESPACE__.'\\LocationMultiExclude',
	'NAME' => Loc::getMessage('INPUT_DELIVERY_LOCATION_MULTI_EXCLUDE')
));

class ProductCategories extends Input\ProductCategories {}

// Deprecated type
Input\Manager::register('DELIVERY_PRODUCT_CATEGORIES', array(
	'CLASS' => __NAMESPACE__.'\\ProductCategories',
	'NAME' => Loc::getMessage('INPUT_DELIVERY_PRODUCT_CATEGORIES')
));

class ButtonSelector extends Input\Base
{
	public static function getViewHtmlSingle(array $input, $values)
	{
		if(!is_array($values))
			throw new ArgumentTypeException('values', 'array');

		$itemName = ($values['NAME'] <> '' ? htmlspecialcharsbx($values['NAME']) : '');

		if($itemName == '' && $input['NAME_DEFAULT'] <> '')
		{
			$itemName = htmlspecialcharsbx($input['NAME_DEFAULT']);
		}

		return $itemName;
	}

	public static function getEditHtmlSingle($name, array $input, $values)
	{
		$input['NAME_DEFAULT'] = trim((string)($input['NAME_DEFAULT'] ?? ''));
		$input['VALUE_DEFAULT'] = trim((string)($input['VALUE_DEFAULT'] ?? ''));

		if (!is_array($values))
		{
			$values = [];
		}
		$values['NAME'] = trim((string)($values['NAME'] ?? ''));
		$values['VALUE'] = trim((string)($values['VALUE'] ?? ''));

		$itemName = htmlspecialcharsbx($values['NAME'] ?: $input['NAME_DEFAULT']);
		$itemValue = htmlspecialcharsbx($values['VALUE'] ?: $input['VALUE_DEFAULT']);

		return '<div>'.
			'<div id="'.$input['READONLY_NAME_ID'].'">'.htmlspecialcharsbx($itemName).'</div>'.
			' <input type="button" value="'.$input['BUTTON']['NAME'].'" onclick="'.$input['BUTTON']['ONCLICK'].' return false;" style="margin-top: 20px;">'.
			'<input type="hidden" name="'.$name.'[NAME]" value="'.$itemName.'">'.
			'<input type="hidden" name="'.$name.'[VALUE]" value="'.$itemValue.'">'.
			'</div>';
	}

	public static function getValueSingle(array $input, $userValue)
	{
		return $userValue;
	}

	public static function getSettings(array $input, $reload)
	{
		return array();
	}

	public static function getError(array $input, $values)
	{
		return self::getErrorSingle($input, $values);
	}

	public static function getErrorSingle(array $input, $values)
	{
		return array();
	}

	static function asSingle($value)
	{
		return $value;
	}
}
Input\Manager::register('DELIVERY_BUTTON_SELECTOR', array(
	'CLASS' => __NAMESPACE__.'\\ButtonSelector',
	'NAME' => Loc::getMessage('INPUT_DELIVERY_BUTTON_SELECTOR')
));
