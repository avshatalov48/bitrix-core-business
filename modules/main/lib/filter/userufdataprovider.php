<?php
namespace Bitrix\Main\Filter;

class UserUFDataProvider extends EntityUFDataProvider
{
	protected $ufReserved = [
		'UF_DEPARTMENT',
		'UF_USER_CRM_ENTITY',
		'UF_PUBLIC',
		'UF_TIMEMAN',
		'UF_TM_REPORT_REQ',
		'UF_TM_FREE',
		'UF_REPORT_PERIOD',
		'UF_1C',
		'UF_TM_ALLOWED_DELTA',
		'UF_SETTING_DATE',
		'UF_LAST_REPORT_DATE',
		'UF_DELAY_TIME',
		'UF_TM_REPORT_DATE',
		'UF_TM_DAY',
		'UF_TM_TIME',
		'UF_TM_REPORT_TPL',
		'UF_TM_MIN_DURATION',
		'UF_TM_MIN_FINISH',
		'UF_TM_MAX_START',
		'UF_CONNECTOR_MD5',
		'UF_WORK_BINDING',
		'UF_IM_SEARCH',
		'UF_BXDAVEX_CALSYNC',
		'UF_BXDAVEX_MLSYNC',
		'UF_UNREAD_MAIL_COUNT',
		'UF_BXDAVEX_CNTSYNC',
		'UF_BXDAVEX_MAILBOX',
		'UF_VI_PASSWORD',
		'UF_VI_BACKPHONE',
		'UF_VI_PHONE',
		'UF_VI_PHONE_PASSWORD'
	];

	protected function postFilterFields(array $fields)
	{
		foreach ($this->getUfReserved() as $ufId)
		{
			if (isset($fields[$ufId]))
			{
				unset($fields[$ufId]);
			}
		}

		$whiteList = $this->getSettings()->getWhiteList();

		if (!empty($whiteList))
		{
			foreach($fields as $ufId => $field)
			{
				if (!in_array($ufId, $whiteList))
				{
					unset($fields[$ufId]);
				}
			}
		}

		return $fields;
	}

}