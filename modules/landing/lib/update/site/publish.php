<?php
namespace Bitrix\Landing\Update\Site;

use Bitrix\Landing\Rights;
use Bitrix\Landing\Site;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Update\Stepper;

class Publish extends Stepper
{
	public const SITE_LIMIT  = 5;

	protected static $moduleId = 'landing';

	/**
	 * One step of publish.
	 * @return bool
	 */
	public function execute(array &$result): bool
	{
		Rights::setGlobalOff();
		if (!isset($result['steps']))
		{
			$result['steps'] = 0;
		}
		$result['steps']++;

		$publishedIds = [];
		$ids = [];
		$stringIds = Option::get('landing', 'unpublished_ids', '');
		if ($stringIds !== '')
		{
			$ids = explode(',', $stringIds);
			$neededIds = array_slice($ids, 0, self::SITE_LIMIT);
			foreach ($neededIds as $id)
			{
				Site::publication($id);
				$publishedIds[] = $id;
			}
		}
		Rights::setGlobalOn();
		if ($publishedIds === $ids)
		{
			Option::delete('landing', array('name' => 'unpublished_ids'));
			return false;
		}
		$ids = array_diff($ids, $publishedIds);
		$stringIds = implode(',', $ids);

		Option::set('landing', 'unpublished_ids',  $stringIds);
		return true;
	}
}