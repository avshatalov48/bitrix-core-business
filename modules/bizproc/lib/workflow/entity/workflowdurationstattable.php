<?php

namespace Bitrix\Bizproc\Workflow\Entity;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class WorkflowDurationStatTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkflowDurationStat_Query query()
 * @method static EO_WorkflowDurationStat_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkflowDurationStat_Result getById($id)
 * @method static EO_WorkflowDurationStat_Result getList(array $parameters = [])
 * @method static EO_WorkflowDurationStat_Entity getEntity()
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat_Collection createCollection()
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat_Collection wakeUpCollection($rows)
 */
class WorkflowDurationStatTable extends DataManager
{
	private const DURATION_ROWS_LIMIT = 20;
	private const AVERAGE_DURATION_DEVIATION_PERCENT = 16;
	private const AVERAGE_DURATIONS_CACHE_TTL = 86400; // 60 * 60 * 24

	private static array $cutDurationStatQueue = [];

	public static function getTableName()
	{
		return 'b_bp_workflow_duration_stat';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configureAutocomplete(true)
				->configurePrimary(true)
			,
			(new StringField('WORKFLOW_ID'))
				->configureRequired(true)
				->configureSize(32)
				->addValidator(new LengthValidator(1, 32))
			,
			(new IntegerField('TEMPLATE_ID'))
				->configureRequired(true)
			,
			(new IntegerField('DURATION'))
				->configureRequired(true)
			,
		];
	}

	public static function getAverageDurationByTemplateId(int $templateId): ?int
	{
		$averageDuration = null;

		if ($templateId > 0)
		{
			$query =
				static::query()
					->setSelect(['ID', 'DURATION'])
					->where('TEMPLATE_ID', $templateId)
					->addOrder('ID', 'DESC')
					->setLimit(self::DURATION_ROWS_LIMIT)
					// ->setCacheTtl(static::AVERAGE_DURATIONS_CACHE_TTL)
			;
			$duration = $query->exec()->fetchCollection()->getDurationList();
			$total = count($duration);

			if ($total > 0)
			{
				$deviationCount = (int)(($total * self::AVERAGE_DURATION_DEVIATION_PERCENT) / 200);
				$length = $total - 2 * $deviationCount;

				$slicedDuration = $duration;
				if ($deviationCount !== 0)
				{
					sort($duration, SORT_NUMERIC);
					$slicedDuration = array_slice($duration, $deviationCount, $length);
				}

				$averageDuration = (int)(array_sum($slicedDuration) / $length);
			}
		}

		return $averageDuration;
	}

	public static function getOutdatedIds(int $templateId): array
	{
		$ids = [];
		if ($templateId > 0)
		{
			$query =
				static::query()
					->addSelect('ID')
					->where('TEMPLATE_ID', $templateId)
					->addOrder('ID', 'DESC')
					->setOffset(static::DURATION_ROWS_LIMIT)
					->setLimit(100)
			;
			$queryResult = $query->exec();
			$ids = $queryResult->fetchCollection()->getIdList();
		}

		return $ids;
	}

	public static function deleteAllByTemplateId(int $templateId)
	{
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$tableName = $sqlHelper->forSql(static::getTableName());

		$connection->queryExecute("DELETE FROM {$tableName} WHERE TEMPLATE_ID = {$templateId}");
	}

	public static function onAfterAdd(Event $event)
	{
		parent::onAfterAdd($event);

		$fields = $event->getParameter('fields');
		$templateId = $fields['TEMPLATE_ID'] ?? 0;

		self::$cutDurationStatQueue[$templateId] = true;
		static $isAddedBackgroundJod = false;
		if (!$isAddedBackgroundJod)
		{
			Main\Application::getInstance()->addBackgroundJob(
				[static::class, 'doBackgroundDurationStatCut'],
				[],
				Main\Application::JOB_PRIORITY_LOW - 10,
			);
			$isAddedBackgroundJod = true;
		}
	}

	public static function doBackgroundDurationStatCut()
	{
		$connection = Application::getConnection();
		$tableName = $connection->getSqlHelper()->forSql(static::getTableName());

		$templateIds = array_keys(self::$cutDurationStatQueue);
		self::$cutDurationStatQueue = [];

		foreach ($templateIds as $templateId)
		{
			$ids = static::getOutdatedIds((int)$templateId);
			if ($ids)
			{
				$connection->query(
					sprintf(
						"DELETE FROM {$tableName} WHERE ID IN (%s)",
						implode(',', $ids)
					),
				);
			}
		}
	}

}
