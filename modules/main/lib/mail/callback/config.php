<?php
namespace Bitrix\Main\Mail\Callback;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Security\Sign\Signer;

/**
 * Class Config
 *
 * @package Bitrix\Main\Mail\Callback
 */
class Config
{
	const SIGHT_SALT = 'main_mail_callback';

	/** @var  string $moduleId Module ID. */
	protected $moduleId;

	/** @var  string $entityType Entity type ID. */
	protected $entityType;

	/** @var  string $entityId Entity ID. */
	protected $entityId;

	/** @var  string $id ID. */
	protected $id;

	/** @var  string $host Host. */
	protected $host;

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
	 * @throws ArgumentException
	 */
	public function setModuleId($moduleId)
	{
		if (empty($moduleId))
		{
			throw new ArgumentException('Parameters `$moduleId` required.');
		}

		$this->id = null;
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
	 * Additional category on callback. Unique per `moduleId`.
	 *
	 * @param string|null $entityType Entity type.
	 * @return $this
	 * @throws ArgumentException
	 */
	public function setEntityType($entityType)
	{
		$this->id = null;
		$this->entityType = $entityType ?: null;
		return $this;
	}

	/**
	 * Get entity ID.
	 * Unique per `moduleId` & `entityType`.
	 * It might be an email or ID of row in some table.
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
	 * @throws ArgumentException
	 */
	public function setEntityId($entityId)
	{
		if (empty($entityId))
		{
			throw new ArgumentException('Parameters `$entityId` required.');
		}

		$this->id = null;
		$this->entityId = $entityId;
		return $this;
	}

	/**
	 * Set host.
	 *
	 * @param string $host Host.
	 * @return $this
	 */
	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}

	/**
	 * Get host.
	 *
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}


	/**
	 * Get id.
	 *
	 * @return string
	 */
	public function getId()
	{
		if (!$this->id)
		{
			$this->id = self::generateId(
				$this->getModuleId(),
				$this->getEntityType(),
				$this->getEntityId()
			);
		}

		return $this->id;
	}

	/**
	 * Get signature.
	 *
	 * @return string|null
	 */
	public function getSignature()
	{
		try
		{
			return (new Signer())->getSignature($this->getSignedString(), self::SIGHT_SALT);
		}
		catch (ArgumentTypeException $exception)
		{
			return null;
		}
	}

	/**
	 * Verify signature.
	 *
	 * @param string $signature Signature.
	 * @return bool
	 */
	public function verifySignature($signature)
	{
		return (new Signer())->validate(
			$this->getSignedString(),
			$signature,
			self::SIGHT_SALT
		);
	}

	protected function getSignedString()
	{
		return $this->getId();
	}

	/**
	 * Generate ID.
	 *
	 * @param string $moduleId Module ID.
	 * @param string|null $entityType Entity type.
	 * @param string|int $entityId Entity ID.
	 * @return $this
	 * @throws ArgumentException
	 */
	public static function generateId($moduleId, $entityType = null, $entityId)
	{
		$entityType = $entityType ?: '';
		return base64_encode("$moduleId/$entityType/$entityId");
	}

	/**
	 * Unpack ID.
	 *
	 * @param string $id ID.
	 * @return $this
	 * @throws ArgumentException
	 */
	public function unpackId($id)
	{
		$id = base64_decode($id);
		$list = explode('/', $id);
		$this->setModuleId($list[0]);
		$this->setEntityType($list[1]);
		$this->setEntityId($list[2]);

		return $this;
	}
}
