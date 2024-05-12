<?php

namespace Bitrix\Lists\Api\Service\ServiceFactory;

use Bitrix\Lists\Api\Data\ServiceFactory\AverageTemplateDurationToGet;
use Bitrix\Lists\Api\Data\ServiceFactory\ElementToAdd;
use Bitrix\Lists\Api\Data\ServiceFactory\ElementToGetDetailInfo;
use Bitrix\Lists\Api\Data\ServiceFactory\ElementToUpdate;
use Bitrix\Lists\Api\Request\ServiceFactory\AddElementRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\GetAverageIBlockTemplateDurationRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\GetElementDetailInfoRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\UpdateElementRequest;
use Bitrix\Lists\Api\Response\ServiceFactory\AddElementResponse;
use Bitrix\Lists\Api\Response\ServiceFactory\GetAverageIBlockTemplateDurationResponse;
use Bitrix\Lists\Api\Response\ServiceFactory\GetElementDetailInfoResponse;
use Bitrix\Lists\Api\Response\ServiceFactory\UpdateElementResponse;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

final class DataService
{
	public function getElementToGetDetailInfoObject(
		GetElementDetailInfoRequest $request,
		GetElementDetailInfoResponse $response
	): ?ElementToGetDetailInfo
	{
		$elementToGet = null;
		try
		{
			$elementToGet = ElementToGetDetailInfo::createFromRequest($request);
		}
		catch (ArgumentOutOfRangeException $exception)
		{
			$parameter = $exception->getParameter();
			if ($parameter === 'iBlockId')
			{
				$response->addError(self::getWrongIBlockError());
			}

			if ($parameter === 'elementId')
			{
				$response->addError(self::getNegativeElementIdError());
			}

			if ($parameter === 'sectionId')
			{
				$response->addError(self::getNegativeSectionIdError());
			}
		}

		return $elementToGet;
	}

	public function getElementToAddObject(
		AddElementRequest $request,
		AddElementResponse $response
	): ?ElementToAdd
	{
		$elementToAdd = null;
		try
		{
			$elementToAdd = ElementToAdd::createFromRequest($request);
		}
		catch (ArgumentOutOfRangeException $exception)
		{
			$parameter = $exception->getParameter();
			if ($parameter === 'iBlockId')
			{
				$response->addError(self::getWrongIBlockError());
			}

			if ($parameter === 'sectionId')
			{
				$response->addError(self::getNegativeSectionIdError());
			}

			if ($parameter === 'createdBy')
			{
				$response->addError(self::getNegativeUserIdError());
			}
		}

		return $elementToAdd;
	}

	public function getElementToUpdateObject(
		UpdateElementRequest $request,
		UpdateElementResponse $response
	): ?ElementToUpdate
	{
		$elementToUpdate = null;
		try
		{
			$elementToUpdate = ElementToUpdate::createFromRequest($request);
		}
		catch (ArgumentOutOfRangeException $exception)
		{
			$parameter = $exception->getParameter();
			if ($parameter === 'elementId')
			{
				$response->addError(self::getNegativeElementIdError());
			}

			if ($parameter === 'iBlockId')
			{
				$response->addError(self::getWrongIBlockError());
			}

			if ($parameter === 'sectionId')
			{
				$response->addError(self::getNegativeSectionIdError());
			}

			if ($parameter === 'modifiedBy')
			{
				$response->addError(self::getNegativeUserIdError());
			}
		}

		return $elementToUpdate;
	}

	public function getAverageTemplateDurationToGetObject(
		GetAverageIBlockTemplateDurationRequest $request,
		GetAverageIBlockTemplateDurationResponse $response,
	): ?AverageTemplateDurationToGet
	{
		$object = null;
		try
		{
			$object = AverageTemplateDurationToGet::createFromRequest($request);
		}
		catch (ArgumentOutOfRangeException $e)
		{
			$response->addError(self::getWrongIBlockError());
		}
		catch (ArgumentException $e)
		{
			$response->addError(new Error('invalid auto execute type'));
		}

		return $object;
	}

	public static function getWrongIBlockError(): Error
	{
		return new Error(Loc::getMessage('LISTS_LIB_API_DATA_SERVICE_ERROR_WRONG_IBLOCK'));
	}

	private static function getNegativeElementIdError(): Error
	{
		return new Error(Loc::getMessage('LISTS_LIB_API_DATA_SERVICE_ERROR_NEGATIVE_ELEMENT_ID'));
	}

	private static function getNegativeSectionIdError(): Error
	{
		return new Error(Loc::getMessage('LISTS_LIB_API_DATA_SERVICE_ERROR_NEGATIVE_SECTION_ID'));
	}

	private static function getNegativeUserIdError(): Error
	{
		return new Error(Loc::getMessage('LISTS_LIB_API_DATA_SERVICE_ERROR_NEGATIVE_CREATED_BY_ID'));
	}
}
