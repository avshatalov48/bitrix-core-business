<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Integration\Yandex\Toloka\ApiRequest;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\Assembler\PoolAssembler;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\Assembler\ProjectAssembler;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\Assembler\TaskSuiteAssembler;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;

if (!Loader::includeModule('sender'))
{
	return;
}
$apiRequest = new ApiRequest();
Loc::loadMessages(__FILE__);

$actions = [];
$actions[] = Controller\Action::create('getProjectList')
	->setHandler(
		function(HttpRequest $request, Controller\Response $response) use ($apiRequest)
		{
			$content = $response->initContentJson();
			$content->set(
				$apiRequest->getProjectList([])
			);
		}
	);

$actions[] = Controller\Action::create('getProjectInfo')
	->setHandler(
		function(HttpRequest $request, Controller\Response $response) use ($apiRequest)
		{
			$content = $response->initContentJson();
			$content->set(
				$apiRequest->getProjectInfo()
			);
		}
	);

$actions[] = Controller\Action::create('createProject')
	->setHandler(
		function(HttpRequest $request, Controller\Response $response) use ($apiRequest)
		{
			$project = ProjectAssembler::toDTO($request);
			$content = $response->initContentJson();

			try
			{
				$content->set(
					is_null($project->getId())? $apiRequest->createProject($project) : $apiRequest->editProject($project)
				);
			}
			catch (\Bitrix\Sender\Integration\Yandex\Toloka\Exception\AccessDeniedException $e)
			{
				$content->addPermissionError(Loc::getMessage('SENDER_TOLOKA_WRONG_OAUTH'));
			}
		}
	);

$actions[] = Controller\Action::create('getPoolList')
	->setHandler(
		function(HttpRequest $request, Controller\Response $response) use ($apiRequest)
		{
			$content = $response->initContentJson();
			$content->set(
				$apiRequest->getPoolList(
					[
						'project_id' => $request->get('project_id')
					]
				)
			);
		}
	);

$actions[] = Controller\Action::create('getGeoList')
	->setHandler(
		function(HttpRequest $request, Controller\Response $response) use ($apiRequest)
		{
			$content = $response->initContentJson();
			$content->set(
				$apiRequest->getGeoList(
					[
						'name' => $request->get('name')
					]
				)
			);
		}
	);

$actions[] = Controller\Action::create('getPoolInfo')
	->setHandler(
		function(HttpRequest $request, Controller\Response $response) use ($apiRequest)
		{
			$content = $response->initContentJson();
			$content->set(
				$apiRequest->getProjectList()
			);
		}
	);

$actions[] = Controller\Action::create('createPool')
	->setHandler(
		function(HttpRequest $request, Controller\Response $response) use ($apiRequest)
		{
			$content = $response->initContentJson();

			$pool = PoolAssembler::toDTO($request);

			if (!is_null($pool->getId()))
			{
				$apiRequest->closePool($pool->getId());
				$apiRequest->deleteTasks($pool->getId());
			}

			$response = is_null($pool->getId())
				? $apiRequest->createPool(
					$pool
				)
				: $apiRequest->editPool(
					$pool
				);

			if ($response['code'])
			{
				$content->addError($response['code']);
				$content->set(
					$response
				);

				return;
			}
			$request->set('id', $response['id']);

			$taskSuite = TaskSuiteAssembler::toDTO($request);

			$response = $apiRequest->createTaskSuite($taskSuite);
			$content->set(
				$response
			);
		}
	);

$actions[] = Controller\Action::create('registerOAuth')
	->setHandler(
		function(HttpRequest $request, Controller\Response $response) use ($apiRequest)
		{
			COption::SetOptionString(
				'sender',
				ApiRequest::ACCESS_CODE,
				$request->get('access_code')
			);

			$content = $response->initContentJson();
			try
			{
				$apiRequest = new ApiRequest();
				$response = $apiRequest->getProjectList([]);
				if(isset($response['error']))
				{
					throw new \Bitrix\Sender\Integration\Yandex\Toloka\Exception\AccessDeniedException();
				}
				$content->set(
					$response
				);
			}
			catch (\Bitrix\Sender\Integration\Yandex\Toloka\Exception\AccessDeniedException $e)
			{
				$content->addPermissionError(Loc::getMessage('SENDER_TOLOKA_WRONG_OAUTH'));
			}
		}
	);

$checker = CommonAjax\Checker::getModifyLetterPermissionChecker();
Controller\Listener::create()
	//	->addChecker($checker)
	->setActions($actions)
	->run();