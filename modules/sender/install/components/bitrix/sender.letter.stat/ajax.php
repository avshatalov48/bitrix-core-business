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
use Bitrix\Sender\Stat;

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

$modifyLetterChecker = CommonAjax\Checker::getModifyLetterPermissionChecker();
$actions = array();
$actions[] = Controller\Action::create('getClickMap')
	->setRequestMethodGet()
	->setHandler(
		function (HttpRequest $request, Controller\Response $response)
		{
			$content = $response->initContentHtml();

			$message = '';
			$letter = new Entity\Letter($request->get('letterId'));
			if ($letter->isSupportHeatMap())
			{
				try
				{
					$message = $letter->getMessage()->getConfiguration()->get('BODY');
				}
				catch (\Bitrix\Main\SystemException $exception)
				{
					$message = $exception->getMessage();
				}
			}

			Loader::includeModule('fileman');
			$charset = Context::getCurrent()->getCulture()->getCharset();
			$message = BlockEditor::getHtmlForEditor($message, $charset);
			$content->set($message);
		}
	);
$actions[] = Controller\Action::create('getReadByTime')
	->setHandler(
		function (HttpRequest $request, Controller\Response $response)
		{
			$stat = Stat\Statistics::create()->filter('chainId', $request->get('letterId'));
			$stat->setCacheTtl(0);

			$content = $response->initContentJson();
			$content->add('recommendedTime', $stat->getRecommendedSendTime());
			$content->add('readingByTimeList', $stat->getReadingByDayTime());
		}
	);
$actions[] = Controller\Action::create('resendErrors')
	->setHandler(
		function (HttpRequest $request, Controller\Response $response)
		{
			$letter = new Entity\Letter($request->get('letterId'));
			$letter->sendErrors();

			$response->initContentJson()->getErrorCollection()->add($letter->getErrors());
		}
	)->addChecker($modifyLetterChecker);
$actions[] = Controller\Action::create('getData')
	->setHandler(
		function (HttpRequest $request, Controller\Response $response)
		{
			$content = $response->initContentJson();

			$letter = new Entity\Letter($request->get('letterId'));
			$postingData = $letter->getLastPostingData();
			$postingId = $postingData['ID'];

			if (!$postingId)
			{
				$content->addError('Posting not found.');
				return;
			}

			$data = Stat\Posting::getData($letter->getId(), array(
				'USER_NAME_FORMAT' => $request->get('nameTemplate'),
				'PATH_TO_USER_PROFILE' => $request->get('pathToUserProfile'),
			));
			$content->set($data);
		}
	);
$checker = CommonAjax\Checker::getViewLetterPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();