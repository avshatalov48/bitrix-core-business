<?php

namespace Bitrix\Calendar\EventCategory\Validator;

use Bitrix\Main\Error;

final class CommonEventCategoryValidators
{
	/**
	 * @return Error[]
	 */
	public static function validateName($name, bool $required = true): array
	{
		$errors = [];

		if ($required && !$name)
		{
			$errors[] = new Error(
				message: 'name required',
				customData: ['field_name' => 'name']
			);
		}

		if (!$errors && (!is_string($name) || mb_strlen($name) > 255))
		{
			$errors[] = new Error(
				message: 'name invalid',
				customData: ['field_name' => 'name']
			);
		}

		return $errors;
	}

	/**
	 * @return Error[]
	 */
	public static function validateDescription($description, bool $required = false): array
	{
		$errors = [];

		if ($required && !$description)
		{
			$errors[] = new Error(
				message: 'description required',
				customData: ['field_name' => 'description']
			);
		}

		if (!$errors && !is_string($description))
		{
			$errors[] = new Error(
				message: 'description invalid',
				customData: ['field_name' => 'description']
			);
		}

		return $errors;
	}

	/**
	 * @return Error[]
	 */
	public static function validateClosed($closed): array
	{
		$errors = [];

		if (
			is_string($closed)
			&& !in_array($closed, ['true', 'false'], true)
		)
		{
			$errors = [
				new Error(
					message: 'closed invalid',
					customData: ['field_name' => 'closed']
				)
			];
		}

		return $errors;
	}

	/**
	 * @return Error[]
	 */
	public static function validateAttendees(bool $closed, array $attendees): array
	{
		$errors = [];
		if (!$closed && !empty($attendees))
		{
			$errors[] = new Error(
				message: 'attendees should be empty',
				code: 'attendees_should_be_empty',
				customData: ['field_name' => 'attendees'],
			);
		}

		return $errors;
	}

	/**
	 * @return Error[]
	 */
	public static function validateDepartmentIds(bool $closed, array $departmentIds): array
	{
		$errors = [];
		if (!$closed && !empty($departmentIds))
		{
			$errors[] = new Error(
				message: 'departments should be empty',
				code: 'departments_should_be_empty',
				customData: ['field_name' => 'departmentIds'],
			);
		}

		return $errors;
	}
}
