<?php

use Bitrix\Main\Localization\Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/**
 * @var array $arParams
 */

?>

<html>
<head>
</head>
<body>
<style type="text/css">
	/* ======================================= DESKTOP STYLES */
	* { -webkit-text-size-adjust: none; }
	body { margin: 0 !important; padding: 0 !important; }
	body,table,td,p,a { -ms-text-size-adjust: 100% !important; -webkit-text-size-adjust: 100% !important; }
	table, tr, td { border-spacing: 0 !important; mso-table-lspace: 0px !important; mso-table-rspace: 0pt !important; border-collapse: collapse !important; mso-line-height-rule:exactly !important;}
	.ExternalClass * { line-height: 100% }
	.mobile-link a, .mobile-link span { text-decoration:none !important; color: inherit !important; border: none !important; }
	/* ======================================= CUSTOM DESKTOP STYLES */

	/* ======================================= MOBILE STYLES */
	@media only screen and (max-width: 640px) {
		body { min-width: 320px; margin: 0; }
		.hide-m { display: none !important; }
		.show-for-small { display: block !important; overflow: visible !important; width: auto !important; max-height: inherit !important; }
		.no-float { float: none !important; }
		.block { display: block !important; }
		.resize-image { width: 100%; height: auto; }
		.center-image { display: block; margin: 0 auto; }

		.text-center { text-align: center !important; }
		.font-14 { font-size: 14px !important; line-height: 16px !important; }
		.font-16 { font-size: 16px !important; line-height: 18px !important; }
		.font-18 { font-size: 18px !important; line-height: 20px !important; }
		.font-20 { font-size: 20px !important; line-height: 22px !important; }
		.font-22 { font-size: 22px !important; line-height: 24px !important; }

		.pad-t-0 { padding-top: 0px !important; }
		.pad-r-0 { padding-right: 0px !important; }
		.pad-b-0 { padding-bottom: 0px !important; }
		.pad-l-0 { padding-left: 0px !important; }
		.pad-t-20 { padding-top: 20px !important; }
		.pad-r-20 { padding-right: 20px !important; }
		.pad-b-20 { padding-bottom: 20px !important; }
		.pad-l-20 { padding-left: 20px !important; }
		.pad-0 { padding-top: 0px !important; padding-right: 0px !important; padding-bottom: 0px !important; padding-left: 0px !important; }
		.pad-10 { padding-top: 10px !important; padding-right: 10px !important; padding-bottom: 10px !important; padding-left: 10px !important; }
		.pad-20 { padding-top: 20px !important; padding-right: 20px !important; padding-bottom: 20px !important; padding-left: 20px !important; }
		.pad-sides-0 { padding-right: 0px !important; padding-left: 0px !important; }
		.pad-sides-10 { padding-right: 10px !important; padding-left: 10px !important; }
		.pad-sides-20 { padding-right: 20px !important; padding-left: 20px !important; }
		.pad-sides-30 { padding-right: 30px !important; padding-left: 30px !important; }

		.w100 { width: 100% !important; min-width: initial !important; }
		.w90 { width: 90% !important; min-width: initial !important; }
		.w50 { width: 50% !important; min-width: initial !important; }
		/* ======================================= CUSTOM MOBILE STYLES */

	}
</style>
<!-- WRAPPER -->
<table class="w100" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="width: 100%; background-image: url(<?= $arParams['MAIL_BACKGROUND']?>); background-repeat: no-repeat; background-position: center; background-size: cover;">
	<tbody>
	<tr>
		<td align="center" class="pad-sides-20" style="padding-top: 60px; padding-bottom: 24px;">
			<!-- CONTAINER -->
			<table class="w100" cellspacing="0" cellpadding="0" border="0" align="center" width="516" style="width: 516px; min-width: 516px; margin: 0 auto;">
				<tbody>
				<tr>
					<td align="center">
						<!-- CONTAINER -->
						<table class="w100" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" bgcolor="#ffffff" style="width: 100%; border-radius: 16px; background-color: #ffffff; margin: 0 auto;box-shadow: 0 6px 10px rgba(82, 92, 105, 0.04);">
							<tbody>
								<tr>
									<td align="center" style="padding-top: 20px; padding-left: 16px; padding-right: 16px; padding-bottom: 18px;">

										<table class="w100" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="width: 100%; border-radius: 16px; margin: 0 auto;">
											<tbody>
													<tr>
														<td align="left" style="">
															<div class="title" style="font-size: 20px;margin-bottom: 14px;color: #525C69;"><?= $arParams['MESSAGE_TITLE']?></div>
														</td>
													</tr>
													<tr>
														<td align="left" style="">
															<table class="w100" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="width: 100%; border-radius: 16px; margin: 0 auto; margin-bottom: 20px;">
																<tbody>
																	<tr>
																		<td valign="top" align="left" style="">
																			<div class="calendar" style="display: inline-block;vertical-align: top;background: <?= $arParams['CALENDAR_COLOR']?>;height: max-content;border-radius: 8px;padding: 1px 4px 4px 4px;margin-right: 8px; box-sizing: border-box;">
																				<div style="font-size: 12px;margin-top: 2px;margin-bottom: 3px;width:66px;color: #fff;text-align: center;box-sizing: border-box;text-transform: uppercase;"><?= $arParams['CALENDAR_WEEKDAY']?></div>
																				<div style="font-size: 35px;font-weight: 700;line-height: 48px;background: #fff;padding: 0 8px;border-radius: 4px;color: #6A737F;text-align: center;white-space: nowrap;"><?= $arParams['CALENDAR_DAY']?></div>
																			</div>
																		</td>
																		<td align="left" style="">
																			<div class="header-info" style="display: inline-block;">
																				<div class="event-name" style="margin-bottom: 5px;font-weight: 600;font-size: 20px;color: #525C69;"><?= $arParams['EVENT_NAME']?></div>
																				<div class="datetime" style="margin: 3px 0;">
																					<div class="interval" style="display: inline-block; vertical-align:middle;margin-right: 5px;font-weight: 600;font-size: 16px;color: #525C69;"><?= $arParams['EVENT_DATETIME']?></div>
																					<div class="timezone" style="display: inline-block; vertical-align:middle;color: #959CA4; font-size: 12px;"><?= $arParams['TIMEZONE']?></div>
																				</div>
																				<div class="status" style="color: <?= $arParams['STATUS_COLOR']?>; font-size: 12px;font-weight: 600; margin-bottom: 10px;"><?= $arParams['OWNER_STATUS']?></div>
																				<?php if (isset($arParams['ICS_FILE'])):?>
																					<a href="<?= $arParams['ICS_FILE']?>" class="btn-ics" style="display:inline-block;text-decoration: none;border-bottom: 1px dashed;font-size: 14px;color: #2066B0;">
																						<?= Loc::getMessage('CALENDAR_SHARING_MAIL_ADD_EVENT_TO_MY_CALENDAR')?>
																					</a>
																				<?php endif?>
																			</div>
																		</td>
																	</tr>
																</tbody>
															</table>
														</td>
													</tr>
													<tr>
														<td align="left" style="">
															<div class="attendees" style="background: #F5F7F8;padding: 14px;border-radius: 12px;">
																<div class="you-invited" style="font-size: 12px;font-weight: 400;color: #959CA4;margin-bottom: 10px;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_YOU_INVITED')?></div>
																<div style="margin-bottom: 8px;">
																	<table class="w100" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="width: 100%;">
																		<tbody>
																		<tr style="text-align: center;">
																			<td align="left" width="30" style="">
																				<img src="<?= $arParams['OWNER_PHOTO']?>" alt="" class="photo" style="display: inline-block;margin-right:10px;vertical-align: middle;width: 30px;height: 30px;border-radius: 50%;">
																			</td>
																			<td align="left" style="">
																				<span class="name" style="display: inline-block;font-weight: 400;font-size: 16px;color:#333;vertical-align: middle;"><?= $arParams['OWNER_NAME']?></span>
																			</td>
																		</tr>
																		</tbody>
																	</table>
																</div>
																<?php if (!empty($arParams['ATTENDEES'])): ?>
																	<div class="more-members" style="font-size: 12px;font-weight: 400;color: #959CA4;margin-bottom: 10px;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_MORE_ATTENDEES') ?></div>
																<?php endif;?>
																<?php foreach($arParams['ATTENDEES'] as $attendee):?>
																	<div style="margin-bottom: 8px;">
																		<table class="w100" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="width: 100%;">
																			<tbody>
																			<tr style="padding-bottom: 8px; text-align: center;">
																				<td align="left" width="30" style="">
																					<span style="display: inline-block;margin-right:10px;vertical-align: middle;width: 30px;height: 30px;border-radius: 50%;background-color: #F7A700; background-image: url(<?= $arParams['COMPONENT_PATH'] ?>/templates/.default/images/calendar-sharing-email-mail.png); background-repeat: no-repeat; background-position: center;"></span>
																				</td>
																				<td align="left" style="">
																					<span class="name" style="display: inline-block;font-weight: 400;font-size: 16px;color:#333;vertical-align: middle;"><?= $attendee['NAME']?></span>
																				</td>
																			</tr>
																			</tbody>
																		</table>
																	</div>
																<?php endforeach?>
															</div>
														</td>
													</tr>
													<tr>
														<td align="left" style="">
															<div class="buttons" style="display:block; margin-top: 20px; text-align: center;">
																<div>
																	<?php if (isset($arParams['VIDEOCONFERENCE_LINK'])):?>
																		<div style="margin-bottom: 10px;"><!--[if mso]>
																			<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://" style="height:40px;v-text-anchor:middle;width:350px;" arcsize="80%" stroke="f" fill="t">
																			<v:fill type="tile" color="#9dcf00" />
																			<w:anchorlock/>
																			<center style="color:#ffffff;font-family:sans-serif;font-size:13px;font-weight:bold;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_CONNECT_TO_VIDEOCONFERENCE')?></center>
																			</v:roundrect>
																			<![endif]-->
																			<a href="<?= $arParams['VIDEOCONFERENCE_LINK']?>"
																			   style="background-color:#9dcf00;border-radius:32px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:350px;-webkit-text-size-adjust:none;mso-hide:all;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_CONNECT_TO_VIDEOCONFERENCE')?></a>
																		</div>
																	<?php endif?>

																	<?php if (isset($arParams['CANCEL_LINK'])):?>
																		<div style="margin-bottom: 10px;"><!--[if mso]>
																			<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://" style="height:40px;v-text-anchor:middle;width:350px;" arcsize="80%" strokecolor="#828B95" fill="t">
																			<v:fill type="tile" color="#ffffff" />
																			<w:anchorlock/>
																			<center style="color:#333333;font-family:sans-serif;font-size:13px;font-weight:bold;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_CANCEL_EVENT')?></center>
																			</v:roundrect>
																			<![endif]-->
																			<a href="<?= $arParams['CANCEL_LINK']?>"
																			   style="background-color:#ffffff;border:1px solid #828B95;border-radius:32px;color:#333333;display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:350px;-webkit-text-size-adjust:none;mso-hide:all;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_CANCEL_EVENT')?></a>
																		</div>
																	<?php endif?>

																	<?php if (isset($arParams['NEW_EVENT_LINK'])):?>
																		<div style="margin-bottom: 10px;"><!--[if mso]>
																			<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://" style="height:40px;v-text-anchor:middle;width:350px;" arcsize="80%" strokecolor="#828B95" fill="t">
																			<v:fill type="tile" color="#ffffff" />
																			<w:anchorlock/>
																			<center style="color:#333333;font-family:sans-serif;font-size:13px;font-weight:bold;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_CREATE_NEW_EVENT')?></center>
																			</v:roundrect>
																			<![endif]-->
																			<a href="<?= $arParams['NEW_EVENT_LINK']?>"
																			   style="background-color:#ffffff;border:1px solid #828B95;border-radius:32px;color:#333333;display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:350px;-webkit-text-size-adjust:none;mso-hide:all;"><?= Loc::getMessage('CALENDAR_SHARING_MAIL_CREATE_NEW_EVENT')?></a>
																		</div>
																	<?php endif?>
																</div>
															</div>
														</td>
													</tr>
												</tbody>
										</table>

									</td>
								</tr>
							</tbody>
						</table>
						<!-- /CONTAINER -->
					</td>
				</tr>
				</tbody>
			</table>
			<!-- /CONTAINER -->
		</td>
	</tr>
	</tbody>
</table>
<!-- /WRAPPER -->
</body>
</html>
