<?php
namespace Bitrix\Main\Copy;

use Bitrix\Main\Result;

/**
 * Interface CopyImplementer for copy entities.
 * @package Bitrix\Main\Copy
 */
abstract class CopyImplementer
{
	/**
	 * @var Result
	 */
	protected $result;

	/**
	 * @var \CUserTypeManager|null
	 */
	protected $userTypeManager;
	protected $ufIgnoreList = [];
	protected $executiveUserId;

	public function __construct()
	{
		$this->result = new Result();
	}

	/**
	 * @return \Bitrix\Main\Error[]
	 */
	public function getErrors()
	{
		return $this->result->getErrors();
	}

	/**
	 * To copy uf fields, you must pass the uf field manager.
	 *
	 * @param \CUserTypeManager $userTypeManager Uf fields.
	 */
	public function setUserFieldManager(\CUserTypeManager $userTypeManager)
	{
		$this->userTypeManager = $userTypeManager;
	}

	/**
	 * To avoid copying specific fields, specify a list of fields to ignore.
	 *
	 * @param array $ufIgnoreList Ignore list.
	 */
	public function setUfIgnoreList(array $ufIgnoreList): void
	{
		$this->ufIgnoreList = $ufIgnoreList;
	}

	/**
	 * To copy on agent need user id.
	 *
	 * @param int $executiveUserId
	 */
	public function setExecutiveUserId(int $executiveUserId): void
	{
		$this->executiveUserId = $executiveUserId;
	}

	protected function copyUfFields(int $entityId, int $copiedEntityId, string $ufObject)
	{
		if (!$this->userTypeManager)
		{
			return;
		}

		$this->userTypeManager->copy($ufObject, $entityId, $copiedEntityId,
			$this, $this->executiveUserId, $this->ufIgnoreList);
	}

	/**
	 * Adds entity.
	 *
	 * @param Container $container
	 * @param array $fields
	 * @return int|bool return entity id or false.
	 */
	abstract public function add(Container $container, array $fields);

	/**
	 * Returns entity fields.
	 *
	 * @param Container $container
	 * @param int $entityId
	 * @return array $fields
	 */
	abstract public function getFields(Container $container, $entityId);

	/**
	 * Preparing data before creating a new entity.
	 *
	 * @param Container $container
	 * @param array $fields List entity fields.
	 * @return array $fields
	 */
	abstract public function prepareFieldsToCopy(Container $container, array $fields);

	/**
	 * Starts copying children entities.
	 *
	 * @param Container $container
	 * @param int $entityId Entity id.
	 * @param int $copiedEntityId Copied entity id.
	 * @return Result
	 */
	abstract public function copyChildren(Container $container, $entityId, $copiedEntityId);

	/**
	 * @param Result[] $results
	 * @return Result
	 */
	protected function getResult(array $results = [])
	{
		$copyResult = new Result();

		$data = [];
		foreach ($results as $result)
		{
			$data = $data + $result->getData();
			if ($result->getErrors())
			{
				$copyResult->addErrors($result->getErrors());
			}
		}

		if ($data)
		{
			$copyResult->setData($data);
		}

		return $copyResult;
	}
}