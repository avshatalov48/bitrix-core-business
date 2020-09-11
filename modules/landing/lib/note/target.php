<?php
namespace Bitrix\Landing\Note;

use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Site\Type;

class Target
{
	/**
	 * User option code for storage notes last sites.
	 */
	const UO_CODE_NOTES_LAST = 'notes_last';

	/**
	 * By landing id remembers user opt.
	 * @param int $landingId Landing id.
	 * @return void
	 */
	public static function rememberLastSite(int $landingId): void
	{
		$res = Landing::getList([
			'select' => [
				'SITE_ID',
				'SITE_TITLE' => 'SITE.TITLE',
				'SITE_TYPE' => 'SITE.TYPE'
			],
			'filter' => [
				'ID' => $landingId
			],
			'limit' => 1
		]);
		if ($row = $res->fetch())
		{
			$option = \CUserOptions::getOption('landing', self::UO_CODE_NOTES_LAST, []);
			foreach ($option as $item)
			{
				if ($item['SITE_ID'] == $row['SITE_ID'])
				{
					return;
				}
			}
			$option[] = $row;
			if (count($option) > 5)
			{
				unset($option[0]);
			}
			\CUserOptions::setOption('landing', self::UO_CODE_NOTES_LAST, array_values($option));
		}
	}

	/**
	 * Returns available entities short pop list for note creating.
	 * @return array
	 */
	public static function getShortList(): array
	{
		$data = [];
		$option = \CUserOptions::getOption('landing', self::UO_CODE_NOTES_LAST, []);
		$option = array_reverse($option);

		// check every item in short list on accessibility
		foreach ($option as $item)
		{
			Type::setScope($item['SITE_TYPE']);
			// because every item may by in different scopes
			$check = Rights::hasAccessForSite(
				$item['SITE_ID'],
				Rights::ACCESS_TYPES['edit']
			);
			if ($check)
			{
				$res = Site::getList([
					'select' => [
						'ID', 'TITLE', 'TYPE'
					],
					'filter' => [
						'ID' => $item['SITE_ID']
					]
				]);
				if ($row = $res->fetch())
				{
					$data[] = $row;
				}
			}
		}

		$data = \Bitrix\Landing\Binding\Group::recognizeSiteTitle(
			$data
		);

		return $data;
	}
}