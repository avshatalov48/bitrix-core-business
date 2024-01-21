<?php

namespace Bitrix\Seo\Sitemap\Internals;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Seo\Sitemap\Job;

Loc::loadMessages(__FILE__);

class JobTable extends Entity\DataManager
{

	public static function getTableName()
	{
		return 'b_seo_sitemap_job';
	}

	public static function getMap()
	{
		return [
			'ID' => new Entity\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID',
			]),
			'SITEMAP_ID' => new Entity\IntegerField('SITEMAP_ID', [
				'required' => true,
				'title' => 'Sitemap ID',
			]),
			'RUNNING' => new Entity\BooleanField('RUNNING', [
				'required' => true,
				'title' => 'If job is running now',
				'values' => ['Y', 'N'],
			]),
			'STATUS' => new Entity\StringField('STATUS', [
				'required' => true,
				'default_value' => Job::STATUS_REGISTER,
				'title' => 'Status of job',
			]),
			'STATUS_MESSAGE' => new Entity\StringField('STATUS_MESSAGE', [
				'title' => 'Text message of status current job',
			]),
			'STEP' => new Entity\IntegerField('STEP', [
				'required' => true,
				'default_value' => 0,
				'title' => 'Current step',
			]),
			'STATE' => new Entity\StringField('STATE', [
				'serialized' => true,
				'title' => 'Process state data',
			]),
			'DATE_MODIFY' => new Entity\DatetimeField('DATE_MODIFY', [
				'title' => 'Date of changes',
			]),
		];
	}

	public static function onBeforeAdd(Entity\Event $event)
	{
		return self::prepareChanges($event);
	}

	/**
	 * Before update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function onBeforeUpdate(Entity\Event $event)
	{
		return self::prepareChanges($event);
	}

	protected static function prepareChanges(Entity\Event $event): Entity\EventResult
	{
		$result = new Entity\EventResult();
		$result->modifyFields([
			'DATE_MODIFY' => (new DateTime()),
		]);

		return $result;
	}
}
