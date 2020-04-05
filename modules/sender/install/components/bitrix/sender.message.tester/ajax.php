<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Security;

if (!Loader::includeModule('sender'))
{
	return;
}

$actions = array();
$actions[] = Controller\Action::create('test')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$data = array(
			'MESSAGE_CODE' => $request->get('messageCode'),
			'MESSAGE_ID' => $request->get('messageId')
		);
		$messageData = $request->get('messageData');
		if (!is_array($messageData))
		{
			$messageData = array();
		}

		\CUtil::decodeURIComponent($messageData);

		$list = $request->get('list');
		if (!is_array($list))
		{
			$list = array();
		}

		$letter = new Entity\Letter;
		$letter->mergeData($data);

		if (is_array($messageData) && count($messageData))
		{
			foreach($letter->getMessage()->getConfiguration()->getOptions() as $option)
			{
				if (!isset($messageData[$option->getCode()]))
				{
					continue;
				}

				if ($option->getType() === \Bitrix\Sender\Message\ConfigurationOption::TYPE_MAIL_EDITOR)
				{
					$value = $messageData[$option->getCode()];
					$value = Security\Sanitizer::fixReplacedStyles($value);
					$value = Security\Sanitizer::sanitizeHtml($value, $option->getValue());
					$messageData[$option->getCode()] = $value;
				}

				if ($option->getType() !== \Bitrix\Sender\Message\ConfigurationOption::TYPE_FILE)
				{
					continue;
				}

				$postFiles = new \Bitrix\Sender\Internals\PostFiles("messageData[" . $option->getCode() . "]");

				$files = $messageData[$option->getCode()];
				$files = is_array($files) ? $files : array();

				$messageData[$option->getCode()] = $postFiles->getFiles([], $files);
			}
			$letter->getMessage()->setConfigurationData($messageData);
		}
		$result = $letter->test($list);

		$content = $response->initContentJson();
		$content->set(array(
			'isSuccess' => $result->isSuccess(),
			'resultErrors' => $result->getErrorMessages(),
		));
	}
);
$checker = CommonAjax\Checker::getViewLetterPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();