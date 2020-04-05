<?php
namespace Bitrix\Landing;

use \Bitrix\Rest\AppTable;

class Repo extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'RepoTable';

	/**
	 * Create new record and return it new id.
	 * @param array $fields Fields array.
	 * @return \Bitrix\Main\Result
	 */
	public static function add($fields)
	{
		$res = parent::add($fields);

		if ($res->isSuccess())
		{
			Block::clearRepositoryCache();
		}

		return $res;
	}

	/**
	 * Update the record.
	 * @param int $id Record id.
	 * @param array $fields New fields record.
	 * @return \Bitrix\Main\Result
	 */
	public static function update($id, $fields = array())
	{
		$res = parent::update($id, $fields);

		if ($res->isSuccess())
		{
			Block::clearRepositoryCache();
		}

		return $res;
	}

	/**
	 * Delete the record.
	 * @param int $id Record id.
	 * @return \Bitrix\Main\Result
	 */
	public static function delete($id)
	{
		$res = parent::delete($id);

		if ($res->isSuccess())
		{
			Block::clearRepositoryCache();
		}

		return $res;
	}

	/**
	 * Get repository.
	 * @return array
	 */
	public static function getRepository()
	{
		$items = array();
		$siteId = Manager::getMainSiteId();
		$siteTemplateId = Manager::getTemplateId($siteId);

		$res = self::getList(array(
			'select' => array(
				'ID', 'NAME', 'DATE_CREATE', 'DESCRIPTION',
				'SECTIONS', 'PREVIEW', 'APP_CODE',
				new \Bitrix\Main\Entity\ExpressionField(
					'DATE_CREATE_TIMESTAMP',
					'UNIX_TIMESTAMP(DATE_CREATE)'
				)
			),
			'filter' => array(
				'=ACTIVE' => 'Y',
				Manager::isTemplateIdSystem($siteTemplateId)
					? array(
						'LOGIC' => 'OR',
						['=SITE_TEMPLATE_ID' => $siteTemplateId],
						['=SITE_TEMPLATE_ID' => false]
					)
					: array(
						['=SITE_TEMPLATE_ID' => $siteTemplateId]
					)
			),
			'order' => array(
				'ID' => 'DESC'
			)
		));
		while ($row = $res->fetch())
		{
			$items['repo_'. $row['ID']] = array(
				'name' => $row['NAME'],
				'namespace' => $row['APP_CODE'],
				'new' => (time() - $row['DATE_CREATE_TIMESTAMP']) < Block::NEW_BLOCK_LT,
				'section' => explode(',', $row['SECTIONS']),
				'description' => $row['DESCRIPTION'],
				'preview' => $row['PREVIEW'],
				'restricted' => true,
				'repo_id' => $row['ID'],
				'app_code' => $row['APP_CODE']
			);
		}

		return $items;
	}

	/**
	 * Return full info from rest block.
	 * @param int $id Block id.
	 * @return array
	 */
	public static function getBlock($id)
	{
		static $manifest = array();

		if (!isset($manifest[$id]))
		{
			$manifest[$id] = array();
			if (($block = self::getById($id)->fetch()))
			{
				$manifestLocal = unserialize($block['MANIFEST']);
				if (!is_array($manifestLocal))
				{
					$manifestLocal = array();
				}
				if (
					isset($manifestLocal['block']) &&
					is_array($manifestLocal['block'])
				)
				{
					$blockDesc = $manifestLocal['block'];
				}
				$manifestLocal['block'] = array(
					'name' => $block['NAME'],
					'description' => $block['DESCRIPTION'],
					'namespace' => $block['APP_CODE'],
					'section' => explode(',', $block['SECTIONS']),
					'preview' => $block['PREVIEW'],
					'restricted' => true,
					'repo_id' => $block['ID'],
					'xml_id' => $block['XML_ID'],
					'app_code' => $block['APP_CODE']
				);
				if (isset($blockDesc['subtype']))
				{
					$manifestLocal['block']['subtype'] = $blockDesc['subtype'];
				}
				$manifest[$id] = $manifestLocal;
			}
		}

		return $manifest[$id];
	}

	/**
	 * Get row by Id.
	 * @param int $id Id.
	 * @return Bitrix\Main\DB\Result
	 */
	public static function getById($id)
	{
		return parent::getList(array(
			'filter' => array(
				'ID' => $id
			)
		));
	}

	/**
	 * Delete all app blocks from repo.
	 * @param string $code App code.
	 * @return void
	 */
	public static function deleteByAppCode($code)
	{
		$codeToDelete = array();
		// delete blocks from repo
		$res = self::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'=APP_CODE' => $code
			)
		));
		while ($row = $res->fetch())
		{
			self::delete($row['ID']);
			$codeToDelete[] = 'repo_' . $row['ID'];
		}
		// delete added blocks
		if (!empty($codeToDelete))
		{
			Block::deleteByCode($codeToDelete);
		}
	}

	/**
	 * Return info about installed app(s).
	 * @param array|int $id Repo block id(s).
	 * @return array
	 */
	public static function getAppInfo($id)
	{
		$isArray = is_array($id);
		if (!$isArray)
		{
			$id = array($id);
		}
		$id = array_fill_keys($id, false);

		if ($id)
		{
			// get app codes from repo first
			$res = self::getList(array(
				'select' => array(
					'ID', 'APP_CODE'
				),
				'filter' => array(
					'ID' => array_keys($id)
				)
			));
			while ($row = $res->fetch())
			{
				if ($row['APP_CODE'])
				{
					$id[$row['ID']] = $row['APP_CODE'];
				}
			}
			// get info about apps
			$apps = self::getAppByCode($id);
			// fill result array
			foreach ($id as &$code)
			{
				if ($code && isset($apps[$code]))
				{
					$code = $apps[$code];
				}
				else
				{
					$code = array();
				}
			}
			unset($code);
		}

		if ($id)
		{
			return $isArray ? $id : array_pop($id);
		}

		return $apps;
	}

	/**
	 * Get info about app by code(s) from Repo.
	 * @param string|array $code App code(s).
	 * @return array
	 */
	public static function getAppByCode($code)
	{
		$apps = array();

		if ($code && \Bitrix\Main\Loader::includeModule('rest'))
		{
			$res = AppTable::getList(array(
				'filter' => array(
					'=CODE' => $code
				)
			));
			while ($row = $res->fetch())
			{
				$row['APP_STATUS'] = AppTable::getAppStatusInfo($row, '');
				$apps[$row['CODE']] = array(
					'CODE' => $row['CODE'],
					'APP_NAME' => $row['APP_NAME'],
					'VERSION' => $row['VERSION'],
					'DATE_FINISH' => $row['DATE_FINISH'],
					'PAYMENT_ALLOW' => $row['APP_STATUS']['PAYMENT_ALLOW']
				);
			}
		}

		if ($apps)
		{
			return is_array($code) ? $apps : array_pop($apps);
		}

		return $apps;
	}
}