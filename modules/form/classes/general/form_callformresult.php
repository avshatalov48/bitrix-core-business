<?php

/***************************************
		Web-form result
***************************************/

class CAllFormResult extends CFormResult_old
{
	public static function GetFileByAnswerID($RESULT_ID, $ANSWER_ID)
	{
		global $DB, $strError;
		$RESULT_ID = intval($RESULT_ID);
		$ANSWER_ID = intval($ANSWER_ID);
		$strSql = "
			SELECT
				USER_FILE_ID,
				USER_FILE_NAME,
				USER_FILE_IS_IMAGE,
				USER_FILE_HASH,
				USER_FILE_SUFFIX,
				USER_FILE_SIZE
			FROM
				b_form_result_answer
			WHERE
				RESULT_ID='".$RESULT_ID."'
			and ANSWER_ID='".$ANSWER_ID."'
			";
		$z = $DB->Query($strSql);
		if ($zr = $z->Fetch()) return $zr; else return false;
	}

	// return file data by file hash
	public static function GetFileByHash($RESULT_ID, $HASH)
	{
		global $DB, $APPLICATION, $strError, $USER;

		$RESULT_ID = intval($RESULT_ID);
		if ($RESULT_ID<=0 || trim($HASH) == '') return;

		$strSql = "
SELECT
	F.ID as FILE_ID,
	F.FILE_NAME,
	F.SUBDIR,
	F.CONTENT_TYPE,
	F.HANDLER_ID,
	F.FILE_SIZE,
	RA.USER_FILE_NAME ORIGINAL_NAME,
	RA.USER_FILE_IS_IMAGE,
	RA.FORM_ID, R.USER_ID
FROM b_form_result R
LEFT JOIN b_form_result_answer RA ON RA.RESULT_ID=R.ID
INNER JOIN b_file F ON (F.ID = RA.USER_FILE_ID)
WHERE R.ID = '".$RESULT_ID."'
AND RA.USER_FILE_HASH = '".$DB->ForSql($HASH, 255)."'
";

		$z = $DB->Query($strSql);
		if ($zr = $z->Fetch())
		{
			$F_RIGHT = CForm::GetPermission($zr['FORM_ID']);
			if ($F_RIGHT >= 20 || ($F_RIGHT >= 15 && $USER->GetID() == $zr['USER_ID']))
			{
				unset($zr['FORM_ID']); unset($zr['USER_ID']);
				return $zr;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	// create new event
	public static function SetEvent($RESULT_ID, $IN_EVENT1=false, $IN_EVENT2=false, $IN_EVENT3=false, $money="", $currency="", $goto="", $chargeback="N")
	{
		global $DB, $strError;

		if (CModule::IncludeModule("statistic"))
		{
			$RESULT_ID = intval($RESULT_ID);
			$strSql = "SELECT FORM_ID FROM b_form_result WHERE ID='".$RESULT_ID."'";
			$z = $DB->Query($strSql);
			if ($zr = $z->Fetch())
			{
				$WEB_FORM_ID = $zr["FORM_ID"];
				$strSql = "SELECT SID, STAT_EVENT1, STAT_EVENT2, STAT_EVENT3 FROM b_form WHERE ID = '".$WEB_FORM_ID."'";
				$z = $DB->Query($strSql);
				$zr = $z->Fetch();

				if ($IN_EVENT1===false)
				{
					$event1 = ($zr["STAT_EVENT1"] == '') ? "form" : $zr["STAT_EVENT1"];
				}
				else $event1 = $IN_EVENT1;

				if ($IN_EVENT2===false)
				{
					$event2 = ($zr["STAT_EVENT2"] == '') ? $zr["SID"] : $zr["STAT_EVENT2"];
				}
				else $event2 = $IN_EVENT2;

				if ($IN_EVENT3===false)
				{
					$event3 = $zr["STAT_EVENT3"] == ''
						? (
							$GLOBALS['APPLICATION']->IsHTTPS() ? "https://" : "http://"
						).$_SERVER["HTTP_HOST"]."/bitrix/admin/form_result_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&find_id=".$RESULT_ID."&find_id_exact_match=Y&set_filter=Y"
						: $zr["STAT_EVENT3"];
				}
				else $event3 = $IN_EVENT3;

				CStatEvent::AddCurrent($event1, $event2, $event3, $money, $currency, $goto, $chargeback);
				return true;
			}
			else $strError .= GetMessage("FORM_ERROR_RESULT_NOT_FOUND")."<br>";
		}
		return false;
	}

	//returns data for questions and answers array
	public static function GetDataByID($RESULT_ID, $arrFIELD_SID, &$arrRES, &$arrANSWER)
	{
		global $DB, $strError;
		$arrReturn = array();
		$RESULT_ID = intval($RESULT_ID);
		$z = CFormResult::GetByID($RESULT_ID);
		if ($arrRES = $z->Fetch())
		{
			$s = '';
			if (is_array($arrFIELD_SID) && count($arrFIELD_SID)>0)
			{
				$str = '';
				foreach($arrFIELD_SID as $field) $str .= ",'".$DB->ForSql($field,50)."'";
				$str = trim($str, ",");
				if ($str <> '') $s = "and SID in ($str)";
			}
			$strSql = "SELECT ID, SID, SID as VARNAME FROM b_form_field WHERE FORM_ID='".$arrRES["FORM_ID"]."' " . $s;
			$q = $DB->Query($strSql);
			while ($qr = $q->Fetch())
			{
				$arrFIELDS[$qr["ID"]] = $qr["SID"];
			}
			if (is_array($arrFIELDS)) $arrKeys = array_keys($arrFIELDS);
			CForm::GetResultAnswerArray($arrRES["FORM_ID"], $arrColumns, $arrAnswers, $arrAnswersSID, array("RESULT_ID"=>$RESULT_ID));

			foreach ($arrAnswers[$RESULT_ID] as $fid => $arrAns)
			{
				if (is_array($arrKeys))
				{
					if (in_array($fid,$arrKeys))
					{
						$sid = $arrFIELDS[$fid];
						$arrANSWER[$sid] = $arrAns;
						$arrA = array_values($arrAns);
						foreach($arrA as $arr) $arrReturn[$sid][] = $arr;
					}
				}
			}
		}
		else return false;

		if (is_array($arrANSWER)) reset($arrANSWER);
		if (is_array($arrReturn)) reset($arrReturn);
		if (is_array($arrRES)) reset($arrRES);

		return $arrReturn;
	}

	// return array of result values for component
	public static function GetDataByIDForHTML($RESULT_ID, $GET_ADDITIONAL="N")
	{
		global $DB, $strError;
		$z = CFormResult::GetByID($RESULT_ID);
		if ($zr=$z->Fetch())
		{
			$arrResult = $zr;
			$additional = ($GET_ADDITIONAL=="Y") ? "ALL" : "N";

			$WEB_FORM_ID = CForm::GetDataByID($arrResult["FORM_ID"], $arForm, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $additional);

			CForm::GetResultAnswerArray($WEB_FORM_ID, $arrResultColumns, $arrResultAnswers, $arrResultAnswersSID, array("RESULT_ID" => $RESULT_ID));
			$arrResultAnswers = $arrResultAnswers[$RESULT_ID];

			$DB_VARS = array();
			foreach ($arQuestions as $key => $arQuestion)
			{
				if ($arQuestion["ADDITIONAL"]!="Y")
				{
					$FIELD_SID = $arQuestion["SID"];
					if (is_array($arAnswers[$FIELD_SID]))
					{
						foreach ($arAnswers[$FIELD_SID] as $key => $arAnswer)
						{
							$arrResultAnswer = $arrResultAnswers[$arQuestion["ID"]][$arAnswer["ID"]];
							$FIELD_TYPE = $arAnswer["FIELD_TYPE"];
							switch ($FIELD_TYPE) :

								case "radio":
								case "dropdown":
									if (intval($arrResultAnswer["ANSWER_ID"])>0)
									{
										$fname = "form_".mb_strtolower($FIELD_TYPE)."_".$FIELD_SID;
										$DB_VARS[$fname] = $arrResultAnswer["ANSWER_ID"];
									}
								break;

								case "checkbox":
								case "multiselect":
									if (intval($arrResultAnswer["ANSWER_ID"])>0)
									{
										$fname = "form_".mb_strtolower($FIELD_TYPE)."_".$FIELD_SID;
										$DB_VARS[$fname][] = $arrResultAnswer["ANSWER_ID"];
									}
								break;

								case "date":
									if ($arrResultAnswer["USER_DATE"] <> '')
									{
										$arrResultAnswer["USER_TEXT"] = $DB->FormatDate(
											$arrResultAnswer["USER_DATE"],
											FORMAT_DATETIME,
											(MakeTimeStamp($arrResultAnswer["USER_TEXT"])+date('Z'))%86400 == 0 ? FORMAT_DATE : FORMAT_DATETIME
										);

										$fname = "form_".mb_strtolower($FIELD_TYPE)."_".$arAnswer["ID"];
										$DB_VARS[$fname] = $arrResultAnswer["USER_TEXT"];
									}

									break;

								case "text":
								case "password":
								case "textarea":
								case "email":
								case "url":
								case "hidden":
									if ($arrResultAnswer["USER_TEXT"] <> '')
									{
										$fname = "form_".mb_strtolower($FIELD_TYPE)."_".$arAnswer["ID"];
										$DB_VARS[$fname] = $arrResultAnswer["USER_TEXT"];
									}
								break;

								case "image":
								case "file":
									if (intval($arrResultAnswer["USER_FILE_ID"])>0)
									{
										$fname = "form_".mb_strtolower($FIELD_TYPE)."_".$arAnswer["ID"];
										$DB_VARS[$fname] = $arrResultAnswer["USER_FILE_ID"];
									}
								break;

							endswitch;
						} //endforeach;
					}
				}
				else
				{
					$FIELD_TYPE = $arQuestion["FIELD_TYPE"];
					$arrResultAnswer = $arrResultAnswers[$arQuestion["ID"]][0];
					switch ($FIELD_TYPE) :
						case "text":
							if ($arrResultAnswer["USER_TEXT"] <> '')
							{
								$fname = "form_textarea_ADDITIONAL_".$arQuestion["ID"];
								$DB_VARS[$fname] = $arrResultAnswer["USER_TEXT"];
							}
							break;
						case "integer":
							if ($arrResultAnswer["USER_TEXT"] <> '')
							{
								$fname = "form_text_ADDITIONAL_".$arQuestion["ID"];
								$DB_VARS[$fname] = $arrResultAnswer["USER_TEXT"];
							}
							break;
						case "date":
							$fname = "form_date_ADDITIONAL_".$arQuestion["ID"];
							$DB_VARS[$fname] = $arrResultAnswer["USER_TEXT"];
							break;
					endswitch;
				}
			}//endforeach
			return $DB_VARS;
		}
	}

	// add new form result
	public static function Add($WEB_FORM_ID, $arrVALUES=false, $CHECK_RIGHTS="Y", $USER_ID=false)
	{
		global $DB, $USER, $strError, $APPLICATION;
		if ($arrVALUES===false) $arrVALUES = $_REQUEST;

		if ($CHECK_RIGHTS != "N") $CHECK_RIGHTS = "Y";

		$WEB_FORM_ID = intval($WEB_FORM_ID);

		if ($WEB_FORM_ID>0)
		{
			$WEB_FORM_ID = intval($WEB_FORM_ID);

			// get form data
			$arForm = array();
			$arQuestions = array();
			$arAnswers = array();
			$arDropDown = array();
			$arMultiSelect = array();

			$WEB_FORM_ID = CForm::GetDataByID($WEB_FORM_ID, $arForm, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect);

			// if new form id is correct
			if ($WEB_FORM_ID>0)
			{
				// check result rights
				$F_RIGHT = CForm::GetPermission($WEB_FORM_ID);

				if (intval($F_RIGHT)>=10 || $CHECK_RIGHTS=="N")
				{
					if (intval($USER_ID)<=0)
					{
						$USER_AUTH = "N";
						$USER_ID = intval($_SESSION["SESS_LAST_USER_ID"]);
						if (intval($USER->GetID())>0)
						{
							$USER_AUTH = "Y";
							$USER_ID = intval($USER->GetID());
						}
					}
					else $USER_AUTH = "Y";

					// check result status
					$fname = "status_".$arForm["SID"];
					$STATUS_ID = (intval($arrVALUES[$fname] ?? 0) <=0) ? CFormStatus::GetDefault($WEB_FORM_ID) : intval($arrVALUES[$fname]);

					if ($STATUS_ID <= 0)
					{
						$strError .= GetMessage("FORM_STATUS_NOT_DEFINED")."<br>";
					}
					else
					{
						// status found
						if ($CHECK_RIGHTS != "N")
						{
							$arPerm = CFormStatus::GetPermissions($STATUS_ID);
						}

						if ($CHECK_RIGHTS == "N" || in_array("MOVE", $arPerm)) // has rights to a new status
						{
							// check restrictions

							if ($arForm["USE_RESTRICTIONS"] == "Y" && intval($USER_ID) > 0)
							{
								$arFilter = array("USER_ID" => $USER_ID);
								if ($arForm["RESTRICT_STATUS"] <> '')
								{
									$arStatus = explode(",", $arForm["RESTRICT_STATUS"]);
									$arFilter = array_merge($arFilter, array("STATUS_ID" => implode(" | ", $arStatus)));
								}

								if (intval($arForm["RESTRICT_USER"]) > 0)
								{
									$rsFormResult = CFormResult::GetList($WEB_FORM_ID, "s_timestamp", "desc", $arFilter, null, "N", intval($arForm["RESTRICT_USER"]));
									$num = 0;
									while ($row = $rsFormResult->Fetch())
									{
										if (++$num >= $arForm["RESTRICT_USER"])
										{
											$strError .= GetMessage("FORM_RESTRICT_USER_ERROR")."<br />";
											break;
										}
									}
								}

								if ($strError == '' && intval($arForm["RESTRICT_TIME"]) > 0)
								{
									$DC2 = time();
									$DC1 = $DC2 - intval($arForm["RESTRICT_TIME"]);
									$arFilter = array_merge($arFilter, array(
										"TIME_CREATE_1" => ConvertTimeStamp($DC1, "FULL"),
										"TIME_CREATE_2" => ConvertTimeStamp($DC2, "FULL"),
									));

									CTimeZone::Disable();
									$rsFormResult = CFormResult::GetList($WEB_FORM_ID, "s_timestamp", "desc", $arFilter, null, "N", 1);
									CTimeZone::Enable();

									if ($rsFormResult->Fetch())
									{
										$strError .= GetMessage("FORM_RESTRICT_TIME_ERROR")."<br>";
									}
								}
							}

							if ($strError == '')
							{
								// save result
								$arFields = array(
									"TIMESTAMP_X"		=> $DB->GetNowFunction(),
									"DATE_CREATE"		=> $DB->GetNowFunction(),
									"STATUS_ID"			=> $STATUS_ID,
									"FORM_ID"			=> $WEB_FORM_ID,
									"USER_ID"			=> intval($USER_ID),
									"USER_AUTH"			=> "'".$USER_AUTH."'",
									"STAT_GUEST_ID"		=> intval($_SESSION["SESS_GUEST_ID"]),
									"STAT_SESSION_ID"	=> intval($_SESSION["SESS_SESSION_ID"]),
									"SENT_TO_CRM"		=> "'N'", // result can be sent only after adding
									);

								foreach (GetModuleEvents('form', 'onBeforeResultAdd', true) as $arEvent)
								{
									ExecuteModuleEventEx($arEvent, array($WEB_FORM_ID, &$arFields, &$arrVALUES));

									if ($ex = $APPLICATION->GetException())
									{
										$strError .= $ex->GetString().'<br />';
										$APPLICATION->ResetException();
									}
								}

								if ($strError == '')
									$RESULT_ID = $DB->Insert("b_form_result", $arFields);
							}
						}
						else
							$strError .= GetMessage("FORM_ERROR_ACCESS_DENIED");
					}

					$RESULT_ID = intval($RESULT_ID);
					// save successful
					if ($RESULT_ID>0)
					{
						$arrANSWER_TEXT = array();
						$arrANSWER_VALUE = array();
						$arrUSER_TEXT = array();

						// process questions
						foreach ($arQuestions as $arQuestion)
						{
							$FIELD_ID = $arQuestion["ID"];
							$FIELD_SID = $arQuestion["SID"];
							$radio = "N";
							$checkbox = "N";
							$multiselect = "N";
							$dropdown = "N";
							if (is_array($arAnswers[$FIELD_SID]))
							{
								// process answers
								foreach ($arAnswers[$FIELD_SID] as $key => $arAnswer)
								{
									$ANSWER_ID = 0;
									$FIELD_TYPE = $arAnswer["FIELD_TYPE"];
									$FIELD_PARAM = $arAnswer["FIELD_PARAM"];
									switch ($FIELD_TYPE) :

										case "radio":
										case "dropdown":

											if (($radio=="N" && $FIELD_TYPE=="radio") ||
												($dropdown=="N" && $FIELD_TYPE=="dropdown"))
											{
												$fname = "form_".$FIELD_TYPE."_".$FIELD_SID;
												$ANSWER_ID = intval($arrVALUES[$fname]);
												if ($ANSWER_ID>0)
												{
													$z = CFormAnswer::GetByID($ANSWER_ID);
													if ($zr = $z->Fetch())
													{
														$arFields = array(
															"RESULT_ID"			=> $RESULT_ID,
															"FORM_ID"			=> $WEB_FORM_ID,
															"FIELD_ID"			=> $FIELD_ID,
															"ANSWER_ID"			=> $ANSWER_ID,
															"ANSWER_TEXT"		=> trim($zr["MESSAGE"]),
															"ANSWER_VALUE"		=> $zr["VALUE"]
															);
														$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
														$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
														CFormResult::AddAnswer($arFields);
													}
													if ($FIELD_TYPE=="radio") $radio = "Y";
													if ($FIELD_TYPE=="dropdown") $dropdown = "Y";
												}
											}

										break;

										case "checkbox":
										case "multiselect":

											if (($checkbox=="N" && $FIELD_TYPE=="checkbox") ||
												($multiselect=="N" && $FIELD_TYPE=="multiselect"))
											{
												$fname = "form_".$FIELD_TYPE."_".$FIELD_SID;
												if (is_array($arrVALUES[$fname]) && count($arrVALUES[$fname])>0)
												{
													foreach($arrVALUES[$fname] as $ANSWER_ID)
													{
														$ANSWER_ID = intval($ANSWER_ID);
														if ($ANSWER_ID>0)
														{
															$z = CFormAnswer::GetByID($ANSWER_ID);
															if ($zr = $z->Fetch())
															{
																$arFields = array(
																	"RESULT_ID"			=> $RESULT_ID,
																	"FORM_ID"			=> $WEB_FORM_ID,
																	"FIELD_ID"			=> $FIELD_ID,
																	"ANSWER_ID"			=> $ANSWER_ID,
																	"ANSWER_TEXT"		=> trim($zr["MESSAGE"]),
																	"ANSWER_VALUE"		=> $zr["VALUE"]
																	);
																$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
																$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
																CFormResult::AddAnswer($arFields);
															}
														}
													}
													if ($FIELD_TYPE=="checkbox") $checkbox = "Y";
													if ($FIELD_TYPE=="multiselect") $multiselect = "Y";
												}
											}

										break;

										case "text":
										case "hidden":
										case "textarea":
										case "password":
										case "email":
										case "url":

											$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
											$ANSWER_ID = intval($arAnswer["ID"]);
											$z = CFormAnswer::GetByID($ANSWER_ID);
											if ($zr = $z->Fetch())
											{
												$arFields = array(
													"RESULT_ID"			=> $RESULT_ID,
													"FORM_ID"			=> $WEB_FORM_ID,
													"FIELD_ID"			=> $FIELD_ID,
													"ANSWER_ID"			=> $ANSWER_ID,
													"ANSWER_TEXT"		=> trim($zr["MESSAGE"]),
													"ANSWER_VALUE"		=> $zr["VALUE"],
													"USER_TEXT"			=> $arrVALUES[$fname]
												);

												$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
												$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
												$arrUSER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["USER_TEXT"]);
												CFormResult::AddAnswer($arFields);
											}

										break;

										case "date":

											$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
											$ANSWER_ID = intval($arAnswer["ID"]);
											$USER_DATE = $arrVALUES[$fname];
											if (CheckDateTime($USER_DATE))
											{
												$z = CFormAnswer::GetByID($ANSWER_ID);
												if ($zr = $z->Fetch())
												{
													$arFields = array(
														"RESULT_ID"			=> $RESULT_ID,
														"FORM_ID"			=> $WEB_FORM_ID,
														"FIELD_ID"			=> $FIELD_ID,
														"ANSWER_ID"			=> $ANSWER_ID,
														"ANSWER_TEXT"		=> trim($zr["MESSAGE"]),
														"ANSWER_VALUE"		=> $zr["VALUE"],
														"USER_DATE"			=> $USER_DATE,
														"USER_TEXT"			=> $USER_DATE
													);
													$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
													$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
													$arrUSER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["USER_TEXT"]);
													CFormResult::AddAnswer($arFields);
												}
											}
											break;

										case "image":

											$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
											$ANSWER_ID = intval($arAnswer["ID"]);
											$arIMAGE = isset($arrVALUES[$fname]) ? $arrVALUES[$fname] : $_FILES[$fname];
											$arIMAGE["MODULE_ID"] = "form";
											$fid = 0;
											if (CFile::CheckImageFile($arIMAGE) == '')
											{
												if ($arIMAGE["name"] <> '')
												{
													$fid = CFile::SaveFile($arIMAGE, "form");
													$fid = intval($fid);
													if ($fid>0)
													{
														$md5 = md5(uniqid(mt_rand(), true).time());
														$z = CFormAnswer::GetByID($ANSWER_ID);
														if ($zr = $z->Fetch())
														{
															$arFields = array(
																"RESULT_ID"				=> $RESULT_ID,
																"FORM_ID"				=> $WEB_FORM_ID,
																"FIELD_ID"				=> $FIELD_ID,
																"ANSWER_ID"				=> $ANSWER_ID,
																"ANSWER_TEXT"			=> trim($zr["MESSAGE"]),
																"ANSWER_VALUE"			=> $zr["VALUE"],
																"USER_TEXT"				=> $arIMAGE["name"],
																"USER_FILE_ID"			=> $fid,
																"USER_FILE_IS_IMAGE"	=> "Y",
																"USER_FILE_HASH"		=> $md5,
																"USER_FILE_NAME"		=> $arIMAGE["name"],
																"USER_FILE_SIZE"		=> $arIMAGE["size"],
															);
															$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
															$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
															$arrUSER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["USER_TEXT"]);
															CFormResult::AddAnswer($arFields);
														}
													}
												}
											}

										break;

										case "file":

											$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
											$ANSWER_ID = intval($arAnswer["ID"]);
											$arFILE = isset($arrVALUES[$fname]) ? $arrVALUES[$fname] : $_FILES[$fname];
											$arFILE["MODULE_ID"] = "form";

											if ($arFILE["name"] <> '')
											{
												$original_name = $arFILE["name"];
												$fid = 0;
												$max_size = COption::GetOptionString("form", "MAX_FILESIZE");
												$upload_dir = COption::GetOptionString("form", "NOT_IMAGE_UPLOAD_DIR");

												$fid = CFile::SaveFile($arFILE, $upload_dir);
												$fid = intval($fid);
												if ($fid>0)
												{
													$md5 = md5(uniqid(mt_rand(), true).time());
													$z = CFormAnswer::GetByID($ANSWER_ID);
													if ($zr = $z->Fetch())
													{
														$arFields = array(
															"RESULT_ID"				=> $RESULT_ID,
															"FORM_ID"				=> $WEB_FORM_ID,
															"FIELD_ID"				=> $FIELD_ID,
															"ANSWER_ID"				=> $ANSWER_ID,
															"ANSWER_TEXT"			=> trim($zr["MESSAGE"]),
															"ANSWER_VALUE"			=> $zr["VALUE"],
															"USER_TEXT"				=> $original_name,
															"USER_FILE_ID"			=> $fid,
															"USER_FILE_NAME"		=> $original_name,
															"USER_FILE_IS_IMAGE"	=> "N",
															"USER_FILE_HASH"		=> $md5,
															"USER_FILE_SUFFIX"		=> $fes,
															"USER_FILE_SIZE"		=> $arFILE["size"],
														);
														$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
														$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
														$arrUSER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["USER_TEXT"]);
														CFormResult::AddAnswer($arFields);
													}
												}
											}

										break;

									endswitch;
								}
								// update search fields
								$arrANSWER_TEXT_upd = $arrANSWER_TEXT[$FIELD_ID];
								$arrANSWER_VALUE_upd = $arrANSWER_VALUE[$FIELD_ID];
								$arrUSER_TEXT_upd = $arrUSER_TEXT[$FIELD_ID];
								TrimArr($arrANSWER_TEXT_upd);
								TrimArr($arrANSWER_VALUE_upd);
								TrimArr($arrUSER_TEXT_upd);
								if (is_array($arrANSWER_TEXT_upd)) $vl_ANSWER_TEXT = trim(implode(" ",$arrANSWER_TEXT_upd));
								if (is_array($arrANSWER_VALUE_upd)) $vl_ANSWER_VALUE = trim(implode(" ",$arrANSWER_VALUE_upd));
								if (is_array($arrUSER_TEXT_upd)) $vl_USER_TEXT = trim(implode(" ",$arrUSER_TEXT_upd));
								if ($vl_ANSWER_TEXT == '') $vl_ANSWER_TEXT = false;
								if ($vl_ANSWER_VALUE == '') $vl_ANSWER_VALUE = false;
								if ($vl_USER_TEXT == '') $vl_USER_TEXT = false;
								$arFields = array(
									"ANSWER_TEXT_SEARCH"	=> $vl_ANSWER_TEXT,
									"ANSWER_VALUE_SEARCH"	=> $vl_ANSWER_VALUE,
									"USER_TEXT_SEARCH"		=> $vl_USER_TEXT
									);
								CFormResult::UpdateField($arFields, $RESULT_ID, $FIELD_ID);
							}
						}

						foreach (GetModuleEvents('form', 'onAfterResultAdd', true) as $arEvent)
						{
							ExecuteModuleEventEx($arEvent, array($WEB_FORM_ID, $RESULT_ID));
						}

						// call change status handler
						CForm::ExecHandlerAfterChangeStatus($RESULT_ID, "ADD");
					}
				}
			}
		}
		return intval($RESULT_ID)>0 ? intval($RESULT_ID) : false;
	}

	// update result
	public static function Update($RESULT_ID, $arrVALUES=false, $UPDATE_ADDITIONAL="N", $CHECK_RIGHTS="Y")
	{
		global $DB, $USER, $strError, $APPLICATION;
		if ($arrVALUES===false) $arrVALUES = $_REQUEST;

		InitBvar($UPDATE_ADDITIONAL);
		// check whether such result exists in db
		$RESULT_ID = intval($RESULT_ID);
		$z = CFormResult::GetByID($RESULT_ID);
		if ($zr=$z->Fetch())
		{
			$arrResult = $zr;
			$additional = ($UPDATE_ADDITIONAL=="Y") ? "ALL" : "N";
			// get form data
			$WEB_FORM_ID = CForm::GetDataByID($arrResult["FORM_ID"], $arForm, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $additional);
			if ($WEB_FORM_ID>0)
			{
				// check form rights
				$F_RIGHT = ($CHECK_RIGHTS!="Y") ? 30 : intval(CForm::GetPermission($WEB_FORM_ID));
				if ($F_RIGHT>=20 || ($F_RIGHT>=15 && $arrResult["USER_ID"]==$USER->GetID()))
				{
					// check result rights (its status rights)
					$arrRESULT_PERMISSION = ($CHECK_RIGHTS!="Y") ? CFormStatus::GetMaxPermissions() : CFormResult::GetPermissions($RESULT_ID);

					// if  rights're correct
					if (in_array("EDIT", $arrRESULT_PERMISSION))
					{
						// update result
						$arFields = array("TIMESTAMP_X"	=> $DB->GetNowFunction());
						$fname = "status_".$arForm["SID"];
						$STATUS_ID = intval($arrVALUES[$fname]);

						$bUpdateStatus = false;
						// if there's new status defined
						if (intval($STATUS_ID)>0)
						{
							// check new status rights
							$arrNEW_STATUS_PERMISSION = ($CHECK_RIGHTS!="Y") ? CFormStatus::GetMaxPermissions() : CFormStatus::GetPermissions($STATUS_ID);

							// if rights're correct
							if (in_array("MOVE",$arrNEW_STATUS_PERMISSION))
							{
								// update it
								$bUpdateStatus = true;
								$arFields["STATUS_ID"] = intval($arrVALUES[$fname]);
							}
						}

						if ($bUpdateStatus)
						{
							foreach (GetModuleEvents('form', 'onBeforeResultStatusChange', true) as $arEvent)
							{
								ExecuteModuleEventEx($arEvent, array($WEB_FORM_ID, $RESULT_ID, &$arFields["STATUS_ID"], $CHECK_RIGHTS));

								if ($ex = $APPLICATION->GetException())
									$strError .= $ex->GetString().'<br />';
							}
						}

						if ($strError == '')
						{
							// call status change handler
							CForm::ExecHandlerBeforeChangeStatus($RESULT_ID, "UPDATE", $arFields["STATUS_ID"]);

							foreach (GetModuleEvents('form', 'onBeforeResultUpdate', true) as $arEvent)
							{
								ExecuteModuleEventEx($arEvent, array($WEB_FORM_ID, $RESULT_ID, &$arFields, &$arrVALUES, $CHECK_RIGHTS));

								if ($ex = $APPLICATION->GetException())
									$strError .= $ex->GetString().'<br />';
							}
						}

						$rows = 0;

						if ($strError == '')
							$rows = $DB->Update("b_form_result", $arFields,"WHERE ID='".$RESULT_ID."'");

						if ($bUpdateStatus)
						{
							foreach (GetModuleEvents('form', 'onAfterResultStatusChange', true) as $arEvent)
							{
								ExecuteModuleEventEx($arEvent, array($WEB_FORM_ID, $RESULT_ID, &$arFields["STATUS_ID"], $CHECK_RIGHTS));
							}
						}

						// if update was successful
						if (intval($rows)>0)
						{
							$arrException = array();

							// gather files info
							$arrFILES = array();
							$strSql = "
								SELECT
									ANSWER_ID,
									USER_FILE_ID,
									USER_FILE_NAME,
									USER_FILE_IS_IMAGE,
									USER_FILE_HASH,
									USER_FILE_SUFFIX,
									USER_FILE_SIZE
								FROM
									b_form_result_answer
								WHERE
									RESULT_ID = $RESULT_ID
								and USER_FILE_ID>0
								";
							$q = $DB->Query($strSql);
							while ($qr = $q->Fetch()) $arrFILES[$qr["ANSWER_ID"]] = $qr;

							if (is_array($arrVALUES["ARR_CLS"])) $arrException = array_merge($arrException, $arrVALUES["ARR_CLS"]);

							// clear all questions and answers  for current result
							CFormResult::Reset($RESULT_ID, false, $UPDATE_ADDITIONAL, $arrException);

							// trace questions and additional fields
							foreach ($arQuestions as $arQuestion)
							{
								$FIELD_ID = $arQuestion["ID"];
								if (is_array($arrException) && count($arrException)>0)
								{
									if (in_array($FIELD_ID, $arrException)) continue;
								}
								$FIELD_SID = $arQuestion["SID"];
								if ($arQuestion["ADDITIONAL"]!="Y")
								{
									// update form questions
									$arrANSWER_TEXT = array();
									$arrANSWER_VALUE = array();
									$arrUSER_TEXT = array();
									$radio = "N";
									$checkbox = "N";
									$multiselect = "N";
									$dropdown = "N";
									// trace answers
									if (is_array($arAnswers[$FIELD_SID]))
									{
										foreach ($arAnswers[$FIELD_SID] as $key => $arAnswer)
										{
											$ANSWER_ID = 0;
											$FIELD_TYPE = $arAnswer["FIELD_TYPE"];
											$FIELD_PARAM = $arAnswer["FIELD_PARAM"];
											switch ($FIELD_TYPE) :

												case "radio":
												case "dropdown":

													if (($radio=="N" && $FIELD_TYPE=="radio") ||
														($dropdown=="N" && $FIELD_TYPE=="dropdown"))
													{
														$fname = "form_".$FIELD_TYPE."_".$FIELD_SID;
														$ANSWER_ID = intval($arrVALUES[$fname]);
														if ($ANSWER_ID>0)
														{
															$z = CFormAnswer::GetByID($ANSWER_ID);
															if ($zr = $z->Fetch())
															{
																$arFields = array(
																	"RESULT_ID"			=> $RESULT_ID,
																	"FORM_ID"			=> $WEB_FORM_ID,
																	"FIELD_ID"			=> $FIELD_ID,
																	"ANSWER_ID"			=> $ANSWER_ID,
																	"ANSWER_TEXT"		=> trim($zr["MESSAGE"]),
																	"ANSWER_VALUE"		=> $zr["VALUE"]
																);
																$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
																$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
																CFormResult::AddAnswer($arFields);
															}
															if ($FIELD_TYPE=="radio") $radio = "Y";
															if ($FIELD_TYPE=="dropdown") $dropdown = "Y";
														}
													}

												break;

												case "checkbox":
												case "multiselect":

													if (($checkbox=="N" && $FIELD_TYPE=="checkbox") ||
														($multiselect=="N" && $FIELD_TYPE=="multiselect"))
													{
														$fname = "form_".$FIELD_TYPE."_".$FIELD_SID;
														if (is_array($arrVALUES[$fname]) && count($arrVALUES[$fname])>0)
														{
															foreach($arrVALUES[$fname] as $ANSWER_ID)
															{
																$ANSWER_ID = intval($ANSWER_ID);
																if ($ANSWER_ID>0)
																{
																	$z = CFormAnswer::GetByID($ANSWER_ID);
																	if ($zr = $z->Fetch())
																	{
																		$arFields = array(
																		"RESULT_ID"			=> $RESULT_ID,
																		"FORM_ID"			=> $WEB_FORM_ID,
																		"FIELD_ID"			=> $FIELD_ID,
																		"ANSWER_ID"			=> $ANSWER_ID,
																		"ANSWER_TEXT"		=> trim($zr["MESSAGE"]),
																		"ANSWER_VALUE"		=> $zr["VALUE"]
																		);
																		$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
																		$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
																		CFormResult::AddAnswer($arFields);
																	}
																}
															}
															if ($FIELD_TYPE=="checkbox") $checkbox = "Y";
															if ($FIELD_TYPE=="multiselect") $multiselect = "Y";
														}
													}

												break;

												case "text":
												case "textarea":
												case "password":
												case "email":
												case "url":
												case "hidden":
													$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
													$ANSWER_ID = intval($arAnswer["ID"]);
													$z = CFormAnswer::GetByID($ANSWER_ID);
													if ($zr = $z->Fetch())
													{
														$arFields = array(
															"RESULT_ID"			=> $RESULT_ID,
															"FORM_ID"			=> $WEB_FORM_ID,
															"FIELD_ID"			=> $FIELD_ID,
															"ANSWER_ID"			=> $ANSWER_ID,
															"ANSWER_TEXT"		=> trim($zr["MESSAGE"]),
															"ANSWER_VALUE"		=> $zr["VALUE"],
															"USER_TEXT"			=> $arrVALUES[$fname]
														);
														$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
														$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
														$arrUSER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["USER_TEXT"]);
														CFormResult::AddAnswer($arFields);
													}

												break;

												case "date":

													$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
													$ANSWER_ID = intval($arAnswer["ID"]);
													$USER_DATE = $arrVALUES[$fname];
													if (CheckDateTime($USER_DATE))
													{
														$z = CFormAnswer::GetByID($ANSWER_ID);
														if ($zr = $z->Fetch())
														{
															$arFields = array(
																"RESULT_ID"			=> $RESULT_ID,
																"FORM_ID"			=> $WEB_FORM_ID,
																"FIELD_ID"			=> $FIELD_ID,
																"ANSWER_ID"			=> $ANSWER_ID,
																"ANSWER_TEXT"		=> trim($zr["MESSAGE"]),
																"ANSWER_VALUE"		=> $zr["VALUE"],
																"USER_DATE"			=> $USER_DATE,
																"USER_TEXT"			=> $USER_DATE
															);
															$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
															$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
															$arrUSER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["USER_TEXT"]);
															CFormResult::AddAnswer($arFields);
														}
													}
													break;

												case "image":

													$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
													$ANSWER_ID = intval($arAnswer["ID"]);
													$arIMAGE = isset($arrVALUES[$fname]) ? $arrVALUES[$fname] : $_FILES[$fname];
													$arIMAGE["old_file"] = $arrFILES[$ANSWER_ID]["USER_FILE_ID"];
													$arIMAGE["del"] = $arrVALUES[$fname."_del"];
													$arIMAGE["MODULE_ID"] = "form";
													$fid = 0;
													if ($arIMAGE["name"] <> '' || $arIMAGE["del"] <> '')
													{
														$new_file="Y";
														if ($arIMAGE["del"] <> '' || CFile::CheckImageFile($arIMAGE) == '')
														{
															$fid = CFile::SaveFile($arIMAGE, "form");
														}
													}
													else $fid = $arrFILES[$ANSWER_ID]["USER_FILE_ID"];

													$fid = intval($fid);
													if ($fid>0)
													{
														$z = CFormAnswer::GetByID($ANSWER_ID);
														if ($zr = $z->Fetch())
														{
															$arFields = array(
																"RESULT_ID"				=> $RESULT_ID,
																"FORM_ID"				=> $WEB_FORM_ID,
																"FIELD_ID"				=> $FIELD_ID,
																"ANSWER_ID"				=> $ANSWER_ID,
																"ANSWER_TEXT"			=> trim($zr["MESSAGE"]),
																"ANSWER_VALUE"			=> $zr["VALUE"],
																"USER_FILE_ID"			=> $fid,
																"USER_FILE_IS_IMAGE"	=> "Y"
																);
															if ($new_file=="Y")
															{
																$arFields["USER_FILE_NAME"] = $arIMAGE["name"];
																$arFields["USER_FILE_SIZE"] = $arIMAGE["size"];
																$arFields["USER_FILE_HASH"] = md5(uniqid(mt_rand(), true).time());

															}
															else
															{
																$arFields["USER_FILE_NAME"] = $arrFILES[$ANSWER_ID]["USER_FILE_NAME"];
																$arFields["USER_FILE_SIZE"] = $arrFILES[$ANSWER_ID]["USER_FILE_SIZE"];
																$arFields["USER_FILE_HASH"] = $arrFILES[$ANSWER_ID]["USER_FILE_HASH"];
															}
															$arFields["USER_TEXT"] = $arFields["USER_FILE_NAME"];

															$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
															$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
															$arrUSER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["USER_TEXT"]);
															CFormResult::AddAnswer($arFields);
														}
													}

												break;

												case "file":

													$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
													$ANSWER_ID = intval($arAnswer["ID"]);
													$arFILE = isset($arrVALUES[$fname]) ? $arrVALUES[$fname] : $_FILES[$fname];
													$arFILE["old_file"] = $arrFILES[$ANSWER_ID]["USER_FILE_ID"];
													$arFILE["del"] = $arrVALUES[$fname."_del"];
													$arFILE["MODULE_ID"] = "form";
													$new_file="N";
													$fid = 0;
													if (trim($arFILE["name"]) <> '' || trim($arFILE["del"]) <> '')
													{
														$new_file="Y";
														$original_name = $arFILE["name"];
														$max_size = COption::GetOptionString("form", "MAX_FILESIZE");
														$upload_dir = COption::GetOptionString("form", "NOT_IMAGE_UPLOAD_DIR");

														$fid = CFile::SaveFile($arFILE, $upload_dir, $max_size);
													}
													else $fid = $arrFILES[$ANSWER_ID]["USER_FILE_ID"];

													$fid = intval($fid);

													if ($fid>0)
													{
														$z = CFormAnswer::GetByID($ANSWER_ID);
														if ($zr = $z->Fetch())
														{
															$arFields = array(
																"RESULT_ID"				=> $RESULT_ID,
																"FORM_ID"				=> $WEB_FORM_ID,
																"FIELD_ID"				=> $FIELD_ID,
																"ANSWER_ID"				=> $ANSWER_ID,
																"ANSWER_TEXT"			=> trim($zr["MESSAGE"]),
																"ANSWER_VALUE"			=> $zr["VALUE"],
																"USER_FILE_ID"			=> $fid,
															);
															if ($new_file=="Y")
															{
																$arFields["USER_FILE_NAME"] = $original_name;
																$arFields["USER_FILE_IS_IMAGE"] = "N";
																$arFields["USER_FILE_HASH"] = md5(uniqid(mt_rand(), true).time());
																$arFields["USER_FILE_SUFFIX"] = $suffix;
																$arFields["USER_FILE_SIZE"] = $arFILE["size"];
															}
															else
															{
																$arFields["USER_FILE_NAME"] = $arrFILES[$ANSWER_ID]["USER_FILE_NAME"];
																$arFields["USER_FILE_IS_IMAGE"] = $arrFILES[$ANSWER_ID]["USER_FILE_IS_IMAGE"];
																$arFields["USER_FILE_HASH"] = $arrFILES[$ANSWER_ID]["USER_FILE_HASH"];
																$arFields["USER_FILE_SUFFIX"] = $arrFILES[$ANSWER_ID]["USER_FILE_SUFFIX"];
																$arFields["USER_FILE_SIZE"] = $arrFILES[$ANSWER_ID]["USER_FILE_SIZE"];
															}
															$arFields["USER_TEXT"] = $arFields["USER_FILE_NAME"];

															$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
															$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
															$arrUSER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["USER_TEXT"]);
															CFormResult::AddAnswer($arFields);
														}
													}

												break;

											endswitch;
										}
									}
									// update fields for searching
									$arrANSWER_TEXT_upd = $arrANSWER_TEXT[$FIELD_ID];
									$arrANSWER_VALUE_upd = $arrANSWER_VALUE[$FIELD_ID];
									$arrUSER_TEXT_upd = $arrUSER_TEXT[$FIELD_ID];
									TrimArr($arrANSWER_TEXT_upd);
									TrimArr($arrANSWER_VALUE_upd);
									TrimArr($arrUSER_TEXT_upd);
									if (is_array($arrANSWER_TEXT_upd)) $vl_ANSWER_TEXT = trim(implode(" ",$arrANSWER_TEXT_upd));
									if (is_array($arrANSWER_VALUE_upd)) $vl_ANSWER_VALUE = trim(implode(" ",$arrANSWER_VALUE_upd));
									if (is_array($arrUSER_TEXT_upd)) $vl_USER_TEXT = trim(implode(" ",$arrUSER_TEXT_upd));
									if ($vl_ANSWER_TEXT == '') $vl_ANSWER_TEXT = false;
									if ($vl_ANSWER_VALUE == '') $vl_ANSWER_VALUE = false;
									if ($vl_USER_TEXT == '') $vl_USER_TEXT = false;
									$arFields = array(
										"ANSWER_TEXT_SEARCH"	=> $vl_ANSWER_TEXT,
										"ANSWER_VALUE_SEARCH"	=> $vl_ANSWER_VALUE,
										"USER_TEXT_SEARCH"		=> $vl_USER_TEXT
										);
									CFormResult::UpdateField($arFields, $RESULT_ID, $FIELD_ID);
								}
								else // update additional fields
								{
									$FIELD_TYPE = $arQuestion["FIELD_TYPE"];
									switch ($FIELD_TYPE) :

										case "text":
											$fname = "form_textarea_ADDITIONAL_".$arQuestion["ID"];
											$arFields = array(
												"RESULT_ID"			=> $RESULT_ID,
												"FORM_ID"			=> $WEB_FORM_ID,
												"FIELD_ID"			=> $FIELD_ID,
												"USER_TEXT"			=> $arrVALUES[$fname],
												"USER_TEXT_SEARCH"	=> mb_strtoupper($arrVALUES[$fname])
											);
											CFormResult::AddAnswer($arFields);
											break;

										case "integer":

											$fname = "form_text_ADDITIONAL_".$arQuestion["ID"];
											$arFields = array(
												"RESULT_ID"			=> $RESULT_ID,
												"FORM_ID"			=> $WEB_FORM_ID,
												"FIELD_ID"			=> $FIELD_ID,
												"USER_TEXT"			=> $arrVALUES[$fname],
												"USER_TEXT_SEARCH"	=> mb_strtoupper($arrVALUES[$fname])
											);
											CFormResult::AddAnswer($arFields);

										break;

										case "date":

											$fname = "form_date_ADDITIONAL_".$arQuestion["ID"];
											$USER_DATE = $arrVALUES[$fname];
											if (CheckDateTime($USER_DATE))
											{
												$arFields = array(
													"RESULT_ID"			=> $RESULT_ID,
													"FORM_ID"			=> $WEB_FORM_ID,
													"FIELD_ID"			=> $FIELD_ID,
													"USER_DATE"			=> $USER_DATE,
													"USER_TEXT"			=> $USER_DATE,
													"USER_TEXT_SEARCH"	=> mb_strtoupper($USER_DATE)
												);
												CFormResult::AddAnswer($arFields);
											}

										break;
									endswitch;
								}
							}

							foreach (GetModuleEvents('form', 'onAfterResultUpdate', true) as $arEvent)
							{
								ExecuteModuleEventEx($arEvent, array($WEB_FORM_ID, $RESULT_ID, $CHECK_RIGHTS));
							}

							// call "after status update" handler
							CForm::ExecHandlerAfterChangeStatus($RESULT_ID, "UPDATE");
							return true;
						}
					}
				}
			}
		}
		return false;
	}

	// set question or field value in existed result
	public static function SetField($RESULT_ID, $FIELD_SID, $VALUE=false)
	{
		global $DB, $strError;
		$RESULT_ID = intval($RESULT_ID);
		if (intval($RESULT_ID)>0)
		{
			$strSql = "
				SELECT
					FORM_ID
				FROM
					b_form_result
				WHERE
					ID = $RESULT_ID
				";
			$z = $DB->Query($strSql);
			$zr = $z->Fetch();
			$WEB_FORM_ID = $zr["FORM_ID"];
			if (intval($WEB_FORM_ID)>0)
			{
				$strSql = "
					SELECT
						ID,
						FIELD_TYPE,
						ADDITIONAL
					FROM
						b_form_field
					WHERE
						FORM_ID = $WEB_FORM_ID
					and SID = '".$DB->ForSql($FIELD_SID,50)."'
					";
				$q = $DB->Query($strSql);
				if ($arField = $q->Fetch())
				{
					$FIELD_ID = $arField["ID"];
					$IS_FIELD = ($arField["ADDITIONAL"]=="Y") ? true : false;

					if ($IS_FIELD)
					{
						$strSql = "
							DELETE FROM
								b_form_result_answer
							WHERE
								RESULT_ID = $RESULT_ID
							and FIELD_ID = $FIELD_ID
							";
						//echo "<pre>".$strSql."</pre>";
						$DB->Query($strSql);

						if ($VALUE <> '')
						{

							$FIELD_TYPE = $arField["FIELD_TYPE"];
							switch ($FIELD_TYPE) :

								case "text":
								case "integer":

									$arFields = array(
										"RESULT_ID"			=> $RESULT_ID,
										"FORM_ID"			=> $WEB_FORM_ID,
										"FIELD_ID"			=> $FIELD_ID,
										"USER_TEXT"			=> $VALUE,
										"USER_TEXT_SEARCH"	=> mb_strtoupper($VALUE)
										);
									CFormResult::AddAnswer($arFields);
								break;

								case "date":

									if (CheckDateTime($VALUE))
									{
										$arFields = array(
											"RESULT_ID"			=> $RESULT_ID,
											"FORM_ID"			=> $WEB_FORM_ID,
											"FIELD_ID"			=> $FIELD_ID,
											"USER_DATE"			=> $VALUE,
											"USER_TEXT"			=> $VALUE,
											"USER_TEXT_SEARCH"	=> mb_strtoupper($VALUE)
											);
										CFormResult::AddAnswer($arFields);
									}
								break;

							endswitch;
						}
					}
					else
					{
						$strSql = "
							SELECT
								USER_FILE_ID
							FROM
								b_form_result_answer
							WHERE
								RESULT_ID = $RESULT_ID
							and FIELD_ID = $FIELD_ID
							and USER_FILE_ID>0
							";
						$rsFiles = $DB->Query($strSql);
						while ($arFile = $rsFiles->Fetch()) CFile::Delete($arFile["USER_FILE_ID"]);

						$strSql = "
							DELETE FROM
								b_form_result_answer
							WHERE
								RESULT_ID = $RESULT_ID
							and FIELD_ID = $FIELD_ID
							";
						$DB->Query($strSql);

						if (is_array($VALUE) && count($VALUE)>0)
						{
							$arrANSWER_TEXT = array();
							$arrANSWER_VALUE = array();
							$arrUSER_TEXT = array();
							foreach ($VALUE as $ANSWER_ID => $val)
							{
								$rsAnswer = CFormAnswer::GetByID($ANSWER_ID);
								if ($arAnswer = $rsAnswer->Fetch())
								{
									switch ($arAnswer["FIELD_TYPE"]) :

										case "radio":
										case "dropdown":
										case "checkbox":
										case "multiselect":

											$arFields = array(
												"RESULT_ID"				=> $RESULT_ID,
												"FORM_ID"				=> $WEB_FORM_ID,
												"FIELD_ID"				=> $FIELD_ID,
												"ANSWER_ID"				=> $ANSWER_ID,
												"ANSWER_TEXT"			=> trim($arAnswer["MESSAGE"]),
												"ANSWER_VALUE"			=> $arAnswer["VALUE"],
											);
											CFormResult::AddAnswer($arFields);
											$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
											$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);

										break;

										case "text":
										case "textarea":
										case "password":
										case "email":
										case "url":
										case "hidden":

											$arFields = array(
												"RESULT_ID"				=> $RESULT_ID,
												"FORM_ID"				=> $WEB_FORM_ID,
												"FIELD_ID"				=> $FIELD_ID,
												"ANSWER_ID"				=> $ANSWER_ID,
												"ANSWER_TEXT"			=> trim($arAnswer["MESSAGE"]),
												"ANSWER_VALUE"			=> $arAnswer["VALUE"],
												"USER_TEXT"				=> $val,
											);
											CFormResult::AddAnswer($arFields);
											$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
											$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
											$arrUSER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["USER_TEXT"]);

										break;

										case "date":

											if (CheckDateTime($val))
											{
												$arFields = array(
													"RESULT_ID"				=> $RESULT_ID,
													"FORM_ID"				=> $WEB_FORM_ID,
													"FIELD_ID"				=> $FIELD_ID,
													"ANSWER_ID"				=> $ANSWER_ID,
													"ANSWER_TEXT"			=> trim($arAnswer["MESSAGE"]),
													"ANSWER_VALUE"			=> $arAnswer["VALUE"],
													"USER_TEXT"				=> $val,
													"USER_DATE"				=> $val
												);
												CFormResult::AddAnswer($arFields);
												$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
												$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
												$arrUSER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["USER_TEXT"]);
											}

										break;

										case "image":

											$arIMAGE = $val;
											if (is_array($arIMAGE) && count($arIMAGE)>0)
											{
												$arIMAGE["MODULE_ID"] = "form";
												if (CFile::CheckImageFile($arIMAGE) == '')
												{
													if (!array_key_exists("MODULE_ID", $arIMAGE) || $arIMAGE["MODULE_ID"] == '')
														$arIMAGE["MODULE_ID"] = "form";

													$fid = CFile::SaveFile($arIMAGE, "form");
													if (intval($fid)>0)
													{
														$arFields = array(
															"RESULT_ID"				=> $RESULT_ID,
															"FORM_ID"				=> $WEB_FORM_ID,
															"FIELD_ID"				=> $FIELD_ID,
															"ANSWER_ID"				=> $ANSWER_ID,
															"ANSWER_TEXT"			=> trim($arAnswer["MESSAGE"]),
															"ANSWER_VALUE"			=> $arAnswer["VALUE"],
															"USER_FILE_ID"			=> $fid,
															"USER_FILE_IS_IMAGE"	=> "Y",
															"USER_FILE_NAME"		=> $arIMAGE["name"],
															"USER_FILE_SIZE"		=> $arIMAGE["size"],
															"USER_TEXT"				=> $arIMAGE["name"]
															);
														CFormResult::AddAnswer($arFields);
														$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
														$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
														$arrUSER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["USER_TEXT"]);
													}
												}
											}

										break;

										case "file":

											$arFILE = $val;
											if (is_array($arFILE) && count($arFILE)>0)
											{
												$arFILE["MODULE_ID"] = "form";
												$original_name = $arFILE["name"];
												$max_size = COption::GetOptionString("form", "MAX_FILESIZE");
												$upload_dir = COption::GetOptionString("form", "NOT_IMAGE_UPLOAD_DIR");
												$fid = CFile::SaveFile($arFILE, $upload_dir, $max_size);
												if (intval($fid)>0)
												{
													$arFields = array(
														"RESULT_ID"				=> $RESULT_ID,
														"FORM_ID"				=> $WEB_FORM_ID,
														"FIELD_ID"				=> $FIELD_ID,
														"ANSWER_ID"				=> $ANSWER_ID,
														"ANSWER_TEXT"			=> trim($arAnswer["MESSAGE"]),
														"ANSWER_VALUE"			=> $arAnswer["VALUE"],
														"USER_FILE_ID"			=> $fid,
														"USER_FILE_IS_IMAGE"	=> "N",
														"USER_FILE_NAME"		=> $original_name,
														"USER_FILE_HASH"		=> md5(uniqid(mt_rand(), true).time()),
														"USER_FILE_SIZE"		=> $arFILE["size"],
														"USER_FILE_SUFFIX"		=> $suffix,
														"USER_TEXT"				=> $original_name,
														);
													CFormResult::AddAnswer($arFields);
													$arrANSWER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_TEXT"]);
													$arrANSWER_VALUE[$FIELD_ID][] = mb_strtoupper($arFields["ANSWER_VALUE"]);
													$arrUSER_TEXT[$FIELD_ID][] = mb_strtoupper($arFields["USER_TEXT"]);
												}
											}

										break;

									endswitch;
								}
							}
							// update search fields
							$arrANSWER_TEXT_upd = $arrANSWER_TEXT[$FIELD_ID];
							$arrANSWER_VALUE_upd = $arrANSWER_VALUE[$FIELD_ID];
							$arrUSER_TEXT_upd = $arrUSER_TEXT[$FIELD_ID];
							TrimArr($arrANSWER_TEXT_upd);
							TrimArr($arrANSWER_VALUE_upd);
							TrimArr($arrUSER_TEXT_upd);
							if (is_array($arrANSWER_TEXT_upd)) $vl_ANSWER_TEXT = trim(implode(" ",$arrANSWER_TEXT_upd));
							if (is_array($arrANSWER_VALUE_upd)) $vl_ANSWER_VALUE = trim(implode(" ",$arrANSWER_VALUE_upd));
							if (is_array($arrUSER_TEXT_upd)) $vl_USER_TEXT = trim(implode(" ",$arrUSER_TEXT_upd));
							if ($vl_ANSWER_TEXT == '') $vl_ANSWER_TEXT = false;
							if ($vl_ANSWER_VALUE == '') $vl_ANSWER_VALUE = false;
							if ($vl_USER_TEXT == '') $vl_USER_TEXT = false;
							$arFields = array(
								"ANSWER_TEXT_SEARCH"	=> $vl_ANSWER_TEXT,
								"ANSWER_VALUE_SEARCH"	=> $vl_ANSWER_VALUE,
								"USER_TEXT_SEARCH"		=> $vl_USER_TEXT
								);
							CFormResult::UpdateField($arFields, $RESULT_ID, $FIELD_ID);
						}
					}
					return true;
				}
			}
		}
		return false;
	}

	// delete result
	public static function Delete($RESULT_ID, $CHECK_RIGHTS="Y")
	{
//		echo $RESULT_ID; exit();
		global $DB, $USER, $APPLICATION, $strError;

		$strError = '';

		$RESULT_ID = intval($RESULT_ID);
		$strSql = "SELECT FORM_ID FROM b_form_result WHERE ID='".$RESULT_ID."'";
		$q = $DB->Query($strSql);
		if ($qr = $q->Fetch())
		{
			// rights check
			$F_RIGHT = ($CHECK_RIGHTS!="Y") ? 20 : CForm::GetPermission($qr["FORM_ID"]);
			if ($F_RIGHT>=20) $RIGHT_OK = "Y";
			else
			{
				$strSql = "SELECT USER_ID FROM b_form_result WHERE ID='".$RESULT_ID."'";
				$z = $DB->Query($strSql);
				$zr = $z->Fetch();
				if ($F_RIGHT>=15 && intval($USER->GetID())==$zr["USER_ID"]) $RIGHT_OK = "Y";
			}

			if ($RIGHT_OK=="Y")
			{
				// rights check by status
				if ($CHECK_RIGHTS == 'Y')
				{
					$arrRESULT_PERMISSION = CFormResult::GetPermissions($RESULT_ID);
					$RIGHT_OK = in_array("DELETE", $arrRESULT_PERMISSION) ? 'Y' : 'N';
				}

				if ($RIGHT_OK=="Y") // delete rights ok
				{
					foreach (GetModuleEvents('form', 'onBeforeResultDelete', true) as $arEvent)
					{
						ExecuteModuleEventEx($arEvent, array($qr["FORM_ID"], $RESULT_ID, $CHECK_RIGHTS));

						if ($ex = $APPLICATION->GetException())
						{
							$strError .= $ex->GetString().'<br />';
							$APPLICATION->ResetException();
						}
					}

					if ($strError == '')
					{
						CForm::ExecHandlerBeforeChangeStatus($RESULT_ID, "DELETE");
						if (CFormResult::Reset($RESULT_ID, true, "Y"))
						{
							// delete result
							$DB->Query("DELETE FROM b_form_result WHERE ID='$RESULT_ID'");
							return true;
						}
					}
				}
			}
			else $strError .= GetMessage("FORM_ERROR_ACCESS_DENIED")."<br>";
		}
		else $strError .= GetMessage("FORM_ERROR_RESULT_NOT_FOUND")."<br>";
		return false;
	}

	// clear result
	public static function Reset($RESULT_ID, $DELETE_FILES=true, $DELETE_ADDITIONAL="N", $arrException=array())
	{
		global $DB, $strError;
		$RESULT_ID = intval($RESULT_ID);
		$strExc = '';

		if (is_array($arrException) && count($arrException)>0)
		{
			foreach ($arrException as $field_id)
			{
				$strExc .= ($strExc === '' ? '' : "','").intval($field_id);
			}
		}

		if ($DELETE_FILES)
		{
			$sqlExc = "";
			if ($strExc <> '') $sqlExc = " and FIELD_ID not in ('$strExc') ";
			// delete result files
			$strSql = "SELECT USER_FILE_ID, ANSWER_ID FROM b_form_result_answer WHERE RESULT_ID='$RESULT_ID' and USER_FILE_ID>0 $sqlExc";
			$z = $DB->Query($strSql);
			while ($zr = $z->Fetch()) CFile::Delete($zr["USER_FILE_ID"]);
		}

		if ($DELETE_ADDITIONAL=="Y")
		{
			$sqlExc = "";
			if ($strExc <> '') $sqlExc = " and FIELD_ID not in ('$strExc') ";
			$DB->Query("DELETE FROM b_form_result_answer WHERE RESULT_ID='$RESULT_ID' $sqlExc");
		}
		else
		{
			$sqlExc = "";
			if ($strExc <> '') $sqlExc = "and F.ID not in ('".$strExc."'')";
			$strSql = "
				SELECT
					F.ID
				FROM
					b_form_result R,
					b_form_field F
				WHERE
					R.ID = $RESULT_ID
				and F.FORM_ID = R.FORM_ID
				and F.ADDITIONAL = 'N'
				$sqlExc
				";
			$z = $DB->Query($strSql);
			while ($zr=$z->Fetch()) $arrD[] = $zr["ID"];
			if (is_array($arrD) && count($arrD)>0) $strD = implode(",",$arrD);
			if ($strD <> '')
			{
				$DB->Query("DELETE FROM b_form_result_answer WHERE RESULT_ID='$RESULT_ID' and FIELD_ID in ($strD)");
			}
		}
		return true;
	}

	// update result status
	public static function SetStatus($RESULT_ID, $NEW_STATUS_ID, $CHECK_RIGHTS="Y")
	{
		global $DB, $USER, $strError, $APPLICATION;
		$NEW_STATUS_ID = intval($NEW_STATUS_ID);
		$RESULT_ID = intval($RESULT_ID);

		if ($RESULT_ID <= 0 || $NEW_STATUS_ID <= 0)
			return false;

		$strSql = "SELECT USER_ID, FORM_ID FROM b_form_result WHERE ID='".$RESULT_ID."'";
		$z = $DB->Query($strSql);
		if ($zr = $z->Fetch())
		{
			$WEB_FORM_ID = intval($zr["FORM_ID"]);

			// rights check
			$RIGHT_OK = "N";
			if ($CHECK_RIGHTS!="Y")
			{
				$dbRes = CFormStatus::GetByID($NEW_STATUS_ID);
				if ($dbRes->Fetch())
				{
					$RIGHT_OK="Y";
				}
			}
			else
			{
				// form rights
				$F_RIGHT = CForm::GetPermission($WEB_FORM_ID);
				if ($F_RIGHT>=20 || ($F_RIGHT>=15 && $USER->GetID()==$zr["USER_ID"]))
				{
					// result rights
					$arrRESULT_PERMISSION = CFormResult::GetPermissions($RESULT_ID);

					// new status rights
					$arrNEW_STATUS_PERMISSION = CFormStatus::GetPermissions($NEW_STATUS_ID);

					if (in_array("EDIT", $arrRESULT_PERMISSION) && in_array("MOVE", $arrNEW_STATUS_PERMISSION))
					{
						$RIGHT_OK = "Y";
					}
				}
			}

			if ($RIGHT_OK=="Y")
			{
				foreach (GetModuleEvents('form', 'onBeforeResultStatusChange', true) as $arEvent)
				{
					ExecuteModuleEventEx($arEvent, array($WEB_FORM_ID, $RESULT_ID, &$NEW_STATUS_ID, $CHECK_RIGHTS));

					if ($ex = $APPLICATION->GetException())
						$strError .= $ex->GetString().'<br />';
				}

				if ($strError == '')
				{
					// call handler before change status
					CForm::ExecHandlerBeforeChangeStatus($RESULT_ID, "SET_STATUS", $NEW_STATUS_ID);
					$arFields = Array(
						"TIMESTAMP_X"	=> $DB->GetNowFunction(),
						"STATUS_ID"		=> "'".intval($NEW_STATUS_ID)."'"
						);
					$DB->Update("b_form_result",$arFields,"WHERE ID='".$RESULT_ID."'");

					foreach (GetModuleEvents('form', 'onAfterResultStatusChange', true) as $arEvent)
					{
						ExecuteModuleEventEx($arEvent, array($WEB_FORM_ID, $RESULT_ID, $NEW_STATUS_ID, $CHECK_RIGHTS));
					}

					// call handler after change status
					CForm::ExecHandlerAfterChangeStatus($RESULT_ID, "SET_STATUS");
					return true;
				}
			}
			else $strError .= GetMessage("FORM_ERROR_ACCESS_DENIED")."<br>";
		}
		else $strError .= GetMessage("FORM_ERROR_RESULT_NOT_FOUND")."<br>";
		return false;
	}

	//send form event notification;
	public static function Mail($RESULT_ID, $TEMPLATE_ID = false)
	{
		global $APPLICATION, $DB, $MESS, $strError;

		$RESULT_ID = intval($RESULT_ID);

		CTimeZone::Disable();
		$arrResult = CFormResult::GetDataByID($RESULT_ID, array(), $arrRES, $arrANSWER);
		CTimeZone::Enable();
		if ($arrResult)
		{
			$z = CForm::GetByID($arrRES["FORM_ID"]);
			if ($arrFORM = $z->Fetch())
			{
				$TEMPLATE_ID = intval($TEMPLATE_ID);

				$arrFormSites = CForm::GetSiteArray($arrRES["FORM_ID"]);
				$arrFormSites = (is_array($arrFormSites)) ? $arrFormSites : array();

				if (!defined('SITE_ID') || !in_array(SITE_ID, $arrFormSites))
					return true;

				$rs = CSite::GetList("sort", "asc", array('ID' => implode('|', $arrFormSites)));
				$arrSites = array();
				while ($ar = $rs->Fetch())
				{
					if ($ar["DEF"]=="Y") $def_site_id = $ar["ID"];
					$arrSites[$ar["ID"]] = $ar;
				}

				$arrFormTemplates = CForm::GetMailTemplateArray($arrRES["FORM_ID"]);
				$arrFormTemplates = (is_array($arrFormTemplates)) ? $arrFormTemplates : array();

				$arrTemplates = array();
				$rs = CEventMessage::GetList("id", "asc", array(
					"ACTIVE"		=> "Y",
					"SITE_ID"		=> SITE_ID,
					"EVENT_NAME"	=> $arrFORM["MAIL_EVENT_TYPE"]
				));

				while ($ar = $rs->Fetch())
				{
					if ($TEMPLATE_ID>0)
					{
						if ($TEMPLATE_ID == $ar["ID"])
						{
							$arrTemplates[$ar["ID"]] = $ar;
							break;
						}
					}
					elseif (in_array($ar["ID"],$arrFormTemplates)) $arrTemplates[$ar["ID"]] = $ar;
				}

				foreach($arrTemplates as $arrTemplate)
				{

					$OLD_MESS = $MESS;
					$MESS = array();
					IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/admin/form_mail.php", $arrSites[$arrTemplate["SITE_ID"]]["LANGUAGE_ID"]);

					$USER_AUTH = " ";
					if (intval($arrRES["USER_ID"])>0)
					{
						$w = CUser::GetByID($arrRES["USER_ID"]);
						$arrUSER = $w->Fetch();
						$USER_ID = $arrUSER["ID"];
						$USER_EMAIL = $arrUSER["EMAIL"];
						$USER_NAME = $arrUSER["NAME"]." ".$arrUSER["LAST_NAME"];
						if ($arrRES["USER_AUTH"]!="Y") $USER_AUTH="(".GetMessage("FORM_NOT_AUTHORIZED").")";
					}
					else
					{
						$USER_ID = GetMessage("FORM_NOT_REGISTERED");
						$USER_NAME = "";
						$USER_AUTH = "";
						$USER_EMAIL = "";
					}

					$arEventFields = array(
						"RS_FORM_ID"			=> $arrFORM["ID"],
						"RS_FORM_NAME"			=> $arrFORM["NAME"],
						"RS_FORM_VARNAME"		=> $arrFORM["SID"],
						"RS_FORM_SID"			=> $arrFORM["SID"],
						"RS_RESULT_ID"			=> $arrRES["ID"],
						"RS_DATE_CREATE"		=> $arrRES["DATE_CREATE"],
						"RS_USER_ID"			=> $USER_ID,
						"RS_USER_EMAIL"			=> $USER_EMAIL,
						"RS_USER_NAME"			=> $USER_NAME,
						"RS_USER_AUTH"			=> $USER_AUTH,
						"RS_STAT_GUEST_ID"		=> $arrRES["STAT_GUEST_ID"],
						"RS_STAT_SESSION_ID"	=> $arrRES["STAT_SESSION_ID"]
						);
					$w = CFormField::GetList($arrFORM["ID"], "ALL");
					while ($wr=$w->Fetch())
					{
						$answer = "";
						$answer_raw = '';
						if (is_array($arrResult[$wr["SID"]]))
						{
							$bHasDiffTypes = false;
							$lastType = '';
							foreach ($arrResult[$wr['SID']] as $arrA)
							{
								if ($lastType == '') $lastType = $arrA['FIELD_TYPE'];
								elseif ($arrA['FIELD_TYPE'] != $lastType)
								{
									$bHasDiffTypes = true;
									break;
								}
							}

							foreach($arrResult[$wr["SID"]] as $arrA)
							{
								if ($wr['ADDITIONAL'] == 'Y')
									$arrA['FIELD_TYPE'] = $wr['FIELD_TYPE'];

								$USER_TEXT_EXIST = (trim($arrA["USER_TEXT"]) <> '');
								$ANSWER_TEXT_EXIST = (trim($arrA["ANSWER_TEXT"]) <> '');
								$ANSWER_VALUE_EXIST = (trim($arrA["ANSWER_VALUE"]) <> '');
								$USER_FILE_EXIST = (intval($arrA["USER_FILE_ID"])>0);

								if ($arrTemplate["BODY_TYPE"]=="html")
								{
									if (
										$bHasDiffTypes
										&&
										!$USER_TEXT_EXIST
										&&
										(
											$arrA['FIELD_TYPE'] == 'text'
											||
											$arrA['FIELD_TYPE'] == 'textarea'
										)
									)
										continue;

									if (trim($answer) <> '') $answer .= "<br />";
									if (trim($answer_raw) <> '') $answer_raw .= ",";

									if ($ANSWER_TEXT_EXIST)
										$answer .= $arrA["ANSWER_TEXT"].': ';

									switch ($arrA['FIELD_TYPE'])
									{
										case 'text':
										case 'textarea':
										case 'hidden':
										case 'date':
										case 'password':
										case 'integer':

											if ($USER_TEXT_EXIST)
											{
												$answer .= trim($arrA["USER_TEXT"]);
												$answer_raw .= trim($arrA["USER_TEXT"]);
											}

										break;

										case 'email':
										case 'url':

											if ($USER_TEXT_EXIST)
											{
												$answer .= '<a href="'.($arrA['FIELD_TYPE'] == 'email' ? 'mailto:' : '').trim($arrA["USER_TEXT"]).'">'.trim($arrA["USER_TEXT"]).'</a>';
												$answer_raw .= trim($arrA["USER_TEXT"]);
											}

										break;

										case 'checkbox':
										case 'multiselect':
										case 'radio':
										case 'dropdown':

											if ($ANSWER_TEXT_EXIST)
											{
												$answer = mb_substr($answer, 0, -2).' ';
												$answer_raw .= $arrA['ANSWER_TEXT'];
											}

											if ($ANSWER_VALUE_EXIST)
											{
												$answer .= '('.$arrA['ANSWER_VALUE'].') ';
												if (!$ANSWER_TEXT_EXIST)
													$answer_raw .= $arrA['ANSWER_VALUE'];
											}

											if (!$ANSWER_VALUE_EXIST && !$ANSWER_TEXT_EXIST)
												$answer_raw .= $arrA['ANSWER_ID'];

											$answer .= '['.$arrA['ANSWER_ID'].']';

										break;

										case 'file':
										case 'image':

											if ($USER_FILE_EXIST)
											{
												$f = CFile::GetByID($arrA["USER_FILE_ID"]);
												if ($fr = $f->Fetch())
												{
													$file_size = CFile::FormatSize($fr["FILE_SIZE"]);
													$url = ($APPLICATION->IsHTTPS() ? "https://" : "http://").$_SERVER["HTTP_HOST"]. "/bitrix/tools/form_show_file.php?rid=".$RESULT_ID. "&hash=".$arrA["USER_FILE_HASH"]."&lang=".LANGUAGE_ID;

													if ($arrA["USER_FILE_IS_IMAGE"]=="Y")
													{
														$answer .= "<a href=\"$url\">".$arrA["USER_FILE_NAME"]."</a> [".$fr["WIDTH"]." x ".$fr["HEIGHT"]."] (".$file_size.")";
													}
													else
													{
														$answer .= "<a href=\"$url&action=download\">".$arrA["USER_FILE_NAME"]."</a> (".$file_size.")";
													}

													$answer_raw .= $arrA['USER_FILE_NAME'];
												}
											}

										break;
									}
								}
								else
								{
									if (
										$bHasDiffTypes
										&&
										!$USER_TEXT_EXIST
										&&
										(
											$arrA['FIELD_TYPE'] == 'text'
											||
											$arrA['FIELD_TYPE'] == 'textarea'
										)
									)
										continue;

									if (trim($answer) <> '') $answer .= "\n";
									if (trim($answer_raw) <> '') $answer_raw .= ",";

									if ($ANSWER_TEXT_EXIST)
										$answer .= $arrA["ANSWER_TEXT"].': ';

									switch ($arrA['FIELD_TYPE'])
									{
										case 'text':
										case 'textarea':
										case 'email':
										case 'url':
										case 'hidden':
										case 'date':
										case 'password':
										case 'integer':

											if ($USER_TEXT_EXIST)
											{
												$answer .= trim($arrA["USER_TEXT"]);
												$answer_raw .= trim($arrA["USER_TEXT"]);
											}

										break;

										case 'checkbox':
										case 'multiselect':
										case 'radio':
										case 'dropdown':

											if ($ANSWER_TEXT_EXIST)
											{
												$answer = mb_substr($answer, 0, -2).' ';
												$answer_raw .= $arrA['ANSWER_TEXT'];
											}

											if ($ANSWER_VALUE_EXIST)
											{
												$answer .= '('.$arrA['ANSWER_VALUE'].') ';
												if (!$ANSWER_TEXT_EXIST)
												{
													$answer_raw .= $arrA['ANSWER_VALUE'];
												}
											}

											if (!$ANSWER_VALUE_EXIST && !$ANSWER_TEXT_EXIST)
											{
												$answer_raw .= $arrA['ANSWER_ID'];
											}

											$answer .= '['.$arrA['ANSWER_ID'].']';

										break;

										case 'file':
										case 'image':

											if ($USER_FILE_EXIST)
											{
												$f = CFile::GetByID($arrA["USER_FILE_ID"]);
												if ($fr = $f->Fetch())
												{
													$file_size = CFile::FormatSize($fr["FILE_SIZE"]);
													$url = ($APPLICATION->IsHTTPS() ? "https://" : "http://").$_SERVER["HTTP_HOST"]. "/bitrix/tools/form_show_file.php?rid=".$RESULT_ID. "&hash=".$arrA["USER_FILE_HASH"]."&action=download&lang=".LANGUAGE_ID;

													if ($arrA["USER_FILE_IS_IMAGE"]=="Y")
													{
														$answer .= $arrA["USER_FILE_NAME"]." [".$fr["WIDTH"]." x ".$fr["HEIGHT"]."] (".$file_size.")\n".$url;
													}
													else
													{
														$answer .= $arrA["USER_FILE_NAME"]." (".$file_size.")\n".$url."&action=download";
													}
												}

												$answer_raw .= $arrA['USER_FILE_NAME'];
											}

										break;
									}
								}
							}
						}

						$arEventFields[$wr["SID"]] = ($answer == '') ? " " : $answer;
						$arEventFields[$wr["SID"].'_RAW'] = ($answer_raw == '') ? " " : $answer_raw;
					}

					CEvent::Send($arrTemplate["EVENT_NAME"], $arrTemplate["SITE_ID"], $arEventFields, "Y", $arrTemplate["ID"]);
					$MESS = $OLD_MESS;
				} //foreach($arrTemplates as $arrTemplate)
				return true;
			}
			else $strError .= GetMessage("FORM_ERROR_FORM_NOT_FOUND")."<br>";
		}
		else $strError .= GetMessage("FORM_ERROR_RESULT_NOT_FOUND")."<br>";
		return false;
	}

	public static function GetCount($WEB_FORM_ID)
	{
		global $DB;
		$strSql = "SELECT count(ID) C FROM b_form_result WHERE FORM_ID=".intval($WEB_FORM_ID);
		$z = $DB->Query($strSql);
		$zr = $z->Fetch();
		return intval($zr["C"]);
	}

	// prepare array of parameters for result filter
	public static function PrepareFilter($WEB_FORM_ID, $arFilter)
	{
		$arrFilterReturn = $arFilter;

		if (array_key_exists("FIELDS", $arFilter))
		{
			$arFilterFields = $arFilter["FIELDS"];

			$rsForm = CForm::GetByID($WEB_FORM_ID);
			$arForm = $rsForm->Fetch();

			if (is_array($arFilterFields) && count($arFilterFields) > 0)
			{
				foreach ($arFilterFields as $arr)
				{
					if ($arr["SID"] <> '')
						$arr["CODE"] = $arr["SID"];
					else
						$arr["SID"] = $arr["CODE"];

					$FIELD_SID = $arr["SID"];

					$FILTER_TYPE = ($arr["FILTER_TYPE"] <> '') ? $arr["FILTER_TYPE"] : "text";

					if ($FILTER_TYPE == "answer_id")
					{
						$PARAMETER_NAME = "ANSWER_ID";
					}
					else
					{
						$PARAMETER_NAME = ($arr["PARAMETER_NAME"] <> '') ? $arr["PARAMETER_NAME"] : "USER";
					}

					$PART = $arr["PART"];

					$FILTER_KEY = $arForm["SID"]."_".$FIELD_SID."_".$PARAMETER_NAME."_".$FILTER_TYPE;
					if ($PART <> '') $FILTER_KEY .= "_".intval($PART);

					$arrFilterReturn[$FILTER_KEY] = $arr["VALUE"];

					if ($FILTER_TYPE=="text")
					{
						$EXACT_MATCH = ($arr["EXACT_MATCH"]=="Y") ? "Y" : "N";
						$arrFilterReturn[$FILTER_KEY."_exact_match"] = $EXACT_MATCH;
					}
				}
			}
			unset($arrFilterReturn["FIELDS"]);
		}
		return $arrFilterReturn;
	}

	public static function SetCRMFlag($RESULT_ID, $flag_value)
	{
		return $GLOBALS['DB']->Query("UPDATE b_form_result SET SENT_TO_CRM='".($flag_value == 'N' ? 'N' : 'Y')."' WHERE ID='".intval($RESULT_ID)."'");
	}
}
