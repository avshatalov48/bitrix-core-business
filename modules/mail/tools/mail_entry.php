<?php

//define('NO_KEEP_STATISTIC', 'Y');
//define('NO_AGENT_STATISTIC','Y');
//define('NO_AGENT_CHECK', true);
//define('DisableEventsCheck', true);

define('NOT_CHECK_PERMISSIONS', true);
//define('BX_SECURITY_SESSION_READONLY', true);

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

$context = Bitrix\Main\Application::getInstance()->getContext();

$request  = $context->getRequest();
$response = $context->getResponse();

CModule::includeModule('mail');

try
{
	$hostname = $request->getPostList()->getRaw('hostname');
	if (empty($hostname))
		throw new Bitrix\Mail\ReceiverException('Empty \'hostname\' request parameter');

	$message = $request->getPostList()->getRaw('message');
	if (empty($message))
		throw new Bitrix\Mail\ReceiverException('Empty \'message\' request parameter');

	try
	{
		$message = Bitrix\Main\Web\Json::decode($message);
	}
	catch (Exception $e)
	{
		throw new Bitrix\Mail\ReceiverException('Invalid \'message\' request parameter');
	}

	if (empty($message['rcpt_to']))
		throw new Bitrix\Mail\ReceiverException('Empty recipients list');

	$rcpt = array();
	foreach ($message['rcpt_to'] as $to)
	{
		if (empty($to['user']) || empty($to['host']))
			continue;
		if (strtolower($hostname) != strtolower($to['host']))
			continue;
		if (preg_match('/^no-?reply$/i', $to['user']))
			continue;

		$rcpt[] = sprintf('%s@%s', $to['user'], $to['host']);
	}

	if (empty($rcpt))
		throw new Bitrix\Mail\ReceiverException('Invalid recipients list');

	$message['files'] = array();
	if (!empty($message['attachments']) && is_array($message['attachments']))
	{
		$emptyFile = array(
			'name'     => '',
			'type'     => '',
			'tmp_name' => '',
			'error'    => UPLOAD_ERR_NO_FILE,
			'size'     => 0
		);

		$imageExts = array(
			'image/bmp'  => array('.bmp'),
			'image/gif'  => array('.gif'),
			'image/jpeg' => array('.jpeg', '.jpg', '.jpe'),
			'image/png'  => array('.png')
		);
		$jpegTypes = array('image/pjpeg', 'image/jpeg', 'image/jpg', 'image/jpe');

		foreach ($message['attachments'] as &$item)
		{
			$itemId = $item['uniqueId'];
			$fileId = md5($item['checksum'].$item['length']);

			$item['fileName'] = trim(trim(trim($item['fileName']), '.'));
			if (empty($item['fileName']))
			{
				$item['fileName'] = $fileId;

				if (strpos($item['contentType'], 'message/') === 0)
					$item['fileName'] .= '.eml';
			}

			if ($item['contentType'])
			{
				if (in_array($item['contentType'], $jpegTypes))
					$item['contentType'] = 'image/jpeg';

				if (is_set($imageExts, $item['contentType']))
				{
					$extPos = strrpos($item['fileName'], '.');
					$ext    = substr($item['fileName'], $extPos);

					if ($extPos === false || !in_array($ext, $imageExts[$item['contentType']]))
						$item['fileName'] .= $imageExts[$item['contentType']][0];
				}
			}

			$message['files'][$fileId] = array_merge(
				empty($_FILES[$itemId]) ? $emptyFile : $_FILES[$itemId],
				array(
					'name' => $item['fileName'],
					'type' => $item['contentType']
				)
			);

			$item['uniqueId'] = $fileId;
		}
		unset($item);
	}

	$success    = false;
	$rcptErrors = array();
	foreach ($rcpt as $to)
	{
		$error  = null;
		$result = Bitrix\Mail\User::onEmailReceived($to, $message, $error);

		if ($result)
			$success = true;
		elseif ($error)
			$rcptErrors[$to] = $error;
	}

	if (!$success)
	{
		if (count($rcptErrors) == count($rcpt))
			throw new Bitrix\Mail\ReceiverException(join('; ', $rcptErrors));

		throw new Bitrix\Main\SystemException(sprintf('Message processing failed (rcpt: %s)', join(', ', $rcpt)));
	}

	$response->setStatus('204 No Content');
}
catch (Bitrix\Mail\ReceiverException $e)
{
	addMessage2Log(sprintf('Mail entry: %s', $e->getMessage()), 'mail', 0, false);
	$response->setStatus('400 Bad Request');
}
catch (Exception $e)
{
	addMessage2Log(sprintf('Mail entry: %s', $e->getMessage()), 'mail', 0, false);
	$response->setStatus('500 Internal Server Error');
}

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php';
