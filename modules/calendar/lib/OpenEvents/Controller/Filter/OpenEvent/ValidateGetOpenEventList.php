<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Filter\OpenEvent;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class ValidateGetOpenEventList extends ActionFilter\Base
{
	public function onBeforeAction(Event $event): ?EventResult
	{
		$request = $this->getAction()->getController()->getRequest();
		$categoryId = $request->get('categoryId');
		if ($categoryId && $categoryId < 0)
		{
			$this->addError(
				new Error(
					message: 'categoryId should be positive integer',
					customData: ['field_name' => 'categoryId'],
				)
			);
		}

		$onlyCurrentUser = $request['onlyCurrentUser'] ?? null;
		$this->validateBool($onlyCurrentUser, 'onlyCurrentUser');

		$onlyWithComments = $request['onlyWithComments'] ?? null;
		$this->validateBool($onlyWithComments, 'onlyWithComments');

		$fromYear = $request['fromYear'] ?? null;
		$this->validateYear($fromYear, 'fromYear');

		$fromMonth = $request['fromMonth'] ?? null;
		$this->validateMonth($fromMonth, 'fromMonth');

		$toYear = $request['toYear'] ?? null;
		$this->validateYear($toYear, 'toYear');

		$toMonth = $request['toMonth'] ?? null;
		$this->validateMonth($toMonth, 'toMonth');

		return null;
	}

	private function validateBool($value, string $fieldName): void
	{
		if (
			is_string($value)
			&& !in_array($value, ['true', 'false'], true)
		)
		{
			$this->addFieldError(
				sprintf('%s should be true|false', $fieldName),
				$fieldName
			);
		}
	}

	private function validateYear($value, string $fieldName): void
	{
		if (
			is_string($value)
			&& $value
			&& (int)$value < 2000
		)
		{
			$this->addFieldError(sprintf('%s should be valid year', $fieldName), $fieldName);
		}
	}

	private function validateMonth($value, string $fieldName): void
	{
		if (
			is_string($value)
			&& $value
			&& ((int)$value < 1 || (int)$value > 12)
		)
		{
			$this->addFieldError(sprintf('%s should be valid month', $fieldName), $fieldName);
		}
	}

	private function addFieldError(string $message, string $fieldName): void
	{
		$this->addError(
			new Error(
				message: $message,
				customData: ['field_name' => $fieldName],
			)
		);
	}
}
