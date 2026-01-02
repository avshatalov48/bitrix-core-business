<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Receiver;

use Bitrix\Main\Application;
use Bitrix\Main\Diag\EventLogger;
use Bitrix\Main\Diag\ExceptionHandlerLog;
use Bitrix\Main\Messenger\Broker\BrokerInterface;
use Bitrix\Main\Messenger\Entity\MessageBox;
use Bitrix\Main\Messenger\Entity\MessageInterface;
use Bitrix\Main\Messenger\Internals\Exception\Broker\AckFailedException;
use Bitrix\Main\Messenger\Internals\Exception\Broker\BrokerReadException;
use Bitrix\Main\Messenger\Internals\Exception\Broker\RejectFailedException;
use Bitrix\Main\Messenger\Internals\Exception\Receiver\ProcessingException;
use Bitrix\Main\Messenger\Internals\Exception\Receiver\RecoverableMessageException;
use Bitrix\Main\Messenger\Internals\Exception\Receiver\UnprocessableMessageException;
use Bitrix\Main\Messenger\Internals\Exception\Receiver\UnrecoverableMessageException;
use Exception;

/**
 * @internal
 */
abstract class AbstractReceiver implements ReceiverInterface
{
	protected int $limit = 50;

	protected string $queueId;

	protected BrokerInterface $broker;

	public function setLimit(int $limit): self
	{
		$this->limit = $limit > 0 ? $limit : 50;

		return $this;
	}

	public function setQueueId(string $queueId): self
	{
		$this->queueId = $queueId;

		return $this;
	}

	public function setBroker(BrokerInterface $broker): self
	{
		$this->broker = $broker;

		return $this;
	}

	/**
	 * @throws BrokerReadException
	 */
	protected function getMessage(): ?MessageBox
	{
		return $this->broker->getOne($this->queueId);
	}

	/**
	 * @throws BrokerReadException
	 */
	protected function getMessages(): iterable
	{
		return $this->broker->get($this->queueId, $this->limit);
	}

	/**
	 * @throws AckFailedException
	 */
	protected function ack(MessageBox $messageBox): void
	{
		$this->broker->ack($messageBox);
	}

	/**
	 * @throws RejectFailedException
	 */
	protected function reject(MessageBox $messageBox): void
	{
		$this->broker->reject($messageBox);
	}

	/**
	 * @throws Exception
	 * @throws UnprocessableMessageException
	 * @throws UnrecoverableMessageException
	 * @throws RecoverableMessageException
	 */
	abstract protected function process(MessageInterface $message): void;

	/**
	 * @throws BrokerReadException
	 * @throws RejectFailedException
	 */
	public function run(): void
	{
		$logger = new EventLogger(
			'main',
			'MESSENGER_QUEUE',
			static function (array $context, string $message)
			{
				$messageBox = $context['message'] ?? null;

				if ($messageBox instanceof MessageBox)
				{
					return [
						'ITEM_ID' => $messageBox->getItemId(),
						'DESCRIPTION' => sprintf(
							'%s. Message: "%s" (%s). Queue: "%s". ItemId: "%s"',
							$message,
							$messageBox->getClassName(),
							$messageBox->getId(),
							$messageBox->getQueueId(),
							$messageBox->getItemId()
						)
					];
				}

				return [
					'ITEM_ID' => null,
				];
			}
		);

		$messageBoxes = $this->getMessages();

		/** @var MessageBox $messageBox */
		foreach ($messageBoxes as $messageBox)
		{
			try
			{
				$this->process($messageBox->getMessage());

				$this->ack($messageBox);
			}
			catch (UnprocessableMessageException $e)
			{
				$this->reject($messageBox);

				Application::getInstance()->getExceptionHandler()->writeToLog(
					$e,
					ExceptionHandlerLog::CAUGHT_EXCEPTION
				);
			}
			catch (UnrecoverableMessageException $e)
			{
				$messageBox->kill();

				$this->reject($messageBox);

				$logger->notice(
					sprintf(
						'Message has unrecoverable case: "%s"',
						$e->getMessage(),
					),
					[
						'message' => $messageBox,
						'exception' => $e
					]
				);
			}
			catch (RecoverableMessageException $e)
			{
				$messageBox->requeue($e->getRetryDelay());

				$this->reject($messageBox);

				$logger->notice(
					sprintf(
						'Message has recoverable case: "%s"',
						$e->getMessage(),
					),
					[
						'message' => $messageBox,
						'exception' => $e
					]
				);
			}
			catch (Exception $e)
			{
				$e = new ProcessingException(
					$messageBox,
					$e->getMessage() . ' Message: ' . $messageBox->getId(),
					$e->getCode(),
					$e
				);

				$this->reject($messageBox);

				Application::getInstance()->getExceptionHandler()->writeToLog(
					$e,
					ExceptionHandlerLog::CAUGHT_EXCEPTION
				);
			}
		}
	}
}
