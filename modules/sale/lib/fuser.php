<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Sale\Internals;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Fuser
{
	function __construct()
	{

	}

	/**
	 * Return fuserId.
	 *
	 * @param bool $skipCreate		Create, if not exist.
	 * @return int|null
	 */
	public static function getId($skipCreate = false)
	{
		global $USER;

		$id = null;

		static $fuserList = array();

		if ((isset($USER) && $USER instanceof \CUser) && $USER->IsAuthorized())
		{
			$currentUserId = (int)$USER->GetID();
			if (!isset($fuserList[$currentUserId]))
			{
				$fuserList[$currentUserId] = static::getIdByUserId($currentUserId);
			}
			$id = $fuserList[$currentUserId];
			unset($currentUserId);
		}

		if ((int)$id <= 0)
		{
			$id = \CSaleUser::getID($skipCreate);
		}
		static::updateSession($id);
		return $id;
	}

	/**
	 * Update session data
	 *
	 * @param int $id				FuserId.
	 * @return void
	 */
	protected static function updateSession($id)
	{
		\CSaleUser::updateSessionSaleUserID();

		$session = Application::getInstance()->getSession();
		if (!$session->isAccessible())
		{
			return;
		}

		if (isset($session['SALE_USER_ID']) && Main\Config\Option::get('sale', 'encode_fuser_id') !== 'Y')
		{
			$session['SALE_USER_ID'] = (int)$session['SALE_USER_ID'];
		}

		if (!isset($session['SALE_USER_ID']) || empty($session['SALE_USER_ID']))
		{
			$session['SALE_USER_ID'] = $id;
		}
	}

	/**
	 * Return fuser code.
	 *
	 * @return int
	 */
	protected static function getCode()
	{
		return \CSaleUser::getFUserCode();
	}

	/**
	 * Return fuserId for user.
	 *
	 * @param int $userId			User Id.
	 * @return false|int
	 * @throws Main\ArgumentException
	 */
	public static function getIdByUserId($userId)
	{
		$res = Internals\FuserTable::getList(array(
			'filter' => array(
				'USER_ID' => $userId
			),
			'select' => array(
				'ID'
			),
			'order' => array('ID' => "DESC")
		));
		if ($fuserData = $res->fetch())
		{
			return (int)$fuserData['ID'];
		}
		else
		{
			/** @var Result $r */
			$r = static::createForUserId($userId);
			if ($r->isSuccess())
			{
				return $r->getId();
			}
		}

		return false;
	}

	/**
	 * Return user by fuserId.
	 *
	 * @param int $fuserId		Fuser Id.
	 * @return int
	 * @throws Main\ArgumentException
	 */
	public static function getUserIdById($fuserId)
	{
		$result = 0;

		$fuserId = (int)$fuserId;
		if ($fuserId <= 0)
			return $result;
		$row = Internals\FuserTable::getList(array(
			'select' => array('USER_ID'),
			'filter' => array('=ID' => $fuserId),
			'order' => array('ID' => "DESC")
		))->fetch();
		if (!empty($row))
			$result = (int)$row['USER_ID'];

		return $result;
	}

	/**
	 * Delete fuserId over several days.
	 *
	 * @param int $days			Interval.
	 * @return void
	 */
	public static function deleteOld($days)
	{
		$expired = new Main\Type\DateTime();
		$expired->add('-'.$days.' days');
		$expiredValue = $expired->format('Y-m-d H:i:s');

		/** @var Main\DB\Connection $connection */
		$connection = Main\Application::getConnection();
		/** @var Main\DB\SqlHelper $sqlHelper */
		$sqlHelper = $connection->getSqlHelper();

		$query = "DELETE FROM b_sale_fuser WHERE
									b_sale_fuser.DATE_UPDATE < ".$sqlHelper->getDateToCharFunction("'".$expiredValue."'")."
									AND b_sale_fuser.USER_ID IS NULL
									AND b_sale_fuser.id NOT IN (select FUSER_ID from b_sale_basket)";
		$connection->queryExecute($query);
	}

	/**
	 * Create new fuserId for user.
	 *
	 * @param int $userId				User id.
	 * @return Main\Entity\AddResult
	 * @throws \Exception
	 */
	protected static function createForUserId($userId)
	{
		$fields = array(
			'DATE_INSERT' => new Main\Type\DateTime(),
			'DATE_UPDATE' => new Main\Type\DateTime(),
			'USER_ID' => $userId,
			'CODE' => md5(time().randString(10))
		);

		/** @var Result $r */
		return Internals\FuserTable::add($fields);
	}
}
