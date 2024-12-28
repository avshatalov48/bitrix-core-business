<?php

namespace Bitrix\Bizproc\Workflow\Template;

use Bitrix\Main\Error;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Result;

/**
 * Class WorkflowTemplateUserOptionTable
 */
class WorkflowTemplateUserOptionTable extends DataManager
{
	public const PINNED = 1;

	public static function getTableName(): string
	{
		return 'b_bp_workflow_template_user_option';
	}

	public static function getMap(): array
	{
		return [
			(new ORM\Fields\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new ORM\Fields\IntegerField('TEMPLATE_ID'))
				->configureRequired()
			,
			(new ORM\Fields\IntegerField('USER_ID'))
				->configureRequired()
			,
			(new ORM\Fields\IntegerField('OPTION_CODE'))
				->configureRequired()
			,
			new ORM\Fields\Relations\Reference(
				'TEMPLATE',
				Entity\WorkflowTemplateTable::class,
				ORM\Query\Join::on('this.TEMPLATE_ID', 'ref.ID')
			),
		];
	}

	public static function isOption(int $option): bool
	{
		return in_array($option, [self::PINNED], true);
	}

	public static function addOption(int $templateId, int $userId, int $option): Result
	{
		$addResult = new Result();

		if ($templateId <= 0 || $userId <= 0 || !static::isOption($option))
		{
			$addResult->addError(new Error('Some parameter is wrong.', 1));
			return $addResult;
		}

		$data = [
			'TEMPLATE_ID' => $templateId,
			'USER_ID' => $userId,
			'OPTION_CODE' => $option,
		];

		$item = self::getList([
			'select' => ['ID'],
			'filter' => $data,
		])->fetch();

		if (!$item)
		{
			$tableAddResult = self::add($data);
			if (!$tableAddResult->isSuccess())
			{
				$addResult->addError(new Error('Adding to table failed.', 2));
				return $addResult;
			}

			return $addResult;
		}

		return $addResult;
	}

	public static function deleteOption(int $templateId, int $userId, int $option): Result
	{
		$deleteResult = new Result();

		if ($templateId <= 0 || $userId <= 0 || !static::isOption($option))
		{
			$deleteResult->addError(new Error('Some parameter is wrong.', 1));
			return $deleteResult;
		}

		$item = self::getList([
			'select' => ['ID'],
			'filter' => [
				'TEMPLATE_ID' => $templateId,
				'USER_ID' => $userId,
				'OPTION_CODE' => $option,
			],
		])->fetch();

		if ($item)
		{
			$tableDeleteResult = self::delete($item);
			if (!$tableDeleteResult->isSuccess())
			{
				$deleteResult->addError(new Error('Deleting from table failed.', 2));

				return $deleteResult;
			}
		}

		return $deleteResult;
	}
}
