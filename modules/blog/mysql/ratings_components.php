<?php

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/blog/general/ratings_components.php");

class CRatingsComponentsBlog extends CAllRatingsComponentsBlog
{
	public static function CalcPost($arConfigs)
	{
		$connection = \Bitrix\Main\Application::getInstance()->getConnection();
		$helper = $connection->getSqlHelper();

		CRatings::AddComponentResults($arConfigs);

		$strSql = "DELETE FROM b_rating_component_results WHERE RATING_ID = '".intval($arConfigs['RATING_ID'])."' AND COMPLEX_NAME = '".$helper->forSql($arConfigs['COMPLEX_NAME'])."'";
		$connection->queryExecute($strSql);

		$strSql = "
			INSERT INTO b_rating_component_results (RATING_ID, MODULE_ID, RATING_TYPE, NAME, COMPLEX_NAME, ENTITY_ID, ENTITY_TYPE_ID, CURRENT_VALUE)
			SELECT
				'".intval($arConfigs['RATING_ID'])."' as RATING_ID,
				'".$helper->forSql($arConfigs['MODULE_ID'])."' as MODULE_ID,
				'".$helper->forSql($arConfigs['RATING_TYPE'])."' as RATING_TYPE,
				'".$helper->forSql($arConfigs['NAME'])."' as NAME,
				'".$helper->forSql($arConfigs['COMPLEX_NAME'])."' as COMPLEX_NAME,
				FT.AUTHOR_ID as ENTITY_ID,
				'".$helper->forSql($arConfigs['ENTITY_ID'])."' as ENTITY_TYPE_ID,
				SUM(RVE.VALUE) * ".floatval($arConfigs['CONFIG']['COEFFICIENT'])." as CURRENT_VALUE
			FROM
				b_rating_voting RV LEFT JOIN b_blog_post FT ON RV.ENTITY_ID = FT.ID,
				b_rating_vote RVE
			WHERE
				RV.ENTITY_TYPE_ID = 'BLOG_POST' AND FT.AUTHOR_ID > 0
				AND RVE.RATING_VOTING_ID = RV.ID".
				((int)$arConfigs['CONFIG']['LIMIT'] > 0 ? " AND RVE.CREATED > ".$helper->addDaysToDateTime(-1 * (int)$arConfigs['CONFIG']['LIMIT']) : "")."
			GROUP BY AUTHOR_ID
		";
		$connection->queryExecute($strSql);

		return true;
	}

	public static function CalcComment($arConfigs)
	{
		$connection = \Bitrix\Main\Application::getInstance()->getConnection();
		$helper = $connection->getSqlHelper();

		CRatings::AddComponentResults($arConfigs);

		$strSql = "DELETE FROM b_rating_component_results WHERE RATING_ID = '".intval($arConfigs['RATING_ID'])."' AND COMPLEX_NAME = '".$helper->forSql($arConfigs['COMPLEX_NAME'])."'";
		$connection->queryExecute($strSql);

		$strSql = "
			INSERT INTO b_rating_component_results (RATING_ID, MODULE_ID, RATING_TYPE, NAME, COMPLEX_NAME, ENTITY_ID, ENTITY_TYPE_ID, CURRENT_VALUE)
			SELECT
				'".intval($arConfigs['RATING_ID'])."' as RATING_ID,
				'".$helper->forSql($arConfigs['MODULE_ID'])."' as MODULE_ID,
				'".$helper->forSql($arConfigs['RATING_TYPE'])."' as RATING_TYPE,
				'".$helper->forSql($arConfigs['NAME'])."' as NAME,
				'".$helper->forSql($arConfigs['COMPLEX_NAME'])."' as COMPLEX_NAME,
				FM.AUTHOR_ID as ENTITY_ID,
				'".$helper->forSql($arConfigs['ENTITY_ID'])."' as ENTITY_TYPE_ID,
				SUM(RVE.VALUE) * ".floatval($arConfigs['CONFIG']['COEFFICIENT'])." as CURRENT_VALUE
			FROM
				b_rating_voting RV LEFT JOIN b_blog_comment FM ON RV.ENTITY_ID = FM.ID,
				b_rating_vote RVE
			WHERE
				RV.ENTITY_TYPE_ID = 'BLOG_COMMENT' AND FM.AUTHOR_ID > 0
				AND RVE.RATING_VOTING_ID = RV.ID".
				((int)$arConfigs['CONFIG']['LIMIT'] > 0 ? " AND RVE.CREATED > ".$helper->addDaysToDateTime(-1 * (int)$arConfigs['CONFIG']['LIMIT']) : "")."
			GROUP BY AUTHOR_ID
		";
		$connection->queryExecute($strSql);

		return true;
	}

	public static function CalcActivity($arConfigs)
	{
		$connection = \Bitrix\Main\Application::getInstance()->getConnection();
		$helper = $connection->getSqlHelper();

		CRatings::AddComponentResults($arConfigs);

		$strSql = "DELETE FROM b_rating_component_results WHERE RATING_ID = '".intval($arConfigs['RATING_ID'])."' AND COMPLEX_NAME = '".$helper->forSql($arConfigs['COMPLEX_NAME'])."'";
		$connection->queryExecute($strSql);

		$daysDepth = $helper->addDaysToDateTime(-30);

		$sqlAllPost = '';
		if (isset($arConfigs['CONFIG']['ALL_POST_COEF']) && $arConfigs['CONFIG']['ALL_POST_COEF'] != 0)
		{
			$sqlAllPost = "
				SELECT
					AUTHOR_ID as ENTITY_ID,
					COUNT(*) * ".floatval($arConfigs['CONFIG']['ALL_POST_COEF'])." as CURRENT_VALUE
				FROM b_blog_post
				WHERE DATE_PUBLISH < {$daysDepth}
						AND PUBLISH_STATUS = '".BLOG_PUBLISH_STATUS_PUBLISH."'
				GROUP BY AUTHOR_ID
				UNION ALL 
			";
		}
		$sqlAllComment = '';
		if (isset($arConfigs['CONFIG']['ALL_COMMENT_COEF']) && $arConfigs['CONFIG']['ALL_COMMENT_COEF'] != 0)
		{
			$sqlAllComment = "
				SELECT
					AUTHOR_ID as ENTITY_ID,
					COUNT(*) * ".floatval($arConfigs['CONFIG']['ALL_COMMENT_COEF'])." as CURRENT_VALUE
				FROM b_blog_comment
				WHERE DATE_CREATE < {$daysDepth}
					AND PUBLISH_STATUS = '".BLOG_PUBLISH_STATUS_PUBLISH."'
				GROUP BY AUTHOR_ID
				UNION ALL 
			";
		}
		$strSql = "
			INSERT INTO b_rating_component_results (RATING_ID, MODULE_ID, RATING_TYPE, NAME, COMPLEX_NAME, ENTITY_ID, ENTITY_TYPE_ID, CURRENT_VALUE)
			SELECT
				'".intval($arConfigs['RATING_ID'])."' as RATING_ID,
				'".$helper->forSql($arConfigs['MODULE_ID'])."' as MODULE_ID,
				'".$helper->forSql($arConfigs['RATING_TYPE'])."' as RATING_TYPE,
				'".$helper->forSql($arConfigs['NAME'])."' as NAME,
				'".$helper->forSql($arConfigs['COMPLEX_NAME'])."' as COMPLEX_NAME,
				ENTITY_ID,
				'".$helper->forSql($arConfigs['ENTITY_ID'])."' as ENTITY_TYPE_ID,
				SUM(CURRENT_VALUE) as CURRENT_VALUE
			FROM (
				".$sqlAllPost."
				SELECT
					AUTHOR_ID as ENTITY_ID,
					
					SUM(case when ". $helper->formatDate('YYYY-MM-DD', 'DATE_PUBLISH'). " > ". $helper->formatDate('YYYY-MM-DD', $helper->addDaysToDateTime(-1)). " then 1 else 0 end) * ".(float)$arConfigs['CONFIG']['TODAY_POST_COEF']." +
					SUM(case when ". $helper->formatDate('YYYY-MM-DD', 'DATE_PUBLISH'). " > ". $helper->formatDate('YYYY-MM-DD', $helper->addDaysToDateTime(-7)). " then 1 else 0 end) * ".(float)$arConfigs['CONFIG']['WEEK_POST_COEF']." +
					COUNT(*) * ".(float)$arConfigs['CONFIG']['MONTH_POST_COEF']." as CURRENT_VALUE
				FROM b_blog_post
				WHERE DATE_PUBLISH  > {$daysDepth}
						AND PUBLISH_STATUS = '".BLOG_PUBLISH_STATUS_PUBLISH."'
				GROUP BY AUTHOR_ID

				UNION ALL
				".$sqlAllComment."
				SELECT
					AUTHOR_ID as ENTITY_ID,
					SUM(case when ". $helper->formatDate('YYYY-MM-DD', 'DATE_CREATE'). " > ". $helper->formatDate('YYYY-MM-DD', $helper->addDaysToDateTime(-1)). " then 1 else 0 end) * ".(float)$arConfigs['CONFIG']['TODAY_COMMENT_COEF']." +
					SUM(case when ". $helper->formatDate('YYYY-MM-DD', 'DATE_CREATE'). " > ". $helper->formatDate('YYYY-MM-DD', $helper->addDaysToDateTime(-7)). " then 1 else 0 end) * ".(float)$arConfigs['CONFIG']['WEEK_COMMENT_COEF']." +
					COUNT(*) * ".(float)$arConfigs['CONFIG']['MONTH_COMMENT_COEF']." as CURRENT_VALUE
				FROM b_blog_comment
				WHERE DATE_CREATE  > {$daysDepth}
					AND PUBLISH_STATUS = '".BLOG_PUBLISH_STATUS_PUBLISH."'
				GROUP BY AUTHOR_ID
			) q
			WHERE ENTITY_ID > 0
			GROUP BY ENTITY_ID
		";

		$connection->queryExecute($strSql);

		return true;
	}
}
