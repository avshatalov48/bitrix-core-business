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
/** @var bool $backgroundForTemplate */
/** @var int $templateId */
/** @var int $userId */
/** @var string $action */

$APPLICATION->SetPageProperty('BodyClass', 'no-all-paddings no-background');
$APPLICATION->SetTitle('');
Toolbar::deleteFavoriteStar();

if (!Loader::includeModule('tasks'))
{
	return;
}

// this variable is defined in the user_templates_template.php and signals that the template is opened via a direct link
if (!isset($backgroundForTemplate) || $backgroundForTemplate === false)
{
	return;
}

$factory = new SliderFactory();
$queryList = Context::getCurrent()->getRequest()->getQueryList()->toArray();
$getParams = empty($queryList) ? '' : '?'. http_build_query($queryList);
try
{
	$factory->setAction($action)->setQueryParams($getParams);

	$slider = $factory->createEntitySlider(
		$templateId,
		SliderFactory::TEMPLATE,
		$userId,
		SliderFactory::PERSONAL_CONTEXT
	);

	$slider->open();
}
catch (SliderException $exception)
{
	$exception->show();
}