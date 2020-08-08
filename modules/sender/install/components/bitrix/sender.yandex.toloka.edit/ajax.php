<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Integration\Yandex\Toloka\ApiRequest;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\InputOutputSpec;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\Project;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\TaskSpec;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\ViewSpec;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\ViewSpecSettings;
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
			$project = new Project();

			$taskSpec = new TaskSpec();
			$inputSpec = new InputOutputSpec();
			$outputSpec = new InputOutputSpec();
			$viewSpec = new ViewSpec();
			$viewSpecSettings = new ViewSpecSettings();

			$inputSpec->setIdentificator($request->get('input_identificator'));
			$inputSpec->setType($request->get('input_type'));

			$outputSpec->setIdentificator($request->get('output_identificator'));
			$outputSpec->setType($request->get('output_type'));

			$viewSpec->setMarkup($request->get('markup'));
			$viewSpec->setScript($request->get('script'));
			$viewSpec->setStyles($request->get('styles'));
			$viewSpec->setAssets(new \Bitrix\Sender\Integration\Yandex\Toloka\DTO\Asset());
			$viewSpec->setSettings($viewSpecSettings);

			$taskSpec->setInputSpec($inputSpec);
			$taskSpec->setOutputSpec($outputSpec);
			$taskSpec->setViewSpec($viewSpec);

			$id = (int)$request->get('id');
			if ($id)
			{
				$project->setId($id);
			}

			$project->setPublicName($request->get('name'));
			$project->setPublicDescription($request->get('description'));
			$project->setPublicInstructions($request->get('instruction'));
			$project->setTaskSpec($taskSpec);
			$content = $response->initContentJson();

			$content->set(
				$id === 0? $apiRequest->createProject($project) : $apiRequest->editProject($project)
			);
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

			$pool = new \Bitrix\Sender\Integration\Yandex\Toloka\DTO\Pool();
			$defaults = new \Bitrix\Sender\Integration\Yandex\Toloka\DTO\PoolDefaults();
			$id = (int)$request->get('id');
			$filters = $request->get('filter');

			if ($id)
			{
				$apiRequest->closePool($id);
				$apiRequest->deleteTasks($id);
				$pool->setId($id);
			}

			if (!empty($filters))
			{
				foreach ($filters as $key => $filter)
				{
					if (is_array($filter))
					{
						foreach ($filter as $filterValue)
						{
							$poolFilter = new \Bitrix\Sender\Integration\Yandex\Toloka\DTO\Filter();
							$pool->addFilter(
								$poolFilter->setValue($filterValue)
									->setKey(strtolower($key))
							);
						}
					}
				}
			}

			$willExpire = DateTime::createFromFormat('d.m.Y H:i:s',
				$request->get('will_expire'));
			if(!$willExpire)
			{
				$willExpire = new DateTime();
				$willExpire->setTime(23,59,59);
			}
			$pool->setMayContainAdultContent(json_decode($request->get('may_contain_adult_content')));
			$pool->setPrivateName($request->get('private_name'));
			$pool->setPublicDescription($request->get('public_description'));
			$pool->setProjectId($request->get('project_id'));
			$pool->setRewardPerAssignment($request->get('reward_per_assignment'));
			$pool->setWillExpire(
				$willExpire->format('Y-m-d\TH:i:s')
			);

			$defaults->setOverlapForNewTaskSuites($request->get('overlap'));
			$defaults->setOverlapForNewTasks($request->get('overlap'));
			$pool->setDefaults($defaults);

			$response = $id === 0
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

			$userTasks = explode(",", $request->get('tasks'));
			$identificator = $request->get('identificator');
			$tasks = [];

			foreach ($userTasks as $task)
			{
				$task = trim($task);
				if(empty($task))
				{
					continue;
				}
				$newTask = new \Bitrix\Sender\Integration\Yandex\Toloka\DTO\Task();

				$inputValue = new \Bitrix\Sender\Integration\Yandex\Toloka\DTO\InputValue();
				$inputValue->setIdentificator($identificator);
				$inputValue->setValue(trim($task));

				$newTask->setPoolId($response['id']);
				$newTask->setInputValues($inputValue);
				$newTask->setOverlap($defaults->getOverlapForNewTasks());

				$tasks[] = $newTask;
			}

			$taskSuite = new \Bitrix\Sender\Integration\Yandex\Toloka\DTO\TaskSuite();
			$taskSuite->setPoolId($response['id']);
			$taskSuite->setTasks($tasks);

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