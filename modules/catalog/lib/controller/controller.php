<?php


namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Model\Event;
use Bitrix\Catalog\RestView\CatalogViewManager;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;
use Bitrix\Rest\Event\EventBind;
use Bitrix\Rest\Event\EventBindInterface;
use Bitrix\Rest\Integration\Controller\Base;

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

	const CATALOG_STORE = ActionDictionary::ACTION_STORE_VIEW;
	const CATALOG_READ = ActionDictionary::ACTION_CATALOG_READ;
	const CATALOG_GROUP = ActionDictionary::ACTION_PRICE_GROUP_EDIT;
	const CATALOG_VAT = ActionDictionary::ACTION_VAT_EDIT;

	public const ERROR_ACCESS_DENIED = 'Access denied';
	protected AccessController $accessController;

	/**
	 * @inheritDoc
	 */
	protected function init()
	{
		parent::init();

		$this->accessController = AccessController::getCurrent();
	}

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

	private function getServiceName(): string
	{
		return (new \ReflectionClass($this))->getShortName();
	}

	protected function getServiceItemName(): string
	{
		$converter = new Converter(Converter::TO_UPPER | Converter::TO_SNAKE_DIGIT);

		return $converter->process($this->getServiceName());
	}

	protected function getServiceListName(): string
	{
		return $this->getServiceItemName() . 'S';
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
