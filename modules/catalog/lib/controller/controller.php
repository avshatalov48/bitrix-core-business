<?php


namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Model\Event;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;
use Bitrix\Rest\Event\EventBind;
use Bitrix\Rest\Event\EventBindInterface;
use Bitrix\Rest\Integration\CatalogViewManager;
use Bitrix\Rest\Integration\Controller\Base;
use phpDocumentor\Reflection\DocBlock\Tags\Method;

class Controller extends Base
{
	const IBLOCK_READ = 'iblock_admin_display';
	const IBLOCK_ELEMENT_READ = 'element_read';
	const IBLOCK_ELEMENT_EDIT = 'element_edit';
	const IBLOCK_ELEMENT_DELETE = 'element_delete';
	const IBLOCK_SECTION_READ = 'section_read';
	const IBLOCK_SECTION_EDIT = 'section_edit';
	const IBLOCK_SECTION_DELETE = 'section_delete';
	const IBLOCK_ELEMENT_EDIT_PRICE = 'element_edit_price';
	const IBLOCK_SECTION_SECTION_BIND = 'section_section_bind';
	const IBLOCK_ELEMENT_SECTION_BIND = 'section_element_bind';
	const IBLOCK_EDIT = 'iblock_edit';
	const CATALOG_STORE = 'catalog_store';
	const CATALOG_READ = 'catalog_read';
	const CATALOG_GROUP = 'catalog_group';
	const CATALOG_VAT = 'catalog_vat';

	public const ERROR_ACCESS_DENIED = 'Access denied';

	protected function createViewManager(Action $action)
	{
		return new CatalogViewManager($action);
	}

	protected static function getApplication()
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		return $APPLICATION;
	}

	protected static function getGlobalUser()
	{
		/** @global \CUser $USER */
		global $USER;

		return $USER;
	}

	protected static function getNavData($start, $orm = false)
	{
		if($start >= 0)
		{
			return ($orm ?
				['limit' => \IRestService::LIST_LIMIT, 'offset' => intval($start)]
				:['nPageSize' => \IRestService::LIST_LIMIT, 'iNumPage' => intval($start / \IRestService::LIST_LIMIT) + 1]
			);
		}
		else
		{
			return ($orm ?
				['limit' => \IRestService::LIST_LIMIT]
				:['nTopCount' => \IRestService::LIST_LIMIT]
			);
		}
	}

	/**
	 *
	 * Get fields rest-view
	 *
	 * @return array|null
	 */
	protected function getViewFields(): ?array
	{
		$view =
			$this
				->getViewManager()
				->getView($this)
		;

		if (!$view)
		{
			return null;
		}

		return $view->prepareFieldInfos($view->getFields());
	}

	/**
	 * @return \Bitrix\Main\ORM\Entity
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getEntity(): Entity
	{
		return $this->getEntityTable()::getEntity();
	}

	// rest-event region
	/**
	 * @implements EventBindInterface
	 */
	public static function getCallbackRestEvent(): array
	{
		return [EventBind::class, 'processItemEvent'];
	}

	/**
	 * @implements EventBindInterface
	 */
	public static function getHandlers(): array
	{
		return (new EventBind(static::class))->getHandlers(static::getBindings());
	}

	/**
	 *
	 * Get bindings from PHP events to REST events
	 *
	 * @return string[]
	 */
	protected static function getBindings(): array
	{
		$entity = (new static())->getEntity();
		$class = $entity->getNamespace() . $entity->getName();

		return [
			Event::makeEventName($class,DataManager::EVENT_ON_AFTER_ADD) => $entity->getModule().'.'.$entity->getName().'.on.add',
			Event::makeEventName($class,DataManager::EVENT_ON_AFTER_UPDATE) => $entity->getModule().'.'.$entity->getName().'.on.update',
			Event::makeEventName($class,DataManager::EVENT_ON_DELETE) => $entity->getModule().'.'.$entity->getName().'.on.delete',
		];
	}
	// endregion
}