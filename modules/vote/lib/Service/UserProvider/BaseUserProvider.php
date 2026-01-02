<?php

namespace Bitrix\Vote\Service\UserProvider;

use Bitrix\Main\UserTable;
use Bitrix\Vote\Model\Dto\BaseUserDto;
use Bitrix\Vote\Model\Dto\UserDtoByIdMap;

class BaseUserProvider implements UserProviderContract
{
	/**
	 * @param list<int> $ids
	 *
	 * @return UserDtoByIdMap
	 */
	public function getByUserIds(array $ids): UserDtoByIdMap
	{
		$result = UserTable::query()
		   ->whereIn('ID', array_values($ids))
		   ->setSelect([
			   'ID',
			   'NAME',
			   'LAST_NAME',
			   'SECOND_NAME',
			   'LOGIN',
			   'PERSONAL_PHOTO',
			   'WORK_POSITION',
		   ])
		   ->exec();


		$byIdMap = new UserDtoByIdMap();
		while($row = $result->fetch())
		{
			$userId = (int)$row['ID'];
			$byIdMap->add($userId, $this->formatUserData($row));
		}

		return $byIdMap;
	}

	/**
	 * @param array{
	 *      ID: int,
	 *      NAME: string|null,
	 *      LAST_NAME: string|null,
	 *      SECOND_NAME: string|null,
	 *      LOGIN: string,
	 *      PERSONAL_PHOTO: string|null,
	 *      WORK_POSITION: string|null,
	 *  } $dbUserFields
	 *
	 * @return BaseUserDto
	 */
	private function formatUserData(array $dbUserFields): BaseUserDto
	{
		return new BaseUserDto(
			id: (int)$dbUserFields['ID'],
			name: $this->formatUserName($dbUserFields),
			image: $this->getUserPhotoUrl($dbUserFields['PERSONAL_PHOTO']),
			workPosition: $dbUserFields['WORK_POSITION'],
		);
	}

	private function formatUserName(array $userFields): string
	{
		return \CUser::FormatName(
			\CSite::GetNameFormat(false),
			$userFields,
			true, false
		);
	}

	private function getUserPhotoUrl(?string $value): ?string
	{
		if (empty($value))
		{
			return null;
		}

		$fileTmp = \CFile::resizeImageGet(
			$value,
			['width' => 64, 'height' => 64],
			BX_RESIZE_IMAGE_EXACT,
			false
		);
		if ($fileTmp !== null && isset($fileTmp['src']))
		{
			return $fileTmp['src'];
		}

		return null;
	}
}