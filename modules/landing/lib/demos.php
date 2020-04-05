<?php
namespace Bitrix\Landing;

class Demos extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Item for show in 'create site'.
	 */
	const TPL_TYPE_SITE = 'S';

	/**
	 * Item for show in 'create page'.
	 */
	const TPL_TYPE_PAGE = 'P';

	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'DemosTable';

	/**
	 * Delete all app items from repo.
	 * @param string $code App code.
	 * @return void
	 */
	public static function deleteByAppCode($code)
	{
		$demos = [];

		// delete blocks from repo
		$res = self::getList([
			'select' => [
				'ID', 'APP_CODE', 'XML_ID'
			],
			'filter' => [
				'=APP_CODE' => $code
			]
		]);
		while ($row = $res->fetch())
		{
			$demos[$row['APP_CODE'] . '.' . $row['XML_ID']] = $row;
			self::delete($row['ID']);
		}

		// and pages, which created with this templates
		if ($demos)
		{
			$res = Landing::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'=TPL_CODE' => array_keys($demos)
				]
			]);
			while ($row = $res->fetch())
			{
				Landing::delete($row['ID'], true);
			}
		}

		unset($demos, $row, $res);
	}
}