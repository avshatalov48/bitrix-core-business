<?php

namespace Bitrix\Lists\Api\Service\IBlockService;

use Bitrix\Lists\Api\Response\CheckPermissionsResponse;
use Bitrix\Lists\Security\ElementRight;
use Bitrix\Lists\Security\IblockRight;

final class AccessService extends \Bitrix\Lists\Api\Service\AccessService
{
	public function canUserReadIBlock(int $iBlockId): CheckPermissionsResponse
	{
		return $this->checkIBlockPermission($iBlockId, IblockRight::READ);
	}

	public function canUserReadIBlockList(): CheckPermissionsResponse
	{
		$response = new CheckPermissionsResponse();

		$checkPermissionsResponse = $this->checkIBlockTypePermission();
		$response->fillFromResponse($checkPermissionsResponse);

		if ($response->isSuccess() && $this->isAccessDeniedPermission($response->getPermission()))
		{
			$response->addError(self::getAccessDeniedError());
		}

		return $response;
	}

	public function canUserReadElement(int $elementId, int $sectionId, int $iBlockId): CheckPermissionsResponse
	{
		$response = new CheckPermissionsResponse();

		$checkPermissionsResponse =
			$this->checkElementPermission($elementId, $sectionId, '', $iBlockId)
		;
		$elementRight = $checkPermissionsResponse->getElementRight();
		$response->fillFromResponse($checkPermissionsResponse);

		if ($response->isSuccess())
		{
			if (
				($elementId !== 0 && !$elementRight?->canRead())
				|| ($elementId === 0 && !$elementRight?->canAdd())
			)
			{
				$response->addError(self::getAccessDeniedError());
			}
		}

		return $response;
	}

	public function canUserReadElementList(?int $iBlockId): CheckPermissionsResponse
	{
		$response = new CheckPermissionsResponse();

		$checkPermissionsResponse = (
			$iBlockId
				? $this->checkIBlockPermission($iBlockId)
				: $this->checkIBlockTypePermission()
		);
		$response->fillFromResponse($checkPermissionsResponse);

		if ($response->isSuccess() && $this->isAccessDeniedPermission($response->getPermission()))
		{
			$response->addError(self::getAccessDeniedError());
		}

		return $response;
	}

	public function canUserAddElement(int $sectionId, int $iBlockId): CheckPermissionsResponse
	{
		return $this->checkElementPermission(0, $sectionId, ElementRight::ADD, $iBlockId);
	}

	public function canUserEditElement(int $elementId, int $sectionId, int $iBlockId): CheckPermissionsResponse
	{
		return $this->checkElementPermission($elementId, $sectionId, ElementRight::EDIT, $iBlockId);
	}
}
