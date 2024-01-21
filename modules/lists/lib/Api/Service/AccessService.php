<?php

namespace Bitrix\Lists\Api\Service;

use Bitrix\Lists\Api\Response\CheckPermissionsResponse;
use Bitrix\Lists\Security\ElementRight;
use Bitrix\Lists\Security\IblockRight;
use Bitrix\Lists\Security\Right;
use Bitrix\Lists\Security\RightParam;
use Bitrix\Lists\Service\Param;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

class AccessService
{
	protected string $iBlockTypeId;
	protected int $iBlockId = 0;
	protected int $socNetGroupId = 0;
	protected int $userId;
	protected bool $isCurrentUser = false;

	/**
	 * @throws ArgumentException
	 */
	public function __construct(int $userId, Param $param)
	{
		$param->checkRequiredInputParams(['IBLOCK_TYPE_ID', 'IBLOCK_ID', 'SOCNET_GROUP_ID']);
		if ($param->hasErrors())
		{
			$firstError = $param->getErrors()[0];

			throw new ArgumentException($firstError->getMessage());
		}

		$paramValues = $param->getParams();
		$this->iBlockTypeId = (string)$paramValues['IBLOCK_TYPE_ID'];
		if ((int)$paramValues['IBLOCK_ID'] > 0)
		{
			$this->iBlockId = (int)$paramValues['IBLOCK_ID'];
		}
		if ((int)$paramValues['SOCNET_GROUP_ID'] > 0)
		{
			$this->socNetGroupId = (int)$paramValues['SOCNET_GROUP_ID'];
		}

		$this->userId = $userId;
		GLOBAL $USER;
		if ($userId > 0 && $userId === (int)$USER->GetID())
		{
			$this->isCurrentUser = true;
		}
	}

	/**
	 * @return int
	 */
	public function getUserId(): int
	{
		return $this->userId;
	}

	/**
	 * @return CheckPermissionsResponse
	 */
	public function checkPermissions(): CheckPermissionsResponse
	{
		$response = new CheckPermissionsResponse();
		if ($this->userId === 0 || !$this->isCurrentUser)
		{
			return $response->addError(static::getNotSupportedUserIdError());
		}

		GLOBAL $USER;
		$param = new Param([
			'IBLOCK_TYPE_ID' => $this->iBlockTypeId,
			'IBLOCK_ID' => $this->iBlockId > 0 ? $this->iBlockId : false,
			'SOCNET_GROUP_ID' => $this->socNetGroupId,
		]);
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		$right = new Right($rightParam, new IblockRight($rightParam));

		$response
			->setRightParam($rightParam)
			->setPermission($right->getPermission())
		;

		if (!$right->checkPermission())
		{
			$response->addErrors($right->getErrors());
		}

		return $response;
	}

	/**
	 * @param int $elementId
	 * @param int $sectionId
	 * @param string|null $entityMethod
	 * @param int|null $iBlockId
	 * @return CheckPermissionsResponse
	 */
	public function checkElementPermission(
		int $elementId = 0,
		int $sectionId = 0,
		string $entityMethod = null,
		int $iBlockId = null,
	): CheckPermissionsResponse
	{
		$response = new CheckPermissionsResponse();
		if ($this->userId === 0 || !$this->isCurrentUser)
		{
			return $response->addError(static::getNotSupportedUserIdError());
		}

		$iBlockId = ((int)$iBlockId > 0) ? (int)$iBlockId : $this->iBlockId;

		GLOBAL $USER;
		$param = new Param([
			'IBLOCK_TYPE_ID' => $this->iBlockTypeId,
			'IBLOCK_ID' => $iBlockId > 0 ? $iBlockId : false,
			'SOCNET_GROUP_ID' => $this->socNetGroupId,
		]);
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);
		$rightParam->setEntityId($elementId);
		$rightParam->setSectionId(max($sectionId, 0));

		$elementRight = new ElementRight($rightParam);
		$right = new Right($rightParam, $elementRight);

		$response
			->setRightParam($rightParam)
			->setElementRight($elementRight)
			->setPermission($right->getPermission())
		;

		if (!$right->checkPermission($entityMethod ?? ''))
		{
			$response->addErrors($right->getErrors());
		}

		return $response;
	}

	/**
	 * @param int|null $iBlockId
	 * @param string|null $entityMethod
	 * @return CheckPermissionsResponse
	 */
	public function checkIBlockPermission(int $iBlockId = null, string $entityMethod = null): CheckPermissionsResponse
	{
		$response = new CheckPermissionsResponse();
		if ($this->userId === 0 || !$this->isCurrentUser)
		{
			return $response->addError(static::getNotSupportedUserIdError());
		}

		$iBlockId = $iBlockId ?? $this->iBlockId;

		GLOBAL $USER;
		$param = new Param([
			'IBLOCK_TYPE_ID' => $this->iBlockTypeId,
			'IBLOCK_ID' => $iBlockId > 0 ? $iBlockId : false,
			'SOCNET_GROUP_ID' => $this->socNetGroupId,
		]);
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		$iBlockRight = new IblockRight($rightParam);
		$right = new Right($rightParam, $iBlockRight);

		$response
			->setRightParam($rightParam)
			->setIBlockRight($iBlockRight)
			->setPermission($right->getPermission())
		;

		if (!$right->checkPermission($entityMethod ?? ''))
		{
			$response->addErrors($right->getErrors());
		}

		return $response;
	}

	/**
	 * @param string|null $iBlockTypeId
	 * @return CheckPermissionsResponse
	 */
	public function checkIBlockTypePermission(string $iBlockTypeId = null): CheckPermissionsResponse
	{
		$response = new CheckPermissionsResponse();
		if ($this->userId === 0 || !$this->isCurrentUser)
		{
			return $response->addError(static::getNotSupportedUserIdError());
		}

		$iBlockTypeId = $iBlockTypeId ?? $this->iBlockTypeId;

		GLOBAL $USER;
		$param = new Param([
			'IBLOCK_TYPE_ID' => $iBlockTypeId,
			'IBLOCK_ID' => false,
			'SOCNET_GROUP_ID' => $this->socNetGroupId,
		]);
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		$right = new Right($rightParam, new IblockRight($rightParam)); // any RightEntity

		$response
			->setRightParam($rightParam)
			->setPermission($right->getPermission())
		;

		if (!$right->checkPermission())
		{
			$response->addErrors($right->getErrors());
		}

		return $response;
	}

	/**
	 * @param string|int $permission
	 * @return bool
	 */
	public function isAccessDeniedPermission(string | int $permission): bool
	{
		return $permission <= \CListPermissions::ACCESS_DENIED;
	}

	/**
	 * @param string|int $permission
	 * @return bool
	 */
	public function isAdminPermission(string | int $permission): bool
	{
		return $permission >= \CListPermissions::IS_ADMIN;
	}

	/**
	 * @param string|int $permission
	 * @return bool
	 */
	public function isCanReadPermission(string | int $permission): bool
	{
		return $permission >= \CListPermissions::CAN_READ;
	}

	/**
	 * @return Error
	 */
	public static function getAccessDeniedError(): Error
	{
		return new Error(Loc::getMessage('LISTS_LIB_API_ACCESS_SERVICE_ERROR_ACCESS_DENIED'));
	}

	/**
	 * @return Error
	 */
	protected static function getNotSupportedUserIdError(): Error
	{
		return new Error(Loc::getMessage('LISTS_LIB_API_ACCESS_SERVICE_ERROR_NOT_SUPPORTED_USER_ID'));
	}
}
