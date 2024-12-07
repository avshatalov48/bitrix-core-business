<?php

namespace Bitrix\Bizproc\Workflow\Task;

use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Search\Content;

/**
 * Class TaskSearchContentTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_TaskSearchContent_Query query()
 * @method static EO_TaskSearchContent_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_TaskSearchContent_Result getById($id)
 * @method static EO_TaskSearchContent_Result getList(array $parameters = [])
 * @method static EO_TaskSearchContent_Entity getEntity()
 * @method static \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent_Collection createCollection()
 * @method static \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent_Collection wakeUpCollection($rows)
 */
class TaskSearchContentTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'b_bp_task_search_content';
	}

	public static function getMap(): array
	{
		return [
			(new ORM\Fields\IntegerField('TASK_ID'))
				->configurePrimary()
			,
			(new ORM\Fields\StringField('WORKFLOW_ID'))
				->configureRequired(true)
				->addValidator(new ORM\Fields\Validators\LengthValidator(1, 32))
			,
			(new ORM\Fields\TextField('SEARCH_CONTENT'))
				->addSaveDataModifier([static::class, 'prepareSearchContent'])
				->configureRequired(true)
			,
			new ORM\Fields\Relations\Reference(
				'TASK',
				TaskTable::class,
				ORM\Query\Join::on('this.TASK_ID', 'ref.ID')
			),
			new ORM\Fields\Relations\OneToMany(
				'USERS',
				TaskUserTable::class,
				'USER_TASKS_SEARCH_CONTENT'
			),
		];
	}

	public static function prepareSearchContent(string $content): ?string
	{
		$content = trim($content);
		if (Content::isIntegerToken($content))
		{
			$content = Content::prepareIntegerToken($content);
		}
		else
		{
			$content = Content::prepareStringToken($content);
		}

		if (Content::canUseFulltextSearch($content, Content::TYPE_MIXED))
		{
			return $content;
		}

		return null;
	}
}
