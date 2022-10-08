<?php

use \Bitrix\Main\Error;
use \Bitrix\Main\Result;

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$result = new Result();

if ($request->get('FILE_ID') && $request->get('SIGN'))
{
	$diskFileId = (int)$request->get('FILE_ID');
	$sign = htmlspecialcharsbx($request->get('SIGN'));

	try
	{
		$signer = new \Bitrix\Main\Security\Sign\Signer;
		$signKey = \CIMDisk::GetFileLinkSign();
		if (is_string($signKey))
		{
			$signer->setKey($signKey);
		}
		$sign = (int)$signer->unsign($sign);
	}
	catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
	{
		try
		{
			$signer = new \Bitrix\Main\Security\Sign\Signer;
			$sign = (int)$signer->unsign($sign);
		}
		catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
		{
		}
	}

	if ($diskFileId === $sign)
	{
		$file = \Bitrix\Disk\File::getById($diskFileId);
		if ($file !== null)
		{
			$fileId = $file->getFileId();
			CFile::ViewByUser($fileId);
		}
		else
		{
			$result->addError(new Error('Missing file'));
			if ($request->get('img') === 'y')
			{
				$errorImageSrc = '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';
				header('Content-type: image/png');
				echo base64_decode($errorImageSrc);
			}
		}
	}
	else
	{
		$result->addError(new Error('Wrong signature or file id'));
	}
}
else
{
	$result->addError(new Error('Missing signature or file id'));
}

if (!$result->isSuccess())
{
	foreach ($result->getErrorMessages() as $errorMessage)
	{
		$lastError = $errorMessage;
	}
	CHTTP::SetStatus('403 Forbidden');
	header("BX-File-Error: $lastError");
}
CMain::FinalActions();
die();