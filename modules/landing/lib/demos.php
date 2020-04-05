<?php
namespace Bitrix\Landing;

class Demos extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'DemoTable';

	/**
	 * Delete all app items from repo.
	 * @param string $code App code.
	 * @return void
	 */
	public static function deleteByAppCode($code)
	{
		// delete blocks from repo
		$res = self::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'=APP_CODE' => $code
			)
		));
		while ($row = $res->fetch())
		{
			self::delete($row['ID']);
		}
	}
}