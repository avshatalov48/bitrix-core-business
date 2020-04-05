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
				'=SYSTEM' => true,
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

			$data['SYSTEM'] = 'Y';

			$segment = new Entity\Segment;
			$segment->mergeData($data)->save();
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
				'=SYSTEM' => true,
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

			$fields = $data['fields'];
			$fields['BX_PRESET_ID'] = $code;

			$item = array(
				'CODE' => $code,
				'NAME' => $data['sender_segment_name'],
				'SORT' => 100,
				'ENDPOINTS' => array(
					array(
						'MODULE_ID' => 'sender',
						'CODE' => $connector->getCode(),
						'FIELDS' => $fields
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

			$fields = $data['fields'];
			$fields['BX_PRESET_ID'] = $code;

			$item = array(
				'CODE' => $code,
				'NAME' => $data['sender_segment_name'],
				'SORT' => 100,
				'ENDPOINTS' => array(
					array(
						'MODULE_ID' => 'sender',
						'CODE' => $connector->getCode(),
						'FIELDS' => $fields
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

	private function getInstalledSegment($code = null)
	{
		$filter = array(
			'=SYSTEM' => true,
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