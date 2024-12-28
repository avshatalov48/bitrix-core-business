<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Handler\Add;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Control\Handler\Trait\SendInvitationTrait;
use Bitrix\Socialnetwork\Item\Workgroup;

class AddInvitationHandler implements AddHandlerInterface
{
	use SendInvitationTrait;

	/**
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function add(AddCommand $command, Workgroup $entity): HandlerResult
	{
		return $this->send($command, $entity);
	}
}