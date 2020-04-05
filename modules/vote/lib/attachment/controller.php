<?php

namespace Bitrix\Vote\Attachment;

use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Vote\EventTable;
use Bitrix\Vote\User;


Loc::loadMessages(__FILE__);

class Controller extends \Bitrix\Vote\Base\Controller
{
	/**
	 * @var $attach \Bitrix\Vote\Attach
	 */
	var $attach;

	protected function listActions()
	{
		return array(
			"vote" => array(
				"need_auth" => false,
				"method" => array("POST")
			),
			"getBallot" => array(
				"method" => array("POST", "GET")
			),
			"stop" => array(
				"method" => array("POST", "GET")
			),
			"resume" => array(
				"method" => array("POST", "GET")
			),
			"getvoted" => array(
				"method" => array("POST", "GET")
			),
			"getmobilevoted" => array(
				"method" => array("POST", "GET"),
				"check_sessid" => false
			),
			"exportXls" => array(
				"method" => array("POST", "GET")
			)
		);
	}

	protected function init()
	{
		if ($this->request->getQuery("attachId"))
			$this->attach = Manager::loadFromAttachId($this->request->getQuery("attachId"));
		else if ($this->request->getQuery("voteId"))
			$this->attach = Manager::loadFromVoteId(array(
				"MODULE_ID" => "vote",
				"ENTITY_TYPE" => "VOTE",
				"ENTITY_ID" => $this->request->getQuery("voteId")
			), $this->request->getQuery("voteId"));
		else
			throw new ArgumentNullException("attach ID");

		AddEventHandler("vote", "onVoteReset", array(&$this, "clearCache"));
		AddEventHandler("vote", "onAfterVoting", array(&$this, "clearCache"));
	}


	protected function processActionVote()
	{
		if ($this->checkRequiredGetParams(array("attachId")))
		{
			if (!$this->attach->canRead($this->getUser()->getId()))
				throw new AccessDeniedException();

			$request = $this->request->getPostList()->toArray();
			if ($this->isAjaxRequest())
				\CUtil::decodeURIComponent($request);

			//TODO decide what should we do with captcha in attaches
			if ($this->attach->voteFor($request))
			{
				if (\Bitrix\Main\Loader::includeModule("pull"))
				{
					$result = array();
					foreach ($this->attach["QUESTIONS"] as $question)
					{
						$result[$question["ID"]] = array(
							"ANSWERS" => array()
						);
						foreach ($question["ANSWERS"] as $answer)
						{
							$result[$question["ID"]]["ANSWERS"][$answer["ID"]] = array(
								"PERCENT" => $answer["PERCENT"],
								"USERS" => [],
								"COUNTER" => $answer["COUNTER"]
							);
						}
					}
					\CPullWatch::AddToStack("VOTE_".$this->attach["VOTE_ID"],
						Array(
							"module_id" => "vote",
							"command" => "voting",
							"params" => Array(
								"VOTE_ID" => $this->attach["VOTE_ID"],
								"AUTHOR_ID" => $this->getUser()->getId(),
								"COUNTER" => $this->attach["COUNTER"],
								"QUESTIONS" => $result
							)
						)
					);
				}

				$this->sendJsonSuccessResponse(array(
					"action" => $this->getAction(),
					"data" => array(
						"attach" => array(
							"ID" => $this->attach["ID"],
							"VOTE_ID" => $this->attach["VOTE_ID"],
							"COUNTER" => $this->attach["COUNTER"],
							"QUESTIONS" => $this->attach["QUESTIONS"]
						)
					)
				));
			}
			elseif (($errors = $this->attach->getErrors()) && !empty($errors))
				$this->errorCollection->add($errors);
			else
				throw new ArgumentException(GetMessage("V_ERROR_4501"));
		}
	}

	protected function processActionGetBallot()
	{
		$attach = $this->attach;
		$eventId = 0;
		$userId = 0;
		if ($this->getUser()->isAdmin() && $this->request->getQuery("eventId") > 0)
			$eventId = $this->request->getQuery("eventId");
		else
		{
			$userId = $this->getUser()->getId();
			if ($attach->canRead($userId) && ($result = $attach->canRevote($userId)) && $result->isSuccess())
			{
				$event = reset($result->getData());
				$eventId = $event["ID"];
			}
		}
		$stat = array();
		$extras = array();
		if ($eventId > 0)
		{
			$dbRes = EventTable::getList(array(
				"select" => array(
					"V_" => "*",
					"Q_" => "QUESTION.*",
					"A_" => "QUESTION.ANSWER.*",
					"U_" => "USER.USER.*",
				),
				"filter" => array(
					"ID" => $eventId,
					"VOTE_ID" => $attach["VOTE_ID"]
				)
			));
			$questions = $attach["QUESTIONS"];
			if ($dbRes && ($res = $dbRes->fetch()))
			{
				$userId = $res["U_ID"];
				$extras = array(
					"VISIBLE" => $res["V_VISIBLE"],
					"VALID" => $res["V_VALID"]
				);
				do
				{
					if (!array_key_exists($res["Q_QUESTION_ID"], $questions) ||
						!array_key_exists($res["A_ANSWER_ID"], $questions[$res["Q_QUESTION_ID"]]["ANSWERS"]))
						continue;
					if (!array_key_exists($res["Q_QUESTION_ID"], $stat))
						$stat[$res["Q_QUESTION_ID"]] = array();

					$stat[$res["Q_QUESTION_ID"]][$res["A_ANSWER_ID"]] = array(
						"EVENT_ID" => $res["A_ID"],
						"EVENT_QUESTION_ID" => $res["Q_ID"],
						"ANSWER_ID" => $res["ANSWER_ID"],
						"ID" => $res["A_ID"], // delete this
						"MESSAGE" => $res["A_MESSAGE"]
					);
				} while ($res = $dbRes->fetch());
			}
		}
		$this->sendJsonSuccessResponse(array(
			"action" => $this->getAction(),
			"data" => array(
				"attach" => array(
					"ID" => $attach["ID"],
					"VOTE_ID" => $attach["VOTE_ID"],
					"FIELD_NAME" => $attach["FIELD_NAME"],
					"QUESTIONS" => $attach["QUESTIONS"]
				),
				"event" => array(
					"id" => $eventId,
					"userId" => $userId,
					"ballot" => $stat,
					"extras" => $extras
				)
			)
		));
	}

	protected function processActionStop()
	{
		$attach = $this->attach;
		$userId = $this->getUser()->getId();
		if (!$attach->canEdit($userId))
			throw new AccessDeniedException();
		$attach->stop();
		$this->sendJsonSuccessResponse(array(
			"action" => $this->getAction(),
			"data" => array(
				"attach" => array(
					"ID" => $this->attach["ID"],
					"VOTE_ID" => $this->attach["VOTE_ID"],
					"COUNTER" => $this->attach["COUNTER"],
					"QUESTIONS" => $this->attach["QUESTIONS"]
				)
			)
		));
	}

	protected function processActionResume()
	{
		$attach = $this->attach;
		$userId = $this->getUser()->getId();
		if (!$attach->canEdit($userId))
			throw new AccessDeniedException();
		$attach->resume();
		$this->sendJsonSuccessResponse(array(
			"action" => $this->getAction(),
			"data" => array(
				"attach" => array(
					"ID" => $this->attach["ID"],
					"VOTE_ID" => $this->attach["VOTE_ID"],
					"COUNTER" => $this->attach["COUNTER"],
					"QUESTIONS" => $this->attach["QUESTIONS"]
				)
			)
		));
	}

	/**
	 * Returns array of users voted for this poll.
	 * @param array $cacheParams Array(voteId => , answerId => ).
	 * @param array $pageParams Array(iNumPage => , nPageSize => , bShowAll => ).
	 * @return array
	 */
	protected static function getVoted(array $cacheParams, array $pageParams)
	{
		$iNumPage = intval($pageParams["iNumPage"]);
		$nPageSize = intval($pageParams["nPageSize"]);
		$showAll = $pageParams["bShowAll"] === true;

		$cache = new \CPHPCache();
		$result = array(
			"statusPage" => "done",
			"items" => array(),
			"hiddenItems" => 0
		);

		$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
		$cacheId = "voted_".serialize(array($cacheParams["answerId"], $nPageSize, $iNumPage));
		$cacheDir = "/vote/".$cacheParams["voteId"]."/voted/";

		if (\Bitrix\Main\Config\Option::get("main", "component_cache_on", "Y") == "Y" && $cache->initCache($cacheTtl, $cacheId, $cacheDir))
		{
			$result = $cache->getVars();
		}
		else
		{
			if ($iNumPage <= 1)
			{
				$res = EventTable::getList(array(
					"select" => array(
						"CNT" => "CNT"
					),
					"runtime" => array(
						new ExpressionField("CNT", "COUNT(*)")
					),
					"filter" => array(
						"VOTE_ID" => $cacheParams["voteId"],
						"!=VISIBLE" => "Y",
						"VALID" => "Y",
						"QUESTION.ANSWER.ANSWER_ID" => $cacheParams["answerId"],
					)
				))->fetch();
				$result["hiddenItems"] = $res["CNT"];
			}

			$dbRes = \CVoteEvent::getUserAnswerStat(array(),
				array(
					"ANSWER_ID" => $cacheParams["answerId"],
					"VALID" => "Y",
					"VISIBLE" => "Y",
					"bGetVoters" => "Y",
					"bGetMemoStat" => "N"
				),
				array(
					"nPageSize" => $nPageSize,
					"bShowAll" => $showAll,
					"iNumPage" => $iNumPage
				)
			);
			$userIds = array();
			$result["statusPage"]= (($dbRes->NavPageNomer >= $dbRes->NavPageCount || $nPageSize > $dbRes->NavRecordCount) ? "done" : "continue");
			while ($res = $dbRes->fetch())
				$userIds[] = $res["AUTH_USER_ID"];

			if (empty($userIds))
				$result["statusPage"] = "done";
			else
			{
				$departments = array();
				if (IsModuleInstalled("extranet") &&
					($iblockId = \COption::GetOptionInt("intranet", "iblock_structure", 0)) &&
					$iblockId > 0)
				{
					$dbRes = \CIBlockSection::GetList(
						array("LEFT_MARGIN" => "DESC"),
						array("IBLOCK_ID" => $iblockId),
						false,
						array("ID", "NAME")
					);

					while ($res = $dbRes->fetch())
						$departments[$res["ID"]] = $res["NAME"];
				}

				$dbRes = \CUser::getList(
					($by = "ID"),
					($order = "ASC"),
					array("ID" => implode("|", $userIds)),
					array(
						"FIELDS" => array("ID", "NAME", "LAST_NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO") +
							(IsModuleInstalled("mail") ? array("EXTERNAL_AUTH_ID") : array()),
						"SELECT" => (IsModuleInstalled("extranet") ? array("UF_DEPARTMENT") : array())
					)
				);
				while ($res = $dbRes->fetch())
				{
					if (array_key_exists("PERSONAL_PHOTO", $res))
					{
						if (!empty($res["PERSONAL_PHOTO"]))
						{
							$f = \CFile::resizeImageGet(
								$res["PERSONAL_PHOTO"],
								array("width" => 64, "height" => 64),
								BX_RESIZE_IMAGE_EXACT,
								false
							);
							$res["PHOTO_SRC"] = ($f["src"] ? $f["src"] : "");
							$res["PHOTO"] = \CFile::showImage($f["src"], 21, 21, "border=0");
						}
						else
						{
							$res["PHOTO"] = $res["PHOTO_SRC"] = "";
						}
					}
					$res["TYPE"] = "";
					if (array_key_exists("EXTERNAL_AUTH_ID", $res) && $res["EXTERNAL_AUTH_ID"] == "email")
						$res["TYPE"] = "mail";
					elseif (array_key_exists("UF_DEPARTMENT", $res))
					{
						if (empty($res["UF_DEPARTMENT"]) || intval($res["UF_DEPARTMENT"][0]) <= 0)
							$res["TYPE"] = "extranet";
						else
						{
							$ds = array();
							foreach ($res["UF_DEPARTMENT"] as $departmentId)
							{
								if (array_key_exists($departmentId, $departments))
									$ds[] = $departments[$departmentId];
							}
							$res["TAGS"] = empty($res["WORK_POSITION"]) ? array() : array($res["WORK_POSITION"]);
							$res["TAGS"] = implode(",", array_merge($res["TAGS"], $ds));
							$res["WORK_DEPARTMENTS"] = $ds;
						}
					}
					$result["items"][$res["ID"]] = $res;
				}
			}

			if (!empty($result["items"]) || !empty($result["hiddenItems"]))
			{
				$cache->startDataCache($cacheTtl, $cacheId, $cacheDir);
				\CVoteCacheManager::setTag($cacheDir, "V", $cacheParams["voteId"]);
				$cache->endDataCache($result);
			}
		}
		return $result;
	}
	protected function processActionGetVoted()
	{
		if (!$this->checkRequiredGetParams(array("answerId")))
			return;
		$answerId = intval($this->request->getQuery("answerId"));
		if ($answerId <= 0)
			throw new ArgumentNullException("Answer ID is required.");
		$attach = $this->attach;
		$userId = $this->getUser()->getId();
		if (!$attach->canRead($userId))
			throw new AccessDeniedException();

		$belong = false;
		foreach ($attach["QUESTIONS"] as $qID => $question)
		{
			if (array_key_exists($answerId, $question["ANSWERS"]))
			{
				$belong = true;
				break;
			}
		}
		if (!$belong)
			throw new AccessDeniedException();

		$nameTemplate = $this->request->getPost("nameTemplate") ?: \CSite::getNameFormat(null, $this->request->getPost("SITE_ID"));
		$iNumPage = $this->request->getPost("iNumPage");
		$nPageSize = 50;
		$items = array();

		$result = self::getVoted(
			array(
				"voteId" => $attach->getVoteId(),
				"answerId" => $answerId),
			array(
				"iNumPage" => $iNumPage,
				"nPageSize" => $nPageSize,
				"bShowAll" => false)
			);

		if ($result["hiddenItems"] > 0)
		{
			$items[] = array(
				"ID" => "HIDDEN",
				"COUNT" => $result["hiddenItems"],
				"FULL_NAME" => Loc::getMessage("VOTE_HIDDEN_VOTES_COUNT", ["#COUNT#" => $result["hiddenItems"]]),
			);
		}
		foreach ($result["items"] as $k => $res)
		{
			$items[] = array(
				"ID" => $res["ID"],
				"TYPE" => $res["TYPE"],
				"PHOTO" => $res["PHOTO"],
				"PHOTO_SRC" => $res["PHOTO_SRC"],
				"FULL_NAME" => \CUser::formatName($nameTemplate, $res),
			);
		}
		$result["items"] = $items;
		$result["pageSize"] = $nPageSize;
		$result["iNumPage"] = $iNumPage;
		$this->sendJsonSuccessResponse(array(
			"action" => $this->getAction(),
			"data" => $result
		));
	}

	protected function processActionGetMobileVoted()
	{
		if (!$this->checkRequiredGetParams(array("answerId")))
			return;
		$answerId = intval($this->request->getQuery("answerId"));
		if ($answerId <= 0)
			throw new ArgumentNullException("Answer ID is required.");
		$attach = $this->attach;
		$userId = $this->getUser()->getId();
		if (!$attach->canRead($userId))
			throw new AccessDeniedException();

		$belong = false;
		foreach ($attach["QUESTIONS"] as $qID => $question)
		{
			if (array_key_exists($answerId, $question["ANSWERS"]))
			{
				$belong = true;
				break;
			}
		}
		if (!$belong)
			throw new AccessDeniedException();

		$nameTemplate = $this->request->getPost("nameTemplate") ?: \CSite::getNameFormat(null, $this->request->getPost("SITE_ID"));

		$result = self::getVoted(
			array(
				"voteId" => $attach->getVoteId(),
				"answerId" => $answerId),
			array(
				"iNumPage" => 1,
				"nPageSize" => 50,
				"bShowAll" => true)
		);

		$items = array();
		foreach ($result["items"] as $k => $res)
		{
			$items[] = array(
				"ID" => $res["ID"],
				"NAME" =>  \CUser::FormatName($nameTemplate, $res, true, false),
				"IMAGE" => $res["PHOTO_SRC"],
				"TAGS" => $res["TAGS"],
				"WORK_POSITION" => $res["WORK_POSITION"],
				"WORK_DEPARTMENTS" => $res["WORK_DEPARTMENTS"],
				"URL" => \CComponentEngine::MakePathFromTemplate(
					"/mobile/users/?user_id=#user_id#",
					array("UID" => $res["ID"], "user_id" => $res["ID"],
						"USER_ID" => $res["ID"])
				)
			);
		}
		$result["items"] = $items;
		$this->sendJsonSuccessResponse(array(
			"action" => $this->getAction(),
			"data" => array(
				"voted" => $items
			),
			"names" => array(
				"voted" =>  "Voted users"
			)
		));
	}

	/**
	 * Exports results in xls.
	 * @return void
	 * @throws AccessDeniedException
	 */
	protected function processActionExportXls()
	{
		$attach = $this->attach;
		$userId = $this->getUser()->getId();
		if (!$attach->canRead($userId))
			throw new AccessDeniedException();
		$attach->exportExcel();
	}

	/**
	 * @param integer $voteId Vote ID.
	 * @return void
	 */
	public function clearCache($voteId)
	{
		BXClearCache(true, "/vote/".$voteId."/voted/");
	}
}