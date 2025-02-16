<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Rest\Event\EventBindInterface;
use Bitrix\Rest\RestException;

final class PriceType extends Controller implements EventBindInterface
{
	use ListAction; // default listAction realization
	use GetAction; // default getAction realization
	use CheckExists; // default implementation of existence check
	use PriceTypeRights;

	const EVENT_ON_ADD = 'OnGroupAdd';
	const EVENT_ON_UPDATE = 'OnGroupUpdate';
	const EVENT_ON_DELETE = 'OnGroupDelete';

	private const USER_GROUP_ADMINS = 1;
	private const USER_GROUP_ALL_USERS = 2;

	//region Actions

	/**
	 * @param array $fields
	 * @return array|null
	 */
	public function addAction(array $fields): ?array
	{
		$application = self::getApplication();
		$application->ResetException();

		$fields['USER_GROUP'] = $fields['USER_GROUP_BUY'] = [self::USER_GROUP_ADMINS, self::USER_GROUP_ALL_USERS];

		$addResult = \CCatalogGroup::Add($fields);
		if (!$addResult)
		{
			if ($application->GetException())
			{
				$this->addError(new Error($application->GetException()->GetString()));
			}
			else
			{
				$this->addError(new Error('Error adding price type'));
			}
			return null;
		}

		return [$this->getServiceItemName() => $this->get($addResult)];
	}

	/**
	 * @param int $id
	 * @param array $fields
	 * @return array|null
	 */
	public function updateAction(int $id, array $fields): ?array
	{
		$existsResult = $this->exists($id);
		if (!$existsResult->isSuccess())
		{
			$this->addErrors($existsResult->getErrors());
			return null;
		}

		$application = self::getApplication();
		$application->ResetException();

		$updateResult = \CCatalogGroup::Update($id, $fields);
		if (!$updateResult)
		{
			if ($application->GetException())
			{
				$this->addError(new Error($application->GetException()->GetString()));
			}
			else
			{
				$this->addError(new Error('Error updating price type'));
			}
			return null;
		}

		return [$this->getServiceItemName() => $this->get($id)];
	}

	/**
	 * @param int $id
	 * @return bool|null
	 */
	public function deleteAction(int $id): ?bool
	{
		$existsResult = $this->exists($id);
		if (!$existsResult->isSuccess())
		{
			$this->addErrors($existsResult->getErrors());
			return null;
		}

		$application = self::getApplication();
		$application->ResetException();

		$deleteResult = \CCatalogGroup::Delete($id);
		if (!$deleteResult)
		{
			if ($application->GetException())
			{
				$this->addError(new Error($application->GetException()->GetString()));
			}
			else
			{
				$this->addError(new Error('Error deleting price type'));
			}
			return null;
		}

		return true;
	}

	/**
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return [$this->getServiceItemName() => $this->getViewFields()];
	}

	/**
	 * public function listAction
	 * @see ListAction::listAction
	 */

	/**
	 * public function getAction
	 * @see GetAction::getAction
	 */

	protected function getErrorCodeEntityNotExists(): string
	{
		return ErrorCode::PRICE_TYPE_ENTITY_NOT_EXISTS;
	}
	//endregion

	/**
	 * @inheritDoc
	 */
	protected function getEntityTable()
	{
		return new \Bitrix\Catalog\GroupTable();
	}

	// rest-event region
	protected static function getBindings(): array
	{
		$entity = (new self())->getEntity();

		return [
			self::EVENT_ON_ADD => $entity->getModule().'.price.type.on.add',
			self::EVENT_ON_UPDATE => $entity->getModule().'.price.type.on.update',
			self::EVENT_ON_DELETE => $entity->getModule().'.price.type.on.delete',
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function getCallbackRestEvent(): array
	{
		return [self::class, 'processItemEvent'];
	}

	/**
	 * @param array $params
	 * @param array $handler
	 * @return array[]
	 */
	public static function processItemEvent(array $params, array $handler): array
	{
		$id = $params[0] ?? null;
		if (!$id)
		{
			throw new RestException('id not found trying to process event');
		}

		return [
			'FIELDS' => [
				'ID' => $id,
			],
		];
	}
	// endregion
}
