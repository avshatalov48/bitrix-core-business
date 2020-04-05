<?php
namespace Bitrix\Main\UserField;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

class TypeHelper
{
	protected $userTypeId;

	public function __construct($userTypeId)
	{
		$this->userTypeId = $userTypeId;
	}

	public function getCssClassName(array $additionalCss = array())
	{
		return trim('fields '.$this->userTypeId.' '.implode(' ', $additionalCss));
	}

	public function wrapSingleField($html, array $additionalCss = array())
	{
		return '<span class="'.HtmlFilter::encode(static::getCssClassName($additionalCss)).' field-item">'.$html.'</span>';
	}

	public function wrapDisplayResult($html, $additionalCss = array())
	{
		return '<span class="'.HtmlFilter::encode(static::getCssClassName($additionalCss)).' field-wrap">'.$html.'</span>';
	}

	public function getMultipleValuesSeparator()
	{
		return '<span class="fields separator"></span>';
	}

	public function getCloneButton($fieldName)
	{
		return '<input type="button" value="'.HtmlFilter::encode(Loc::getMessage('USER_TYPE_PROP_ADD')).'" onclick="BX.Main.UF.Factory.get(\''.$this->userTypeId.'\').addRow(\''.\CUtil::jsEscape($fieldName).'\', this);" />';
	}
}