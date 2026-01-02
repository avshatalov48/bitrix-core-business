<?php

namespace Bitrix\UI\Infrastructure\Agent\EntityEditorConfig;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Ui\EntityForm\EntityFormConfigAcTable;
use Bitrix\Ui\EntityForm\EntityFormConfigTable;
use Bitrix\UI\Infrastructure\Agent\AgentBase;
use Bitrix\UI\Integration\HumanResources\DepartmentQueries;
use Bitrix\UI\Integration\HumanResources\HumanResources;
use COption;

class CrmAccessCodesConverterAgent extends AgentBase
{
	public const AGENT_DONE_STOP_IT = false;
	public const PERIODICAL_AGENT_RUN_LATER = true;
	public const LIMIT = 100;
	private const MODULE_NAME = 'ui';

	public static function doRun(): bool
	{
		$instance = new self();

		if (!HumanResources::getInstance()->isUsed())
		{
			$instance->setExecutionPeriod(86400);

			return self::PERIODICAL_AGENT_RUN_LATER;
		}

		$instance->setIsConvertingOption();

		if ($instance->hasUnconvertedAccessCodes())
		{
			$isInProgress = $instance->execute();
			if ($isInProgress)
			{
				return self::PERIODICAL_AGENT_RUN_LATER;
			}
		}

		$instance->cleanUp();

		return self::AGENT_DONE_STOP_IT;
	}

	private function setIsConvertingOption(): void
	{
		COption::SetOptionString(self::MODULE_NAME, HumanResources::IS_CONVERTED_OPTION_NAME, 'N');
	}

	private function cleanUp(): void
	{
		COption::RemoveOption(self::MODULE_NAME, HumanResources::IS_CONVERTED_OPTION_NAME);
	}

	private function hasUnconvertedAccessCodes(): bool
	{
		$result = $this->getQuery()
			->setSelect(['ID'])
			->setLimit(1)
			->fetch()
		;

		return (bool)($result['ID'] ?? false);
	}

	private function execute(): bool
	{
		$items = $this->getItems();

		$accessCodes = array_map(
			static fn(string $code) => str_replace('DR', 'D', $code),
			array_unique(array_column($items, 'ACCESS_CODE')),
		);

		if (empty($accessCodes))
		{
			return false;
		}

		$departmentsQueries = DepartmentQueries::getInstance();
		$humanResources = HumanResources::getInstance();
		foreach ($items as $item)
		{
			$department = $departmentsQueries->getDepartmentByAccessCode($item['ACCESS_CODE']);
			if (!$department)
			{
				continue;
			}

			EntityFormConfigAcTable::update($item['ID'], [
				'ACCESS_CODE' => $humanResources->buildAccessCode('SNDR', $department->id),
			]);
		}

		return true;
	}

	private function getItems(): array
	{
		return $this->getQuery()
			->setSelect(['*'])
			->setLimit(self::LIMIT)
			->fetchAll()
		;
	}

	private function getQuery(): Query
	{
		return EntityFormConfigAcTable::query()
			->registerRuntimeField(
				new Reference(
					'CONFIG_CATEGORY',
					EntityFormConfigTable::class,
					Join::on('this.CONFIG_ID', 'ref.ID')
						->where('ref.CATEGORY', 'crm')
						->where('ref.OPTION_CATEGORY', 'crm.entity.editor'),
					['join_type' => Join::TYPE_LEFT],
				),
			)
			->whereLike('ACCESS_CODE', 'DR%')
		;
	}
}
