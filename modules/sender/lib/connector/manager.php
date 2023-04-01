<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Connector;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\Integration;

/**
 * Class Manager
 * @package Bitrix\Sender\Connector
 */
class Manager
{
	/**
	 * Return connector for contacts.
	 *
	 * @param array $data Event Data.
	 * @return mixed
	 * @deprecated
	 */
	public static function onConnectorListContact($data)
	{
		return $data;
	}

	/**
	 * Return connector for recipients.
	 *
	 * @param array $data Event Data.
	 * @return mixed
	 * @deprecated
	 */
	public static function onConnectorListRecipient($data)
	{
		return $data;
	}

	/**
	 * Return list of connectors.
	 *
	 * @param array $data Event Data.
	 * @return array
	 */
	public static function onConnectorList($data)
	{
		return Integration\EventHandler::onConnectorList($data);
	}

	/**
	 * Get fields from endpoint.
	 *
	 * @param array $endpointList Endpoints.
	 * @return array
	 */
	public static function getFieldsFromEndpoint(array $endpointList)
	{
		$arResult = array();
		foreach($endpointList as $endpoint)
		{
			$arResult[$endpoint['MODULE_ID']][$endpoint['CODE']][] = $endpoint['FIELDS'];
		}
		
		return $arResult;
	}

	/**
	 * Get endpoint from fields.
	 *
	 * @param array $postData Post data.
	 * @return array|null
	 */
	public static function getEndpointFromFields(array $postData)
	{
		$result = null;
		$fieldsTmp = array();

		foreach($postData as $moduleId => $settings)
		{
			if (is_numeric($moduleId))
			{
				$moduleId = '';
			}

			foreach($settings as $code => $items)
			{
				foreach($items as $num => $field)
				{
					if (isset($fieldsTmp[$moduleId][$code][$num]) && is_array($field))
					{
						foreach($field as $fieldName => $fieldValue)
						{
							if(!isset($fieldsTmp[$moduleId][$code][$num][$fieldName]))
							{
								$fieldsTmp[$moduleId][$code][$num][$fieldName] = $fieldValue;
							}
							else
							{
								if(!is_array($fieldsTmp[$moduleId][$code][$num][$fieldName]))
								{
									$fieldsTmp[$moduleId][$code][$num][$fieldName] = array(
										$fieldsTmp[$moduleId][$code][$num][$fieldName]
									);
								}

								if(is_array($fieldValue))
								{
									$fieldsTmp[$moduleId][$code][$num][$fieldName] = array_merge(
										$fieldsTmp[$moduleId][$code][$num][$fieldName],
										$fieldValue
									);
								}
								else
								{
									$fieldsTmp[$moduleId][$code][$num][$fieldName][] = $fieldValue;
								}

							}
						}
					}
					else
					{
						if ($field && is_string($field))
						{
							try
							{
								$field = Json::decode($field);
							}
							catch (\Exception $exception)
							{
							}
						}
						else
						{
							$field = null;
						}
						$fieldsTmp[$moduleId][$code][$num] = $field;
					}
				}
			}
		}


		foreach($fieldsTmp as $moduleId => $settings)
		{
			if(is_numeric($moduleId))
			{
				$moduleId = '';
			}

			foreach($settings as $code => $items)
			{
				foreach($items as $filter => $fields)
				{
					if (!is_array($result))
					{
						$result = array();
					}

					$result[] = array(
						'MODULE_ID' => $moduleId,
						'CODE' => $code,
						'FIELDS' => $fields,
						'FILTER_ID' => $moduleId . "_" . $code . "_" . $filter,
					);
				}
			}
		}

		return $result;
	}

	/**
	 * Return instance of connector by endpoint array.
	 *
	 * @param array
	 * @return Base|null
	 */
	public static function getConnector(array $endpoint)
	{
		$connector = null;
		$connectors = static::getConnectorList(array($endpoint));
		foreach($connectors as $connector)
		{
			break;
		}

		return $connector;
	}

	/**
	 * Return array of instances of connector by endpoints array.
	 *
	 * @param array
	 * @return Base[]
	 */
	public static function getConnectorList(array $endpointList = null)
	{
		$connectorList = array();

		$connectorClassList = static::getConnectorClassList($endpointList);
		foreach($connectorClassList as $connectorDescription)
		{
			/** @var Base $connector */
			$connector = new $connectorDescription['CLASS_NAME'];
			$connector->setModuleId($connectorDescription['MODULE_ID']);
			$connectorList[] = $connector;
		}

		return $connectorList;
	}

	/**
	 * Return array of connectors information by endpoints array.
	 *
	 * @param array
	 * @return array
	 */
	public static function getConnectorClassList(array $endpointList = null)
	{
		$resultList = array();
		$moduleIdFilter = null;
		$moduleConnectorFilter = null;

		if($endpointList)
		{
			$moduleIdFilter = array();
			foreach($endpointList as $endpoint)
			{
				$moduleIdFilter[] = $endpoint['MODULE_ID'];
				$moduleConnectorFilter[$endpoint['MODULE_ID']][] = $endpoint['CODE'];
			}
		}

		$data = array();
		$event = new Event('sender', 'OnConnectorList', array($data), $moduleIdFilter);
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() == EventResult::ERROR)
			{
				continue;
			}

			$eventResultParameters = $eventResult->getParameters();

			if($eventResultParameters && array_key_exists('CONNECTOR', $eventResultParameters))
			{
				$connectorClassNameList = $eventResultParameters['CONNECTOR'];
				if (!is_array($eventResultParameters['CONNECTOR']))
				{
					$connectorClassNameList = array($connectorClassNameList);
				}
				foreach ($connectorClassNameList as $connectorClassName)
				{
					if(!is_subclass_of($connectorClassName,  '\Bitrix\Sender\Connector'))
					{
						continue;
					}

					/**
					 * @var \Bitrix\Sender\Connector $connectorInstance
					 */
					$connectorInstance = new $connectorClassName;

					$connectorCode = $connectorInstance->getCode();
					if($moduleConnectorFilter && !in_array($connectorCode, $moduleConnectorFilter[$eventResult->getModuleId()]))
					{
						continue;
					}

					$connectorName =  $connectorInstance->getName();
					$connectorRequireConfigure =$connectorInstance->requireConfigure();

					$resultList[] = array(
						'MODULE_ID' => $eventResult->getModuleId(),
						'CLASS_NAME' => $connectorClassName,
						'CODE' => $connectorCode,
						'NAME' => $connectorName,
						'REQUIRE_CONFIGURE' => $connectorRequireConfigure,
					);
				}
			}
		}

		if(!empty($resultList))
			usort($resultList, array(__CLASS__, 'sort'));

		return $resultList;
	}

	/**
	 * Sort.
	 *
	 * @param $a
	 * @param $b
	 * @return int
	 */
	public static function sort($a, $b)
	{
		if ($a['NAME'] == $b['NAME'])
			return 0;

		return ($a['NAME'] < $b['NAME']) ? -1 : 1;
	}
}