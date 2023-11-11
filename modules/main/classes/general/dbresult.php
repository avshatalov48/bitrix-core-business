<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

use Bitrix\Main;

IncludeModuleLangFile(__FILE__);

abstract class CAllDBResult
{
	var $result;
	var $arResult;
	var $arReplacedAliases; // replace tech. aliases in Fetch to human aliases
	var $arResultAdd;
	var $bNavStart = false;
	var $bShowAll = false;
	var $NavNum, $NavPageCount, $NavPageNomer, $NavPageSize, $NavShowAll, $NavRecordCount;
	var $bFirstPrintNav = true;
	var $PAGEN, $SIZEN;
	var $SESS_SIZEN, $SESS_ALL, $SESS_PAGEN;
	var $add_anchor = "";
	var $bPostNavigation = false;
	var $bFromArray = false;
	var $bFromLimited = false;
	var $nPageWindow = 5;
	var $nSelectedCount = false;
	var $arGetNextCache = false;
	var $bDescPageNumbering = false;
	/** @var array */
	var $arUserFields = false;
	var $usedUserFields = false;
	/** @var array */
	var $SqlTraceIndex = false;
	/** @var CDatabase */
	var $DB;
	var $NavRecordCountChangeDisable = false;
	var $is_filtered = false;
	var $nStartPage = 0;
	var $nEndPage = 0;
	/** @var Main\DB\Result */
	var $resultObject = null;

	/** @param CDBResult $res */
	public function __construct($res = null)
	{
		$obj = is_object($res);
		if ($obj && is_subclass_of($res, "CAllDBResult"))
		{
			$this->result = $res->result;
			$this->nSelectedCount = $res->nSelectedCount;
			$this->arResult = $res->arResult;
			$this->arResultAdd = $res->arResultAdd;
			$this->bNavStart = $res->bNavStart;
			$this->NavPageNomer = $res->NavPageNomer;
			$this->bShowAll = $res->bShowAll;
			$this->NavNum = $res->NavNum;
			$this->NavPageCount = $res->NavPageCount;
			$this->NavPageSize = $res->NavPageSize;
			$this->NavShowAll = $res->NavShowAll;
			$this->NavRecordCount = $res->NavRecordCount;
			$this->bFirstPrintNav = $res->bFirstPrintNav;
			$this->PAGEN = $res->PAGEN;
			$this->SIZEN = $res->SIZEN;
			$this->bFromArray = $res->bFromArray;
			$this->bFromLimited = $res->bFromLimited;
			$this->nPageWindow = $res->nPageWindow;
			$this->bDescPageNumbering = $res->bDescPageNumbering;
			$this->SqlTraceIndex = $res->SqlTraceIndex;
			$this->DB = $res->DB;
			$this->arUserFields = $res->arUserFields;
		}
		elseif ($obj && $res instanceof Main\DB\ArrayResult)
		{
			$this->InitFromArray($res->getResource());
		}
		elseif ($obj && $res instanceof Main\DB\Result)
		{
			$this->result = $res->getResource();
			$this->resultObject = $res;
		}
		elseif (is_array($res))
		{
			$this->arResult = $res;
		}
		else
		{
			$this->result = $res;
		}
	}

	public function __sleep()
	{
		return [
			'result',
			'arResult',
			'arReplacedAliases',
			'arResultAdd',
			'bNavStart',
			'bShowAll',
			'NavNum',
			'NavPageCount',
			'NavPageNomer',
			'NavPageSize',
			'NavShowAll',
			'NavRecordCount',
			'bFirstPrintNav',
			'PAGEN',
			'SIZEN',
			'add_anchor',
			'bPostNavigation',
			'bFromArray',
			'bFromLimited',
			'nPageWindow',
			'nSelectedCount',
			'arGetNextCache',
			'bDescPageNumbering',
		];
	}

	/**
	 * Returns the next row of the result in a form of associated array or false on empty set.
	 *
	 * @return array | false
	 */
	function Fetch()
	{
		global $DB;

		if ($this->bNavStart || $this->bFromArray)
		{
			if (!is_array($this->arResult))
			{
				$res = false;
			}
			elseif ($res = current($this->arResult))
			{
				next($this->arResult);
			}
		}
		else
		{
			if ($this->SqlTraceIndex)
			{
				$start_time = microtime(true);
			}

			$res = $this->FetchInternal();

			if ($this->SqlTraceIndex)
			{
				/** @noinspection PhpUndefinedVariableInspection */
				$exec_time = round(microtime(true) - $start_time, 10);
				$DB->addDebugTime($this->SqlTraceIndex, $exec_time);
				$DB->timeQuery += $exec_time;
			}
		}

		return $res;
	}

	/**
	 * @return array | false
	 */
	protected function FetchInternal()
	{
		if ($this->resultObject !== null)
		{
			$res = $this->resultObject->fetch();
		}
		else
		{
			$res = $this->FetchRow();

			if (!$res)
			{
				return false;
			}

			$this->AfterFetch($res);
		}
		return $res;
	}

	abstract protected function FetchRow();

	abstract public function SelectedRowsCount();

	abstract public function AffectedRowsCount();

	abstract public function FieldsCount();

	abstract public function FieldName($iCol);

	abstract protected function GetRowsCount(): ?int;

	abstract protected function Seek(int $offset): void;

	function NavQuery($strSql, $cnt, $arNavStartParams, $bIgnoreErrors = false)
	{
		global $DB;

		if (isset($arNavStartParams["SubstitutionFunction"]))
		{
			$arNavStartParams["SubstitutionFunction"]($this, $strSql, $cnt, $arNavStartParams);
			return null;
		}

		$bDescPageNumbering = $arNavStartParams["bDescPageNumbering"] ?? false;

		$this->InitNavStartVars($arNavStartParams);
		$this->NavRecordCount = $cnt;

		if ($this->NavShowAll)
		{
			$this->NavPageSize = $this->NavRecordCount;
		}

		//calculate total pages depend on rows count. start with 1
		$this->NavPageCount = ($this->NavPageSize > 0 ? floor($this->NavRecordCount / $this->NavPageSize) : 0);
		if ($bDescPageNumbering)
		{
			$makeweight = 0;
			if ($this->NavPageSize > 0)
			{
				$makeweight = ($this->NavRecordCount % $this->NavPageSize);
			}
			if ($this->NavPageCount == 0 && $makeweight > 0)
			{
				$this->NavPageCount = 1;
			}

			//page number to display
			$this->calculatePageNumber($this->NavPageCount);

			//rows to skip
			$NavFirstRecordShow = 0;
			if ($this->NavPageNomer != $this->NavPageCount)
			{
				$NavFirstRecordShow += $makeweight;
			}

			$NavFirstRecordShow += ($this->NavPageCount - $this->NavPageNomer) * $this->NavPageSize;
			$NavLastRecordShow = $makeweight + ($this->NavPageCount - $this->NavPageNomer + 1) * $this->NavPageSize;
		}
		else
		{
			if ($this->NavPageSize > 0 && ($this->NavRecordCount % $this->NavPageSize > 0))
			{
				$this->NavPageCount++;
			}

			//calculate total pages depend on rows count. start with 1
			$this->calculatePageNumber(1, true, (bool)($arNavStartParams["checkOutOfRange"] ?? false));
			if ($this->NavPageNomer === null)
			{
				return null;
			}

			//rows to skip
			$NavFirstRecordShow = $this->NavPageSize * ($this->NavPageNomer - 1);
			$NavLastRecordShow = $this->NavPageSize * $this->NavPageNomer;
		}

		$NavAdditionalRecords = 0;
		if (is_set($arNavStartParams, "iNavAddRecords"))
		{
			$NavAdditionalRecords = $arNavStartParams["iNavAddRecords"];
		}

		if (!$this->NavShowAll)
		{
			$strSql .= " LIMIT " . ($NavLastRecordShow - $NavFirstRecordShow + $NavAdditionalRecords) . " OFFSET " . $NavFirstRecordShow;
		}

		if (is_object($this->DB))
		{
			$res_tmp = $this->DB->Query($strSql, $bIgnoreErrors);
		}
		else
		{
			$res_tmp = $DB->Query($strSql, $bIgnoreErrors);
		}

		// Return false on sql errors (if $bIgnoreErrors == true)
		if ($bIgnoreErrors && ($res_tmp === false))
		{
			return false;
		}

		$this->result = $res_tmp->result;
		$this->DB = $res_tmp->DB;

		if ($this->SqlTraceIndex)
		{
			$start_time = microtime(true);
		}

		$temp_arrray = [];
		$temp_arrray_add = [];
		$tmp_cnt = 0;

		while ($ar = $this->FetchInternal())
		{
			$tmp_cnt++;
			if (intval($NavLastRecordShow - $NavFirstRecordShow) > 0 && $tmp_cnt > ($NavLastRecordShow - $NavFirstRecordShow))
			{
				$temp_arrray_add[] = $ar;
			}
			else
			{
				$temp_arrray[] = $ar;
			}
		}

		if ($this->SqlTraceIndex)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$exec_time = round(microtime(true) - $start_time, 10);
			$DB->addDebugTime($this->SqlTraceIndex, $exec_time);
			$DB->timeQuery += $exec_time;
		}

		$this->arResult = (!empty($temp_arrray) ? $temp_arrray : false);
		$this->arResultAdd = (!empty($temp_arrray_add) ? $temp_arrray_add : false);
		$this->nSelectedCount = $cnt;
		$this->bDescPageNumbering = $bDescPageNumbering;
		$this->bFromLimited = true;

		return null;
	}

	/**
	 * @deprecated
	 */
	public function NavContinue()
	{
		if (is_array($this->arResultAdd) && !empty($this->arResultAdd))
		{
			$this->arResult = $this->arResultAdd;
			return true;
		}
		return false;
	}

	/**
	 * @deprecated
	 */
	public function IsNavPrint()
	{
		if ($this->NavRecordCount == 0 || ($this->NavPageCount == 1 && !$this->NavShowAll))
		{
			return false;
		}

		return true;
	}

	public function NavPrint($title, $show_allways = false, $StyleText = "text", $template_path = false)
	{
		echo $this->GetNavPrint($title, $show_allways, $StyleText, $template_path);
	}

	public function GetNavPrint($title, $show_allways = false, $StyleText = "text", $template_path = false, $arDeleteParam = false)
	{
		$res = '';
		$add_anchor = $this->add_anchor;

		$sBegin = GetMessage("nav_begin");
		$sEnd = GetMessage("nav_end");
		$sNext = GetMessage("nav_next");
		$sPrev = GetMessage("nav_prev");
		$sAll = GetMessage("nav_all");
		$sPaged = GetMessage("nav_paged");

		$nPageWindow = $this->nPageWindow;

		if (!$show_allways)
		{
			if ($this->NavRecordCount == 0 || ($this->NavPageCount == 1 && !$this->NavShowAll))
			{
				return '';
			}
		}

		$sUrlPath = GetPagePath();

		$arDel = ["PAGEN_" . $this->NavNum, "SIZEN_" . $this->NavNum, "SHOWALL_" . $this->NavNum, "PHPSESSID"];
		if (is_array($arDeleteParam))
		{
			$arDel = array_merge($arDel, $arDeleteParam);
		}
		$strNavQueryString = DeleteParam($arDel);
		if ($strNavQueryString <> "")
		{
			$strNavQueryString = htmlspecialcharsbx("&" . $strNavQueryString);
		}

		if ($template_path !== false && !file_exists($template_path) && file_exists($_SERVER["DOCUMENT_ROOT"] . $template_path))
		{
			$template_path = $_SERVER["DOCUMENT_ROOT"] . $template_path;
		}

		if ($this->bDescPageNumbering === true)
		{
			if ($this->NavPageNomer + floor($nPageWindow / 2) >= $this->NavPageCount)
			{
				$nStartPage = $this->NavPageCount;
			}
			else
			{
				if ($this->NavPageNomer + floor($nPageWindow / 2) >= $nPageWindow)
				{
					$nStartPage = $this->NavPageNomer + floor($nPageWindow / 2);
				}
				else
				{
					if ($this->NavPageCount >= $nPageWindow)
					{
						$nStartPage = $nPageWindow;
					}
					else
					{
						$nStartPage = $this->NavPageCount;
					}
				}
			}

			if ($nStartPage - $nPageWindow >= 0)
			{
				$nEndPage = $nStartPage - $nPageWindow + 1;
			}
			else
			{
				$nEndPage = 1;
			}
			//echo "nEndPage = $nEndPage; nStartPage = $nStartPage;";
		}
		else
		{
			if ($this->NavPageNomer > floor($nPageWindow / 2) + 1 && $this->NavPageCount > $nPageWindow)
			{
				$nStartPage = $this->NavPageNomer - floor($nPageWindow / 2);
			}
			else
			{
				$nStartPage = 1;
			}

			if ($this->NavPageNomer <= $this->NavPageCount - floor($nPageWindow / 2) && $nStartPage + $nPageWindow - 1 <= $this->NavPageCount)
			{
				$nEndPage = $nStartPage + $nPageWindow - 1;
			}
			else
			{
				$nEndPage = $this->NavPageCount;
				if ($nEndPage - $nPageWindow + 1 >= 1)
				{
					$nStartPage = $nEndPage - $nPageWindow + 1;
				}
			}
		}

		$this->nStartPage = $nStartPage;
		$this->nEndPage = $nEndPage;

		if ($template_path !== false && file_exists($template_path))
		{
			/*
						$this->bFirstPrintNav - is first tiem call
						$this->NavPageNomer - number of current page
						$this->NavPageCount - total page count
						$this->NavPageSize - page size
						$this->NavRecordCount - records count
						$this->bShowAll - show "all" link
						$this->NavShowAll - is all shown
						$this->NavNum - number of navigation
						$this->bDescPageNumbering - reverse paging

						$this->nStartPage - first page in chain
						$this->nEndPage - last page in chain

						$strNavQueryString - query string
						$sUrlPath - current url

						Url for link to the page #PAGE_NUMBER#:
						$sUrlPath.'?PAGEN_'.$this->NavNum.'='.#PAGE_NUMBER#.$strNavQueryString.'#nav_start"'.$add_anchor
			*/

			ob_start();
			include($template_path);
			$res = ob_get_contents();
			ob_end_clean();
			$this->bFirstPrintNav = false;
			return $res;
		}

		if ($this->bFirstPrintNav)
		{
			$res .= '<a name="nav_start' . $add_anchor . '"></a>';
			$this->bFirstPrintNav = false;
		}

		$res .= '<font class="' . $StyleText . '">' . $title . ' ';
		if ($this->bDescPageNumbering === true)
		{
			$makeweight = ($this->NavRecordCount % $this->NavPageSize);
			$NavFirstRecordShow = 0;
			if ($this->NavPageNomer != $this->NavPageCount)
			{
				$NavFirstRecordShow += $makeweight;
			}

			$NavFirstRecordShow += ($this->NavPageCount - $this->NavPageNomer) * $this->NavPageSize + 1;

			if ($this->NavPageCount == 1)
			{
				$NavLastRecordShow = $this->NavRecordCount;
			}
			else
			{
				$NavLastRecordShow = $makeweight + ($this->NavPageCount - $this->NavPageNomer + 1) * $this->NavPageSize;
			}

			$res .= $NavFirstRecordShow;
			$res .= ' - ' . $NavLastRecordShow;
			$res .= ' ' . GetMessage("nav_of") . ' ';
			$res .= $this->NavRecordCount;
			$res .= "\n<br>\n</font>";

			$res .= '<font class="' . $StyleText . '">';

			if ($this->NavPageNomer < $this->NavPageCount)
			{
				$res .= '<a href="' . $sUrlPath . '?PAGEN_' . $this->NavNum . '=' . $this->NavPageCount . $strNavQueryString . '#nav_start' . $add_anchor . '">' . $sBegin . '</a>&nbsp;|&nbsp;<a href="' . $sUrlPath . '?PAGEN_' . $this->NavNum . '=' . ($this->NavPageNomer + 1) . $strNavQueryString . '#nav_start' . $add_anchor . '">' . $sPrev . '</a>';
			}
			else
			{
				$res .= $sBegin . '&nbsp;|&nbsp;' . $sPrev;
			}

			$res .= '&nbsp;|&nbsp;';

			$NavRecordGroup = $nStartPage;
			while ($NavRecordGroup >= $nEndPage)
			{
				$NavRecordGroupPrint = $this->NavPageCount - $NavRecordGroup + 1;
				if ($NavRecordGroup == $this->NavPageNomer)
				{
					$res .= '<b>' . $NavRecordGroupPrint . '</b>&nbsp';
				}
				else
				{
					$res .= '<a href="' . $sUrlPath . '?PAGEN_' . $this->NavNum . '=' . $NavRecordGroup . $strNavQueryString . '#nav_start' . $add_anchor . '">' . $NavRecordGroupPrint . '</a>&nbsp;';
				}
				$NavRecordGroup--;
			}
			$res .= '|&nbsp;';
			if ($this->NavPageNomer > 1)
			{
				$res .= '<a href="' . $sUrlPath . '?PAGEN_' . $this->NavNum . '=' . ($this->NavPageNomer - 1) . $strNavQueryString . '#nav_start' . $add_anchor . '">' . $sNext . '</a>&nbsp;|&nbsp;<a href="' . $sUrlPath . '?PAGEN_' . $this->NavNum . '=1' . $strNavQueryString . '#nav_start' . $add_anchor . '">' . $sEnd . '</a>&nbsp;';
			}
			else
			{
				$res .= $sNext . '&nbsp;|&nbsp;' . $sEnd . '&nbsp;';
			}
		}
		else
		{
			$res .= ($this->NavPageNomer - 1) * $this->NavPageSize + 1;
			$res .= ' - ';
			if ($this->NavPageNomer != $this->NavPageCount)
			{
				$res .= $this->NavPageNomer * $this->NavPageSize;
			}
			else
			{
				$res .= $this->NavRecordCount;
			}
			$res .= ' ' . GetMessage("nav_of") . ' ';
			$res .= $this->NavRecordCount;
			$res .= "\n<br>\n</font>";

			$res .= '<font class="' . $StyleText . '">';

			if ($this->NavPageNomer > 1)
			{
				$res .= '<a href="' . $sUrlPath . '?PAGEN_' . $this->NavNum . '=1' . $strNavQueryString . '#nav_start' . $add_anchor . '">' . $sBegin . '</a>&nbsp;|&nbsp;<a href="' . $sUrlPath . '?PAGEN_' . $this->NavNum . '=' . ($this->NavPageNomer - 1) . $strNavQueryString . '#nav_start' . $add_anchor . '">' . $sPrev . '</a>';
			}
			else
			{
				$res .= $sBegin . '&nbsp;|&nbsp;' . $sPrev;
			}

			$res .= '&nbsp;|&nbsp;';

			$NavRecordGroup = $nStartPage;
			while ($NavRecordGroup <= $nEndPage)
			{
				if ($NavRecordGroup == $this->NavPageNomer)
				{
					$res .= '<b>' . $NavRecordGroup . '</b>&nbsp';
				}
				else
				{
					$res .= '<a href="' . $sUrlPath . '?PAGEN_' . $this->NavNum . '=' . $NavRecordGroup . $strNavQueryString . '#nav_start' . $add_anchor . '">' . $NavRecordGroup . '</a>&nbsp;';
				}
				$NavRecordGroup++;
			}
			$res .= '|&nbsp;';
			if ($this->NavPageNomer < $this->NavPageCount)
			{
				$res .= '<a href="' . $sUrlPath . '?PAGEN_' . $this->NavNum . '=' . ($this->NavPageNomer + 1) . $strNavQueryString . '#nav_start' . $add_anchor . '">' . $sNext . '</a>&nbsp;|&nbsp;<a href="' . $sUrlPath . '?PAGEN_' . $this->NavNum . '=' . $this->NavPageCount . $strNavQueryString . '#nav_start' . $add_anchor . '">' . $sEnd . '</a>&nbsp;';
			}
			else
			{
				$res .= $sNext . '&nbsp;|&nbsp;' . $sEnd . '&nbsp;';
			}
		}

		if ($this->bShowAll)
		{
			$res .= $this->NavShowAll ? '|&nbsp;<a href="' . $sUrlPath . '?SHOWALL_' . $this->NavNum . '=0' . $strNavQueryString . '#nav_start' . $add_anchor . '">' . $sPaged . '</a>&nbsp;' : '|&nbsp;<a href="' . $sUrlPath . '?SHOWALL_' . $this->NavNum . '=1' . $strNavQueryString . '#nav_start' . $add_anchor . '">' . $sAll . '</a>&nbsp;';
		}

		$res .= '</font>';
		return $res;
	}

	public function ExtractFields($strPrefix = "str_", $bDoEncode = true)
	{
		return $this->NavNext(true, $strPrefix, $bDoEncode);
	}

	public function ExtractEditFields($strPrefix = "str_")
	{
		return $this->NavNext(true, $strPrefix, true, false);
	}

	public function GetNext($bTextHtmlAuto = true, $use_tilda = true)
	{
		if ($arRes = $this->Fetch())
		{
			if (!$this->arGetNextCache)
			{
				$this->arGetNextCache = [];
				foreach ($arRes as $FName => $arFValue)
				{
					$this->arGetNextCache[$FName] = array_key_exists($FName . "_TYPE", $arRes);
				}
			}
			if ($use_tilda)
			{
				$arTilda = [];
				foreach ($arRes as $FName => $arFValue)
				{
					if (isset($this->arGetNextCache[$FName]) && $this->arGetNextCache[$FName] && $bTextHtmlAuto)
					{
						$arTilda[$FName] = FormatText($arFValue, $arRes[$FName . "_TYPE"]);
					}
					elseif (is_array($arFValue))
					{
						$arTilda[$FName] = htmlspecialcharsEx($arFValue);
					}
					elseif ($arFValue != '' && preg_match("/[;&<>\"]/", $arFValue))
					{
						$arTilda[$FName] = htmlspecialcharsEx($arFValue);
					}
					else
					{
						$arTilda[$FName] = $arFValue;
					}
					$arTilda["~" . $FName] = $arFValue;
				}
				return $arTilda;
			}
			else
			{
				foreach ($arRes as $FName => $arFValue)
				{
					if ($this->arGetNextCache[$FName] && $bTextHtmlAuto)
					{
						$arRes[$FName] = FormatText($arFValue, $arRes[$FName . "_TYPE"]);
					}
					elseif (is_array($arFValue))
					{
						$arRes[$FName] = htmlspecialcharsEx($arFValue);
					}
					elseif (preg_match("/[;&<>\"]/", $arFValue))
					{
						$arRes[$FName] = htmlspecialcharsEx($arFValue);
					}
				}
			}
		}
		return $arRes;
	}

	public static function NavStringForCache($nPageSize = 0, $bShowAll = true, $iNumPage = false)
	{
		$NavParams = CDBResult::GetNavParams($nPageSize, $bShowAll, $iNumPage);
		return "|" . ($NavParams["SHOW_ALL"] ? "" : $NavParams["PAGEN"]) . "|" . $NavParams["SHOW_ALL"] . "|";
	}

	public static function GetNavParams($nPageSize = 0, $bShowAll = true, $iNumPage = false)
	{
		/** @global CMain $APPLICATION */
		global $NavNum, $APPLICATION;

		$bDescPageNumbering = false; //it can be extracted from $nPageSize

		if (is_array($nPageSize))
		{
			$params = $nPageSize;
			if (isset($params["iNumPage"]))
			{
				$iNumPage = $params["iNumPage"];
			}
			if (isset($params["nPageSize"]))
			{
				$nPageSize = $params["nPageSize"];
			}
			if (isset($params["bDescPageNumbering"]))
			{
				$bDescPageNumbering = $params["bDescPageNumbering"];
			}
			if (isset($params["bShowAll"]))
			{
				$bShowAll = $params["bShowAll"];
			}
			if (isset($params["NavShowAll"]))
			{
				$NavShowAll = $params["NavShowAll"];
			}
			if (isset($params["sNavID"]))
			{
				$sNavID = $params["sNavID"];
			}
		}

		$nPageSize = intval($nPageSize);
		$NavNum = intval($NavNum);

		$PAGEN_NAME = "PAGEN_" . ($NavNum + 1);
		$SHOWALL_NAME = "SHOWALL_" . ($NavNum + 1);

		global ${$PAGEN_NAME}, ${$SHOWALL_NAME};

		if ($iNumPage === false)
		{
			$PAGEN = ${$PAGEN_NAME} ?? 0;
		}
		else
		{
			$PAGEN = $iNumPage;
		}

		$PAGEN = (int)$PAGEN;
		$SHOWALL = ${$SHOWALL_NAME};

		$application = Main\Application::getInstance();

		$inSession = (CPageOption::GetOptionString("main", "nav_page_in_session", "Y") == "Y") && $application->getKernelSession()->isStarted();

		if ($inSession)
		{
			$md5Path = md5($sNavID ?? $APPLICATION->GetCurPage());
			$SESS_PAGEN = $md5Path . "SESS_PAGEN_" . ($NavNum + 1);
			$SESS_ALL = $md5Path . "SESS_ALL_" . ($NavNum + 1);

			$localStorage = $application->getLocalSession('navigation');
			$session = $localStorage->getData();
		}

		if ($PAGEN <= 0)
		{
			if ($inSession && isset($session[$SESS_PAGEN]) && $session[$SESS_PAGEN] > 0)
			{
				$PAGEN = $session[$SESS_PAGEN];
			}
			elseif ($bDescPageNumbering === true)
			{
				$PAGEN = 0;
			}
			else
			{
				$PAGEN = 1;
			}
		}

		//Number of records on a page
		$SIZEN = $nPageSize;
		if ($SIZEN < 1)
		{
			$SIZEN = 10;
		}

		//Show all records
		$SHOW_ALL = ($bShowAll && (isset($SHOWALL) ? ($SHOWALL == 1) : ($inSession && isset($session[$SESS_ALL]) && $session[$SESS_ALL] == 1)));

		//$NavShowAll comes from $nPageSize array
		$res = [
			"PAGEN" => $PAGEN,
			"SIZEN" => $SIZEN,
			"SHOW_ALL" => ($NavShowAll ?? $SHOW_ALL),
		];

		if ($inSession)
		{
			$localStorage->set($SESS_PAGEN, $PAGEN);
			$localStorage->set($SESS_ALL, $SHOW_ALL);
			$res["SESS_PAGEN"] = $SESS_PAGEN;
			$res["SESS_ALL"] = $SESS_ALL;
		}

		return $res;
	}

	public function InitNavStartVars($nPageSize = 0, $bShowAll = true, $iNumPage = false)
	{
		if (is_array($nPageSize) && isset($nPageSize["bShowAll"]))
		{
			$this->bShowAll = $nPageSize["bShowAll"];
		}
		else
		{
			$this->bShowAll = $bShowAll;
		}

		$this->bNavStart = true;

		$arParams = self::GetNavParams($nPageSize, $bShowAll, $iNumPage);

		$this->PAGEN = $arParams["PAGEN"];
		$this->SIZEN = $arParams["SIZEN"];
		$this->NavShowAll = $arParams["SHOW_ALL"];
		$this->NavPageSize = $arParams["SIZEN"];
		$this->SESS_SIZEN = $arParams["SESS_SIZEN"] ?? null;
		$this->SESS_PAGEN = $arParams["SESS_PAGEN"] ?? null;
		$this->SESS_ALL = $arParams["SESS_ALL"] ?? null;

		global $NavNum;

		$NavNum++;
		$this->NavNum = $NavNum;

		if ($this->NavNum > 1)
		{
			$add_anchor = "_" . $this->NavNum;
		}
		else
		{
			$add_anchor = "";
		}

		$this->add_anchor = $add_anchor;
	}

	public function NavStart($nPageSize = 0, $bShowAll = true, $iNumPage = false)
	{
		if ($this->bFromLimited)
		{
			return;
		}

		if (is_array($nPageSize))
		{
			$this->InitNavStartVars($nPageSize);
		}
		else
		{
			$this->InitNavStartVars(intval($nPageSize), $bShowAll, $iNumPage);
		}

		if ($this->bFromArray)
		{
			$this->NavRecordCount = count($this->arResult);
			if ($this->NavRecordCount < 1)
			{
				return;
			}

			if ($this->NavShowAll)
			{
				$this->NavPageSize = $this->NavRecordCount;
			}

			$this->NavPageCount = floor($this->NavRecordCount / $this->NavPageSize);
			if ($this->NavRecordCount % $this->NavPageSize > 0)
			{
				$this->NavPageCount++;
			}

			$useSession = (CPageOption::GetOptionString("main", "nav_page_in_session", "Y") == "Y");
			$this->calculatePageNumber(1, $useSession);

			$NavFirstRecordShow = $this->NavPageSize * ($this->NavPageNomer - 1);
			$NavLastRecordShow = $this->NavPageSize * $this->NavPageNomer;

			$this->arResult = array_slice($this->arResult, $NavFirstRecordShow, $NavLastRecordShow - $NavFirstRecordShow);
		}
		else
		{
			$this->DBNavStart();
		}
	}

	protected function calculatePageNumber(int $defaultNumber = 1, bool $useSession = true, bool $checkOutOfRange = false)
	{
		$application = Main\Application::getInstance();

		$correct = false;
		if ($this->PAGEN > 0 && $this->PAGEN <= $this->NavPageCount)
		{
			$this->NavPageNomer = $this->PAGEN;
			$correct = true;
		}
		elseif ($useSession && $this->SESS_PAGEN && $application->getKernelSession()->isStarted())
		{
			$localStorage = $application->getLocalSession('navigation');
			$session = $localStorage->getData();

			if ($session[$this->SESS_PAGEN] > 0 && $session[$this->SESS_PAGEN] <= $this->NavPageCount)
			{
				$this->NavPageNomer = $session[$this->SESS_PAGEN];
				$correct = true;
			}
		}

		if (!$correct)
		{
			if ($checkOutOfRange !== true)
			{
				$this->NavPageNomer = $defaultNumber;
			}
			else
			{
				$this->NavPageNomer = null;
			}
		}
	}

	function DBNavStart()
	{
		global $DB;

		//total rows count
		if (($count = $this->GetRowsCount()) !== null)
		{
			$this->NavRecordCount = $count;
		}
		else
		{
			return;
		}

		if ($this->NavRecordCount < 1)
		{
			return;
		}

		if ($this->NavShowAll)
		{
			$this->NavPageSize = $this->NavRecordCount;
		}

		//calculate total pages depend on rows count. start with 1
		$this->NavPageCount = floor($this->NavRecordCount / $this->NavPageSize);
		if ($this->NavRecordCount % $this->NavPageSize > 0)
		{
			$this->NavPageCount++;
		}

		//page number to display. start with 1
		$this->calculatePageNumber();

		//rows to skip
		$NavFirstRecordShow = $this->NavPageSize * ($this->NavPageNomer - 1);
		$NavLastRecordShow = $this->NavPageSize * $this->NavPageNomer;

		if ($this->SqlTraceIndex)
		{
			$start_time = microtime(true);
		}

		$this->Seek($NavFirstRecordShow);

		$this->arResult = [];
		for ($i = $NavFirstRecordShow; $i < $NavLastRecordShow; $i++)
		{
			if (($res = $this->FetchInternal()))
			{
				$this->arResult[] = $res;
			}
			else
			{
				break;
			}
		}

		if ($this->SqlTraceIndex)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$exec_time = round(microtime(true) - $start_time, 10);
			$DB->addDebugTime($this->SqlTraceIndex, $exec_time);
			$DB->timeQuery += $exec_time;
		}
	}

	public function InitFromArray($arr)
	{
		if (is_array($arr))
		{
			reset($arr);
			$this->nSelectedCount = count($arr);
		}
		else
		{
			$this->nSelectedCount = false;
		}

		$this->arResult = $arr;
		$this->bFromArray = true;
	}

	public function NavNext($bSetGlobalVars = true, $strPrefix = "str_", $bDoEncode = true, $bSkipEntities = true)
	{
		$arr = $this->Fetch();
		if ($arr && $bSetGlobalVars)
		{
			foreach ($arr as $key => $val)
			{
				$varname = $strPrefix . $key;
				global $$varname;

				if ($bDoEncode && !is_array($val) && !is_object($val))
				{
					if ($bSkipEntities)
					{
						$$varname = htmlspecialcharsEx($val);
					}
					else
					{
						$$varname = htmlspecialcharsbx($val);
					}
				}
				else
				{
					$$varname = $val;
				}
			}
		}
		return $arr;
	}

	public function GetPageNavString($navigationTitle, $templateName = "", $showAlways = false, $parentComponent = null)
	{
		return $this->GetPageNavStringEx($dummy, $navigationTitle, $templateName, $showAlways, $parentComponent);
	}

	public function GetPageNavStringEx(&$navComponentObject, $navigationTitle, $templateName = "", $showAlways = false, $parentComponent = null, $componentParams = [])
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		ob_start();

		$params = array_merge(
			[
				"NAV_TITLE" => $navigationTitle,
				"NAV_RESULT" => $this,
				"SHOW_ALWAYS" => $showAlways,
			],
			$componentParams
		);

		$navComponentObject = $APPLICATION->IncludeComponent(
			"bitrix:system.pagenavigation",
			$templateName,
			$params,
			$parentComponent,
			[
				"HIDE_ICONS" => "Y",
			]
		);

		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

	public function SetUserFields($arUserFields)
	{
		$this->usedUserFields = false;

		if (is_array($arUserFields))
		{
			$this->arUserFields = $arUserFields;
		}
		else
		{
			$this->arUserFields = false;
		}
	}

	protected function AfterFetch(&$res)
	{
		global $USER_FIELD_MANAGER;

		if ($this->arUserFields)
		{
			//Cache actual user fields on first fetch
			if ($this->usedUserFields === false)
			{
				$this->usedUserFields = [];
				foreach ($this->arUserFields as $userField)
				{
					if (isset($userField['FIELD_NAME']) && array_key_exists($userField['FIELD_NAME'], $res))
					{
						$this->usedUserFields[] = $userField;
					}
				}
			}
			// We need to call OnAfterFetch for each user field
			foreach ($this->usedUserFields as $userField)
			{
				$name = $userField['FIELD_NAME'];
				if ($userField['MULTIPLE'] === 'Y')
				{
					if ($res[$name] !== null)
					{
						if (mb_substr($res[$name], 0, 1) !== 'a' && $res[$name] > 0)
						{
							$res[$name] = $USER_FIELD_MANAGER->LoadMultipleValues($userField, $res[$name]);
						}
						else
						{
							$res[$name] = unserialize($res[$name], ['allowed_classes' => false]);
						}
					}
					else
					{
						$res[$name] = false;
					}
				}
				$res[$name] = $USER_FIELD_MANAGER->OnAfterFetch($userField, $res[$name]);
			}
		}

		if ($this->arReplacedAliases)
		{
			foreach ($this->arReplacedAliases as $tech => $human)
			{
				$res[$human] = $res[$tech];
				unset($res[$tech]);
			}
		}
	}
}
