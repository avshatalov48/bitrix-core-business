<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\DateType;
use Bitrix\Main\Localization\Loc;

/**
 * @var array $arResult
 */

$result = [];
if(
	$arResult['userField']['EDIT_IN_LIST'] === 'Y'
	&&
	$arResult['userField']['ENTITY_VALUE_ID'] < 1
	&&
	$arResult['userField']['SETTINGS']['DEFAULT_VALUE']['TYPE'] === DateType::TYPE_NOW
)
{
	$value = ConvertTimeStamp(
		time() + CTimeZone::GetOffset(),
		DateType::FORMAT_TYPE_SHORT
	);
	$result[] = CAdminCalendar::CalendarDate(
		str_replace('[]', '[0]', $arResult['fieldName']),
		$value,
		20,
		false
	);
}
else if(
	$arResult['userField']['EDIT_IN_LIST'] === 'Y'
	&&
	$arResult['userField']['ENTITY_VALUE_ID'] < 1
	&&
	$arResult['userField']['SETTINGS']['DEFAULT_VALUE']['TYPE'] !== DateType::TYPE_NONE
)
{
	$value = str_replace(
		' 00:00:00',
		'',
		CDatabase::FormatDate(
			$arResult['userField']['SETTINGS']['DEFAULT_VALUE']['VALUE'],
			'YYYY-MM-DD HH:MI:SS',
			CLang::GetDateFormat(DateType::FORMAT_TYPE_SHORT)
		)
	);

	$result[] = CAdminCalendar::CalendarDate(
		str_replace('[]', '[0]', $arResult['fieldName']),
		$value,
		20,
		false
	);
}
else if($arResult['userField']['EDIT_IN_LIST'] === 'Y')
{
	foreach($arResult['value'] as $key => $value)
	{

		if (
			!$value
			&&
			$arResult['userField']['SETTINGS']['DEFAULT_VALUE']['TYPE']===DateType::TYPE_NOW
		)
		{
			$value = ConvertTimeStamp(
				time()+CTimeZone::GetOffset(),
				DateType::FORMAT_TYPE_SHORT
			);
		}

		$result[] = CAdminCalendar::CalendarDate(
			str_replace('[]', '[' . $key . ']', $arResult['fieldName']),
			$value,
			20,
			false
		);
	}
}
elseif($arResult['additionalParameters']['VALUE'] <> '')
{
	foreach($arResult['value'] as $key => $value)
	{
		$result[] = $value;
	}
}
else
{
	$result[] = '&nbsp;';
}

?>

<table id='table_<?= $arResult['userField']['FIELD_NAME'] ?>'>
	<?php
	foreach($result as $item)
	{
		?>
		<tr>
			<td>
				<?= $item ?>
			</td>
		</tr>
		<?php
	}

	if($arResult['userField']['MULTIPLE'] === 'Y')
	{
		$rowClass = '';
		$fieldNameX = str_replace('_', 'x', $arResult['userField']['FIELD_NAME']);
		?>
		<tr>
			<td style='padding-top: 6px;'>
				<input
					type='button'
					value='<?= Loc::getMessage('USER_TYPE_PROP_ADD') ?>'
					onClick="
						addNewRow(
							'table_<?= $arResult['userField']['FIELD_NAME'] ?>',
							'<?= $fieldNameX ?>|<?= $arResult['userField']['FIELD_NAME'] ?>|<?= $arResult['userField']['FIELD_NAME'] ?>_old_id'
						)"
				>
			</td>
		</tr>
		<?php
	}
	?>
</table>