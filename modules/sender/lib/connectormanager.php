<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class ConnectorManager
{
	/**
	 * Return connector for contacts
	 * @param array $data Event Data
	 * @return mixed
	 */
	public static function onConnectorListContact($data)
	{
		$data['CONNECTOR'] = 'Bitrix\Sender\SenderConnectorContact';

		return $data;
	}

	/**
	 * Return connector for recipients
	 * @param array $data Event Data
	 * @return mixed
	 */
	public static function onConnectorListRecipient($data)
	{
		$data['CONNECTOR'] = 'Bitrix\Sender\SenderConnectorRecipient';

		return $data;
	}

	/**
	 * Return list of connectors
	 * @param array $data Event Data
	 * @return mixed
	 */
	public static function onConnectorList($data)
	{
		$data['CONNECTOR'] = array(
			'Bitrix\Sender\SenderConnectorUnSubscribers'
		);

		return $data;
	}

	/**
	 * @param array $endpointList
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
	 * @param array $fields
	 * @return array|null
	 */
	public static function getEndpointFromFields(array $fields)
	{
		$arEndpointList = null;
		$fieldsTmp = array();

		foreach($fields as $moduleId => $arConnectorSettings)
		{
			if (is_numeric($moduleId)) $moduleId = '';
			foreach($arConnectorSettings as $connectorCode => $arConnectorFields)
		{
				foreach($arConnectorFields as $k => $field)
				{
					if (isset($fieldsTmp[$moduleId][$connectorCode][$k]) && is_array($field))
					{
						foreach($field as $fieldName => $fieldValue)
						{
							if(!isset($fieldsTmp[$moduleId][$connectorCode][$k][$fieldName]))
							{
								$fieldsTmp[$moduleId][$connectorCode][$k][$fieldName] = $fieldValue;
							}
							else
							{
								if(!is_array($fieldsTmp[$moduleId][$connectorCode][$k][$fieldName]))
								{
									$fieldsTmp[$moduleId][$connectorCode][$k][$fieldName] = array(
										$fieldsTmp[$moduleId][$connectorCode][$k][$fieldName]
									);
								}

								if(is_array($fieldValue))
								{
									$fieldsTmp[$moduleId][$connectorCode][$k][$fieldName] = array_merge(
										$fieldsTmp[$moduleId][$connectorCode][$k][$fieldName],
										$fieldValue
									);
								}
								else
								{
									$fieldsTmp[$moduleId][$connectorCode][$k][$fieldName][] = $fieldValue;
								}

							}
						}
					}
					else
						$fieldsTmp[$moduleId][$connectorCode][$k] = $field;
				}
			}
		}


		foreach($fieldsTmp as $moduleId => $arConnectorSettings)
		{
			if(is_numeric($moduleId)) $moduleId = '';
			foreach($arConnectorSettings as $connectorCode => $arConnectorFields)
			{
				foreach($arConnectorFields as $arFields)
				{
					$arEndpoint = array();
					$arEndpoint['MODULE_ID'] = $moduleId;
					$arEndpoint['CODE'] = $connectorCode;
					$arEndpoint['FIELDS'] = $arFields;
					$arEndpointList[] = $arEndpoint;
				}
			}
		}

		return $arEndpointList;
	}

	/**
	 * Return instance of connector by endpoint array.
	 *
	 * @param array
	 * @return \Bitrix\Sender\Connector|null
	 */
	public static function getConnector(array $endpoint)
	{
		$connector = null;
		$arConnector = static::getConnectorList(array($endpoint));
		/** @var \Bitrix\Sender\Connector $connector */
		foreach($arConnector as $connector)
		{
			break;
		}

		return $connector;
	}

	/**
	 * Return array of instances of connector by endpoints array.
	 *
	 * @param array|null
	 * @return \Bitrix\Sender\Connector[]
	 */
	public static function getConnectorList(array $endpointList = null)
	{
		$connectorList = array();

		$connectorClassList = static::getConnectorClassList($endpointList);
		foreach($connectorClassList as $connectorDescription)
		{
			/** @var \Bitrix\Sender\Connector $connector */
			$connector = new $connectorDescription['CLASS_NAME'];
			$connector->setModuleId($connectorDescription['MODULE_ID']);
			$connectorList[] = $connector;
		}

		return $connectorList;
	}

	/**
	 * Return array of connectors information by endpoints array.
	 *
	 * @param array|null
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

					$connectorCode = call_user_func(array($connectorClassName, 'getCode'));
					if($moduleConnectorFilter && !in_array($connectorCode, $moduleConnectorFilter[$eventResult->getModuleId()]))
					{
						continue;
					}

					$connectorName = call_user_func(array($connectorClassName, 'getName'));
					$connectorRequireConfigure = call_user_func(array($connectorClassName, 'requireConfigure'));
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