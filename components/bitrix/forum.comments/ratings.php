<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
include_once __DIR__."/base.php";
class CCommentRatings extends CCommentBase
{
	var $arRatings = array();
	var $display = array("BEFORE_HEADER" => true, "AFTER_ACTIONS" => false);

	function OnCommentsDisplayTemplate($output, $arParams, $arResult)
	{
		if (empty($arResult["MESSAGES"]))
		{
			$GLOBALS["APPLICATION"]->SetAdditionalCSS("/bitrix/components/bitrix/rating.vote/templates/like/popup.css");
			$GLOBALS["APPLICATION"]->SetAdditionalCSS("/bitrix/components/bitrix/rating.vote/templates/".$arParams["RATING_TYPE"]."/style.css");
		}
	}

	function OnPrepareComments($component)
	{
		if ($component !== $this->component)
		{
			return;
		}
		$arResult =& $this->component->arResult;

		$arMessages =& $arResult['MESSAGES'];

		$arMessageIDs = array_keys($arMessages);
		$arRatings = CRatings::GetRatingVoteResult('FORUM_POST', $arMessageIDs);
		if ($arRatings)
		{
			foreach($arRatings as $postID => $arRating)
			{
				$this->arRatings[$postID] = $arRating;
				if (array_key_exists($postID, $arMessages))
					$arMessages[$postID]["RATING"] = $arRating;
			}
		}
	}

	function RatingDisplay($top = true, $commentID, $authorID)
	{
		$arParams = &$this->component->arParams;
		static $arEmptyRating = array(
			"USER_VOTE" => 0,
			"USER_HAS_VOTED" => 'N',
			"TOTAL_VOTES" => 0,
			"TOTAL_POSITIVE_VOTES" => 0,
			"TOTAL_NEGATIVE_VOTES" => 0,
			"TOTAL_VALUE" => 0
		);

		ob_start();
			if ($top)
			{
				?>
				<div class="review-rating rating_vote_graphic">
				<?
			}
			else
			{
				?>
				<span class="rating_vote_text">
				<span class="separator"></span>
				<?
			}
					$arRatingParams = Array(
							"ENTITY_TYPE_ID" => "FORUM_POST",
							"ENTITY_ID" => $commentID,
							"OWNER_ID" => $authorID,
							"PATH_TO_USER_PROFILE" => $arParams["~URL_TEMPLATES_PROFILE_VIEW"],
							"AJAX_MODE" => "N"
						);
					if (isset($this->arRatings[$commentID]))
						$arRating = $this->arRatings[$commentID];
					else
						$arRating = $arEmptyRating;
					$arRatingParams = array_merge($arRatingParams, $arRating);
					$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:rating.vote", $arParams["RATING_TYPE"], $arRatingParams, $this->component, array("HIDE_ICONS" => "Y"));
			if ($top)
			{
				?>
				</div>
				<?
			}
			else
			{
				?>
				</span>
				<?
			}
		return ob_get_clean();
	}

	function OnCommentDisplay($arComment)
	{
		$arReturn = array();
		foreach ($this->display as $display => $graphic)
		{
			$arReturn[] = array('DISPLAY' => $display, 'SORT' => '50', 'TEXT' => $this->RatingDisplay($graphic, $arComment['ID'], $arComment['AUTHOR_ID']));
		}
		return $arReturn;
	}
}
?>
