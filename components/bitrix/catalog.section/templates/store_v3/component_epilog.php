<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $templateData
 * @var string $templateFolder
 * @var CatalogSectionComponent $component
 */

//	lazy load and big data json answers
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$json = (
	$request->isAjaxRequest()
	&& $request->get('action') === 'showMore'
);

if (!$json)
{
	if (!empty($templateData['TEMPLATE_LIBRARY']))
	{
		$loadCurrency = false;

		if (!empty($templateData['CURRENCIES']))
		{
			$loadCurrency = \Bitrix\Main\Loader::includeModule('currency');
		}

		CJSCore::Init($templateData['TEMPLATE_LIBRARY']);

		if ($loadCurrency)
		{
			?>
			<script>
				BX.Currency.setCurrencies(<?=$templateData['CURRENCIES']?>);
			</script>
			<?php
		}
	}
}

if ($json)
{
	$content = ob_get_clean();

	[, $itemsContainer] = explode('<!-- items-container -->', $content);
	$paginationContainer = '';
	if ($templateData['USE_PAGINATION_CONTAINER'])
	{
		[, $paginationContainer] = explode('<!-- pagination-container -->', $content);
	}
	[, $epilogue] = explode('<!-- component-end -->', $content);

	if (isset($arParams['AJAX_MODE']) && $arParams['AJAX_MODE'] === 'Y')
	{
		$component->prepareLinks($paginationContainer);
	}

	$component::sendJsonAnswer([
		'items' => $itemsContainer,
		'pagination' => $paginationContainer,
		'epilogue' => $epilogue,
		'navParams' => $templateData['NAV_PARAMS'],
		'parameters' => $templateData['SIGNED_PARAMETERS'],
	]);
}