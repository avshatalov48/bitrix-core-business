<?php
namespace Bitrix\Sale\TradingPlatform\Vk;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);


/**
 * Class Journal
 * Work with statistic of exports: time of start end finish of export, count of processed items
 *
 * @package Bitrix\Sale\TradingPlatform\Vk
 */
class Journal
{
	private $exportId;
	private $type;
	
	
	/**
	 * Journal constructor.
	 * @param $exportId - int, ID of export profile
	 * @param $type - string, type of current journal. May be ALBUMS or PRODUCTS.
	 */
	public function __construct($exportId, $type)
	{
		$this->exportId = $exportId;
		$this->type = $type;
	}
	
	
	public static function getCurrentProcess($exportId)
	{
		$resProfiles = ExportProfileTable::getList(array(
				"filter" => array('=ID' => $exportId),
				"select" => array("PROCESS"),
			)
		);
		$profile = $resProfiles->fetch();

//		if PROCESS empty of set STOP flag - return false
		if (!empty($profile['PROCESS']) && !(array_key_exists('STOP', $profile['PROCESS']) && $profile['PROCESS']['STOP']))
			return $profile['PROCESS'];
		else
			return false;
	}
	
	
	public static function getProgressMessage($exportId, $type)
	{
		$type = Feed\Manager::prepareType($type);
		
		$journal = self::getStatistic($type, $exportId);
		$count = $journal[$type]['COUNT'] ? $journal[$type]['COUNT'] : 0;
		
		$detailsMsg = '<p>' . date('G:i - ') . Loc::getMessage('VK_JOURNAL_PROCESSING_COUNT', array("#C1" => $count)) . '</p>';
		$detailsMsg .= '<p><i>' . Loc::getMessage('VK_JOURNAL_RUNNING_NOTIFY_1') . '<br>' . Loc::getMessage('VK_JOURNAL_RUNNING_NOTIFY_2') . '</i></p>';
		ob_start();
		\CAdminMessage::ShowMessage(array(
			"MESSAGE" => Loc::getMessage("VK_JOURNAL_NOW_EXPORT_" . $type),
			"DETAILS" =>
				'<p>' . $detailsMsg . '</p>' .
				'<input
					type="button"
					value="' . Loc::getMessage("VK_JOURNAL_BUTTON_STOP_PROCESS") . '"
					onclick="if(confirm(\'' . Loc::getMessage("VK_JOURNAL_BUTTON_STOP_PROCESS_ALERT") . '\'))
						{BX.Sale.VkAdmin.stopProcess(' . $exportId . ');}">',
			"HTML" => true,
			"TYPE" => "PROGRESS",
		));
		$res = ob_get_clean();
		
		return $res;
	}
	
	public static function getProgressFinishMessage($ok = true)
	{
		$msg = $ok ?
			Loc::getMessage("VK_JOURNAL_EXPORT_FININSH") :
			Loc::getMessage("VK_JOURNAL_EXPORT_ABORT");
		ob_start();
		\CAdminMessage::ShowMessage(array(
				"MESSAGE" => $msg,
				"DETAILS" => '',
				"TYPE" => "OK")
		);
		$res = ob_get_clean();
		
		return $res;
	}
	
	
	public static function getTooMuchTimeExportMessage()
	{
		ob_start();
		\CAdminMessage::ShowMessage(array
			(
				"MESSAGE" => Loc::getMessage("VK_JOURNAL_TOO_MUCH_TIMES_NOTIFY_1"),
				"DETAILS" => Loc::getMessage("VK_JOURNAL_TOO_MUCH_TIMES_NOTIFY_2"),
				"TYPE" => "ERROR",
			)
		);
		$res = ob_get_clean();
		
		return $res;
	}
	
	
	public static function getCriticalErrorsMessage($exportId, $txt)
	{
		ob_start();
		\CAdminMessage::ShowMessage(array("MESSAGE" => $txt, "HTML" => true, "TYPE" => "ERROR", "DETAILS" => ''));
		$res = ob_get_clean();
		
		return $res;
	}
	
	
	public static function saveProcessParams($exportId, $type = false, $position = false, $execCount = false)
	{
//		save only if not set STOP flag
		if (!self::checkStopProcessFlag($exportId))
		{
			$exportId = \EscapePHPString($exportId);
//			if set data - update, if not - clear process
			$data = array();
			if ($type !== false && $position !== false && $execCount !== false)
				$data = array(
					'TYPE' => $type,
					'EXEC_COUNT' => $execCount,
					'START_POSITION' => $position,
				);
			$resUpdate = ExportProfileTable::update($exportId, array("PROCESS" => $data));
			
			return $resUpdate->isSuccess();
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Remove process STOP flag
	 * @param $exportId
	 * @return bool
	 */
	public static function clearStopProcessParams($exportId)
	{
		$resUpdate = ExportProfileTable::update($exportId, array("PROCESS" => NULL));
		
		return $resUpdate->isSuccess();
	}
	
	/**
	 * set flag STOP in params - preserve repeated process run
	 * @param $exportId
	 * @return bool
	 */
	public static function stopProcessParams($exportId)
	{
		$exportId = \EscapePHPString($exportId);
		$data = array('STOP' => true);
		$resUpdate = ExportProfileTable::update($exportId, array("PROCESS" => $data));
		
		return $resUpdate->isSuccess();
	}
	
	
	public static function checkStopProcessFlag($exportId)
	{
		$exportId = \EscapePHPString($exportId);
		
		$resProfiles = ExportProfileTable::getList(array(
				"filter" => array('=ID' => $exportId),
				"select" => array("PROCESS"),
			)
		);
		$profile = $resProfiles->fetch();
		
		return
			isset($profile['PROCESS']) &&
			(array_key_exists('STOP', $profile['PROCESS']) && $profile['PROCESS']['STOP']);
	}
	
	
	/**
	 * Return journal for current export ID
	 *
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getJournal()
	{
		$resJournal = ExportProfileTable::getList(array(
				"filter" => array('=ID' => $this->exportId),
				"select" => array("JOURNAL"),
			)
		);
		$journal = $resJournal->fetch();
		
		return $journal["JOURNAL"];
	}
	
	
	/**
	 * Increase number of processed items for current type of export.
	 *
	 * @param $count
	 * @return bool
	 * @throws \Exception
	 */
	public function addItemsCount($count)
	{
		$journal = $this->getJournal();

//		existing journal - increment count
		if (isset($journal[$this->type]["START"]) && !isset($journal[$this->type]["END"]))
		{
			$journal[$this->type]["COUNT"] = intval($journal[$this->type]["COUNT"]) + intval($count);
		}

//		if not set START or set both START and END - it is new item, init them
		else
		{
			$journal[$this->type] = array(
				"START" => time(),
				"COUNT" => intval($count),
			);
			unset($journal[$this->type]["END"], $journal[$this->type]["ABORT"]);
		}
		
		$resUpdate = ExportProfileTable::update($this->exportId, array("JOURNAL" => $journal));
		
		return $resUpdate->isSuccess();
	}
	
	
	/**
	 * Init log for current export type and set starting time.
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function start()
	{
//		remove old values and init new journal log for current type
		$journal = $this->getJournal();
		$journal[$this->type] = array('START' => time());
		
		$resUpdate = ExportProfileTable::update($this->exportId, array("JOURNAL" => $journal));
		
		return $resUpdate->isSuccess();
	}
	
	/**
	 * Close log for current type by set finish time
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function end()
	{
//		finish current type journal
		$journal = $this->getJournal();
		$journal[$this->type]["END"] = time();
		
		$resUpdate = ExportProfileTable::update($this->exportId, array("JOURNAL" => $journal));
		
		return $resUpdate->isSuccess();
	}
	
	
	private static function getStatistic($type, $exportId)
	{
		$resJournal = ExportProfileTable::getList(array(
				"filter" => array('=ID' => $exportId),
				"select" => array("JOURNAL"),
			)
		);
		$journal = $resJournal->fetch();
		if (!$journal)
			return false;
		else
			$journal = $journal["JOURNAL"];
		
		//check if process stopped, but journal not ending
		return self::getCheckedEndingJournal($type, $exportId, $journal);
	}
	
	
	/**
	 * Get formatted string of statistic to selected type and export ID
	 *
	 * @param $type
	 * @param $exportId
	 * @return bool|string
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getStatisticText($type, $exportId)
	{
		$journal = self::getStatistic($type, $exportId);
		
		$result = "<h4>" . Loc::getMessage("VK_JOURNAL_" . $type) . "</h4>";

//		never running
		if (!isset($journal[$type]["START"]) && !isset($journal[$type]["END"]))
		{
			$result .= "<p>" . Loc::getMessage("VK_JOURNAL_NEVER") . "</p>";
		}

//		export was finished
		elseif (isset($journal[$type]["END"]))
		{
			$result .= "<p>";
			$result .= Loc::getMessage("VK_JOURNAL_LAST_EXPORT", array(
				'#D1' => date('d.m.y', $journal[$type]["END"]),
				'#D2' => date('G:i', $journal[$type]["END"]),
			));
			$result .= isset($journal[$type]["ABORT"]) && $journal[$type]["ABORT"] ?
				' ' . Loc::getMessage("VK_JOURNAL_LAST_EXPORT_ABORTED") : '';
			$result .= "</p>";

//				add count items
			if ($journal[$type]["COUNT"])
				$result .= "<p>" .
					Loc::getMessage("VK_JOURNAL_COUNT_WAS", array(
						'#C1' => $journal[$type]["COUNT"],
					)) .
					"</p>";
		}

//		running now
		elseif (isset($journal[$type]["START"]))
		{
			$result .= "<p>" . Loc::getMessage("VK_JOURNAL_EXPORT_NOW") . "</p>";

//			add count items
			if ($journal[$type]["COUNT"])
				$result .=
					"<p>" .
					Loc::getMessage("VK_JOURNAL_COUNT_NOW", array(
						'#C1' => $journal[$type]["COUNT"],
					)) .
					"</p>";
		}
		
		return $result;
	}
	
	
	/**
	 * Check if export was aborted by user or if journal still not start process
	 * Return journal with modify values
	 *
	 * @param $type
	 * @param $exportId
	 * @param $journal
	 * @return mixed
	 * @throws \Exception
	 */
	private static function getCheckedEndingJournal($type, $exportId, $journal)
	{
		$runningProcess = self::getCurrentProcess($exportId);
		$runningProcessType = Feed\Manager::prepareType($runningProcess['TYPE']);
		
		if ($runningProcess && $runningProcessType == $type)
		{
//			if process was running, but not eyt cleared journal
			if (isset($journal[$type]["START"]) && isset($journal[$type]["END"]))
			{
				unset($journal[$type]["END"]);
				unset($journal[$type]["ABORT"]);
				$journal[$type]["COUNT"] = 0;
				
				ExportProfileTable::update($exportId, array("JOURNAL" => $journal));
			}
		}
		
		else
		{
//			if journal not closed, but process not running, it means that process was stopped manually - close journal
			if (isset($journal[$type]["START"]) && !isset($journal[$type]["END"]))
			{
				$journal[$type]["ABORT"] = true;
				$journal[$type]["END"] = $journal[$type]["START"];
				
				ExportProfileTable::update($exportId, array("JOURNAL" => $journal));
			}
		}
		
		return $journal;
	}
}