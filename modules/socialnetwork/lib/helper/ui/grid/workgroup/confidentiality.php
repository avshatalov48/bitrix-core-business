<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Helper\UI\Grid\Workgroup;

use Bitrix\Main\Localization\Loc;

class Confidentiality
{
	public static function getValue(array $params = []): string
	{
		$confidentialityCode = (string)($params['code'] ?? '');

		switch ($confidentialityCode)
		{
			case 'open':
				$result = Loc::getMessage('SOCIALNETWORK_GROUP_LIST_TEMPLATE_PRIVACY_TYPE_OPEN');
				break;
			case 'closed':
				$result = Loc::getMessage('SOCIALNETWORK_GROUP_LIST_TEMPLATE_PRIVACY_TYPE_CLOSED');
				break;
			case 'secret':
				$result = Loc::getMessage('SOCIALNETWORK_GROUP_LIST_TEMPLATE_PRIVACY_TYPE_SECRET');
				break;
			default:
				$result = '';
		}

		return $result;
	}
}
