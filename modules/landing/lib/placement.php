<?php
namespace Bitrix\Landing;

class Placement extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'PlacementTable';

	/**
	 * Delete records by app id.
	 * @param int $id App id.
	 * @return void
	 */
	public static function deleteByAppId($id)
	{
		$res = self::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'=APP_ID' => $id
			)
 		));
		while ($row = $res->fetch())
		{
			self::delete($row['ID']);
		}
	}
}