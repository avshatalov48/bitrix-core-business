<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

final class SocialnetworkGroupCreate extends \CBitrixComponent
{
	public function onPrepareComponentParams($params)
	{
		if (
			isset($params["LID"])
			&& !empty($params["LID"])
		)
		{
			$res = \Bitrix\Main\SiteTable::getList([
				'filter' => [
					'=LID' => $params["LID"],
					'=ACTIVE' => 'Y'
				],
				'select' => ['LID']
			]);
			if ($siteFields = $res->fetch())
			{
				$this->setSiteId($params["LID"]);
			}
		}

		return $params;
	}

	public function executeComponent()
	{
		return $this->__includeComponent();
	}
}