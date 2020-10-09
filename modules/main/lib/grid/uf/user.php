<?

namespace Bitrix\Main\Grid\Uf;

use Bitrix\Main\UserTable;
use Bitrix\Main\Loader;

class User extends Base
{
	protected $userFieldsReserved = [
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

	public function __construct()
	{
		parent::__construct(UserTable::getUfId());
	}

	public function addUFHeaders(&$gridHeaders, $import = false)
	{
		$userUFList = $this->getEntityUFList();

		foreach($userUFList as $FIELD_NAME => $uf)
		{
			if(
				!isset($uf['SHOW_IN_LIST'])
				|| $uf['SHOW_IN_LIST'] !== 'Y'
				|| !isset($uf['EDIT_IN_LIST'])
				|| $uf['EDIT_IN_LIST'] !== 'Y'
			)
			{
				continue;
			}

			$type = $uf['USER_TYPE']['BASE_TYPE'];

			if (in_array($uf['USER_TYPE']['USER_TYPE_ID'], ['enumeration', 'iblock_section', 'iblock_element']))
			{
				$type = 'list';
			}
			else if ($uf['USER_TYPE']['USER_TYPE_ID'] == 'boolean')
			{
				$type = 'list';

				//Default value must be placed at first position.
				$defaultValue = (
					isset($uf['SETTINGS']['DEFAULT_VALUE'])
						? (int)$uf['SETTINGS']['DEFAULT_VALUE']
						: 0
				);
			}
			elseif ($uf['USER_TYPE']['BASE_TYPE'] == 'datetime')
			{
				$type = 'date';
			}
			elseif (
				$uf['USER_TYPE']['USER_TYPE_ID'] == 'crm_status'
				&& Loader::includeModule('crm')
			)
			{
				$type = 'list';
			}
			elseif(mb_substr($uf['USER_TYPE']['USER_TYPE_ID'], 0, 5) === 'rest_')
			{
				// skip REST type fields here
				continue;
			}

			if($type === 'string')
			{
				$type = 'text';
			}
			elseif(
				$type === 'int'
				|| $type === 'double'
			)
			{
				//HACK: \CMainUIGrid::prepareEditable does not recognize 'number' type
				$type = 'int';
			}

			$gridHeaders[$FIELD_NAME] = array(
				'id' => $FIELD_NAME,
				'name' => htmlspecialcharsbx($uf['LIST_COLUMN_LABEL'] <> '' ? $uf['LIST_COLUMN_LABEL'] : $FIELD_NAME),
				'sort' => $uf['MULTIPLE'] == 'N' ? $FIELD_NAME : false,
				'default' => false,
				'editable' => false,
				'type' => $type
			);

			if ($import)
			{
				$gridHeaders[$FIELD_NAME]['mandatory'] = (
					$uf['MANDATORY'] === 'Y'
						? 'Y'
						: 'N'
				);
			}
		}

	}

}