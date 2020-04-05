<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Template as TemplateCore;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\PublicActionResult;

Loc::loadMessages(__FILE__);

class Template
{
	/**
	 * Get available templates.
	 * @param array $params Params ORM array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getList($params = array())
	{
		$result = new PublicActionResult();

		$data = array();
		$res = TemplateCore::getList($params);
		while ($row = $res->fetch())
		{
			$data[] = $row;
		}
		$result->setResult($data);

		return $result;
	}

	/**
	 * Set new template refs for site.
	 * @param int $id Entity id.
	 * @param string $type Entity type.
	 * @param string $method Method: set or get.
	 * @param array $data Ref array (area => landing).
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	protected static function refProcess($id, $type, $method, $data = array())
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		if ($type == TemplateRef::ENTITY_TYPE_SITE)
		{
			$entityClass = Site::class;
			$method = $method . 'ForSite';
		}
		else if ($type == TemplateRef::ENTITY_TYPE_LANDING)
		{
			$entityClass = Landing::class;
			$method = $method . 'ForLanding';
		}

		if (isset($entityClass))
		{
			$entity = $entityClass::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'ID' => $id
				)
			))->fetch();
			if ($entity)
			{
				$res = TemplateRef::$method(
					$id,
					$data
				);
				$result->setResult(
					$res == null ? true : $res
				);
			}
			else
			{
				$error->addError(
					'ENTITY_NOT_FOUND',
					Loc::getMessage('LANDING_ENTITY_NOT_FOUND')
				);
			}
		}

		$result->setError($error);

		return $result;
	}

	/**
	 * Set new template refs for site.
	 * @param int $id Site id.
	 * @param array $data Ref array (area => landing).
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function setSiteRef($id, $data = array())
	{
		return self::refProcess($id, TemplateRef::ENTITY_TYPE_SITE, 'set', $data);
	}

	/**
	 * Set new template refs for landing.
	 * @param int $id Landing id.
	 * @param array $data Ref array (area => landing).
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function setLandingRef($id, $data = array())
	{
		return self::refProcess($id, TemplateRef::ENTITY_TYPE_LANDING, 'set', $data);
	}

	/**
	 * Get template refs for site.
	 * @param int $id Site id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getSiteRef($id)
	{
		return self::refProcess($id, TemplateRef::ENTITY_TYPE_SITE, 'get');
	}

	/**
	 * Get template refs for landing.
	 * @param int $id Site id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getLandingRef($id)
	{
		return self::refProcess($id, TemplateRef::ENTITY_TYPE_LANDING, 'get');
	}
}