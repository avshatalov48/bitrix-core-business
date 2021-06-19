<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arParams
 * @var array $arResult
 * @var string $strErrorMessage
 * @param CBitrixComponent $component
 * @param CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if (!($arParams['AJAX_POST'] == 'Y'
		&& (
			$request->isPost()
				&& $request->getPost('save_product_review') === 'Y')
		||
			!$request->isPost()
				&& $request->get('ajax') == 'y'
	)
)
{
	return;
}

$response = ob_get_clean();
$arError = $arResult["~ERROR_MESSAGE"];
$FHParser = new CForumSimpleHTMLParser($response);

if (!empty($arResult["~ERROR_MESSAGE"]))
{
	$JSResult = [
		'status' => 'errored',
		'errors' => $arResult["~ERROR_MESSAGE"],
		'data' => [
			'errorHtml' => $FHParser->getTagHTML('div[data-bx-role=error-message]')
		]
	];
}
else if ($request->get("preview_comment") && $request->get("preview_comment") !== 'N')
{
	$JSResult = array(
		'status' => 'success',
		'action' => 'preview',
		'data' => [
			'previewHtml' => $FHParser->getTagHTML('div[class=reviews-preview]')
		],
	);
	if (mb_strpos($JSResult['previewMessage'], 'onForumImageLoad') !== false)
	{
		$SHParser = new CForumSimpleHTMLParser($APPLICATION->GetHeadStrings());
		$scripts = $SHParser->getInnerHTML('<!--LOAD_SCRIPT-->', '<!--END_LOAD_SCRIPT-->');

		if ($scripts !== "")
			$JSResult['data']['previewHtml'] = $scripts."\n".$JSResult['data']['previewHtml'];
	}
}
else // a message was added
{
	$JSResult = array(
		'status' => 'success',
		'action' => 'add',
		'data' => [
			'allMessages' => true,
			'messageId' => $arResult["RESULT"],
			'messages' => $FHParser->getTagHTML('div[data-bx-role=messages]'),
			'navigationTop' => $FHParser->getTagHTML('div[data-bx-role=navigation-top]'),
			'navigationBottom' => $FHParser->getTagHTML('div[data-bx-role=navigation-bottom]'),
			'pageNumber' => $arResult['PAGE_NUMBER'],
			'pageCount' => $arResult['PAGE_COUNT']
		]
	);
}

$APPLICATION->RestartBuffer();
while (ob_end_clean());
header('Content-Type:application/json; charset=UTF-8');
\CMain::FinalActions(\Bitrix\Main\Web\Json::encode($JSResult));

$newResponse = new \Bitrix\Main\Engine\Response\AjaxJson($JSResult);
\Bitrix\Main\Context::getCurrent()->setResponse($newResponse);
