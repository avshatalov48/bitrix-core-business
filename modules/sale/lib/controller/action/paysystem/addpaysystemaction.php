<?php

namespace Bitrix\Sale\Controller\Action\PaySystem;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class AddPaySystemAction
 * @package Bitrix\Sale\Controller\Action\PaySystem
 * @example BX.ajax.runAction("sale.paysystem.entity.addPaySystem", { data: { fields: { actionFile:'', [psMode:''] }}});
 * @internal
 */
class AddPaySystemAction extends Sale\Controller\Action\BaseAction
{
	/** @var Sale\PaySystem\BaseServiceHandler $handlerClassName */
	private $handlerClassName;
	private array $handlerDescription = [];
	private array $handlerModeList = [];

	private function checkParams(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		if (empty($fields['ACTION_FILE']))
		{
			$result->addError(
				new Main\Error(
					'actionFile not found',
					Sale\Controller\ErrorEnumeration::ADD_PAY_SYSTEM_ACTION_ACTION_FILE_NOT_FOUND
				)
			);
		}

		if (!empty($fields['PS_MODE']))
		{
			$this->loadHandlerModeList($fields['ACTION_FILE']);
			if (!array_key_exists($fields['PS_MODE'], $this->handlerModeList))
			{
				$result->addError(
					new Main\Error(
						"psMode \"{$fields['PS_MODE']}\" not available",
						Sale\Controller\ErrorEnumeration::ADD_PAY_SYSTEM_ACTION_PS_MODE_NOT_AVAILABLE
					)
				);
			}
		}

		return $result;
	}

	public function run(array $fields)
	{
		$result = [];

		$checkParamsResult = $this->checkParams($fields);
		if (!$checkParamsResult->isSuccess())
		{
			$this->addErrors($checkParamsResult->getErrors());
			return $result;
		}

		$createPaySystemResult = $this->createPaySystem($fields);
		if (!$createPaySystemResult->isSuccess())
		{
			$this->addErrors($createPaySystemResult->getErrors());
			return $result;
		}

		return [
			'ID' => $createPaySystemResult->getId(),
		];
	}

	private function createPaySystem(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		$name = $fields['NAME'] ?? '';
		if (empty($name))
		{
			$name = $this->getDefaultPaySystemName($fields['ACTION_FILE'], $fields['PS_MODE'] ?? null);
		}

		$actionFile = $fields['ACTION_FILE'];
		$psMode = $fields['PS_MODE'] ?? '';

		$paySystemParams = [
			'NAME' => $name,
			'PSA_NAME' => $name,
			'ACTION_FILE' => $actionFile,
			'PS_MODE' => $psMode,
			'NEW_WINDOW' => $fields['NEW_WINDOW'] ?: 'N',
			'ACTIVE' => $fields['ACTIVE'] ?: 'Y',
			'DESCRIPTION' => $fields['DESCRIPTION'] ?? '',
			'XML_ID' => $fields['XML_ID'] ?? Sale\PaySystem\Manager::generateXmlId(),
			'ENTITY_REGISTRY_TYPE' => $fields['ENTITY_REGISTRY_TYPE'] ?? Sale\Registry::REGISTRY_TYPE_ORDER
		];

		if (isset($fields['ENTITY_REGISTRY_TYPE']))
		{
			$paySystemParams['ENTITY_REGISTRY_TYPE'] = $fields['ENTITY_REGISTRY_TYPE'];
		}

		if (isset($fields['LOGOTYPE']))
		{
			$paySystemParams['LOGOTIP'] = self::saveFile($fields['LOGOTYPE']);
		}
		else
		{
			$documentRoot = Main\Application::getDocumentRoot();

			if ($psMode)
			{
				$image = '/bitrix/images/sale/sale_payments/' . $actionFile . '/' . $psMode . '.png';
				if (Main\IO\File::isFileExists($documentRoot . $image))
				{
					$paySystemParams['LOGOTIP'] = \CFile::MakeFileArray($image);
				}
			}

			if (!isset($paySystemParams['LOGOTIP']))
			{
				$image = '/bitrix/images/sale/sale_payments/' . $actionFile . '.png';
				if (Main\IO\File::isFileExists($documentRoot . $image))
				{
					$paySystemParams['LOGOTIP'] = \CFile::MakeFileArray($image);
				}
			}

			if (isset($paySystemParams['LOGOTIP']))
			{
				$paySystemParams['LOGOTIP']['MODULE_ID'] = 'sale';
				\CFile::SaveForDB($paySystemParams, 'LOGOTIP', 'sale/paysystem/logotip');
			}
		}

		$addResult = Sale\PaySystem\Manager::add($paySystemParams);
		if ($addResult->isSuccess())
		{
			$id = $addResult->getId();
			Sale\PaySystem\Manager::update(
				$id,
				[
					'PARAMS' => serialize(
						[
							'BX_PAY_SYSTEM_ID' => $id,
						]
					),
					'PAY_SYSTEM_ID' => $id,
				]
			);

			$personTypeId = $fields['PERSON_TYPE_ID'] ?? 0;

			if (isset($fields['SETTINGS']) && is_array($fields['SETTINGS']))
			{
				foreach ($fields['SETTINGS'] as $key => $value)
				{
					Sale\BusinessValue::setMapping(
						$key,
						Sale\PaySystem\Service::PAY_SYSTEM_PREFIX . $id,
						$personTypeId,
						[
							'PROVIDER_KEY' => $value['TYPE'] ?? '',
							'PROVIDER_VALUE' => $value['VALUE'] ?? '',
						]
					);
				}
			}

			if ($personTypeId > 0)
			{
				static::savePersonTypeId($id, $personTypeId);
			}

			$result->setId($id);
		}
		else
		{
			$result->addErrors($addResult->getErrors());
		}

		return $result;
	}

	private function getDefaultPaySystemName(string $actionFile, ?string $psMode): string
	{
		$this->loadHandlerDescription($actionFile, $psMode);

		$name = $this->handlerDescription['NAME'] ?? '';

		if ($psMode)
		{
			$this->loadHandlerModeList($actionFile);

			$psModeName = $this->handlerModeList[$psMode] ?? '';

			$name .= ": $psModeName";
		}

		return $name ?: 'untitled';
	}

	private function includeHandler(string $actionFile): void
	{
		if ($this->handlerClassName)
		{
			return;
		}

		[$this->handlerClassName] = Sale\PaySystem\Manager::includeHandler($actionFile);
	}

	private function loadHandlerDescription(string $actionFile, ?string $psMode): void
	{
		if ($this->handlerDescription)
		{
			return;
		}

		$this->handlerDescription = Sale\PaySystem\Manager::getHandlerDescription($actionFile, $psMode);
	}

	private function loadHandlerModeList(string $actionFile): void
	{
		if ($this->handlerModeList)
		{
			return;
		}

		$this->includeHandler($actionFile);

		$this->handlerModeList = $this->handlerClassName::getHandlerModeList();
	}

	private static function saveFile($fileContent)
	{
		$file = \CRestUtil::saveFile($fileContent);
		if ($file)
		{
			$file['MODULE_ID'] = 'sale';
			return \CFile::SaveFile($file, 'sale');
		}

		return null;
	}

	private static function savePersonTypeId($serviceId, $personTypeId): void
	{
		$params = [
			'filter' => [
				'SERVICE_ID' => $serviceId,
				'SERVICE_TYPE' => Sale\Services\Base\RestrictionManager::SERVICE_TYPE_PAYMENT,
				'=CLASS_NAME' => '\\'.Sale\Services\PaySystem\Restrictions\PersonType::class
			]
		];

		$serviceRestrictionIterator = Sale\Internals\ServiceRestrictionTable::getList($params);
		if ($serviceRestrictionData = $serviceRestrictionIterator->fetch())
		{
			$restrictionId = $serviceRestrictionData['ID'];
		}
		else
		{
			$restrictionId = 0;
		}

		$fields = [
			'SERVICE_ID' => $serviceId,
			'SERVICE_TYPE' => Sale\Services\Base\RestrictionManager::SERVICE_TYPE_PAYMENT,
			'SORT' => 100,
			'PARAMS' => ['PERSON_TYPE_ID' => [$personTypeId]]
		];

		Sale\Services\PaySystem\Restrictions\PersonType::save($fields, $restrictionId);
	}
}