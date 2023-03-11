<?php

declare(strict_types = 1);

namespace Bitrix\Sale\Controller\Engine\ActionFilter;

use Bitrix\Main;

class CheckWritePermission extends Main\Engine\ActionFilter\Base
{
	public function onBeforeAction(Main\Event $event): ?Main\EventResult
	{
		if (!$this->hasPermission())
		{
			$this->addError(new Main\Error(
				Main\Localization\Loc::getMessage('SALE_CONTROLLER_ENGINE_ACTIONFILTER_CHECK_WRITE_PERMISSION')
			));

			return new Main\EventResult(Main\EventResult::ERROR, null, null, $this);
		}

		return null;
	}

	protected function hasPermission(): bool
	{
		global $APPLICATION;
		$saleModulePermissions = $APPLICATION->GetGroupRight('sale');

		return $saleModulePermissions >= 'W';
	}
}