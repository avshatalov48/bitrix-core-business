<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CMainUISelectorComponentAjaxController extends \Bitrix\Main\Engine\Controller
{
	public function getTreeItemRelationAction($entityType = false, $categoryId = false)
	{
		$result = array();

		$event = new Event("main", "OnUISelectorActionProcessAjax", array(
			'action' => 'getTreeItemRelation',
			'requestFields' => array(
				'options' => array(
					'entityType' => $entityType,
					'categoryId' => $categoryId
				),
			)
		));
		$event->send();
		$eventResultList = $event->getResults();

		if (is_array($eventResultList) && !empty($eventResultList))
		{
			foreach ($eventResultList as $eventResult)
			{
				if ($eventResult->getType() == EventResult::SUCCESS)
				{
					$resultParams = $eventResult->getParameters();
					$result = $resultParams['result'];
					break;
				}
			}
		}

		return $result;
	}

	public function getDataAction(array $options = array(), array $entityTypes = array(), array $selectedItems = array())
	{
		return \Bitrix\Main\UI\Selector\Entities::getData($options, $entityTypes, $selectedItems);
	}

	public function doSearchAction($searchString = '', $searchStringConverted = '', $currentTimestamp = 0, array $options = array(), array $entityTypes = array(), array $additionalData = array())
	{
		$result = \Bitrix\Main\UI\Selector\Entities::search($options, $entityTypes, array(
			'searchString' => $searchString,
			'searchStringConverted' => $searchStringConverted,
			'additionalData' => $additionalData
		));
		$result['currentTimestamp'] = $currentTimestamp;

		return $result;
	}

	public function loadAllAction($entityType)
	{
		return \Bitrix\Main\UI\Selector\Entities::loadAll($entityType);
	}

	public function saveDestinationAction($context, $itemId)
	{
		if (
			!empty($context)
			&& !empty($itemId)
		)
		{
			\Bitrix\Main\UI\Selector\Entities::save([
				'context' => $context,
				'code' => $itemId
			]);
		}
	}

}
