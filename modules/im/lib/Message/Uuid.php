<?php

namespace Bitrix\Im\Message;

use Bitrix\Main\Application;
use Bitrix\Im\Model\MessageUuidTable;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\DB\SqlHelper;
use Bitrix\Main\Type\DateTime;

class Uuid
{
	private const TTL = 3600;

	/** @var string */
	private $tableName;

	/** @var string */
	private $uuid;

	/** @var DateTime */
	private $dateCreate;

	/** @var int */
	private $messageId;

	/** @var bool */
	private $messageLoaded = false;

	/** @var Connection */
	protected $connection;

	/** @var SqlHelper */
	protected $sqlHelper;

	/**
	 * @param string $uuid UUID value.
	 */
	public function __construct(string $uuid)
	{
		$this->uuid = $uuid;
		$this->connection = Application::getInstance()->getConnection();
		$this->sqlHelper = $this->connection->getSqlHelper();
		$this->tableName = $this->sqlHelper->forSql(MessageUuidTable::getTableName());
	}

	private function setMessageId(?int $messageId): void
	{
		$this->messageId = $messageId;
	}

	private function setDateCreate(DateTime $dateCreate): void
	{
		$this->dateCreate = $dateCreate;
	}

	/**
	 * Method is trying to add a new UUID to the DB via INSERT IGNORE query.
	 * If there is already a record with the same UUID, then we will get false.
	 *
	 * @return bool
	 */
	public function add(): bool
	{
		$preparedData = $this->sqlHelper->prepareInsert($this->tableName, [
			'DATE_CREATE' => (new DateTime()),
			'UUID' => $this->uuid,
		]);

		$this->connection->queryExecute(
			$this->sqlHelper->getInsertIgnore(
				$this->sqlHelper->quote($this->tableName),
				" ({$preparedData[0]}) ",
				" VALUES ({$preparedData[1]})"
			)
		);
		$rowsAffected = $this->connection->getAffectedRowsCount();

		return $rowsAffected > 0;
	}

	/**
	 * Validates UUIDv4 string.
	 *
	 * @return bool
	 */
	public static function validate(string $uuid): bool
	{
		$regex = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

		return preg_match($regex, $uuid) === 1;
	}

	/**
	 * Loads record from the table by UUID.
	 *
	 * @return void
	 */
	private function loadMessage(): void
	{
		$uuidRecord = MessageUuidTable::getRow([
			'select' => ['MESSAGE_ID', 'DATE_CREATE'],
			'filter' => [
				'=UUID' => $this->uuid,
			]
		]);

		if (is_null($uuidRecord))
		{
			return;
		}

		$this->messageLoaded = true;
		$this->setMessageId($uuidRecord['MESSAGE_ID']);
		$this->setDateCreate($uuidRecord['DATE_CREATE']);
	}

	/**
	 * Returns message ID.
	 *
	 * @return int|null
	 */
	public function getMessageId(): ?int
	{
		if (!$this->messageLoaded)
		{
			$this->loadMessage();
		}

		return $this->messageId;
	}

	/**
	 * Updates date create in the record by UUID.
	 */
	private function updateDateCreate(): void
	{
		MessageUuidTable::update($this->uuid, [
			'DATE_CREATE' => new DateTime()
		]);
	}

	/**
	 * Updates message_id for the certain UUID.
	 *
	 * @param int $messageId Message id.
	 */
	public function updateMessageId(int $messageId): void
	{
		MessageUuidTable::update($this->uuid, [
			'MESSAGE_ID' => $messageId
		]);
	}

	/**
	 * Deletes UUID record from the DB table.
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		$deleteResult = MessageUuidTable::delete($this->uuid);

		return $deleteResult->isSuccess();
	}

	/**
	 * Checks if the UUID record is expired.
	 *
	 * @return bool
	 */
	private function isExpired(): bool
	{
		if (!$this->messageLoaded)
		{
			$this->loadMessage();
		}

		if ($this->dateCreate instanceof DateTime)
		{
			$dateCreateTimestamp = $this->dateCreate->getTimestamp();

			return (time() - $dateCreateTimestamp) > self::TTL;
		}

		return false;
	}

	/**
	 * Updates UUID record with current date and time, if it is expired.
	 *
	 * @return bool
	 */
	public function updateIfExpired(): bool
	{
		if ($this->isExpired())
		{
			$this->updateDateCreate();

			return true;
		}

		return false;
	}

	/**
	 * Agent. Deletes old records (older than 30 days) from the table.
	 *
	 * @return string
	 */
	public static function cleanOldRecords(): string
	{
		$daysBeforeExpire = 31;
		$connection = Application::getInstance()->getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$tableName = $sqlHelper->forSql(MessageUuidTable::getTableName());
		$expiredDateTime = (new DateTime())->add("-$daysBeforeExpire days");
		$expiredDateTimePrepared = $sqlHelper->convertToDbDateTime($expiredDateTime);

		$query = "DELETE FROM $tableName WHERE DATE_CREATE < $expiredDateTimePrepared;";
		$connection->queryExecute($query);

		return __METHOD__. '();';
	}
}