<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Control\Exception\GroupNotAddedException;
use Bitrix\Socialnetwork\Control\GroupResult;
use Bitrix\Socialnetwork\Control\Observer\Add\FeatureObserver;
use Bitrix\Socialnetwork\Control\Observer\Add\InvitationObserver;
use Bitrix\Socialnetwork\Item\Workgroup;
use CSocNetGroup;

class AddCommandHandler extends AbstractCommandHandler
{
	/** @var AddCommand */
	protected AbstractCommand $command;

	public function __invoke(): GroupResult
	{
		$result = new GroupResult();

		try
		{
			$this->save();

			$this->notify();
		}
		catch (GroupNotAddedException $e)
		{
			$result->addError(Error::createFromThrowable($e));

			return $result;
		}

		$result->setGroup($this->entity);

		return $result;
	}

	/**
	 * @throws GroupNotAddedException
	 */
	protected function save(): void
	{
		$id = (int)CSocNetGroup::createGroup($this->command->ownerId, $this->mapper->toArray($this->command));

		global $APPLICATION;
		if ($e = $APPLICATION->GetException())
		{
			throw new GroupNotAddedException($e->msg);
		}

		$group = Workgroup::getById($id);
		if (false === $group)
		{
			throw new GroupNotAddedException('No such group');
		}

		$this->entity = $group;
	}

	protected function init(): void
	{
		parent::init();

		$this->addObserver(new InvitationObserver());
		$this->addObserver(new FeatureObserver());
	}
}