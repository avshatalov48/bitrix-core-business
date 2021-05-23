<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Mail\Address;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Security;

if (!Bitrix\Main\Loader::includeModule('sender'))
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

		$parameters = [];
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

				if ($option->getType() === \Bitrix\Sender\Message\ConfigurationOption::TYPE_AUDIO)
				{
					$value = $messageData[$option->getCode()];
					$messageData[$option->getCode()] = $letter->getMessage()->getAudioValue($option->getCode(), $value);
				}

				if ($option->getType() === \Bitrix\Sender\Message\ConfigurationOption::TYPE_EMAIL)
				{
					$value = $messageData[$option->getCode()];
					if (\Bitrix\Sender\Integration\Sender\AllowedSender::isAllowed($value))
					{
						$address = new Address();
						$address->set($value);
						$value = $address->get();
					}
					else
					{
						$value = "";
					}
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
			if ($messageData['CAMPAIGN_ID'])
			{
				$parameters['CAMPAIGN_ID'] = $messageData['CAMPAIGN_ID'];
			}
		}
		$configuration = $letter->getMessage()->getConfiguration();

		$result = $configuration->checkOptions();
		if ($result->isSuccess())
		{
			$result = $letter->test($list, $parameters);
		}

		$content = $response->initContentJson();
		$content->set(array(
			'isSuccess' => $result->isSuccess(),
			'resultErrors' => $result->getErrorMessages(),
			'errorCode' => array_reduce(
				$result->getErrors(),
				function ($prev, $error)  // get first error code
				{
					return $prev <> ''? $prev : $error->getCode();
				},
				''
			)
		));
	}
);
$checker = CommonAjax\Checker::getViewLetterPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();