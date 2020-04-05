<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Registry;
use Bitrix\Sale\TradeBindingCollection;

class TradeBinding extends Controller
{
	public function getFieldsAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\TradeBinding();
		return ['TRADE_BINDING'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var TradeBindingCollection $tradeBindingCollection */
		$tradeBindingCollection = $registry->get(Registry::ENTITY_TRADE_BINDING_COLLECTION);

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

			return count(
				$tradeBindingCollection::getList(['filter'=>$filter])->fetchAll()
			);
		});
	}

	static public function prepareFields($fields)
	{
		return isset($fields['TRADE_BINDINGS'])?['TRADE_BINDINGS'=>$fields['TRADE_BINDINGS']]:[];
	}
}