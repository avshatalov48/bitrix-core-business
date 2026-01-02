<?php

namespace Bitrix\Im\V2\Chat\ExternalChat;

use Bitrix\Im\V2\Result;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\EventResult;

abstract class Event extends \Bitrix\Main\Event
{
	private const TYPE_TEMPLATE = 'On#ActionName#ExternalChat#EntityType#';

	public function __construct(string $entityType, $parameters)
	{
		parent::__construct('im', $this->formatType($entityType), $parameters);
	}

	public function hasResult(): bool
	{
		return $this->getFirstResult() !== null;
	}

	public function getFirstResult(): ?EventResult
	{
		return $this->getResults()[0] ?? null;
	}

	public function getResult(): Result
	{
		$result = new Result();
		$firstResult = $this->getFirstResult();
		if ($firstResult === null)
		{
			return $result;
		}

		if ($firstResult->getType() === EventResult::ERROR)
		{
			return $result->addError(new ExternalError(ExternalError::FROM_EVENT));
		}

		return $result->setResult($firstResult->getParameters());
	}

	protected function getParameterFromResult(string $key): mixed
	{
		$parameters = $this->getFirstResult()?->getParameters() ?? [];
		if (!is_array($parameters))
		{
			return null;
		}

		return $parameters[$key] ?? null;
	}

	abstract protected function getActionName(): string;

	protected function formatType(string $entityType): string
	{
		$converter = new Converter(Converter::TO_CAMEL | Converter::UC_FIRST);
		$entityTypeInCamelCase = $converter->process($entityType);
		$actionName = $this->getActionName();

		return strtr(self::TYPE_TEMPLATE, ['#ActionName#' => $actionName, '#EntityType#' => $entityTypeInCamelCase]);
	}
}