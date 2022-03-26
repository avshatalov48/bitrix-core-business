<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Error;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Result;
use Bitrix\Sale\TradeBindingCollection;

class TradeBinding extends ControllerBase
{
	public function getFieldsAction()
	{
		$view = $this->getViewManager()
			->getView($this);

		return ['TRADE_BINDING'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var TradeBindingCollection $tradeBindingCollection */
		$tradeBindingCollection = $registry->get(Registry::ENTITY_TRADE_BINDING_COLLECTION);
// print_r($select);die;
		$tradeBindings = $tradeBindingCollection::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit()
			]
		)->fetchAll();

		return new Page('TRADE_BINDINGS', $tradeBindings, function() use ($filter)
		{
			$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
			/** @var TradeBindingCollection $tradeBindingCollection */
			$tradeBindingCollection = $registry->get(Registry::ENTITY_TRADE_BINDING_COLLECTION);

			return (int) $tradeBindingCollection::getList([
				'select' => ['CNT'],
				'filter'=>$filter,
				'runtime' => [
					new ExpressionField('CNT', 'COUNT(ID)')
				]
			])->fetch()['CNT'];
		});
	}

	static public function prepareFields($fields)
	{
		return isset($fields['TRADE_BINDINGS'])?['TRADE_BINDINGS'=>$fields['TRADE_BINDINGS']]:[];
	}

	protected function checkReadPermissionEntity(): Result
	{
		$r = new Result();

		$saleModulePermissions = self::getApplication()->GetGroupRight("sale");
		if ($saleModulePermissions  == "D")
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}
		return $r;
	}
}