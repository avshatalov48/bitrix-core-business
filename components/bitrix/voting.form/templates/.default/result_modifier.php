<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (empty($arResult["VOTE"])):
	return false;
elseif (empty($arResult["QUESTIONS"])):
	return true;
endif;

foreach ($arResult["QUESTIONS"] as $questionKey => $arQuestion):
	$bFountActive = false;
	$FirstAnswerKey = false;
	$ActiveAnswerKey = false;
	foreach ($arQuestion["ANSWERS"] as $answerKey => $arAnswer):
		if ($FirstAnswerKey === false):
			$FirstAnswerKey = $answerKey;
		endif;
		$arAnswer["FIELD_PARAM"] = ($arAnswer["FIELD_PARAM"] <> '' ? $arAnswer["FIELD_PARAM"] : "");
		switch ($arAnswer["FIELD_TYPE"]):
			case 0:
				if (isset($_REQUEST["vote_radio_".$arAnswer["QUESTION_ID"]]) && $_REQUEST["vote_radio_".$arAnswer["QUESTION_ID"]] == $arAnswer["ID"]):
					$bFountActive = true;
					$arAnswer["FIELD_PARAM"] .= " checked='checked' ";
				endif;
			break;
			case 1://checkbox
				if (!is_array($_REQUEST["vote_checkbox_".$arAnswer["QUESTION_ID"]])):
				elseif (in_array($arAnswer["ID"], $_REQUEST["vote_checkbox_".$arAnswer["QUESTION_ID"]])):
					$bFountActive = true;
					$arAnswer["FIELD_PARAM"] .= " checked='checked' ";
				endif;
			break; 
			case 2://dropdown
				if (!is_set($_REQUEST, "vote_dropdown_".$arAnswer["QUESTION_ID"])):
				else:
					foreach ($arAnswer["DROPDOWN"] as $key => $arDropDown):
						if ($_REQUEST["vote_dropdown_".$arAnswer["QUESTION_ID"]] == $arDropDown["ID"]):
							$bFountActive = true;
							$arAnswer["DROPDOWN"][$key]["FIELD_PARAM"] = " selected='selected' ";
							break;
						endif;
					endforeach; 
				endif;
			break;
			case 3://multiselect
				if (!is_array($_REQUEST["vote_multiselect_".$arAnswer["QUESTION_ID"]])):
				else:
					foreach ($arAnswer["MULTISELECT"] as $key => $arMultiSelect):
						if (in_array($arDropDown["ID"], $_REQUEST["vote_multiselect_".$arAnswer["QUESTION_ID"]])):
							$bFountActive = true;
							$arAnswer["MULTISELECT"][$key]["FIELD_PARAM"] = " selected='selected' ";
							break;
						endif;
					endforeach; 
				endif;
			break; 
			case 4://text field
				if (!empty($_REQUEST["vote_field_".$arAnswer["ID"]])):
					$bFountActive = true;
					$arAnswer["FIELD_TEXT"] = htmlspecialcharsbx($_REQUEST["vote_field_".$arAnswer["ID"]]);
				endif;
			break;
			case 5://memo
				if (!empty($_REQUEST["vote_memo_".$arAnswer["ID"]])):
					$bFountActive = true;
					$arAnswer["FIELD_TEXT"] = htmlspecialcharsbx($_REQUEST["vote_memo_".$arAnswer["ID"]]);
				endif;
			break;
		endswitch;
		if ($bFountActive):
			$arResult["QUESTIONS"][$questionKey]["ANSWERS"][$answerKey] = $arAnswer;
			break;
		endif;
	endforeach; 
	if (!$bFountActive && $FirstAnswerKey !== false):
		$arAnswer = $arResult["QUESTIONS"][$questionKey]["ANSWERS"][$FirstAnswerKey];
		$arAnswer["FIELD_PARAM"] = ($arAnswer["FIELD_PARAM"] <> '' ? $arAnswer["FIELD_PARAM"] : "");
		switch ($arAnswer["FIELD_TYPE"]):
			case 0:
				$arAnswer["FIELD_PARAM"] .= " checked='checked' ";
			break;
		endswitch;
		$arResult["QUESTIONS"][$questionKey]["ANSWERS"][$FirstAnswerKey] = $arAnswer;
	endif;
endforeach;
?>