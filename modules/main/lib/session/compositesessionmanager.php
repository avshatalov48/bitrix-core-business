<?php

namespace Bitrix\Main\Session;

final class CompositeSessionManager
{
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

		if ($this->kernelSession instanceof KernelSessionProxy)
		{
			$this->kernelSession->regenerateId();

			return;
		}

		$this->kernelSession->regenerateId();
		$this->session->regenerateId();
	}
}