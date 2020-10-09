<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
	$arParams['RATING_ID'] 		= intval($arParams['RATING_ID']);
	$arParams['ENTITY_ID'] 		= intval($arParams['ENTITY_ID']);
	if (isset($arParams['LINK']))
		$arResult['LINK'] = $arParams['LINK'];

	$arResult["SHOW_RATING_NAME"] = 'Y';
	if (isset($arParams['SHOW_RATING_NAME']) && $arParams['SHOW_RATING_NAME'] == 'N')
	{
		$arResult["SHOW_RATING_NAME"] = 'N';
	} 
	else 
	{
		if(isset($arParams['RATING_NAME']))
		{
			$arResult["RATING_NAME"] = $arParams["RATING_NAME"];
		}
		else 
		{
			if ($arParams['RATING_ID'] > 0)
			{
				$arRating = CRatings::GetArrayByID($arParams['RATING_ID']);
				if ($arRating = CRatings::GetArrayByID($arParams['RATING_ID']))
					$arResult['RATING_NAME'] = $arRating['NAME'];
				else
					$arParams['TEMPLATE_HIDE'] = 'Y';
			}
			else
				$arResult['RATING_NAME'] = GetMessage('RATING_NAME');
		}
	}
	
	if (isset($arParams['RESULT_TYPE']) && $arParams['RESULT_TYPE'] == 'POSITION')
	{
		$arResult['RESULT_TYPE'] = 'POSITION';
		
		if(isset($arParams['CURRENT_POSITION']))
			$arResult["CURRENT_POSITION"] = intval($arParams["CURRENT_POSITION"]);
		
		if(isset($arParams['PREVIOUS_POSITION']))
			$arResult["PREVIOUS_POSITION"] = intval($arParams["PREVIOUS_POSITION"]);
		
		if (!isset($arParams['CURRENT_POSITION']) || !isset($arParams['PREVIOUS_POSITION']))
		{
			$arComponentRatingResult = CRatings::GetRatingResult($arParams["RATING_ID"] , $arParams['ENTITY_ID']);
			$arResult['CURRENT_POSITION']  = (array_key_exists('CURRENT_POSITION', $arComponentRatingResult) ? $arComponentRatingResult['CURRENT_POSITION'] : 0);
			$arResult['PREVIOUS_POSITION']  = (array_key_exists('PREVIOUS_POSITION', $arComponentRatingResult) ? $arComponentRatingResult['PREVIOUS_POSITION'] : 0);
		}
		
		$arResult['PROGRESS_POSITION'] = $arResult['PREVIOUS_POSITION'] - $arResult['CURRENT_POSITION'];
		
		if ($arResult['PROGRESS_POSITION'] > 0) 
		{
			$arResult['PROGRESS_POSITION'] = $arResult['PREVIOUS_POSITION'] - $arResult['CURRENT_POSITION'];
			$arResult['PROGRESS_POSITION_DIRECT'] = 'up';
		} 
		elseif ($arResult['PROGRESS_POSITION'] < 0) 
		{
			$arResult['PROGRESS_POSITION'] = $arResult['CURRENT_POSITION'] - $arResult['PREVIOUS_POSITION'];
			$arResult['PROGRESS_POSITION_DIRECT'] = 'down';
		} 
		else 
		{
			$arResult['PROGRESS_POSITION_DIRECT'] = 'unchanged';
		}
	} 
	else 
	{
		$arResult['RESULT_TYPE'] = 'VALUE';
				
		if(isset($arParams['CURRENT_VALUE']))
			$arResult["CURRENT_VALUE"] = floatval($arParams["CURRENT_VALUE"]);
		
		if(isset($arParams['PREVIOUS_VALUE']))
			$arResult["PREVIOUS_VALUE"] = floatval($arParams["PREVIOUS_VALUE"]);
			
		if (!isset($arParams['CURRENT_VALUE']) || !isset($arParams['PREVIOUS_VALUE']))
		{
			$arComponentRatingResult = CRatings::GetRatingResult($arParams["RATING_ID"] , $arParams['ENTITY_ID']);
			$arResult['CURRENT_VALUE']  = (array_key_exists('CURRENT_VALUE', $arComponentRatingResult) ? $arComponentRatingResult['CURRENT_VALUE'] : 0);
			$arResult['PREVIOUS_VALUE']  = (array_key_exists('PREVIOUS_VALUE', $arComponentRatingResult) ? $arComponentRatingResult['PREVIOUS_VALUE'] : 0);
		}
		
		if(isset($arParams['PROGRESS_VALUE'])) 
		{
			$arResult['PROGRESS_VALUE'] = $arParams['PROGRESS_VALUE'];
		}
		else
		{
			$arResult['PROGRESS_VALUE'] = $arResult['CURRENT_VALUE'] - $arResult['PREVIOUS_VALUE'];
			$arResult['PROGRESS_VALUE'] = round($arResult['PROGRESS_VALUE'], 2);
			$arResult['PROGRESS_VALUE'] = $arResult['PROGRESS_VALUE'] > 0? "+".$arResult['PROGRESS_VALUE']: $arResult['PROGRESS_VALUE'];
		}
		
		if(isset($arParams['ROUND_CURRENT_VALUE'])) 
			$arResult['ROUND_CURRENT_VALUE'] = $arParams['ROUND_CURRENT_VALUE'];
		else
			$arResult['ROUND_CURRENT_VALUE'] = round($arResult['CURRENT_VALUE']) == 0? 0: round($arResult['CURRENT_VALUE']);
			
		if(isset($arParams['ROUND_PREVIOUS_VALUE'])) 
			$arResult['ROUND_PREVIOUS_VALUE'] = $arParams['ROUND_PREVIOUS_VALUE'];
		else
			$arResult['ROUND_PREVIOUS_VALUE'] = round($arResult['PREVIOUS_VALUE']) == 0? 0: round($arResult['PREVIOUS_VALUE']);
	}
	
	if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
		$this->IncludeComponentTemplate();
	
	return $arResult;
?>