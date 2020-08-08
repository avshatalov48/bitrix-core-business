<?php


namespace Bitrix\Sale\Exchange\Integration\Controller;


use Bitrix\Main\Engine\Controller;
use Bitrix\Sale\Exchange\Integration;

class Scenarios extends Controller
{
	public function activityAddsFromOrderListAction(array $params)
	{
		$scenario = new Integration\Service\Scenarios\Controller\ActivityAdd();
		return $this
			->toArray(
				$scenario->adds($params));
	}

	public function resolveContactFieldsValuesFromOrderListAction(array $params)
	{
		return ['result' => (new Integration\Service\Scenarios\RefreshClient\Contact())
			->resolve($params)];
	}
	public function resolveUserTypeIAfterComparingRemotelyRelationFromOrderListAction(array $params)
	{
		//$result == 0 if all dependents exists

		$contacts = (new Integration\Service\Scenarios\RefreshClient\Contact())
			->diff($params)
			->toArray();

		return ['result' => $contacts];
	}
	public function contactAddsFromOrderListAction(array $params)
	{
		$scenario = new Integration\Service\Scenarios\RefreshClient\Contact();
		return $this
			->toArray(
				$scenario->adds($params)
			);
	}

	public function dealContactItemsGetAction($id)
	{
		return ['result' => (new Integration\Service\Scenarios\RefreshClientsDeal\Contact())
			->itemsGet($id)];
	}
	public function dealContactUpdatesAction($id, array $items, array $contacts)
	{
		return ['result' => (new Integration\Service\Scenarios\RefreshClientsDeal\Contact())
			->updates($id, $items, $contacts)];
	}
	public function dealContactAddsAction($id, array $items)
	{
		return ['result' => (new Integration\Service\Scenarios\RefreshClientsDeal\Contact())
			->adds($id, $items)];
	}

	protected function toArray(Integration\Service\Internal\Container\Collection $collection)
	{
		$result = [];
		/** @var Integration\Service\Internal\Container\Item $item */
		foreach ($collection as $item)
		{
			$result[$item->getInternalIndex()]= [
				'result'=>$item->getEntity()->getFieldsValues(),
				'result_error'=>$item->hasError() ? $item->getError()->getMessage(): '',
			];
		}
		return $result;
	}
}