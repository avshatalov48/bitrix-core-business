<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

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
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

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

		$list = $request->get('list');
		if (!is_array($list))
		{
			$list = array();
		}

		$letter = new Entity\Letter;
		$letter->mergeData($data);

		$parameters = prepareOptions($letter, $messageData);
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
					return $prev <> '' ? $prev : $error->getCode();
				},
				''
			)
		));
	}
)
;
$actions[] = Controller\Action::create('consent')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$errorMsg = null;
		$result = null;
		$transport = \Bitrix\Sender\Transport\Adapter::create($request->get('messageCode'));

		$messageData = $request->get('messageData');
		$letter = new Entity\Letter;
		$letter->mergeData(array(
			'MESSAGE_CODE' => $request->get('messageCode'),
			'MESSAGE_ID' => $request->get('messageId')
			)
		);
		$parameters = prepareOptions($letter, $messageData);

		$agreement = new \Bitrix\Main\UserConsent\Agreement(
			(int)$letter->getMessage()->getConfiguration()->get('APPROVE_CONFIRMATION_CONSENT'),
			['fields' => ['IP']]
		);
		if (!$agreement->isExist() || !$agreement->isActive())
		{
			$content = $response->initContentJson();
			$content->set(array(
				'isSuccess' => false,
				'resultErrors' => [
					\Bitrix\Main\Localization\Loc::getMessage("SENDER_MESSAGE_TESTER_ERROR_UNKNOWN_AGREEMENT")
				],
			));

			return;
		}
		try
		{
			$list = is_array($list = $request->get('list'))? $list : [];
			foreach ($list as $code)
			{
				$sendResult = $transport->sendTestConsent(
					$letter->getMessage(),
					['CONTACT_CODE' => $code, 'SITE_ID' => SITE_ID]
				);
				$result = isset($result) ? $sendResult && $result : $sendResult;
			}
		}
		catch (\Exception $exception)
		{
			$errorMsg = $exception->getMessage();
		}
		$content = $response->initContentJson();
		$content->set(array(
			'isSuccess' => $result,
			'resultErrors' => $errorMsg,
		));
	}
)
;

function prepareOptions(&$letter, &$messageData)
{
	$parameters = [];
	if (is_array($messageData) && count($messageData))
	{
		foreach ($letter->getMessage()->getConfiguration()->getOptions() as $option)
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
		if ($messageData['CAMPAIGN_ID'] ?? false)
		{
			$parameters['CAMPAIGN_ID'] = $messageData['CAMPAIGN_ID'];
		}
	}
	return $parameters;
}

$checker = CommonAjax\Checker::getViewLetterPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();