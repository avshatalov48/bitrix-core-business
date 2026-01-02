<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter\Handler;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Converter\Command\AbstractConverterCommand;
use Bitrix\Socialnetwork\Integration\Intranet\ThemePicker;

class SetDefaultThemeHandler extends AbstractHandler
{
	public function execute(AbstractConverterCommand $command): Result
	{
		$group = $command->getGroup();
		$themePicker = ThemePicker::getThemePicker($group->getId(), $command->getInitiatorId(), $group->getSiteId());

		$defaultThemeId = ThemePicker::getDefaultPortalThemeId();
		if ($defaultThemeId !== null)
		{
			$themePicker?->setCurrentThemeId($defaultThemeId);
		}

		return new Result();
	}
}
