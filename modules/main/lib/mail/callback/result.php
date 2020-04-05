<?php

namespace Bitrix\Main\Mail\Callback;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Mail\Tracking;

/**
 * Class Result
 *
 * @package Bitrix\Main\Mail\Callback
 */
class Result
{
	/** @var  string $moduleId Module ID. */
	protected $moduleId;

	/** @var  string $entityType Entity type. */
	protected $entityType;

	/** @var  string $entityId Entity ID. */
	protected $entityId;

	/** @var  string $email Email. */
	protected $email;

	/** @var  int $dateSent Date sent timestamp. */
	protected $dateSent = 0;

	/** @var  bool $isError Is error. */
	protected $isError;

	/** @var  bool $isPermanentError Is permanent error. */
	protected $isPermanentError;

	/** @var  bool $isBlacklistable Is email blacklistable. */
	protected $isBlacklistable;

	/** @var  string $message Status text. */
	protected $message;

	/** @var  string $description Status description. */
	protected $description;

	/**
	 * Return true if result is belong to module ID and entity type.
	 *
	 * @param string $moduleId Module ID.
	 * @param string $entityType|null Entity type.
	 * @return bool
	 */
	public function isBelongTo($moduleId, $entityType = null)
	{
		if ($moduleId !== $this->moduleId)
		{
			return false;
		}

		if ($entityType != $this->entityType)
		{
			return false;
		}

		return true;
	}

	/**
	 * Return true if result is newest than custom date.
	 *
	 * @param DateTime|integer $dateSent Date sent.
	 * @return bool
	 */
	public function isNewest($dateSent)
	{
		if ($dateSent instanceof DateTime)
		{
			return $dateSent->getTimestamp() > $this->getDateSent();
		}

		if (is_numeric($dateSent))
		{
			$dateSent = (int) $dateSent;
			return $dateSent > $this->getDateSent();
		}

		return true;
	}

	/**
	 * Get module ID.
	 *
	 * @return string
	 */
	public function getModuleId()
	{
		return $this->moduleId;
	}

	/**
	 * Set module ID.
	 *
	 * @param string $moduleId Module ID.
	 * @return $this
	 */
	public function setModuleId($moduleId)
	{
		$this->moduleId = $moduleId;
		return $this;
	}

	/**
	 * Get entity type.
	 *
	 * @return string
	 */
	public function getEntityType()
	{
		return $this->entityType;
	}

	/**
	 * Set entity type.
	 *
	 * @param string $entityType Entity type.
	 * @return $this
	 */
	public function setEntityType($entityType)
	{
		$this->entityType = $entityType ?: null;
		return $this;
	}

	/**
	 * Get entity ID.
	 *
	 * @return string
	 */
	public function getEntityId()
	{
		return $this->entityId;
	}

	/**
	 * Set entity ID.
	 *
	 * @param string $entityId Entity ID.
	 * @return $this
	 */
	public function setEntityId($entityId)
	{
		$this->entityId = $entityId;
		return $this;
	}

	/**
	 * Get email.
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Set email.
	 *
	 * @param string $email Email.
	 * @return $this
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * Get date sent.
	 *
	 * @return string
	 */
	public function getDateSent()
	{
		return $this->dateSent;
	}

	/**
	 * Set date sent.
	 *
	 * @param string $dateSent Date sent.
	 * @return $this
	 */
	public function setDateSent($dateSent)
	{
		$this->dateSent = $dateSent;
		return $this;
	}

	/**
	 * Return true if error.
	 *
	 * @return bool
	 */
	public function isError()
	{
		return $this->isError;
	}

	/**
	 * Set as error.
	 *
	 * @param bool $isError Is error.
	 * @return $this
	 */
	public function setError($isError)
	{
		$this->isError = $isError;
		return $this;
	}

	/**
	 * Return true if permanent error.
	 *
	 * @return bool
	 */
	public function isPermanentError()
	{
		return $this->isPermanentError;
	}

	/**
	 * Set as permanent error.
	 *
	 * @param bool $isPermanentError Is permanent error.
	 * @return $this
	 */
	public function setPermanentError($isPermanentError)
	{
		$this->isPermanentError = $isPermanentError;
		return $this;
	}

	/**
	 * Return true if email is blacklistable.
	 *
	 * @return bool
	 */
	public function isBlacklistable()
	{
		return $this->isBlacklistable;
	}

	/**
	 * Set as blacklistable.
	 *
	 * @param bool $isBlacklistable Is blacklistable.
	 * @return $this
	 */
	public function setBlacklistable($isBlacklistable)
	{
		$this->isBlacklistable = $isBlacklistable;
		return $this;
	}

	/**
	 * Get message text.
	 *
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * Set message text.
	 *
	 * @param string $message Message.
	 * @return $this
	 */
	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * Get description text.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set description text.
	 *
	 * @param string $description Description.
	 * @return $this
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * Send event of receiving result.
	 *
	 * @return bool
	 */
	public function sendEvent()
	{
		return Tracking::changeStatus($this);
	}
}
