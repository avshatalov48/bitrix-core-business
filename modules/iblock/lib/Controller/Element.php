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
		return $this->forward($this->getController(), $actionName, $this->getParameters());
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

	protected function getParameters()
	{
		$parameters = $this->getSourceParametersList()[0];

		// add iblock object
		$parameters['iblock'] = $this->getIblock();

		return $parameters;
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
