<?php

namespace Bitrix\Main\Session\Legacy;

use Bitrix\Main\Session\KernelSessionProxy;
use Bitrix\Main\Session\SessionInterface;
use Bitrix\Main\SystemException;

final class HealerEarlySessionStart
{
	public function process(SessionInterface $kernelSession)
	{
		if (($kernelSession instanceof KernelSessionProxy) && $kernelSession->isActive() && !$kernelSession->isStarted())
		{
			session_write_close();

			$exception = new SystemException(
				'Attention! The session_start function was called before the Bitrix Kernel was started. ' .
				'The session will be closed to avoid errors. It\'s strongly recommended to avoid session usage before initializing the Bitrix Kernel.'
			);
			trigger_error($exception->getMessage(), E_USER_DEPRECATED);
			$application = \Bitrix\Main\Application::getInstance();
			$exceptionHandler = $application->getExceptionHandler();
			$exceptionHandler->writeToLog($exception);
		}
	}
}