<?php
namespace Bitrix\Landing\Controller;

use Bitrix\Landing\PublicActionResult;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\UserTable;

class User extends Controller
{
	public function getDefaultPreFilters(): array
	{
		return [
			new Authentication(),
			new ActionFilter\Extranet()
		];
	}

	/**
	 * Returns the username.
	 * @param int $userId.
	 * @return string
	 */
	public static function getUserNameByIdAction(int $userId): string
	{
		$res = UserTable::getList([
			'select' => [
				'NAME',
				'LAST_NAME'
			],
			'filter' => [
				'ID' => $userId
			]
		]);
		$row = $res->fetch();
		$result = new PublicActionResult();
		$result->setResult([
			'NAME' => \CUser::formatName(
				\CSite::getNameFormat(false),
				$row, true, false
			)
		]);
		return $result->getResult()['NAME'];
	}

	/**
	 * Returns user set information
	 * @param array $setUserId.
	 * @return array
	 */
	public static function getUsersInfoAction(array $setUserId): array
	{
		$res = UserTable::getList([
			'select' => [
				'NAME',
				'LAST_NAME',
				'PERSONAL_PHOTO',
			],
			'filter' => [
				'ID' => $setUserId
			]
		]);
		$setName = [];
		$personalPhoto = [];

		while ($row = $res->fetch())
		{
			$name = new PublicActionResult();
			$name->setResult([
				'NAME' => \CUser::formatName(
					\CSite::getNameFormat(false),
					$row, true, false
				)
			]);
			$setName[] = $name->getResult()['NAME'];
			if ($row['PERSONAL_PHOTO'])
			{
				$personalPhoto[] = $row['PERSONAL_PHOTO'];
			}
			else
			{
				$personalPhoto[] = null;
			}
		}

		$data = [];
		$data['name'] = $setName;
		$data['personalPhoto'] = $personalPhoto;

		return $data;
	}
}