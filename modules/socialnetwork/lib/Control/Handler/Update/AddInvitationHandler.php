<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Handler\Update;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Control\Handler\Trait\SendInvitationTrait;
use Bitrix\Socialnetwork\Item\Workgroup;

class AddInvitationHandler implements UpdateHandlerInterface
{
	use SendInvitationTrait;

	/**
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function update(UpdateCommand $command, Workgroup $entityBefore, Workgroup $entityAfter): HandlerResult
	{
		return $this->send($command, $entityBefore);
	}
}