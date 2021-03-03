<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Config;

use Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields;

interface IConfigField extends Fields\IField
{
	public static function prepareValue($value);

}