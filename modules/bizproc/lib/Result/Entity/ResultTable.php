<?php

namespace Bitrix\Bizproc\Result\Entity;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\SystemException;

/**
 * Class ResultTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Result_Query query()
 * @method static EO_Result_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Result_Result getById($id)
 * @method static EO_Result_Result getList(array $parameters = [])
 * @method static EO_Result_Entity getEntity()
 * @method static \Bitrix\Bizproc\Result\Entity\EO_Result createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Result\Entity\EO_Result_Collection createCollection()
 * @method static \Bitrix\Bizproc\Result\Entity\EO_Result wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Result\Entity\EO_Result_Collection wakeUpCollection($rows)
 */
class ResultTable extends Main\ORM\Data\DataManager
{
	public static function getTableName(): string
	{
		return 'b_bp_workflow_result';
	}

	/**
	 * @throws SystemException
	 */
	public static function getMap(): array
	{
		return [
			(new Entity\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			(new Entity\StringField('WORKFLOW_ID'))
				->addValidator(new LengthValidator(1, 32)),
			(new Entity\StringField('ACTIVITY'))
				->addValidator(new LengthValidator(1, 128)),
			(new ArrayField('RESULT'))
				->configureSerializationJson(),
			new Entity\DatetimeField('CREATED_DATE'),
			new Entity\IntegerField('PRIORITY'),
		];
	}

	/**
	 * @throws SystemException
	 */
	public static function upsert(array $data): void
	{
		$priority = (int)($data['PRIORITY'] ?? 0);

		$currentResult =
			self::query()
				->setSelect(['ID', 'PRIORITY'])
				->where('WORKFLOW_ID', $data['WORKFLOW_ID'])
				->exec()
				->fetchAll();

		if (empty($currentResult))
		{
			$data['CREATED_DATE'] = new Main\Type\DateTime();
			self::add($data);
		}
		else if (isset($currentResult[0]['PRIORITY']) && ($currentResult[0]['PRIORITY'] <= $priority))
		{
			self::update($currentResult[0]['ID'], $data);
		}
	}

	public static function deleteByWorkflowId(string $workflowId): void
	{
		$iterator = static::query()->setFilter(['=WORKFLOW_ID' => $workflowId])->exec();

		while ($result = $iterator->fetchObject())
		{
			$result->delete();
		}
	}
}

