<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Scenarios;

class DealUpdate extends Base
{
	public function update($id, array $params)
	{
		static::dealAddsRelation($params);

		$activity = new ActivityAdd();
		$activity->adds($activity::prepareFields($params));

		$deal = new RefreshClientsDeal\Contact();
		$deal->refreshById($id, $params);
	}
}