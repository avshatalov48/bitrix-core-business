<?php

namespace Bitrix\Main\Web;

use Bitrix\Main\Application;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type;

class FileDecodeFilter implements Type\IRequestFilter
{
	/**
	 * @param array $values
	 * @return array
	 */
	public function filter(array $values)
	{
		if (Application::getInstance()->isUtfMode())
		{
			return null;
		}

		if (empty($values['files']) || !is_array($values['files']))
		{
			return null;
		}

		return [
			'files' => Encoding::convertEncoding($values['files'], 'UTF-8', SITE_CHARSET),
		];
	}
}