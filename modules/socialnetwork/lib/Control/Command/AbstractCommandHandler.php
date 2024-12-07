<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

use Bitrix\Socialnetwork\Control\GroupResult;
use Bitrix\Socialnetwork\Control\Mapper\Mapper;
use Bitrix\Socialnetwork\Control\Observer\Add\InvitationObserver;
use Bitrix\Socialnetwork\Control\Observer\ObserverInterface;
use Bitrix\Socialnetwork\Item\Workgroup;
use CSocNetAllowed;

abstract class AbstractCommandHandler
{
	protected AbstractCommand $command;
	protected Workgroup $entity;
	protected Mapper $mapper;

	/** @var ObserverInterface[]  */
	protected array $observers = [];

	public function __construct(AbstractCommand $command)
	{
		$this->command = $command;

		$this->init();
	}

	abstract public function __invoke(): GroupResult;

	protected function notify(): void
	{
		foreach ($this->observers as $observer)
		{
			$observer->update($this->command, $this->entity);
		}
	}

	protected function addObserver(ObserverInterface $observer): void
	{
		$this->observers[] = $observer;
	}

	protected function init(): void
	{
		$this->mapper = new Mapper();
	}
}