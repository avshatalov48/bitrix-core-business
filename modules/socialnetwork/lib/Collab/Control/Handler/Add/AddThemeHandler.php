<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Add;

use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Handler\Add\AddHandlerInterface;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Integration\Intranet\ThemePicker;
use Bitrix\Socialnetwork\Item\Workgroup;

class AddThemeHandler implements AddHandlerInterface
{
	public function add(AddCommand $command, Workgroup $entity): HandlerResult
	{
		$themePicker = ThemePicker::getThemePicker($entity->getId(), $command->getInitiatorId(), $entity->getSiteId());

		$defaultThemeId = ThemePicker::getDefaultPortalThemeId();
		if ($defaultThemeId !== null)
		{
			// don't care about errors
			$themePicker?->setCurrentThemeId($defaultThemeId);
		}

		return new HandlerResult();
	}
}