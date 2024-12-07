<?php
IncludeModuleLangFile(__FILE__);

class CRubric
{
	public $LAST_ERROR = '';

	//Get list
	public static function GetList($aSort=[], $aFilter=[])
	{
		global $DB;

		$arFilter = [];
		foreach ($aFilter as $key => $val)
		{
			if ($val == '')
			{
				continue;
			}

			$key = mb_strtoupper($key);
			switch ($key)
			{
				case 'ID':
				case 'ACTIVE':
				case 'VISIBLE':
				case 'LID':
				case 'AUTO':
				case 'CODE':
					$arFilter[] = 'R.' . $key . " = '" . $DB->ForSql($val) . "'";
					break;
				case 'NAME':
					$arFilter[] = "R.NAME like '%" . $DB->ForSql($val) . "%'";
					break;
			}
		}

		$arOrder = [];
		foreach ($aSort as $key => $val)
		{
			$ord = (mb_strtoupper($val) !== 'ASC' ? 'DESC' : 'ASC');
			$key = mb_strtoupper($key);

			switch ($key)
			{
				case 'ID':
				case 'NAME':
				case 'SORT':
				case 'LAST_EXECUTED':
				case 'VISIBLE':
				case 'LID':
				case 'AUTO':
				case 'CODE':
					$arOrder[] = 'R.' . $key . ' ' . $ord;
					break;
				case 'ACT':
					$arOrder[] = 'R.ACTIVE ' . $ord;
					break;
			}
		}
		if (count($arOrder) == 0)
		{
			$arOrder[] = 'R.ID DESC';
		}
		$sOrder = "\nORDER BY " . implode(', ',$arOrder);

		if (count($arFilter) == 0)
		{
			$sFilter = '';
		}
		else
		{
			$sFilter = "\nWHERE " . implode("\nAND ", $arFilter);
		}

		$strSql = '
			SELECT
				R.ID
				,R.NAME
				,R.CODE
				,R.SORT
				,R.LID
				,R.ACTIVE
				,R.DESCRIPTION
				,R.AUTO
				,R.VISIBLE
				,' . $DB->DateToCharFunction('R.LAST_EXECUTED', 'FULL') . ' AS LAST_EXECUTED
				,R.FROM_FIELD
				,R.DAYS_OF_MONTH
				,R.DAYS_OF_WEEK
				,R.TIMES_OF_DAY
				,R.TEMPLATE
			FROM
				b_list_rubric R
			' . $sFilter . $sOrder;

		return $DB->Query($strSql);
	}

	//Get by ID
	public static function GetByID($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql = '
			SELECT
				R.*
				,' . $DB->DateToCharFunction('R.LAST_EXECUTED', 'FULL') . ' AS LAST_EXECUTED
			FROM b_list_rubric R
			WHERE R.ID = ' . $ID . '
		';

		return $DB->Query($strSql);
	}

	//Count of subscribers
	public static function GetSubscriptionCount($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql = "
			SELECT COUNT('x') AS CNT
			FROM b_subscription_rubric SR
			WHERE SR.LIST_RUBRIC_ID = " . $ID . '
		';

		$res = $DB->Query($strSql);
		if ($res_arr = $res->Fetch())
		{
			return intval($res_arr['CNT']);
		}
		else
		{
			return 0;
		}
	}

	// delete
	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);

		$DB->StartTransaction();

		$res = $DB->Query('DELETE FROM b_subscription_rubric WHERE LIST_RUBRIC_ID=' . $ID);
		if ($res)
		{
			$res = $DB->Query('DELETE FROM b_posting_rubric WHERE LIST_RUBRIC_ID=' . $ID);
		}
		if ($res)
		{
			$res = $DB->Query('DELETE FROM b_list_rubric WHERE ID=' . $ID);
		}

		if ($res)
		{
			$DB->Commit();
		}
		else
		{
			$DB->Rollback();
		}

		return $res;
	}

	public static function OnBeforeLangDelete($lang)
	{
		global $DB, $APPLICATION;
		$rs = $DB->Query("SELECT count(*) C FROM b_list_rubric WHERE LID='" . $DB->ForSql($lang, 2) . "'");
		$ar = $rs->Fetch();
		if ($ar['C'] > 0)
		{
			$APPLICATION->ThrowException(GetMessage('class_rub_err_exists', ['#COUNT#' => $ar['C']]));
			return false;
		}
		else
		{
			return true;
		}
	}

	//check fields before writing
	public function CheckFields($arFields)
	{
		global $DB;
		$this->LAST_ERROR = '';
		$aMsg = [];

		if ($arFields['NAME'] == '')
		{
			$aMsg[] = ['id' => 'NAME', 'text' => GetMessage('class_rub_err_name')];
		}
		if ($arFields['LID'] <> '')
		{
			$r = CLang::GetByID($arFields['LID']);
			if (!$r->Fetch())
			{
				$aMsg[] = ['id' => 'LID', 'text' => GetMessage('class_rub_err_lang')];
			}
		}
		else
		{
			$aMsg[] = ['id' => 'LID', 'text' => GetMessage('class_rub_err_lang2')];
		}
		if ($arFields['DAYS_OF_MONTH'] <> '')
		{
			$arDoM = explode(',', $arFields['DAYS_OF_MONTH']);
			$arFound = [];
			foreach ($arDoM as $strDoM)
			{
				if (preg_match('/^(\d{1,2})$/', trim($strDoM), $arFound))
				{
					if (intval($arFound[1]) < 1 || intval($arFound[1]) > 31)
					{
						$aMsg[] = ['id' => 'DAYS_OF_MONTH', 'text' => GetMessage('class_rub_err_dom')];
						break;
					}
				}
				elseif (preg_match('/^(\d{1,2})-(\d{1,2})$/', trim($strDoM), $arFound))
				{
					if (intval($arFound[1]) < 1 || intval($arFound[1]) > 31 || intval($arFound[2]) < 1 || intval($arFound[2]) > 31 || intval($arFound[1]) >= intval($arFound[2]))
					{
						$aMsg[] = ['id' => 'DAYS_OF_MONTH', 'text' => GetMessage('class_rub_err_dom')];
						break;
					}
				}
				else
				{
					$aMsg[] = ['id' => 'DAYS_OF_MONTH', 'text' => GetMessage('class_rub_err_dom2')];
					break;
				}
			}
		}
		if ($arFields['DAYS_OF_WEEK'] <> '')
		{
			$arDoW = explode(',', $arFields['DAYS_OF_WEEK']);
			$arFound = [];
			foreach ($arDoW as $strDoW)
			{
				if (preg_match('/^(\d)$/', trim($strDoW), $arFound))
				{
					if (intval($arFound[1]) < 1 || intval($arFound[1]) > 7)
					{
						$aMsg[] = ['id' => 'DAYS_OF_WEEK', 'text' => GetMessage('class_rub_err_dow')];
						break;
					}
				}
				else
				{
					$aMsg[] = ['id' => 'DAYS_OF_WEEK', 'text' => GetMessage('class_rub_err_dow2')];
					break;
				}
			}
		}
		if ($arFields['TIMES_OF_DAY'] <> '')
		{
			$arToD = explode(',', $arFields['TIMES_OF_DAY']);
			$arFound = [];
			foreach ($arToD as $strToD)
			{
				if (preg_match('/^(\d{1,2}):(\d{1,2})$/', trim($strToD), $arFound))
				{
					if (intval($arFound[1]) > 23 || intval($arFound[2]) > 59)
					{
						$aMsg[] = ['id' => 'TIMES_OF_DAY', 'text' => GetMessage('class_rub_err_tod')];
						break;
					}
				}
				else
				{
					$aMsg[] = ['id' => 'TIMES_OF_DAY', 'text' => GetMessage('class_rub_err_tod2')];
					break;
				}
			}
		}
		if ($arFields['TEMPLATE'] <> '' && !CPostingTemplate::IsExists($arFields['TEMPLATE']))
		{
			$aMsg[] = ['id' => 'TEMPLATE', 'text' => GetMessage('class_rub_err_wrong_templ')];
		}
		if ($arFields['AUTO'] == 'Y')
		{
			if ((mb_strlen($arFields['FROM_FIELD']) < 3) || !check_email($arFields['FROM_FIELD']))
			{
				$aMsg[] = ['id' => 'FROM_FIELD', 'text' => GetMessage('class_rub_err_email')];
			}
			if (mb_strlen($arFields['DAYS_OF_MONTH']) + mb_strlen($arFields['DAYS_OF_WEEK']) <= 0)
			{
				$aMsg[] = ['id' => 'DAYS_OF_MONTH', 'text' => GetMessage('class_rub_err_days_missing')];
			}
			if ($arFields['TIMES_OF_DAY'] == '')
			{
				$aMsg[] = ['id' => 'TIMES_OF_DAY', 'text' => GetMessage('class_rub_err_times_missing')];
			}
			if ($arFields['TEMPLATE'] == '')
			{
				$aMsg[] = ['id' => 'TEMPLATE', 'text' => GetMessage('class_rub_err_templ_missing')];
			}
			if (is_set($arFields, 'FROM_FIELD') && $arFields['FROM_FIELD'] == '')
			{
				$aMsg[] = ['id' => 'FROM_FIELD', 'text' => GetMessage('class_rub_err_from')];
			}
			if ($arFields['LAST_EXECUTED'] == '')
			{
				$aMsg[] = ['id' => 'LAST_EXECUTED', 'text' => GetMessage('class_rub_err_le_missing')];
			}
			elseif (is_set($arFields, 'LAST_EXECUTED') && $arFields['LAST_EXECUTED'] !== false && $DB->IsDate($arFields['LAST_EXECUTED'], false, false, 'FULL') !== true)
			{
				$aMsg[] = ['id' => 'LAST_EXECUTED', 'text' => GetMessage('class_rub_err_le_wrong')];
			}
		}

		if (!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS['APPLICATION']->ThrowException($e);
			$this->LAST_ERROR = $e->GetString();
			return false;
		}
		return true;
	}

	//add
	public function Add($arFields)
	{
		global $DB;

		if (!$this->CheckFields($arFields))
		{
			return false;
		}

		$ID = $DB->Add('b_list_rubric', $arFields);

		if ($ID > 0 && $arFields['ACTIVE'] == 'Y' && $arFields['AUTO'] == 'Y' && COption::GetOptionString('subscribe', 'subscribe_template_method') !== 'cron')
		{
				CAgent::AddAgent('CPostingTemplate::Execute();', 'subscribe', 'N', COption::GetOptionString('subscribe', 'subscribe_template_interval'));
		}
		return $ID;
	}

	//update
	public function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);

		if (!$this->CheckFields($arFields))
		{
			return false;
		}

		$strUpdate = $DB->PrepareUpdate('b_list_rubric', $arFields);
		if ($strUpdate != '')
		{
			$strSql = 'UPDATE b_list_rubric SET ' . $strUpdate . ' WHERE ID=' . $ID;
			$DB->Query($strSql);
			if ($ID > 0 && $arFields['ACTIVE'] == 'Y' && $arFields['AUTO'] == 'Y' && COption::GetOptionString('subscribe', 'subscribe_template_method') !== 'cron')
			{
					CAgent::AddAgent('CPostingTemplate::Execute();', 'subscribe', 'N', COption::GetOptionString('subscribe', 'subscribe_template_interval'));
			}
		}
		return true;
	}
}
