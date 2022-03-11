<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Connector;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\ListTable;
use Bitrix\Sender\Posting\SegmentDataBuilder;
use Bitrix\Sender\UI;
use Bitrix\Sender\Entity;

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

$actions = array();
$actions[] = Controller\Action::create('getCount')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$content = $response->initContentJson();
		$content->set(array(
			'filterId' => '',
			'count' => Connector\DataCounter::getDefaultArray(),
		));

		$settings = $request->get('CONNECTOR_SETTING');
		if(!$settings || !is_array($settings))
		{
			return;
		}

		$endpoints = Connector\Manager::getEndpointFromFields($settings);
		foreach ($endpoints as $endpoint)
		{
			$connector = Connector\Manager::getConnector($endpoint);
			if (!$connector)
			{
				continue;
			}

			$fieldValues = $endpoint['FIELDS'];
			if (!is_array($fieldValues))
			{
				$fieldValues = array();
			}

			$connector->setFieldValues($fieldValues);
			$content->add('count', $connector->getDataCounter()->getArray());
			break;
		}
	}
);
$actions[] = Controller\Action::create('getFilterData')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$content = $response->initContentJson();
		$content->set(array(
			'filterId' => '',
			'data' => array(),
			'num' => 0,
			'count' => Connector\DataCounter::getDefaultArray(),
		));

		$filterId = $request->get('filterId');
		$groupId = $request->get('groupId');
		if(!$filterId)
		{
			return;
		}

		$paths = explode('_', $filterId);
		$num = array_pop($paths);
		$moduleId = array_shift($paths);
		$code = implode('_', $paths);

		$connector = Connector\Manager::getConnector(array(
			'MODULE_ID' => $moduleId,
			'CODE' => $code
		));

		if (!$connector || !($connector instanceof Connector\BaseFilter))
		{
			$content->addError('Filter not found.');
			return;
		}

		$content->add('num', $num);

		$fields = $connector->getUiFilterData($filterId);

		$endpoint = [
			'CODE' => $code,
			'FIELDS' => $fields,
			'MODULE_ID' => $moduleId,
		];
		$content->add('data', $fields);

		$connector->setDataTypeId(null);
		$connector->setFieldValues($fields);

		$entitySegment = new Entity\Segment($groupId);
		$endpoints = $entitySegment->getData()['ENDPOINTS'];

		for ($i = 0; $i < count($endpoints); $i++)
		{
			if ($endpoints[$i]['FILTER_ID'] === $filterId)
			{
				unset($endpoints[$i]);
			}
		}

		$endpoint['FILTER_ID'] = $filterId;
		$endpoints[] = $endpoint;
		$data = [
			'NAME' => trim($request->get('name')),
			'HIDDEN' => $request->get('hidden') == 'Y' ? 'Y' : 'N',
			'ENDPOINTS' => $endpoints,
		];
		$entitySegment->mergeData($data);
		$entitySegment->save();

		if ($entitySegment->getErrors())
		{
			$content->addError($entitySegment->getErrors()[0]);
			return;
		}

		$dataBuilder = new SegmentDataBuilder($groupId, $filterId, $endpoint);
		if (!$dataBuilder->prepareForAgent(false, true))
		{
			$content->add('count', (
			$connector instanceof Connector\IncrementallyConnector
				? $dataBuilder->calculateCurrentFilterCount()->getArray()
				: $connector->getDataCounter()->getArray()
			));

			return;
		}

		$content->add('waiting', true);
	}
);
$actions[] = Controller\Action::create('getContactSets')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$view = UI\TileView::create()->addSection(UI\TileView::SECTION_ALL);

		$list = ListTable::getList([
			'select' => ['ID', 'NAME'],
			'order' => ['ID' => 'DESC']
		]);
		foreach ($list as $item)
		{
			$view->addTile($item['ID'], $item['NAME'], []);
		}

		// get response
		$response->initContentJson()->set(array(
			'list' => $view->get(),
		));
	}
);
$actions[] = Controller\Action::create('actualizeSegment')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$groupId = (int)$request->get('GROUP_ID');

		if ($groupId)
		{
			SegmentDataBuilder::actualize($groupId);
		}

		$content = $response->initContentJson();
	}
);

$checker = CommonAjax\Checker::getViewSegmentPermissionChecker();
Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();