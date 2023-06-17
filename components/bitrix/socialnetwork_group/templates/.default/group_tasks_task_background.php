<?php

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Tasks\Slider\Exception\SliderException;
use Bitrix\Tasks\Slider\Factory\SliderFactory;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @global CMain $APPLICATION */
/** @var bool $backgroundForTask */
/** @var bool $showPersonalTasks */
/** @var int $taskId */
/** @var int $userId */
/** @var int $groupId */
/** @var string $action */

$APPLICATION->SetPageProperty('BodyClass', 'no-all-paddings no-background');
$APPLICATION->SetTitle('');
Toolbar::deleteFavoriteStar();

if (!Loader::includeModule('tasks'))
{
	return;
}

// this variable is defined in the group_tasks_task.php and signals that the task is opened via a direct link
if (!isset($backgroundForTask) || $backgroundForTask === false)
{
	return;
}

$queryList = Context::getCurrent()->getRequest()->getQueryList()->toArray();
$getParams = empty($queryList) ? '' : '?'. http_build_query($queryList);

if (isset($showPersonalTasks) && $showPersonalTasks === true)
{
	$context = SliderFactory::PERSONAL_CONTEXT;
	$ownerId = $userId;
}
else
{
	$context = SliderFactory::GROUP_CONTEXT;
	$ownerId = $groupId;
}

$factory = new SliderFactory();

try
{
	$factory->setAction($action)->setQueryParams($getParams);

	$slider = $factory->createEntitySlider(
		$taskId,
		SliderFactory::TASK,
		$ownerId,
		$context
	);

	$slider->open();
}
catch (SliderException $exception)
{
	$exception->show();
}