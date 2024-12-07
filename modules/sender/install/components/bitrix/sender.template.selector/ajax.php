<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Main\HttpRequest;

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

$actions = array();
$actions[] = CommonAjax\ActionGetTemplate::get();
$actions[] =  Controller\Action::create('load')->setHandler(
	function(HttpRequest $request, Controller\Response $response) {
		$lastId = (int) $request->getRaw('lastId');

		$templateType = $request->getRaw('templateType');
		$content = $response->initContentJson();

		if ($templateType !== 'USER')
		{
			return;
		}

		$filter = [
			'ACTIVE' => 'Y',
			'<ID' => $lastId,
		];

		$templateDb = \Bitrix\Sender\TemplateTable::getList([
			'filter' => $filter,
			'order' => ['ID' => 'DESC'],
			'limit' => \Bitrix\Sender\TemplateTable::PER_PAGE_LIMIT,
		]);

		$count = \Bitrix\Sender\TemplateTable::getCount($filter);

		$resultList = [];

		$counter = 0;
		while ($template = $templateDb->fetch())
		{
			$resultList[] = [
				'id' => 'USER|'.$template['ID'].'|'.++$counter,
				'name' => $template['NAME'] ?? '',
				'description' => $template['DESC'] ?? '',
				'image' => $template['ICON'] ?? '',
				'hot' => $template['HOT'] ?? '',
				'hint' => $template['HINT'] ?? '',
				'rowId' => $template['CATEGORY'] ?? '',
				'count' => $count,
				'data' => [
					'templateId' => $template['ID'],
					'templateType' => $template['TYPE'] ?? 'USER',
					'messageFields' => [
						[
							'code' => 'MESSAGE',
							'value' => \Bitrix\Sender\Security\Sanitizer::fixTemplateStyles(
								$template['CONTENT'] ?? ''
							),
							'onDemand' => \Bitrix\Sender\TemplateTable::isContentForBlockEditor(
								$template['CONTENT'] ?? ''
							),
						],
						[
							'code' => 'SUBJECT',
							'value' => $template['NAME'] ?? '',
						],
					],
					'segments' => $template['SEGMENTS'] ?? '',
					'dispatch' => $template['DISPATCH'] ?? '',
				],
			];
		}

		$content->set([
			'data' => ['items' => $resultList,],
			'error' => false,
			'status' => 'success',
		]);
	}
);

$checker = CommonAjax\Checker::getViewLetterPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();