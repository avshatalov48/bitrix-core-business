<?php

namespace Bitrix\Rest\Repository;

use Bitrix\Main\Entity\Query;
use Bitrix\Rest\Entity\Collection\IntegrationCollection;
use Bitrix\Rest\Entity\Integration;
use Bitrix\Rest\Enum\APAuth\PasswordType;
use Bitrix\Rest\Enum\Integration\ElementCodeType;
use Bitrix\Rest\Preset\EO_Integration_Query;
use Bitrix\Rest\Preset\IntegrationTable;

class IntegrationRepository implements \Bitrix\Rest\Contract\Repository\IntegrationRepository
{
	public function __construct(
		private readonly \Bitrix\Rest\Model\Mapper\Integration $mapper,
	)
	{}

	public function getCloudPaidIntegrations(): IntegrationCollection
	{
		$integrationList = $this->buildNotSystemIntegrationsQuery()
			->addSelect('*')
			->setOrder(['ID' => 'ASC'])
			->fetchAll();

		return $this->createIntegrationCollectionFromModelArray($integrationList);
	}

	public function getBoxedPaidIntegrations(): IntegrationCollection
	{
		$integrationList = $this->buildNotSystemIntegrationsQuery()
			->addSelect('*')
			->setOrder(['ID' => 'ASC'])
			->addFilter('!=ELEMENT_CODE', ElementCodeType::IN_WEBHOOK->value)
			->fetchAll();

		return $this->createIntegrationCollectionFromModelArray($integrationList);
	}

	public function hasUserIntegrations(): bool
	{
		$integration = $this->buildNotSystemIntegrationsQuery()
			->setLimit(1)
			->setSelect(['ID']);

		return !empty($integration->fetch());
	}

	public function hasNotInWebhookUserIntegrations(): bool
	{
		$query = $this->buildNotSystemIntegrationsQuery()
			->setLimit(1)
			->setSelect(['ID'])
			->addFilter('!=ELEMENT_CODE', ElementCodeType::IN_WEBHOOK->value);

		return !empty($query->fetch());
	}

	/**
	 * @return EO_Integration_Query
	 */
	private function buildNotSystemIntegrationsQuery(): Query
	{
		return IntegrationTable::query()
			->where(Query::filter()
				->logic('or')
				->where([
					['PASSWORD.TYPE', PasswordType::User->value],
					['PASSWORD.TYPE', null]
				])
			);
	}

	public function getById(int $id): ?Integration
	{
		$integration = IntegrationTable::query()
			->addSelect('*')
			->where('ID', $id)
			->setLimit(1)
			->fetch();

		if (!$integration)
		{
			return null;
		}

		return $this->mapper->mapArrayToEntity($integration);
	}

	/**
	 * @param \Bitrix\Rest\Preset\EO_Integration_Collection $modelCollection
	 * @return IntegrationCollection
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function createIntegrationCollectionFromModelArray(array $modelCollection): IntegrationCollection
	{
		$collection = new IntegrationCollection();
		foreach ($modelCollection as $model)
		{
			$collection->add($this->mapper->mapArrayToEntity($model));
		}

		return $collection;
	}
}