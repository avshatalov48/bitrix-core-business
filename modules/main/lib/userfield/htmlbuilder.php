<?php

namespace Bitrix\Main\UserField;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

/**
 * Class HtmlBuilder
 * @package Bitrix\Main\UserField
 */
class HtmlBuilder
{
	protected $userTypeId;

	public function __construct($userTypeId)
	{
		$this->userTypeId = $userTypeId;
	}

	public function getCssClassName(array $additionalCss = array())
	{
		return trim('fields ' . $this->userTypeId . ' ' . implode(' ', $additionalCss));
	}

	public function wrapSingleField($html, array $additionalCss = array())
	{
		return '<span class="' . HtmlFilter::encode(static::getCssClassName($additionalCss)) . ' field-item">' . $html . '</span>';
	}

	public function wrapDisplayResult($html, $additionalCss = array())
	{
		return '<span class="' . HtmlFilter::encode(static::getCssClassName($additionalCss)) . ' field-wrap">' . $html . '</span>';
	}

	public function getMultipleValuesSeparator()
	{
		return '<span class="fields separator"></span>';
	}

	public function getCloneButton($fieldName)
	{
		return '<input type="button" value="' . HtmlFilter::encode(Loc::getMessage('USER_TYPE_PROP_ADD')) . '" onclick="BX.Main.UF.Factory.get(\'' . $this->userTypeId . '\').addRow(\'' . \CUtil::jsEscape($fieldName) . '\', this);" />';
	}

	public function getMobileCloneButton($fieldName)
	{
		return '<div class="add-field-button" onclick="BX.Main.UF.Factory.get(\'' .
			$this->userTypeId . '\').addMobileRow(\'' . \CUtil::jsEscape($fieldName) . '\', this);" >' . Loc::getMessage('CRM_FIELDS_ADD_FIELD') . '</div>';
	}

	/**
	 * @param array|null $attributes
	 * @param bool $encode
	 * @return string|null
	 */
	public function buildTagAttributes(?array $attributes, bool $encode = true): ?string
	{
		$s = '';
		if($attributes)
		{
			foreach($attributes as $attribute => $value)
			{
				if($encode)
				{
					$s .= htmlspecialcharsbx($attribute) . '="' . htmlspecialcharsbx($value) . '" ';
				}
				else
				{
					$s .= $attribute . '="' . $value . '" ';
				}
			}
		}

		return $s;
	}

	/**
	 * @param string|null $url
	 * @return string
	 */
	public function encodeUrl(?string $url): string
	{
		if(!preg_match('/^(callto:|mailto:|[a-z0-9]+:\/\/)/i', $url))
		{
			$url = 'http://' . $url;
		}

		return (new Uri($url))->getUri();
	}
}