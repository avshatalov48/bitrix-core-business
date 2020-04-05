<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if (!function_exists('getDoublePicturesForItem'))
{
	function getDoublePicturesForItem(&$item, $propertyCode)
	{
		$result = array(
			'PICT' => false,
			'SECOND_PICT' => false
		);

		if (!empty($item) && is_array($item))
		{
			if (!empty($item['PREVIEW_PICTURE']))
			{
				if (!is_array($item['PREVIEW_PICTURE']))
					$item['PREVIEW_PICTURE'] = CFile::GetFileArray($item['PREVIEW_PICTURE']);
				if (isset($item['PREVIEW_PICTURE']['ID']))
				{
					$result['PICT'] = array(
						'ID' => intval($item['PREVIEW_PICTURE']['ID']),
						'SRC' => $item['PREVIEW_PICTURE']['SRC'],
						'WIDTH' => intval($item['PREVIEW_PICTURE']['WIDTH']),
						'HEIGHT' => intval($item['PREVIEW_PICTURE']['HEIGHT'])
					);
				}
			}
			if (!empty($item['DETAIL_PICTURE']))
			{
				$keyPict = (empty($result['PICT']) ? 'PICT' : 'SECOND_PICT');
				if (!is_array($item['DETAIL_PICTURE']))
					$item['DETAIL_PICTURE'] = CFile::GetFileArray($item['DETAIL_PICTURE']);
				if (isset($item['DETAIL_PICTURE']['ID']))
				{
					$result[$keyPict] = array(
						'ID' => intval($item['DETAIL_PICTURE']['ID']),
						'SRC' => $item['DETAIL_PICTURE']['SRC'],
						'WIDTH' => intval($item['DETAIL_PICTURE']['WIDTH']),
						'HEIGHT' => intval($item['DETAIL_PICTURE']['HEIGHT'])
					);
				}
			}
			if (empty($result['SECOND_PICT']))
			{
				if (
					'' != $propertyCode &&
					isset($item['PROPERTIES'][$propertyCode]) &&
					'F' == $item['PROPERTIES'][$propertyCode]['PROPERTY_TYPE']
				)
				{
					if (
						isset($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']) &&
						!empty($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE'])
					)
					{
						$fileValues = (
							isset($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']['ID']) ?
							array(0 => $item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']) :
							$item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']
						);
						foreach ($fileValues as &$oneFileValue)
						{
							$keyPict = (empty($result['PICT']) ? 'PICT' : 'SECOND_PICT');
							$result[$keyPict] = array(
								'ID' => intval($oneFileValue['ID']),
								'SRC' => $oneFileValue['SRC'],
								'WIDTH' => intval($oneFileValue['WIDTH']),
								'HEIGHT' => intval($oneFileValue['HEIGHT'])
							);
							if ('SECOND_PICT' == $keyPict)
								break;
						}
						if (isset($oneFileValue))
							unset($oneFileValue);
					}
					else
					{
						$propValues = $item['PROPERTIES'][$propertyCode]['VALUE'];
						if (!is_array($propValues))
							$propValues = array($propValues);
						foreach ($propValues as &$oneValue)
						{
							$oneFileValue = CFile::GetFileArray($oneValue);
							if (isset($oneFileValue['ID']))
							{
								$keyPict = (empty($result['PICT']) ? 'PICT' : 'SECOND_PICT');
								$result[$keyPict] = array(
									'ID' => intval($oneFileValue['ID']),
									'SRC' => $oneFileValue['SRC'],
									'WIDTH' => intval($oneFileValue['WIDTH']),
									'HEIGHT' => intval($oneFileValue['HEIGHT'])
								);
								if ('SECOND_PICT' == $keyPict)
									break;
							}
						}
						if (isset($oneValue))
							unset($oneValue);
					}
				}
			}
		}
		return $result;
	}
}
?>