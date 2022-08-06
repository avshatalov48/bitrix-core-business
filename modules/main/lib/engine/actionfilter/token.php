<?php

namespace Bitrix\Main\Engine\ActionFilter;

use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Engine\ActionFilter\Service;

class Token extends Base
{
	protected const ERROR_RESTRICTED_BY_SIGN_CHECK = 'restricted_by_sign';

	/** @var string */
	protected $entityHeaderName;

	/** @var string */
	protected $tokenHeaderName;

	/** @var \Closure */
	protected $getEntityClosure;

	final public function __construct(\Closure $getEntityClosure)
	{
		$this->entityHeaderName = Service\Token::getEntityHeader();
		$this->tokenHeaderName = Service\Token::getTokenHeader();
		$this->getEntityClosure = $getEntityClosure;

		parent::__construct();
	}

	final public function onBeforeAction(Event $event)
	{
		$entityValue = (string)Context::getCurrent()->getRequest()->getHeader($this->entityHeaderName);
		$tokenValue = (string)Context::getCurrent()->getRequest()->getHeader($this->tokenHeaderName);

		if (!$this->check($entityValue, $tokenValue))
		{
			Context::getCurrent()->getResponse()->setStatus(403);
			$this->addError(new Error(
				'Access restricted by sign check',
				self::ERROR_RESTRICTED_BY_SIGN_CHECK
			));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}

	protected function check(string $entityValue = '', string $tokenValue = ''): bool
	{
		global $USER;

		$result = false;
		try
		{
			$result = ($entityValue === (new Service\Token($USER->getId()))->unsign($tokenValue, ($this->getEntityClosure)()));
		}
		catch (\Exception $e)
		{
		}

		return $result;
	}
}
