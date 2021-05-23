<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Fileman\Block\Editor as BlockEditor;
use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

$actions = array();
$actions[] = CommonAjax\ActionGetTemplate::get();
$checker = CommonAjax\Checker::getViewLetterPermissionChecker();
$actions[] = Controller\Action::create('prepareHtml')
				->setRequestMethodGet()
				->setHandler(
					function (HttpRequest $request, Controller\Response $response)
					{
						$content = $response->initContentHtml();

						$letter = new Entity\Letter($request->get('messageId'));
						$message = $letter->getMessage()->getConfiguration()->get('BODY');

						Loader::includeModule('fileman');
						$charset = Context::getCurrent()->getCulture()->getCharset();
						$message = BlockEditor::getHtmlForEditor($message, $charset);
						$content->set($message);
					}
				);

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();