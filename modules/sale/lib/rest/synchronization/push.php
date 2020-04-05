<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

Bitrix\Main\Loader::includeModule('sale');

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

\Bitrix\Sale\Rest\Synchronization\LoggerDiag::addMessage('_POST',var_export($request->getPostList()->toArray(), true));

if($request->getPost('event') !== null)
{
	Bitrix\Main\Loader::includeModule('sale');

	\Bitrix\Sale\Rest\Synchronization\LoggerDiag::addMessage('_POST',var_export($request->getPostList(), true));

	$b24 = new Bitrix\Sale\Rest\Synchronization\Synchronizer();
	$b24->incomingReplication();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>