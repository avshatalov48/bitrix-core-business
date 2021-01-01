<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? $updatedTitle = sprintf(' (%s)', GetMessage("EC_CALENDAR_ICAL_MAIL_TEMPLATE_TITLE_ADD_UPDATE"))?>
<style>
	a {
		color: #2066B0;
	}
</style>
<div style="padding:20px 20px 41px 20px; background-color: #EDEEF0; display: block;">
	<table cellpadding="0" cellspacing="0" width="500" align="center" style="font-size:0; padding-bottom: 15px;">
		<tbody>
			<tr>
				<td style="margin: 0;padding: 38px 33px 38px 33px; background: #2fc7f7; color: #ffffff; font-size: 30px; font-family: 'HelveticaNeue', Arial, Helvetica, sans-serif;" align="left" valign="middle">
					<?=$arResult["TITLE"]?>
				</td>
			</tr>
		<!-- ***************** CONTENT  ********************-->
			<tr>
				<td>
					<table cellpadding="0" cellspacing="0" bgcolor="#ffffff" width="500" style="padding: 0 33px 43px 33px;color: #333;margin: 0;font-family: 'HelveticaNeue', Arial, 'Helvetica CY', 'Nimbus Sans L', sans-serif;font-size: 15px;line-height: 20px;">
						<tbody>
						<tr>
							<td style="padding-top: 30px; line-height: 18px; font-weight: bold; color: <?= in_array('NAME', $arResult['CHANGE_FIELDS']) ? ' #84AD00' : '#4a4a4a'?>">
								<?= GetMessage("EC_CALENDAR_ICAL_MAIL_TEMPLATE_TITLE_THEME")
								. (empty(in_array('NAME', $arResult['CHANGE_FIELDS'])) ? '' : $updatedTitle)?>:
							</td>
						</tr>
						<tr>
							<td style="color: #525C69;">
								<?=$arResult["NAME"]?>
							</td>
						</tr>
						<tr>
							<td style="padding-top: 15px; line-height: 18px; font-weight: bold; color: <?= !empty(array_intersect(['DATE_FROM', 'RRULE'], $arResult['CHANGE_FIELDS'])) ? ' #84AD00' : '#4a4a4a'?>">
								<?= GetMessage("EC_CALENDAR_ICAL_MAIL_TEMPLATE_TITLE_WHEN")
								. (empty(array_intersect(['DATE_FROM', 'RRULE'], $arResult['CHANGE_FIELDS'])) ? '' : $updatedTitle)?>:
							</td>
						</tr>
						<tr>
							<td style="color: #525C69;">
								<?=$arResult["DATE_FROM"]?>
							</td>
						</tr>
						<? if($arResult['LOCATION'] !== ''):?>
						<tr>
							<td style="padding-top: 15px; line-height: 18px; font-weight: bold; color: <?= in_array('LOCATION', $arResult['CHANGE_FIELDS']) ? ' #84AD00' : '#4a4a4a'?>">
								<?= GetMessage("EC_CALENDAR_ICAL_MAIL_TEMPLATE_TITLE_WHERE")
								. (empty(in_array('LOCATION', $arResult['CHANGE_FIELDS'])) ? '' : $updatedTitle)?>:
							</td>
						</tr>
						<tr>
							<td style="color: #525C69;">
								<?=$arResult["LOCATION"]?>
							</td>
						</tr>
						<?endif?>
						<tr>
							<td style="padding-top: 15px; line-height: 18px; font-weight: bold">
								<?= GetMessage("EC_CALENDAR_ICAL_MAIL_TEMPLATE_TITLE_ORGANIZER")?>:
							</td>
						</tr>
						<tr>
							<td style="color: #525C69;">
								<?=$arResult["ORGANIZER"]?>
							</td>
						</tr>
<!--						<tr>-->
<!--							<td style="padding-top: 15px; line-height: 18px; font-weight: bold; color: --><?//= in_array('ATTENDEES', $arResult['CHANGE_FIELDS']) ? ' #84AD00' : '#4a4a4a'?><!--">-->
<!--								--><?//= GetMessage("EC_CALENDAR_ICAL_MAIL_TEMPLATE_TITLE_ATTENDEES")
//								. (empty(in_array('ATTENDEES', $arResult['CHANGE_FIELDS'])) ? '' : $updatedTitle)?><!--:-->
<!--							</td>-->
<!--						</tr>-->
<!--						<tr>-->
<!--							<td style="color: #525C69;">-->
<!--								--><?//=$arResult["ATTENDEES_LIST"]?>
<!--							</td>-->
<!--						</tr>-->
						<? if($arResult['DESCRIPTION'] !== ''):?>
						<tr>
							<td style="padding-top: 15px; line-height: 18px; font-weight: bold; color: <?= in_array('DESCRIPTION', $arResult['CHANGE_FIELDS']) ? ' #84AD00' : '#4a4a4a'?>">
								<?= GetMessage("EC_CALENDAR_ICAL_MAIL_TEMPLATE_TITLE_DESCRIPTION")
								. (empty(in_array('DESCRIPTION', $arResult['CHANGE_FIELDS'])) ? '' : $updatedTitle)?>:
							</td>
						</tr>
						<tr>
							<td style="color: #525C69;">
								<?=str_replace("#$&#$&#$&", "<br />", $arResult["DESCRIPTION"])?>
							</td>
						</tr>
						<?endif?>
						<? if($arResult['FILES'] !== ''):?>
						<tr>
							<td style="padding-top: 25px; font-weight: bold; color: <?= in_array('FILES', $arResult['CHANGE_FIELDS']) ? ' #84AD00' : '#4a4a4a'?>">
								<?= GetMessage("EC_CALENDAR_ICAL_MAIL_TEMPLATE_TITLE_FILES")
								. (empty(in_array('FILES', $arResult['CHANGE_FIELDS'])) ? '' : $updatedTitle)?>:
							</td>
						</tr>
						<tr>
							<td style="color: #525C69;">
								<?=htmlspecialcharsback($arResult["FILES"])?>
							</td>
						</tr>
						<?endif?>
					</table>
				</td>
			</tr>
		<!-- ***************** END CONTENT  ********************-->
		</tbody>
	</table>
	<div align="center" valign="middle" style="background-color: #EDEEF0;">
		<?if (\Bitrix\Main\Loader::includeModule('intranet')):?>
			<a href="<?=CIntranetUtils::getB24Link('pub'); ?>" target="_blank" style="color: #525C69;text-decoration: none;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 9px;display: inline-block;vertical-align: middle;"><?
				?><table><tr><td valign="middle"><span style="display: inline-block; vertical-align:middle; margin-right: 2px;opacity: .5;"><?=GetMessage('EC_CALENDAR_ICAL_MAIL_USER_CHARGED')?></span></td>
				<td valign="middle"><img height="13" width="65" src="/bitrix/components/bitrix/calendar.ical.mail/templates/.default/images/<?=GetMessage('EC_CALENDAR_ICAL_MAIL_USER_BITRIX24_IMAGEFILE')?>" alt="<?=GetMessage('EC_CALENDAR_ICAL_MAIL_USER_BITRIX24_IMAGEFILE_ALT')?>" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;border: none;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 17px;color: #71a5b6;font-weight: bold;vertical-align: middle;"></td><?
				?></tr></table></a>
		<?endif;?>
	</div>
</div>
