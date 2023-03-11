<?php


namespace Bitrix\Catalog\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Rest\Event\EventBindInterface;
use Bitrix\Rest\RestException;

final class PriceType extends Controller implements EventBindInterface
{
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

		return ['PRICE_TYPE' => $this->get($addResult)];
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

		return ['PRICE_TYPE' => $this->get($id)];
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
		return ['PRICE_TYPE' => $this->getViewFields()];
	}

	/**
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param PageNavigation|null $pageNavigation
	 * @return Page
	 */
	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): Page
	{
		return new Page(
			'PRICE_TYPES',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	/**
	 * @param $id
	 * @return array|null
	 */
	public function getAction($id): ?array
	{
		$r = $this->exists($id);
		if ($r->isSuccess())
		{
			return ['PRICE_TYPE' => $this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}
	//endregion

	/**
	 * @inheritDoc
	 */
	protected function getEntityTable()
	{
		return new \Bitrix\Catalog\GroupTable();
	}

	/**
	 * @inheritDoc
	 */
	protected function exists($id)
	{
		$r = new Result();
		if (isset($this->get($id)['ID']) == false)
		{
			$r->addError(new Error('Price type is not exists'));
		}

		return $r;
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
	 * @param array $arParams
	 * @param array $arHandler
	 * @return array[]
	 */
	public static function processItemEvent(array $arParams, array $arHandler): array
	{
		$id = $arParams[0] ?? null;
		if (!$id)
		{
			throw new RestException('id not found trying to process event');
		}

		return [
			'FIELDS' => [
				'ID' => $id
			],
		];
	}
	// endregion
}
