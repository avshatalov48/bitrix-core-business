<?php

namespace Bitrix\Sale\Cashbox\Inputs;

use Bitrix\Main\Localization;
use Bitrix\Sale\Internals\Input;

Localization\Loc::loadMessages(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/internals/input.php");

/**
 * Class File
 * @package Bitrix\Sale\Cashbox\Inputs
 */
class File extends Input\Base
{
	/**
	 * @param array $input
	 * @param $value
	 * @return string
	 */
	public static function getViewHtmlSingle(array $input, $value)
	{
		$result = '<span>';
		if ($value)
		{
			$result .= Localization\Loc::getMessage('SALE_CASHBOX_INPUT_SECURITY_FILE_CONTROL_LOADED');
		}
		$result .= '</span>&nbsp;';

		return $result;
	}

	/**
	 * @param $name
	 * @param array $input
	 * @param $value
	 * @return string
	 */
	public static function getEditHtmlSingle($name, array $input, $value)
	{
		$input['ONCHANGE'] =
			"var anchor = this.previousElementSibling.previousElementSibling;".
			"if (anchor.firstChild) anchor.removeChild(anchor.firstChild);".
			"anchor.appendChild(document.createTextNode(this.value.split(/(\\\\|\\/)/g).pop()));".
			$input['ONCHANGE'];

		$fileAttributes = static::extractAttributes($input,
			array('DISABLED'=>'', 'AUTOFOCUS'=>'', 'REQUIRED'=>''),
			array('FORM'=>1, 'ACCEPT'=>1));

		$otherAttributes = static::extractAttributes($input, array('DISABLED'=>''), array('FORM'=>1), false);

		return static::getViewHtmlSingle($input, $value)
			.'<input type="hidden" name="'.$name.'" value="'.htmlspecialcharsbx($value).'"'.$otherAttributes.'>'
			.'<input type="file" name="'.$name.'" style="position:absolute; visibility:hidden"'.$fileAttributes.'>'
			.'<input type="button" value="'.Localization\Loc::getMessage('SALE_CASHBOX_INPUT_SECURITY_FILE_CONTROL_BROWSE').'" onclick="this.previousSibling.click()">'
			.(
			$input['NO_DELETE']
				? ''
				: '<label>'.Localization\Loc::getMessage('SALE_CASHBOX_INPUT_SECURITY_FILE_CONTROL_DELETE').' <input type="checkbox" name="'.$name.'[DELETE]" onclick="'
				."var button = this.parentNode.previousSibling, file = button.previousSibling;"
				."button.disabled = file.disabled = this.checked;"
				.'"'.$otherAttributes.'></label>'
			);
	}

	/**
	 * @param array $input
	 * @param $value
	 * @return array|void
	 */
	public static function getErrorSingle(array $input, $value)
	{
		$errors = [];
		return $errors;
	}
}

/**
 * @deprecated Type SECURITY_FILE_CONTROL is deprecated. Use DATABASE_FILE
 * @see DATABASE_FILE
 */
Input\Manager::register('SECURITY_FILE_CONTROL', array(
	'CLASS' => __NAMESPACE__.'\\File',
	'NAME' => Localization\Loc::getMessage('SALE_CASHBOX_INPUT_SECURITY_FILE_CONTROL')
));

Input\Manager::register('DATABASE_FILE', array(
	'CLASS' => __NAMESPACE__.'\\File',
	'NAME' => Localization\Loc::getMessage('SALE_CASHBOX_INPUT_SECURITY_FILE_CONTROL')
));
