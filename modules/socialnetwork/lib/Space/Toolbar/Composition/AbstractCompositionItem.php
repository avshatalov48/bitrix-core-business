<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Composition;

use Bitrix\Socialnetwork\Space\Toolbar\Composition\Item\BusinessProcess;
use Bitrix\Socialnetwork\Space\Toolbar\Composition\Item\CalendarEvent;
use Bitrix\Socialnetwork\Space\Toolbar\Composition\Item\ListElement;
use Bitrix\Socialnetwork\Space\Toolbar\Composition\Item\Message;
use Bitrix\Socialnetwork\Space\Toolbar\Composition\Item\Task;

abstract class AbstractCompositionItem
{
	protected string $moduleId = '';

	public static function createFromModuleId(string $moduleId): ?static
	{
		/** @var AbstractCompositionItem $class */
		$class = static::getClass($moduleId);
		if (is_null($class))
		{
			return null;
		}

		return new $class();
	}

	public function getModuleId(): string
	{
		return $this->moduleId;
	}
	public function isHidden(): bool
	{
		return false;
	}

	public function getBoundItem(): ?AbstractCompositionItem
	{
		return null;
	}

	public function hasBoundItem(): bool
	{
		return !is_null($this->getBoundItem());
	}

	private static function getClass(string $moduleId): ?string
	{
		return match ($moduleId)
		{
			'bizproc' => BusinessProcess::class,
			'calendar' => CalendarEvent::class,
			'lists' => ListElement::class,
			'blog' => Message::class,
			'tasks' => Task::class,
			default => null,
		};
	}
}