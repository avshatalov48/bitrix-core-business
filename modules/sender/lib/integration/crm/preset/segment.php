<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\Preset;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\DB\SqlQueryException;

use Bitrix\Sender\Preset;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Integration\Crm\Connectors;

Loc::loadMessages(__FILE__);

/**
 * class Segment
 * @package Bitrix\Sender\Integration\Crm\Preset
 */
class Segment implements Preset\Installation\iInstallable
{
	/**
	 * Get installable ID.
	 *
	 * @return string
	 */
	public function getId()
	{
		return 'crm-segment';
	}

	/**
	 * Return true if it is installed.
	 *
	 * @return bool
	 */
	public function isInstalled()
	{
		$list = Entity\Segment::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=IS_SYSTEM' => true,
				'%CODE' => 'crm_%'
			),
			'limit' => 1
		));

		return ($list->fetch()) ? true : false;
	}

	/**
	 * Install.
	 *
	 * @return bool
	 * @throws SqlQueryException
	 */
	public function install()
	{
		Loader::includeModule('crm');
		foreach ($this->getSegments() as $data)
		{
			if ($this->getInstalledSegment($data['CODE']))
			{
				continue;
			}

			$data['IS_SYSTEM'] = 'Y';

			try
			{
				$segment = new Entity\Segment;
				$segment->mergeData($data)->save();
			}
			catch (SqlQueryException $exception)
			{
				if (mb_strpos($exception->getDatabaseMessage(), '(1062)') === false)
				{
					throw $exception;
				}
			}


		}

		return true;
	}

	/**
	 * Uninstall.
	 *
	 * @return bool
	 */
	public function uninstall()
	{
		$segments = Entity\Segment::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=IS_SYSTEM' => true,
				'%CODE' => 'crm_%'
			)
		));
		foreach ($segments as $segment)
		{
			if (Entity\Segment::removeById($segment['ID']))
			{
				continue;
			}

			return false;
		}

		return true;
	}

	private function getSegments()
	{
		$endpointsAll = array();

		$list = array();
		$connector = new Connectors\Lead;
		foreach (Connectors\Lead::getUiFilterPresets() as $code => $data)
		{
			if (empty($data['sender_segment_name']))
			{
				continue;
			}

			$segmentCode = $code;
			if (!empty($data['sender_segment_business_case']))
			{
				$segmentCode = "case_" . $segmentCode;
			}

			$fields = $data['fields'];
			$fields['BX_PRESET_ID'] = $code;

			$item = array(
				'CODE' => $segmentCode,
				'NAME' => $data['sender_segment_name'],
				'SORT' => 100,
				'ENDPOINTS' => array(
					array(
						'MODULE_ID' => 'sender',
						'CODE' => $connector->getCode(),
						'FIELDS' => self::convertPresetFields($fields)
					)
				)
			);

			$list[] = $item;
			if ($item['CODE'] === 'crm_lead_all')
			{
				$endpointsAll = array_merge($endpointsAll, $item['ENDPOINTS']);
			}
		}


		$connector = new Connectors\Client;
		foreach (Connectors\Client::getUiFilterPresets() as $code => $data)
		{
			if (empty($data['sender_segment_name']))
			{
				continue;
			}

			$segmentCode = $code;
			if (!empty($data['sender_segment_business_case']))
			{
				$segmentCode = "case_" . $segmentCode;
			}

			$fields = $data['fields'];
			$fields['BX_PRESET_ID'] = $code;

			$item = array(
				'CODE' => $segmentCode,
				'NAME' => $data['sender_segment_name'],
				'SORT' => 100,
				'ENDPOINTS' => array(
					array(
						'MODULE_ID' => 'sender',
						'CODE' => $connector->getCode(),
						'FIELDS' => self::convertPresetFields($fields)
					)
				)
			);

			$list[] = $item;
			if ($item['CODE'] === 'crm_client_all')
			{
				$endpointsAll = array_merge($endpointsAll, $item['ENDPOINTS']);
			}
		}

		if (count($endpointsAll) > 1)
		{
			$list[] = array(
				'CODE' => Entity\Segment::CODE_ALL,
				'NAME' => Loc::getMessage('SENDER_INTEGRATION_CRM_PRESET_SEGMENT_ALL'),
				'SORT' => 50,
				'ENDPOINTS' => $endpointsAll
			);
		}

		return $list;
	}

	private static function convertPresetFields($fields)
	{
		if (!is_array($fields))
		{
			return $fields;
		}

		$codes = ['allow_year', 'datesel', 'from', 'to', 'days'];
		$result = [];
		foreach ($fields as $key => $value)
		{
			$baseKey = null;
			foreach ($codes as $code)
			{
				$code = "_" . $code;
				if (mb_substr($key, -mb_strlen($code)) == $code)
				{
					$baseKey = mb_substr($key, 0, -mb_strlen($code));
					break;
				}
			}

			if ($baseKey)
			{
				if (empty($result[$baseKey]))
				{
					$result[$baseKey] = [];
				}

				$result[$baseKey][$key] = $value;
			}
			else
			{
				$result[$key] = $value;
			}
		}

		return $result;
	}

	private function getInstalledSegment($code = null)
	{
		$filter = array(
			'=IS_SYSTEM' => true,
		);
		if ($code)
		{
			$filter['=CODE'] = $code;
		}
		else
		{
			$filter['CODE'] = 'crm_%';
		}

		$list = Entity\Segment::getList(array(
			'select' => array('ID'),
			'filter' => $filter,
			'limit' => 1
		));
		if ($segment = $list->fetch())
		{
			return $segment['ID'];
		}
		else
		{
			return null;
		}
	}
}