<?php

namespace Bitrix\Rest\Event;

use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\IO\Path;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\Event;
use Bitrix\Rest\RestException;

class EventBind
{
	/** @var string|EventBindInterface */
	private string $class;

	/**
	 * @param string $class
	 * @throws NotImplementedException
	 * @throws \ReflectionException
	 */
	public function __construct(string $class)
	{
		$reflection = new \ReflectionClass($class);
		if ($reflection->implementsInterface('\\Bitrix\\Rest\\Event\\EventBindInterface'))
		{
			$this->class = $class;
		}
		else
		{
			throw new NotImplementedException($class.' is not implemented interface EventBindInterface');
		}
	}

	/**
	 *
	 * Get config, handlers and bindings PHP events to REST events
	 *
	 * @param array $names
	 * @return array
	 */
	public function getHandlers(array $names): array
	{
		$bindings = [];
		$eventNames = $this->bind($names);

		foreach ($eventNames as $internalName => $externalName)
		{
			$bindings[$externalName] = $this->getItemEventInfo($internalName, $this->class::getCallbackRestEvent());
		}

		return $bindings;
	}

	/**
	 *
	 * Get bindings internal event name to external name
	 *
	 * @param array $eventNames
	 * @return array
	 */
	private function bind(array $eventNames): array
	{
		$result = [];

		foreach ($eventNames as $internalName => $externalName)
		{
			$isAssociativeArray = !is_numeric($internalName);
			if ($isAssociativeArray)
			{
				$result[$internalName] = $externalName;
			}
			else
			{
				$internalName = $externalName;
				$result[$internalName] = $this->makeExternalEventName($externalName);
			}
		}

		return $result;
	}

	private function getItemEventInfo(string $eventName, array $callback): array
	{
		return [
			$this->getModuleId(),
			$eventName,
			$callback,
			[
				'category' => \Bitrix\Rest\Sqs::CATEGORY_CRM,
			],
		];
	}

	private function makeExternalEventName(string $eventName): string
	{
		$converter = new Converter(Converter::TO_SNAKE);
		$eventName = str_replace('::', '\\', $eventName);
		$eventName = str_replace('\\', '', $eventName);
		$name = $converter->process($eventName);

		return str_replace('_','.', $this->getModuleId().'_'.$name);
	}

	/**
	 * @throws \ReflectionException
	 * @throws \Bitrix\Main\IO\InvalidPathException
	 */
	private function getFilePath(string $class): string
	{
		$reflector = new \ReflectionClass($class);
		return  Path::normalize($reflector->getFileName());
	}

	private function getModuleId(): string
	{
		return getModuleId($this->getFilePath($this->class));
	}

	/**
	 *
	 * Handler for result improvement to REST event handlers
	 *
	 * @param array $arParams
	 * @param array $arHandler
	 * @return array[]
	 * @throws RestException
	 */
	public static function processItemEvent(array $arParams, array $arHandler): array
	{
		$id = null;
		$event = $arParams[0] ?? null;

		if (!$event)
		{
			throw new RestException('event object not found trying to process event');
		}

		if ($event instanceof Event)
		{
			$item = $event->getParameter('id');
			$id = is_array($item) ? $item['ID']: $item;
		}

		if (!$id)
		{
			throw new RestException('id not found trying to process event');
		}

		return [
			'FIELDS' => [
				'ID' => $id
			],
		];
	}
}