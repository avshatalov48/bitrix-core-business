<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Stat;

use Bitrix\Sender\Entity;

class Posting
{
	public static function getData($letterId, array $parameters = array())
	{
		$letter = new Entity\Letter($letterId);
		$postingData = $letter->getLastPostingData();
		$postingId = $postingData['POSTING_ID'];

		if (isset($parameters['USER_NAME_FORMAT']))
		{
			$userNameFormat = $parameters['USER_NAME_FORMAT'];
		}
		else
		{
			$userNameFormat = \CSite::GetNameFormat(true);
		}
		if (isset($parameters['PATH_TO_USER_PROFILE']))
		{
			$pathToUser = $parameters['PATH_TO_USER_PROFILE'];
		}
		else
		{
			$pathToUser = '/bitrix/admin/user_edit.php?ID=#id#&lang=' . LANGUAGE_ID;
		}

		$pathToUser = str_replace('#id#', intval($postingData['CREATED_BY']), $pathToUser);

		$data = array(
			'counters' => array(),
			'clickList' => array()
		);
		$data['posting']['linkParams'] = $postingData['LINK_PARAMS'];
		if ($postingData['DATE_SENT'])
		{
			$data['posting']['dateSent'] = FormatDate('x', $postingData['DATE_SENT']->getTimestamp());
		}

		$data['posting']['createdBy'] = array(
			'id' => $postingData['CREATED_BY'],
			'name' => \CUser::FormatName(
				$userNameFormat,
				array(
					"TITLE" => $postingData['CREATED_BY_TITLE'],
					"NAME" => $postingData['CREATED_BY_NAME'],
					"SECOND_NAME" => $postingData['CREATED_BY_SECOND_NAME'],
					"LAST_NAME" => $postingData['CREATED_BY_LAST_NAME'],
					"LOGIN" => $postingData['CREATED_BY_LOGIN'],
				),
				true, false
			),
			'url' => $pathToUser,
		);

		if (!$postingId)
		{
			return $data;
		}

		$postingStat = Statistics::create()->filter('postingId', $postingId);
		$postingStat->setCacheTtl(0);
		$data['clickList'] = $postingStat->getClickLinks();
		$data['counters'] = array();
		$counters = $postingStat->getCounters();
		foreach ($counters as $counter)
		{
			$data['counters'][$counter['CODE']] = $counter;
		}

		return $data;
	}
}

