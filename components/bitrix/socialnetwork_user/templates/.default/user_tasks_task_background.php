<?php

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;
use Bitrix\Tasks\Slider\Exception\SliderException;
use Bitrix\Tasks\Slider\Factory\SliderFactory;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

//disable loader for autotests
$skipLoader = (int)Context::getCurrent()->getRequest()->get('sl') === 1;
if (!$skipLoader)
{
	Extension::load('loader');
?>
	<script>
		const target = document.querySelector('.workarea-content');
		const loader = new BX.Loader({
			target: target,
		});
		loader.show();
	</script>
<?php
}

/** @global CMain $APPLICATION */
/** @var bool $backgroundForTask */
/** @var int $taskId */
/** @var int $userId */
/** @var string $action */

$APPLICATION->SetPageProperty('BodyClass', 'no-all-paddings no-background');
$APPLICATION->SetTitle('');

Toolbar::deleteFavoriteStar();

if (!Loader::includeModule('tasks'))
{
	return;
}

// this variable is defined in the user_tasks_task.php and signals that the task is opened via a direct link
if (!isset($backgroundForTask) || $backgroundForTask === false)
{
	return;
}

$factory = new SliderFactory();
$queryList = Context::getCurrent()->getRequest()->getQueryList()->toArray();
$getParams = empty($queryList) ? '' : '?'. http_build_query($queryList);
// special case for comment
if (isset($queryList['MID']))
{
	$getParams .= '#com' . $queryList['MID'];
}
if (isset($queryList['commentId']))
{
	$getParams .= '#com' . $queryList['commentId'];
}
try
{
	$factory->setAction($action)->setQueryParams($getParams);

	$slider = $factory->createEntitySlider(
		$taskId,
		SliderFactory::TASK,
		$userId,
		SliderFactory::PERSONAL_CONTEXT
	);

	$slider->open();
}
catch (SliderException $exception)
{
	$exception->show();
}