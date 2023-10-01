<?php

namespace Bitrix\Catalog\Controller\AgentContract;

use Bitrix\Main;
use Bitrix\Catalog;

class Entity extends Main\Engine\Controller
{
	protected function processBeforeAction(Main\Engine\Action $action)
	{
		if (!Catalog\v2\AgentContract\AccessController::check())
		{
			$this->addError(
				new Main\Error(
					Main\Localization\Loc::getMessage('CATALOG_CONTROLLER_AGENT_CONTRACT_ENTITY_PERMISSION_DENIED')
				)
			);
			return false;
		}

		return parent::processBeforeAction($action);
	}

	/**
	 * @example BX.ajax.runAction("catalog.agentcontract.entity.delete", { data: { id: #id }});
	 *
	 * @param int $id
	 * @return void
	 */
	public function deleteAction(int $id): void
	{
		$deleteResult = Catalog\v2\AgentContract\Manager::delete($id);
		if (!$deleteResult->isSuccess())
		{
			$this->addErrors($deleteResult->getErrors());
		}
	}

	/**
	 * @example BX.ajax.runAction("catalog.agentcontract.entity.deleteList", { data: { ids: #ids }});
	 *
	 * @param array $ids
	 * @return void
	 */
	public function deleteListAction(array $ids): void
	{
		foreach ($ids as $id)
		{
			$deleteResult = Catalog\v2\AgentContract\Manager::delete($id);
			if (!$deleteResult->isSuccess())
			{
				$this->addErrors($deleteResult->getErrors());
			}
		}
	}
}
