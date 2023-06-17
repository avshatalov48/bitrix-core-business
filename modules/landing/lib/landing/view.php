<?php
namespace Bitrix\Landing\Landing;

use Bitrix\Landing\PublicAction\Block;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Type\DateTime;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Internals\ViewTable;
use \Bitrix\Landing\Controller;

class View
{
	/**
	 * Key for session array for storing viewed landings.
	 */
	const SESSION_VIEWS_KEY = 'LANDING_VIEWS';

	/**
	 * Inc views for page.
	 * @param int $lid Landing id.
	 * @return void
	 */
	protected static function incViewsPage($lid)
	{
		$lid = (int)$lid;
		$res = ViewTable::getList([
			'select' => [
				'SUM'
			],
			'filter' => [
				'LID' => $lid
			],
			'runtime' => [
				new Entity\ExpressionField(
					'SUM', 'SUM(%s)', ['VIEWS']
				)
			]
		]);
		if ($row = $res->fetch())
		{
			Rights::setOff();
			Landing::update($lid, [
				'VIEWS' => $row['SUM'],
				'DATE_MODIFY' => false
			]);
			Rights::setOn();
		}
	}

	/**
	 * Inc views of page.
	 * @param int $lid Landing id.
	 * @param int $uid User id (current by default).
	 * @return void
	 */
	public static function inc($lid, $uid = null)
	{
		if (!$uid)
		{
			$uid = Manager::getUserId();
		}
		$lid = (int)$lid;
		$uid = (int)$uid;
		$date = new DateTime;

		if ($uid <= 0)
		{
			return;
		}
		if (!isset($_SESSION[self::SESSION_VIEWS_KEY]))
		{
			$_SESSION[self::SESSION_VIEWS_KEY] = [];
		}

		if (!in_array($lid, $_SESSION[self::SESSION_VIEWS_KEY]))
		{
			$res = ViewTable::getList([
				'select' => [
					'ID', 'VIEWS'
				],
				'filter' => [
					'LID' => $lid,
					'USER_ID' => $uid
				]
			]);
			if ($row = $res->fetch())
			{
				$result = ViewTable::update($row['ID'], [
					'VIEWS' => $row['VIEWS'] + 1,
					'LAST_VIEW' => $date
				]);
			}
			else
			{
				$result = ViewTable::add([
					'VIEWS' => 1,
					'LID' => $lid,
					'USER_ID' => $uid,
					'FIRST_VIEW' => $date,
					'LAST_VIEW' => $date,
				]);
			}
			if ($result->isSuccess())
			{
				self::incViewsPage($lid);
				$_SESSION[self::SESSION_VIEWS_KEY][] = $lid;
			}
		}
	}

	public static function getNumberUniqueViews(int $lid): int
	{
		$lid = (int)$lid;
		$res = ViewTable::getList([
			'select' => [
				'COUNT'
			],
			'filter' => [
				'LID' => $lid
			],
			'runtime' => [
				new Entity\ExpressionField(
					'COUNT', 'COUNT(*)'
				)
			]
		]);

		if ($row = $res->fetch())
		{
			$views = (int)$row['COUNT'];
		}

		if (isset($views) && is_int($views))
		{
			return $views;
		}

		return 0;
	}

	public static function getUniqueUserData(int $lid): array
	{
		$res = ViewTable::getList([
			'select' => [
				'USER_ID'
			],
			'filter' => [
				'LID' => $lid
			]
		]);
		$data = [];
		while ($row = $res->fetch())
		{
			$data['id'][] = $row['USER_ID'];
		}
		$usersInfo = Controller\User::getUsersInfoAction($data['id']);
		$data['name'] = $usersInfo['name'];
		$countUsers = count($usersInfo['name']);
		for ($i = 0; $i < $countUsers; $i++)
		{
			if ($usersInfo['personalPhoto'][$i])
			{
				$avatar = \CFile::ResizeImageGet(
					$usersInfo['personalPhoto'][$i],
					['width' => 58, 'height' => 58],
					BX_RESIZE_IMAGE_EXACT
				);
			}
			if (isset($avatar['src']))
			{
				$data['avatar'][] = $avatar['src'];
			}
			else
			{
				$data['avatar'][] = '';
			}
			unset($avatar);
		}

		return $data;
	}
}