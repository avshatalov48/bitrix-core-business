<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2020 Bitrix
 */

namespace Bitrix\Iblock\Controller;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Application;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\Contract\FallbackActionInterface;
use Bitrix\Main\Engine\Controller;

/**
 * This is a facade class
 * @see DefaultElement
 *
 * @package    bitrix
 * @subpackage main
 */
final class Element extends Controller implements FallbackActionInterface
{
	protected $iblock;

	protected function getDefaultPreFilters()
	{
		return [];
	}

	/**
	 * Proxying all actions to the real controller
	 *
	 * @param string $actionName
	 *
	 * @return \Bitrix\Main\HttpResponse|mixed|null
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function fallbackAction($actionName)
	{
		$this->setSourceParametersList(array_merge(
			$this->getSourceParametersList(), [['iblock' => $this->getIblock()]]
		));

		return $this->forward($this->getController(), $actionName);
	}

	/**
	 * @return Controller
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 */
	protected function getController()
	{
		$iblock = $this->getIblock();

		$serviceLocator = ServiceLocator::getInstance();
		$serviceId = "iblock.element.{$iblock->getApiCode()}.rest.controller";

		if ($serviceLocator->has($serviceId))
		{
			// get from service locator
			$controller = $serviceLocator->get($serviceId);
		}
		else
		{
			$controller = DefaultElement::class;
		}

		return $controller;
	}

	protected function getIblock()
	{
		if ($this->iblock === null)
		{
			$iblockId = Application::getInstance()->getContext()->getRequest()->get('iblockId');

			$this->iblock = IblockTable::getByPrimary($iblockId, [
				'select' => ['ID', 'API_CODE']
			])->fetchObject();
		}

		return $this->iblock;
	}
}
