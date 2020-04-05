<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Socialnetwork\WorkgroupSubjectSiteTable;

class WorkgroupSubject
{
	public static function syncSiteId($params = array())
	{
		if (!is_array($params))
		{
			return false;
		}

		$subjectId = (isset($params['subjectId']) && intval($params['subjectId']) > 0 ? intval($params['subjectId']) : false);
		$groupSiteIdList = (isset($params['siteId']) && is_array($params['siteId']) && !empty($params['siteId']) ? $params['siteId'] : array());

		foreach($groupSiteIdList as $key => $siteId)
		{
			if (empty($siteId))
			{
				unset($groupSiteIdList[$key]);
			}
		}

		if (
			empty($subjectId)
			|| empty($groupSiteIdList)
		)
		{
			return false;
		}

		$subjectSiteList = array();

		$res = WorkgroupSubjectSiteTable::getList(array(
			'filter' => array(
				'SUBJECT_ID' => $subjectId
			),
			'select' => array('SITE_ID')
		));

		while ($subjectSite = $res->fetch())
		{
			$subjectSiteList[] = $subjectSite['SITE_ID'];
		}

		$addSubjectSiteList = array_diff($groupSiteIdList, $subjectSiteList);
		$addSubjectSiteList = array_unique($addSubjectSiteList);

		if (!empty($addSubjectSiteList))
		{
			foreach ($addSubjectSiteList as $siteId)
			{
				WorkgroupSubjectSiteTable::add(array(
					'SUBJECT_ID' => $subjectId,
					'SITE_ID' => $siteId
				));
			}
		}

		return true;
	}
}
