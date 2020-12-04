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
				'Attention! There is session_start before Bitrix Kernel' .
				'to continue correctly session will be closed. Highly recommended avoid usage session before Bitrix Kernel.'
			);
			trigger_error($exception->getMessage(), E_USER_DEPRECATED);
			$application = \Bitrix\Main\Application::getInstance();
			$exceptionHandler = $application->getExceptionHandler();
			$exceptionHandler->writeToLog($exception);
		}
	}
}