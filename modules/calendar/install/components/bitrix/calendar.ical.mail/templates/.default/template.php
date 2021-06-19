<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<style type="text/css"`>
	/* ======================================= DESKTOP STYLES */
	* { -webkit-text-size-adjust: none; }
	body { margin: 0 !important; padding: 0 !important; }
	body,table,td,p,a { -ms-text-size-adjust: 100% !important; -webkit-text-size-adjust: 100% !important; }
	table, tr, td { border-spacing: 0 !important; mso-table-lspace: 0px !important; mso-table-rspace: 0pt !important; border-collapse: collapse !important; mso-line-height-rule:exactly !important;}
	.ExternalClass * { line-height: 100% }
	.mobile-link a, .mobile-link span { text-decoration:none !important; color: inherit !important; border: none !important; }
	/* ======================================= CUSTOM DESKTOP STYLES */

	/* ======================================= MOBILE STYLES */
	@media only screen and (max-width: 450px) {
		.rounded-bg {
			height: 25px!important;
		}
	}

	@media only screen and (max-width: 520px) {
		body { min-width: 320px; margin: 0; }
		.hide-m { display: none !important; }
		.show-for-small { display: block !important; overflow: visible !important; width: auto !important; max-height: inherit !important; }
		.no-float { float: none !important; }
		.block { display: block !important; }
		.resize-image { width: 100%; height: auto; }
		.center-image { display: block; margin: 0 auto; }

		.text-center { text-align: center !important; }
		.font-12 { font-size: 12px !important; line-height: 12px !important; }
		.font-13 { font-size: 13px !important; line-height: 14px !important; }
		.font-14 { font-size: 14px !important; line-height: 16px !important; }
		.font-16 { font-size: 16px !important; line-height: 18px !important; }
		.font-18 { font-size: 18px !important; line-height: 20px !important; }
		.font-20 { font-size: 20px !important; line-height: 22px !important; }
		.font-22 { font-size: 22px !important; line-height: 24px !important; }
		.font-23 { font-size: 23px !important; line-height: 26px !important; }

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
		.main-btn {
			width: 250px!important;
			line-height: 51px!important;
		}

		.no-text-decoration {
			text-decoration: none!important;
		}

		.date-top {
			width: 71px!important;
			height: 25px!important;
		}

		.date-bottom {
			width: 71px!important;
			height: 50px!important;
		}
	}
</style>

<div style="background-color:#7bceeb; background-size: cover;">
	<!--[if gte mso 9]>
	<v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
		<v:fill type="tile" src="<?= $arResult['COMPONENT_PATH'] ?>/templates/.default/images/bg.jpg" color="#7bceeb" size="cover"/>
	</v:background>
	<![endif]-->
	<table height="auto" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td valign="top" align="left" background="<?= $arResult['COMPONENT_PATH'] ?>/templates/.default/images/bg.jpg" style="background-size: cover;">
				<div style="padding:20px 10px; background-color: #2fc7f7; background-image: url(<?= $arResult['COMPONENT_PATH'] ?>'/templates/.default/images/bg.jpg'); background-repeat: no-repeat; background-size: cover; display: block;">
					<div style="margin: 0;Margin: 0;padding: 38px 33px 38px 33px; color: #ffffff; font-size: 32px; font-family: 'OpenSans-Bold', 'HelveticaNeue', Arial, Helvetica, sans-serif; text-align: center; text-transform: uppercase;">
						<?= $arResult['TITLE'] ?>
					</div>

					<table class="w100 mobile-width" cellpadding="0" cellspacing="0" width="100%" align="center" style="font-size:0; padding-bottom: 15px;border-collapse:collapse;text-align:center;">
						<tbody>
						<!-- ***************** CONTENT  ********************-->
						<tr>
							<td>
								<table align="center" class="w100" cellpadding="0" cellspacing="0" width="500" style="padding: 0 33px 43px 33px;color: #333;font-family: 'HelveticaNeue', Arial, 'Helvetica CY', 'Nimbus Sans L', sans-serif;font-size: 15px;line-height: 20px; border:0;margin:0 auto; Margin: 0 auto; border-spacing:0;border-collapse:collapse;text-align:center;">
									<tbody>
									<tr>
										<td>
											<table align="center" cellpadding="0" cellspacing="0" style="width: 100%; border-spacing:0;border-collapse:collapse;">
												<tbody>

												<!--rounded white header block-->
												<tr>
													<td align="center" height="35" style="padding: 0; margin: 0; Margin: 0; height: 35px; line-height: 10px;">
														<img width="500" height="35" class="rounded-bg" src="<?= $arResult['COMPONENT_PATH'] ?>/templates/.default/images/top-head.png" style="display: block; width: 500px; height: 35px;" alt="">
													</td>
												</tr>

												<!--event image date-->
												<?php if ($arResult['IS_SHOW_DATE_ICON']): ?>
													<tr>
														<td align="center" style="padding-top: 0; padding-bottom: 30px; padding-left: 10px; padding-right: 10px; line-height: 9px; background-color: #fff; font-weight: bold; color:#ffffff;">
															<table align="center" class="w100" cellpadding="0" cellspacing="0" style="width: 100%; border-spacing:0;border-collapse:collapse;">
																<tbody>
																	<tr>
																		<td align="center" style="padding: 0; margin: 0; Margin: 0;">
																			<img width="84" height="30" class="date-top" style="margin: 0; Margin: 0; border: 0; width: 84px; height: 30px; text-align:center;" src="<?= $arResult['ICON_MONTH_PATH'] ?>" alt="month">
																		</td>
																	</tr>
																	<tr>
																		<td align="center" style="padding: 0; margin: 0; Margin: 0;">
																			<img width="84" height="60" class="date-bottom" style="margin: 0; Margin: 0; border: 0; width: 84px; height: 60px; text-align:center;" src="<?= $arResult['COMPONENT_PATH'] ?>/templates/.default/images/bottom_<?= $arResult['DATE_NUMBER'].".png" ?>" alt="date">
																		</td>
																	</tr>
																</tbody>
															</table>
														</td>
													</tr>
												<?php endif ?>

												<!--event name-->
												<tr>
													<td class="font-23" style="padding:0;padding-top:24px;padding-bottom:2px;padding-left: 10px; padding-right: 10px;margin:0;line-height:37px;background-color:#fff;text-align:center; font-size: 32px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #333333;">
														<?= $arResult['NAME'] ?>
													</td>
												</tr>

												<!--edit detail info-->
												<?php if ($arResult['IS_SHOW_EDIT_FIELDS']): ?>
													<tr>
														<td style="padding-top: 15px; padding-bottom: 15px;padding-left: 10px; padding-right: 10px; line-height: 18px; background-color: #fff; text-align:center;">
															<div class="font-12" style="padding-top: 3px; padding-bottom: 3px; font-size: 13px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #8ebb00; opacity: .69; text-align:center;">
																<?= $arResult['EDIT_FIELDS'] ?>
															</div>
														</td>
													</tr>
												<?php endif ?>

												<!--cancel info-->
												<?php if ($arResult['IS_SHOW_CANCEL_INVITATION_ALERT']): ?>
													<tr>
														<td style="padding-top: 15px; padding-bottom: 15px;padding-left: 10px; padding-right: 10px; line-height: 18px; background-color: #fff; text-align:center;">
															<div class="font-12" style="padding-top: 3px; padding-bottom: 3px; font-size: 13px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #de2b24; opacity: .69; text-align:center;">
																<?= $arResult['CANCEL_INVITATION_ALERT'] ?>
															</div>
														</td>
													</tr>
												<?php endif ?>

												<!--detail link-->
												<?php if ($arResult['IS_SHOW_DETAIL_LINK']): ?>
													<tr>
														<td style="padding-top: 15px; padding-bottom: 15px;padding-left: 10px; padding-right: 10px; line-height: 18px; background-color: #fff; text-align:center;">
															<a class="mobile-link font-13 no-text-decoration" href="<?= $arResult['DETAIL_LINK'] ?>" style="font-size: 14px; font-family: 'OpenSans-Regular', 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #0b66c3; text-decoration: underline">
																<?= \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_SHOW_DETAILS_PUB_LINK_EVENT') ?>
															</a>
														</td>
													</tr>
												<?php endif ?>

												<!--event duration-->
												<tr>
													<td align="center" style="padding-top: 30px; padding-bottom: 30px; padding-left: 10px; padding-right: 10px; background-color: #fff; text-align:center;">
														<?php if ($arResult['FULL_DAY']): ?>
															<?php if ($arResult['IS_LONG_DATETIME_FORMAT']): ?>
																<div class="font-18" style="padding-top: 7px; padding-bottom: 7px; font-size: 20px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #333333; text-align:center;">
																	<?= $arResult['DATE_FROM'] . " -" ?>
																</div>
																<div class="font-18" style="padding-top: 7px; padding-bottom: 7px; font-size: 20px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #333333; text-align:center;">
																	<?= $arResult['DATE_TO'] ?>
																</div>
															<?php else: ?>
																<div class="font-18" style="padding-top: 7px; padding-bottom: 7px; font-size: 20px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #333333; text-align:center;">
																	<?= $arResult['DATE_FROM'] ?>
																</div>
																<div class="font-18" style="padding-top: 7px; padding-bottom: 7px; font-size: 20px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #333333; text-align:center;">
																	<?= \Bitrix\Main\Localization\Loc::getMessage('EC_VIEW_FULL_DAY') ?>
																</div>
															<?php endif ?>
														<?php else: ?>
															<?php if ($arResult['IS_LONG_DATETIME_FORMAT']): ?>
																<div class="font-18" style="padding-top: 7px; padding-bottom: 7px; font-size: 20px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #333333; text-align:center;">
																	<?= $arResult['DATE_FROM'] . ' ' . $arResult['TIME_FROM'] . ' -' ?>
																</div>
																<div class="font-18" style="padding-top: 7px; padding-bottom: 7px; font-size: 20px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #333333; text-align:center;">
																	<?= $arResult['DATE_TO'] . ' ' . $arResult['TIME_TO'] ?>
																</div>
															<?php else: ?>
																<div class="font-18" style="padding-top: 7px; padding-bottom: 7px; font-size: 20px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #333333; text-align:center;">
																	<?= $arResult['DATE_FROM'] ?>
																</div>
																<div class="font-18" style="padding-top: 7px; padding-bottom: 7px; font-size: 20px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #333333; text-align:center;">
																	<?= $arResult['TIME_FROM'] . ' - ' . $arResult['TIME_TO'] ?>
																</div>
															<?php endif ?>
															<?php if ($arResult['IS_SHOW_RRULE']): ?>
																<div class="font-18" style="padding-top: 7px; padding-bottom: 7px; font-size: 20px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #333333; text-align:center;">
																	<?= $arResult['RRULE'] ?>
																</div>
															<?php endif ?>

															<!--offset block-->
															<div class="font-12" style="padding-top: 3px; padding-bottom: 3px; font-size: 13px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; color: #525C69; opacity: .69; text-align:center;">
																UTC
																<?php if ($arResult['IS_SHOW_TIME_OFFSET']): ?>
																	<?= $arResult['OFFSET_FROM'] . ' ' . $arResult['TIMEZONE_NAME_FROM'] ?>
																<?php endif ?>
															</div>
														<?php endif ?>
													</td>
												</tr>

												<!--decision buttons-->
												<?php if ($arResult['IS_SHOW_DECISION_BUTTON']): ?>
													<tr>
														<td align="center" style="background-color: #fff; text-align:center;">
															<?php if ($arResult['IS_SHOW_CHOOSE_DECISION_BUTTON']): ?>
															<table align="center" class="w100" cellpadding="0" cellspacing="0" style="width: 100%; border-spacing:0;border-collapse:collapse;">
																<tr>
																	<td align="center" style="padding-top: 25px; padding-bottom: 18px;">
																		<div style="padding-left: 10px; padding-right: 10px; text-align:center;"><!--[if mso]>
																			<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://" style="height:51px;v-text-anchor:middle;width:300px;" arcsize="57%" stroke="f" fillcolor="#BBED21">
																				<w:anchorlock/>
																				<center>
																			<![endif]-->
																			<a href="<?=$arResult['DECISION_YES_LINK']?>"
																			   class="mobile-link font-16 main-btn"
																			   style="background-color:#BBED21;border-radius:29px;color:#333333;display:inline-block;font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif;font-size:17px;line-height:51px;text-align:center;text-decoration:none;width:290px;-webkit-text-size-adjust:none;cursor:pointer;">
																				<?=\Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_DECISION_TITLE_YES')?>
																			</a>
																			<!--[if mso]>
																			</center>
																			</v:roundrect>
																			<![endif]-->
																		</div>
																	</td>
																</tr>
																<tr>
																	<td align="center">
																		<div style="padding-bottom: 0; height: 51px; text-align:center;">
																			<a class="mobile-link font-16" href="<?=$arResult['DECISION_NO_LINK']?>" style="line-height: 51px; font-size: 17px; font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif; text-decoration: none; color: #525C69; opacity: .68; text-align:center;">
																				<?=\Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_DECISION_TITLE_NO')?>
																			</a>
																		</div>
																	</td>
																</tr>
															</table>
															<?php else: ?>
																<div style="padding-top: 25px; padding-bottom: 18px; padding-left: 10px; padding-right: 10px; text-align:center;"><!--[if mso]>
																	<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://" style="height:51px;v-text-anchor:middle;width:300px; text-align:center;" arcsize="57%" stroke="f" fillcolor="#BBED21">
																		<w:anchorlock/>
																		<center>
																	<![endif]-->
																	<a href="<?=$arResult['CHANGE_DECISION_LINK']?>"
																	   class="mobile-link font-16 main-btn"
																	   style="background-color:#CBF1FD;border-radius:29px;color:#333333;display:inline-block;font-family: 'OpenSans-SemiBold', 'HelveticaNeue', Arial, Helvetica, sans-serif;font-size:17px;line-height:51px;text-align:center;text-decoration:none;width:290px;-webkit-text-size-adjust:none;cursor:pointer;">
																		<?= \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_CHANGE_DECISION_TITLE') ?>
																	</a>
																	<!--[if mso]>
																	</center>
																	</v:roundrect>
																	<![endif]-->
																</div>
															<?php endif ?>
														</td>
													</tr>
												<?php endif ?>

												<!--rounded white footer block-->
												<tr>
													<td align="center" height="35" style="padding: 0; margin: 0; Margin: 0; height: 35px; line-height: 10px;">
														<img width="500" height="35" class="rounded-bg" src="<?=$arResult['COMPONENT_PATH']?>/templates/.default/images/bottom-footer.png" style="display: block; width: 500px; height: 35px;" alt="">
													</td>
												</tr>

												</tbody>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<!-- ***************** END CONTENT  ********************-->
						</tbody>
					</table>

					<!--footer-->
					<div align="center" valign="middle" style="padding-top: 20px;">
						<a class="mobile-link" href="#" target="_blank" style="color: #ffffff;text-decoration: none;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 16px;display: inline-block;vertical-align: middle;">
							<table>
								<tr>
									<td valign="middle" align="center">
										<img height="20" width="99" src="<?= $arResult['COMPONENT_PATH'] ?>/templates/.default/images/<?= GetMessage('EC_CALENDAR_ICAL_MAIL_USER_BITRIX24_IMAGEFILE_LOGO') ?>" alt="" style="padding-left: 10px; padding-bottom: 3px; outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;border: none;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 17px;color: #71a5b6;font-weight: bold;vertical-align: middle;">
										<br>
										<div style="display: block; vertical-align:middle; font-size: 16px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-weight: bold; color: #ffffff;">
											<?= GetMessage('EC_CALENDAR_BITRIX24_SLOGAN') ?>
										</div>
									</td>
								</tr>
							</table>
						</a>
					</div>
				</div>
			</td>
		</tr>
	</table>
</div>

