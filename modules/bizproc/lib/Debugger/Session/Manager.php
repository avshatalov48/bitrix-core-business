<?php

namespace Bitrix\Bizproc\Debugger\Session;

use Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTable;
use Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionWorkflowContextTable;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

class Manager
{
	public const ERROR_INCORRECT_MODE = 'INCORRECT DEBUGGER MODE';
	public const ERROR_DEBUGGER_ALREADY_STARTED = 'DEBUGGER IS ALREADY STARTED';

	private static ?Session $activeSession = null;
	private static bool $isActiveSessionChecked = false;

	private const CACHE_TTL = 3600;

	private static function getList(array $filter = []): ?Session
	{
		return DebuggerSessionTable::getList([
			'select' => ['*', 'DOCUMENTS', 'WORKFLOW_CONTEXTS'],
			'filter' => $filter,
		])->fetchObject();
	}

	public static function getCachedSession(): ?Session
	{
		return DebuggerSessionTable::getList([
			'select' => ['*'],
			'filter' => ['=ACTIVE' => 'Y'],
			'cache' => ['ttl' => self::CACHE_TTL],
		])->fetchObject();
	}

	public static function getActiveSession(): ?Session
	{
		if (!self::$isActiveSessionChecked)
		{
			self::setActiveSession(static::getList(['=ACTIVE' => 'Y']));
		}

		return self::$activeSession;
	}

	public static function getSessionById(string $sessionId): ?Session
	{
		if (self::$activeSession && self::$activeSession->getId() === $sessionId)
		{
			return self::$activeSession;
		}

		$session = static::getList(['=ID' => $sessionId]);
		if ($session && $session->isActive())
		{
			self::setActiveSession($session);
		}

		return $session;
	}

	public static function startSession(array $parameterDocumentType, int $mode, int $userId, int $categoryId = 0)
	{
		if (!Mode::isMode($mode))
		{
			return static::getResult(self::ERROR_INCORRECT_MODE);
		}

		[$module, $entity, $documentType] = \CBPHelper::ParseDocumentId($parameterDocumentType);

		$session = new Session();
		$session
			->setId(uniqid('', true))
			->setModuleId($module)
			->setEntity($entity)
			->setDocumentType($documentType)
			->setMode($mode)
			->setStartedBy($userId)
			//->setStartedDate(new \Bitrix\Main\Type\DateTime())
			->setActive(true)
			->setDocumentCategoryId($categoryId)
		;

		$activeSession = self::getActiveSession();
		if ($activeSession)
		{
			return self::getResult(
				self::ERROR_DEBUGGER_ALREADY_STARTED,
				['session' => $activeSession]
			);
		}
		self::clearStaticCache();

		$result = $session->save();
		if ($result->isSuccess())
		{
			/** @var Session $sessionFromResult */
			$sessionFromResult = $result->getObject();
			$sessionFromResult->fillWorkflowContexts();
			$sessionFromResult->fillDocuments();
			self::setActiveSession($sessionFromResult);
		}

		return $result;
	}

	public static function finishSession(Session $session)
	{
		$result = $session->finish();
		if ($result->isSuccess())
		{
			self::clearStaticCache();
		}

		return $result;
	}

	public static function canUserStartSession(int $userId, array $parameterDocumentType): bool
	{
		$hasRights = self::canUserDebugAutomation($userId, $parameterDocumentType);
		if ($hasRights)
		{
			return self::canStartSession();
		}

		return false;
	}

	public static function canUserDebugAutomation(int $userId, array $parameterDocumentType): bool
	{
		return static::isAvailable($parameterDocumentType) && \CBPDocument::canUserOperateDocumentType(
			\CBPCanUserOperateOperation::DebugAutomation,
			$userId,
			$parameterDocumentType
		);
	}

	public static function canStartSession(): bool
	{
		if (self::getActiveSession())
		{
			return false;
		}

		return true;
	}

	private static function getResult(string $errorCode, array $data = []): \Bitrix\Main\Result
	{
		$result = new \Bitrix\Main\Result();
		$result->setData($data);

		$error = static::getCustomError($errorCode, $data);
		if ($error)
		{
			$result->addError($error);
		}

		return $result;
	}

	private static function getCustomError(string $errorCode, array $data = []): ?\Bitrix\Main\Error
	{
		/** @var Session $session */
		$session = $data['session'];

		if ($errorCode === self::ERROR_INCORRECT_MODE)
		{
			return new \Bitrix\Main\Error(
				Loc::getMessage('BIZPROC_DEBUGGER_SESSION_MANAGER_ERROR_INCORRECT_MODE'),
				$errorCode
			);
		}
		elseif ($errorCode === self::ERROR_DEBUGGER_ALREADY_STARTED)
		{
			return new \Bitrix\Main\Error($session->getShortDescription(), $errorCode);
		}

		return null;
	}

	public static function deleteInactiveSession(string $sessionId): Result
	{
		$session = static::getList(['=ID' => $sessionId]);

		$result = new Result();
		if (!$session)
		{
			$errorMessage = Loc::getMessage('BIZPROC_DEBUGGER_SESSION_MANAGER_ERROR_SESSION_NOT_FOUND');
			$result->addError(new Error($errorMessage));
		}
		else if ($session->isActive())
		{
			$errorMessage = Loc::getMessage('BIZPROC_DEBUGGER_SESSION_MANAGER_ERROR_SESSION_STILL_ACTIVE');
			$result->addError(new Error($errorMessage));
		}
		else
		{
			$deletionResult = $session->deleteAll();

			$result->setData($deletionResult->getData());
			$result->addErrors($deletionResult->getErrors());
		}

		return $result;
	}

	public static function isDebugWorkflow(string $workflowId): bool
	{
		return (bool)DebuggerSessionWorkflowContextTable::getRow([
			'filter' => ['=WORKFLOW_ID' => $workflowId],
		]);
	}

	public static function getDebuggerState(): DebuggerState
	{
		$session = static::getActiveSession();

		if ($session)
		{
			return new DebuggerState($session->getDebuggerState());
		}

		return DebuggerState::undefined();
	}

	public static function setDebuggerState(DebuggerState $state)
	{
		$session = static::getActiveSession();

		if ($session)
		{
			$session->setDebuggerState($state->getId());
			$session->save();
		}
	}

	private static function isAvailable(array $documentType): bool
	{
		//todo: temporary

		return (
			$documentType[0] === 'crm'
			&& $documentType[2] === 'DEAL'
		);
	}

	private static function setActiveSession(?Session $activeSession)
	{
		self::$activeSession = $activeSession;
		self::$isActiveSessionChecked = true;
	}

	private static function clearStaticCache()
	{
		self::$activeSession = null;
		self::$isActiveSessionChecked = false;
	}
}
