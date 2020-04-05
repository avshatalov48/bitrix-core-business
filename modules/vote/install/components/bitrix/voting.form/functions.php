<?
if (!function_exists("_GetAnswerArray"))
{
	function _GetAnswerArray($QuestionID, $FieldType, &$arAnswers)
	{
		$arReturn = Array();
		if (array_key_exists($QuestionID, $arAnswers))
		{
			foreach ($arAnswers[$QuestionID] as $arAnswer)
			{
				if ($arAnswer["FIELD_TYPE"] == $FieldType)
					$arReturn[] = $arAnswer;
			}
		}
		return $arReturn;
	}
}
?>