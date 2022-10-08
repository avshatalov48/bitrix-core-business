<?php

namespace Bitrix\Main\Session;

use Bitrix\Main\Event;

final class CompositeSessionManager
{
	public const EVENT_REGENERATE_SESSION_ID = 'onRegenerateSessionId';

	/**	@var SessionInterface */
	private $kernelSession;
	/**	@var SessionInterface */
	private $session;

	/**
	 * CompositeSessionManager constructor.
	 * @param SessionInterface $kernelSession
	 * @param SessionInterface $session
	 */
	public function __construct(SessionInterface $kernelSession, SessionInterface $session)
	{
		$this->kernelSession = $kernelSession;
		$this->session = $session;
	}

	public function start(): void
	{
		if (!$this->kernelSession->isStarted())
		{
			$this->kernelSession->start();
		}
		if (!$this->session->isStarted())
		{
			$this->session->start();
		}
	}

	public function destroy(): void
	{
		$this->start();

		if ($this->kernelSession instanceof KernelSessionProxy)
		{
			$this->kernelSession->destroy();

			return;
		}

		$this->kernelSession->destroy();
		$this->session->destroy();
	}

	public function clear(): void
	{
		$this->start();

		if ($this->kernelSession instanceof KernelSessionProxy)
		{
			$this->kernelSession->clear();

			return;
		}

		$this->kernelSession->clear();
		$this->session->clear();
	}

	public function regenerateId(): void
	{
		$this->start();

		$this->kernelSession->regenerateId();
		if (!($this->kernelSession instanceof KernelSessionProxy))
		{
			$this->session->regenerateId();
		}

		(new Event('main', self::EVENT_REGENERATE_SESSION_ID, [
			'newSessionId' => $this->kernelSession->getId(),
		]))->send();
	}
}