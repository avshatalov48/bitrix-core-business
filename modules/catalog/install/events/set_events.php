<?
global $DB;

use Bitrix\Main\Localization\Loc;

$messages = Loc::loadLanguageFile(__FILE__);

if(!empty($messages))
{
	$listEventType = "'CATALOG_PRODUCT_SUBSCRIBE_LIST_CONFIRM', 'CATALOG_PRODUCT_SUBSCRIBE_NOTIFY', 
		'CATALOG_PRODUCT_SUBSCRIBE_NOTIFY_REPEATED'";
	$rs = $DB->query('SELECT count(*) CNT FROM b_event_type WHERE EVENT_NAME IN ('.$listEventType.')');
	$ar = $rs->fetch();
	if($ar['CNT'] <= 0)
	{
		$templateTotal = '
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<style>
					body
					{
						font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
						font-size: 14px;
						color: #000;
					}
				</style>
			</head>
			<body>
			<table cellpadding="0" cellspacing="0" width="850" style="background-color: #d1d1d1; border-radius: 2px; 
				border:1px solid #d1d1d1; margin: 0 auto;" border="1" bordercolor="#d1d1d1">
				<tr>
					<td height="83" width="850" bgcolor="#eaf3f5" style="border: none; padding-top: 23px; 
						padding-right: 17px; padding-bottom: 24px; padding-left: 17px;">
						<table cellpadding="0" cellspacing="0" border="0" width="100%">
							<tr>
								<td bgcolor="#ffffff" height="75" style="font-weight: bold; text-align: 
								center; font-size: 26px; color: #0b3961;">#TITLE#</td>
							</tr>
							<tr>
								<td bgcolor="#bad3df" height="11"></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td width="850" bgcolor="#f7f7f7" valign="top" style="border: none; padding-top: 0; 
						padding-right: 44px; padding-bottom: 16px; padding-left: 44px;">
						<p style="margin-top:30px; margin-bottom: 28px; font-weight: bold; font-size: 19px;">#SUB_TITLE#</p>
						<p style="margin-top: 0; margin-bottom: 20px; line-height: 20px;">#TEXT#</p>
					</td>
				</tr>
				<tr>
					<td height="40px" width="850" bgcolor="#f7f7f7" valign="top" style="border: none; padding-top: 0; 
						padding-right: 44px; padding-bottom: 30px; padding-left: 44px;">
						<p style="border-top: 1px solid #d1d1d1; margin-bottom: 5px; margin-top: 0; padding-top: 20px; 
							line-height:21px;">#FOOTER_BR# 
							<a href="http://#SERVER_NAME#" style="color:#2e6eb6;">#FOOTER_SHOP#</a><br />
							E-mail: <a href="mailto:#DEFAULT_EMAIL_FROM#" style="color:#2e6eb6;">#DEFAULT_EMAIL_FROM#</a>
							#UNSUBSCRIBE#
						</p>
					</td>
				</tr>
			</table>
			</body>
			</html>
		';

		$unsubscribeTemplate = '<br><a href="#UNSUBSCRIBE_URL#">#FOOTER_UNSUBSCRIBE#</a>';

		$eventType = new CEventType;
		$eventMessage = new CEventMessage;
		$listEventName = array('CATALOG_PRODUCT_SUBSCRIBE_LIST_CONFIRM', 'CATALOG_PRODUCT_SUBSCRIBE_NOTIFY',
			'CATALOG_PRODUCT_SUBSCRIBE_NOTIFY_REPEATED');
		$listEventMayUnsubscribe = array('CATALOG_PRODUCT_SUBSCRIBE_NOTIFY', 'CATALOG_PRODUCT_SUBSCRIBE_NOTIFY_REPEATED');

		$languageIterator = Bitrix\Main\Localization\LanguageTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=ACTIVE' => 'Y')
		));
		while ($lang = $languageIterator->fetch())
		{
			$sites = array();
			$siteIterator = Bitrix\Main\SiteTable::getList(array(
				'select' => array('LID'),
				'filter' => array('LANGUAGE_ID' => $lang['ID'])
			));
			while ($site = $siteIterator->fetch())
				$sites[] = $site['LID'];

			foreach($listEventName as $eventName)
			{
				if(in_array($eventName, $listEventMayUnsubscribe))
					$template = str_replace("#UNSUBSCRIBE#", $unsubscribeTemplate, $templateTotal);
				else
					$template = str_replace("#UNSUBSCRIBE#", '', $templateTotal);

				$message = str_replace(
					array(
						'#TITLE#',
						'#SUB_TITLE#',
						'#TEXT#',
						'#FOOTER_BR#',
						'#FOOTER_SHOP#',
						'#FOOTER_UNSUBSCRIBE#',
					),
					array(
						$messages[$eventName.'_HTML_TITLE'],
						$messages[$eventName.'_HTML_SUB_TITLE'],
						$messages[$eventName.'_HTML_TEXT'],
						$messages['SMAIL_FOOTER_BR'],
						$messages['SMAIL_FOOTER_SHOP'],
						$messages['SMAIL_UNSUBSCRIBE'],
					),
					$template);

				$eventType->add(array(
					'LID' => $lang['ID'],
					'EVENT_NAME' => $eventName,
					'NAME' => $messages[$eventName.'_NAME'],
					'DESCRIPTION' => $messages[$eventName.'_DESC'],
				));
				if(!empty($sites))
				{
					$eventMessage->add(array(
						'ACTIVE' => 'Y',
						'EVENT_NAME' => $eventName,
						'LID' => $sites,
						'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
						'EMAIL_TO' => '#EMAIL_TO#',
						'BCC' => '#BCC#',
						'SUBJECT' => $messages[$eventName.'_SUBJECT'],
						'MESSAGE' => $message,
						'BODY_TYPE' => 'html',
					));
				}
			}
		}
	}
}