<?php

namespace Bitrix\Main\Grid\Row\Assembler\Field;

use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Main\UserTable;
use CSite;
use CUser;

/**
 * Assembles row values of user type columns.
 */
class UserFieldAssembler extends FieldAssembler
{
	private array $userCache = [];

	/**
	 * Load user name.
	 *
	 * @internal for get username use `getUserName` method.
	 *
	 * @param int $userId
	 *
	 * @return string returns empty string if not found user.
	 */
	protected function loadUserName(int $userId): string
	{
		$nameFormat = CSite::GetNameFormat();

		$row = UserTable::getRow([
			'select' => [
				'ID',
				'LOGIN',
				'NAME',
				'LAST_NAME',
				'SECOND_NAME',
				'EMAIL',
				'TITLE',
			],
			'filter' => [
				'=ID' => $userId,
			],
		]);
		if ($row)
		{
			return CUser::FormatName($nameFormat, $row, true, true);
		}

		return '';
	}

	/**
	 * Get user name.
	 *
	 * @param int $userId
	 *
	 * @return string|null
	 */
	private function getUserName(int $userId): ?string
	{
		if (!isset($this->userCache[$userId]))
		{
			$this->userCache[$userId] = $this->loadUserName($userId);
		}

		return $this->userCache[$userId];
	}

	/**
	 * @param mixed $value
	 *
	 * @return string|null
	 */
	protected function prepareColumn($value)
	{
		if (isset($value) && is_numeric($value))
		{
			$value = (int)$value;
			if ($value > 0)
			{
				return $this->getUserName($value);
			}
		}

		return null;
	}
}
