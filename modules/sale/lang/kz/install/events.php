<?php
$MESS["SALE_CHECK_PRINT_ERROR_HTML_SUB_TITLE"] = "Сәлеметсіз бе!";
$MESS["SALE_CHECK_PRINT_ERROR_HTML_TEXT"] = "
Қандай да бір себептермен #ORDER_DATE# күнгі №#ORDER_ACCOUNT_NUMBER# тапсырыс бойынша №#CHECK_ID# чекті басып шығару мүмкін болмады!

Орын алған жағдайдың себебін жою үшін сілтеме арқылы өтіңіз:
#LINK_URL#
";
$MESS["SALE_CHECK_PRINT_ERROR_HTML_TITLE"] = "Чекті басып шығарудағы қате";
$MESS["SALE_CHECK_PRINT_ERROR_SUBJECT"] = "Чекті басып шығарудағы қате";
$MESS["SALE_CHECK_PRINT_ERROR_TYPE_DESC"] = "#ORDER_ACCOUNT_NUMBER# - тапсырыс коды
#ORDER_DATE# - тапсырыс күні
#ORDER_ID# - тапсырыс ID-і
#CHECK_ID# - чек нөмірі";
$MESS["SALE_CHECK_PRINT_ERROR_TYPE_NAME"] = "Чекті басып шығару кезіндегі қате туралы хабарлама";
$MESS["SALE_CHECK_PRINT_HTML_SUB_TITLE"] = "Құрметті #ORDER_USER#,";
$MESS["SALE_CHECK_PRINT_HTML_TEXT"] = "
Фискалдық чектер туралы ФЗ-54 Заңының талаптарына сәйкес сіздің тапсырысыңыз бойынша төлем жасалды және фискалдық кассалық чек қалыптастырылды, оны сіз сілтеме бойынша көре аласыз:

#CHECK_LINK#

#ORDER_DATE# күнгі №#ORDER_ID# тапсырыс бойынша толық ақпарат алу үшін #LINK_URL# сайтына өтіңіз
";
$MESS["SALE_CHECK_PRINT_HTML_TITLE"] = "Сіз #SITE_NAME# сайтында тапсырысты төледіңіз";
$MESS["SALE_CHECK_PRINT_SUBJECT"] = "Чекке сілтеме";
$MESS["SALE_CHECK_PRINT_TYPE_DESC"] = "#ORDER_ID# - тапсырыс коды
#ORDER_DATE# - тапсырыс күні
#ORDER_USER# - тапсырыс беруші
#ORDER_ACCOUNT_NUMBER_ENCODE# - тапсырыс коды (сілтемелер үшін)
#ORDER_PUBLIC_URL# - авторландырусыз тапсырысты қарауға арналған сілтеме (интернет-дүкен модулінде баптау қажет етіледі)
#CHECK_LINK# - чекке сілтеме";
$MESS["SALE_CHECK_PRINT_TYPE_NAME"] = "Чекті басып шығару туралы хабарлама";
$MESS["SALE_CHECK_VALIDATION_ERROR_HTML_SUB_TITLE"] = "Сәлеметсіз бе!";
$MESS["SALE_CHECK_VALIDATION_ERROR_HTML_TEXT"] = "
Қандай да бір себептермен #ORDER_DATE# күнгі №#ORDER_ACCOUNT_NUMBER# тапсырыс чек қалыптастырылмады!

Орын алған жағдайдың себебін жою үшін сілтеме арқылы өтіңіз:
#LINK_URL#
";
$MESS["SALE_CHECK_VALIDATION_ERROR_HTML_TITLE"] = "Чекті қалыптастырудағы қате";
$MESS["SALE_CHECK_VALIDATION_ERROR_SUBJECT"] = "Чекті қалыптастырудағы қате";
$MESS["SALE_CHECK_VALIDATION_ERROR_TYPE_DESC"] = "#ORDER_ACCOUNT_NUMBER# - тапсырыс коды
#ORDER_DATE# - тапсырыс күні
#ORDER_ID# - тапсырыс ID-і";
$MESS["SALE_CHECK_VALIDATION_ERROR_TYPE_NAME"] = "Чекті қалыптастыру кезіндегі қате туралы хабарлама";
$MESS["SALE_MAIL_EVENT_TEMPLATE"] = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"ru\" lang=\"ru\">
<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html;charset=#SITE_CHARSET#\"/>
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
<table cellpadding=\"0\" cellspacing=\"0\" width=\"850\" style=\"background-color: #d1d1d1; border-radius: 2px; border:1px solid #d1d1d1; margin: 0 auto;\" border=\"1\" bordercolor=\"#d1d1d1\">
	<tr>
		<td height=\"83\" width=\"850\" bgcolor=\"#eaf3f5\" style=\"border: none; padding-top: 23px; padding-right: 17px; padding-bottom: 24px; padding-left: 17px;\">
			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">
				<tr>
					<td bgcolor=\"#ffffff\" height=\"75\" style=\"font-weight: bold; text-align: center; font-size: 26px; color: #0b3961;\">#TITLE#</td>
				</tr>
				<tr>
					<td bgcolor=\"#bad3df\" height=\"11\"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width=\"850\" bgcolor=\"#f7f7f7\" valign=\"top\" style=\"border: none; padding-top: 0; padding-right: 44px; padding-bottom: 16px; padding-left: 44px;\">
			<p style=\"margin-top:30px; margin-bottom: 28px; font-weight: bold; font-size: 19px;\">#SUB_TITLE#</p>
			<p style=\"margin-top: 0; margin-bottom: 20px; line-height: 20px;\">#TEXT#</p>
		</td>
	</tr>
	<tr>
		<td height=\"40px\" width=\"850\" bgcolor=\"#f7f7f7\" valign=\"top\" style=\"border: none; padding-top: 0; padding-right: 44px; padding-bottom: 30px; padding-left: 44px;\">
			<p style=\"border-top: 1px solid #d1d1d1; margin-bottom: 5px; margin-top: 0; padding-top: 20px; line-height:21px;\">#FOOTER_BR# <a href=\"http://#SERVER_NAME#\" style=\"color:#2e6eb6;\">#FOOTER_SHOP#</a><br />
				E-mail: <a href=\"mailto:#SALE_EMAIL#\" style=\"color:#2e6eb6;\">#SALE_EMAIL#</a>
			</p>
		</td>
	</tr>
</table>
</body>
</html>";
$MESS["SALE_NEW_ORDER_DESC"] = "#ORDER_ID# - тапсырыс коды
#ORDER_ACCOUNT_NUMBER_ENCODE# - тапсырыс коды (сілтемелер үшін)
#ORDER_REAL_ID# - тапсырыстың нақты ID-і
#ORDER_DATE# - тапсырыс күні
#ORDER_USER# - тапсырыс беруші
#PRICE# - тапсырыс сомасы
#EMAIL# - тапсырыс берушінің E-Mail-ы
#BCC# - жасырын көшірме E-Mail-ы
#ORDER_LIST# - тапсырыс құрамы
#ORDER_PUBLIC_URL# - авторландырусыз тапсырысты қарауға арналған сілтеме (интернет-дүкен модулінде баптау қажет етіледі)
#SALE_EMAIL# - сату бөлімінің E-Mail-ы";
$MESS["SALE_NEW_ORDER_HTML_SUB_TITLE"] = "Құрметті #ORDER_USER#,";
$MESS["SALE_NEW_ORDER_HTML_TEXT"] = "Сіздің #ORDER_DATE# күнгі #ORDER_ID# нөмірі бар тапсырыс қабылданды.

Тапсырыс құны: #PRICE#.

Тапсырыс құрамы:
#ORDER_LIST#

Сіз #SITE_NAME# сайтының жеке бөліміне кіру арқылы тапсырысыңыздың орындалуын (орындалудың қай сатысында екенін) бақылай аласыз.

Бұл бөлімге кіру үшін #SITE_NAME# сайт пайдаланушысының логині мен құпиясөзін енгізу қажет екенін ескеріңіз.

Тапсырысты жою үшін #SITE_NAME# сайтының жеке бөлімінде қолжетімді тапсырыстан бас тарту функциясын қолданыңыз.

Өтінеміз, #SITE_NAME# сайтының әкімшілігіне жүгінгенде МІНДЕТТІ ТҮРДЕ тапсырысыңыздың нөмірін көрсетіңіз - #ORDER_ID#.

Сатып алғаныңыз үшін рахмет!
";
$MESS["SALE_NEW_ORDER_HTML_TITLE"] = "Сіз #SITE_NAME# дүкенінде тапсырыс жасадыңыз";
$MESS["SALE_NEW_ORDER_MESSAGE"] = "#SITE_NAME# сайтының ақпараттық хабарламасы
------------------------------------------

Құрметті #ORDER_USER#,

Сіздің #ORDER_DATE# күнгі #ORDER_ID# нөмірі бар тапсырыс қабылданды.

Тапсырыс құны: #PRICE#.

Тапсырыс құрамы:
#ORDER_LIST#

Сіз #SITE_NAME# сайтының жеке бөліміне кіру
арқылы тапсырысыңыздың орындалуын (ол орындалудың қай сатысында
екенін) бақылай аласыз. Бұл бөлімге кіру үшін #SITE_NAME#
сайтының логині мен құпиясөзін енгізу қажет
екеніне назар аударыңыз.

Тапсырысты жою үшін #SITE_NAME# сайтының
жеке бөлімінде қолжетімді бас тарту
функциясын қолданыңыз.

Өтінеміз, #SITE_NAME# сайтының әкімшілігіне жүгінгенде
Тапсырыс нөмірін МІНДЕТТІ ТҮРДЕ көрсетіңіз - #ORDER_ID#.

Сатып алғаныңыз үшін рахмет!
";
$MESS["SALE_NEW_ORDER_NAME"] = "Жаңа тапсырыс";
$MESS["SALE_NEW_ORDER_RECURRING_DESC"] = "#ORDER_ID# - тапсырыс коды
#ORDER_ACCOUNT_NUMBER_ENCODE# - тапсырыс коды (сілтемелер үшін)
#ORDER_REAL_ID# - тапсырыстың нақты ID-і
#ORDER_DATE# - тапсырыс күні
#ORDER_USER# - тапсырыс беруші
#PRICE# - тапсырыс сомасы
#EMAIL# - тапсырыс берушінің E-Mail-ы
#BCC# - жасырын көшірме E-Mail-ы
#ORDER_LIST# - тапсырыс құрамы
#SALE_EMAIL# - сату бөлімінің E-Mail-ы";
$MESS["SALE_NEW_ORDER_RECURRING_MESSAGE"] = "#SITE_NAME# сайтының ақпараттық хабарламасы
------------------------------------------

Құрметті #ORDER_USER#,

Сіздің #ORDER_DATE# күнгі #ORDER_ID# нөмірі бар жазылымды ұзартуға байланысты тапсырысыңыз қабылданды.

Тапсырыс құны: #PRICE#.

Тапсырыс құрамы:
#ORDER_LIST#

Сіз #SITE_NAME# сайтының жеке бөліміне кіру
арқылы тапсырысыңыздың орындалуын (ол орындалудың қай сатысында
екенін) бақылай аласыз. Бұл бөлімге кіру үшін #SITE_NAME#
сайтының логині мен құпиясөзін енгізу қажет
екеніне назар аударыңыз.

Тапсырысты жою үшін #SITE_NAME# сайтының
жеке бөлімінде қолжетімді бас тарту
функциясын қолданыңыз.

Өтінеміз, #SITE_NAME# сайтының әкімшілігіне жүгінгенде
Тапсырыс нөмірін МІНДЕТТІ ТҮРДЕ көрсетіңіз - #ORDER_ID#.

Сатып алғаныңыз үшін рахмет!
";
$MESS["SALE_NEW_ORDER_RECURRING_NAME"] = "Жазылымды ұзартуға жаңа тапсырыс";
$MESS["SALE_NEW_ORDER_RECURRING_SUBJECT"] = "#SITE_NAME#: Жазылымды созуға жаңа тапсырыс N#ORDER_ID#";
$MESS["SALE_NEW_ORDER_SUBJECT"] = "#SITE_NAME#: N#ORDER_ID# жаңа тапсырысы";
$MESS["SALE_ORDER_CANCEL_DESC"] = "#ORDER_ID# - тапсырыс коды
#ORDER_ACCOUNT_NUMBER_ENCODE# - тапсырыс коды (сілтемелер үшін)
#ORDER_REAL_ID# - тапсырыстың нақты ID-і
#ORDER_DATE# - тапсырыс күні
#EMAIL# - пайдаланушының E-Mail-і
#ORDER_CANCEL_DESCRIPTION# - болдырмау себебі
#ORDER_PUBLIC_URL# - авторландырусыз тапсырысты қарауға арналған сілтеме (интернет-дүкен модулінде баптау қажет етіледі)
#SALE_EMAIL# - сату бөлімінің E-Mail-ы";
$MESS["SALE_ORDER_CANCEL_HTML_SUB_TITLE"] = "#ORDER_DATE# күнгі #ORDER_ID# тапсырысы жойылды.";
$MESS["SALE_ORDER_CANCEL_HTML_TEXT"] = "#ORDER_CANCEL_DESCRIPTION#

Тапсырыс бойынша толық ақпарат алу үшін http://#SERVER_NAME#/personal/order/#ORDER_ACCOUNT_NUMBER_ENCODE#/ сайтына өтіңіз
";
$MESS["SALE_ORDER_CANCEL_HTML_TITLE"] = "#SITE_NAME#: N#ORDER_ID# тапсырысын болдырмау";
$MESS["SALE_ORDER_CANCEL_MESSAGE"] = "#SITE_NAME# сайтының ақпараттық хабарламасы
------------------------------------------

#ORDER_DATE# күнгі #ORDER_ID# тапсырысы жойылды.

#ORDER_CANCEL_DESCRIPTION#

Тапсырыс бойынша толық ақпарат алу үшін http://#SERVER_NAME#/personal/order/#ORDER_ACCOUNT_NUMBER_ENCODE#/ сайтына өтіңіз

#SITE_NAME#
";
$MESS["SALE_ORDER_CANCEL_NAME"] = "Тапсырысты болдырмау";
$MESS["SALE_ORDER_CANCEL_SUBJECT"] = "#SITE_NAME#: N#ORDER_ID# тапсырысын болдырмау";
$MESS["SALE_ORDER_DELIVERY_DESC"] = "#ORDER_ID# - тапсырыс коды
#ORDER_ACCOUNT_NUMBER_ENCODE# - тапсырыс коды (сілтемелер үшін)
#ORDER_REAL_ID# - тапсырыстың нақты ID-і
#ORDER_DATE# - тапсырыс күні
#EMAIL# - пайдаланушының E-Mail-і
#ORDER_PUBLIC_URL# - авторландырусыз тапсырысты қарауға арналған сілтеме (интернет-дүкен модулінде баптау қажет етіледі)
#SALE_EMAIL# - сату бөлімінің E-Mail-ы";
$MESS["SALE_ORDER_DELIVERY_HTML_SUB_TITLE"] = "#ORDER_DATE# күнгі #ORDER_ID# нөмірі бар тапсырысты жеткізуге рұқсат етілді.";
$MESS["SALE_ORDER_DELIVERY_HTML_TEXT"] = "Тапсырыс бойынша толық ақпарат алу үшін http://#SERVER_NAME#/personal/order/#ORDER_ACCOUNT_NUMBER_ENCODE#/ сайтына өтіңіз
";
$MESS["SALE_ORDER_DELIVERY_HTML_TITLE"] = "Тапсырысыңызды #SITE_NAME# сайтынан жеткізуге рұқсат етілді";
$MESS["SALE_ORDER_DELIVERY_MESSAGE"] = "#SITE_NAME# сайтының ақпараттық хабарламасы
------------------------------------------

#ORDER_DATE# күнгі #ORDER_ID# нөмірі бар тапсырысты жеткізуге рұқсат етілді.

Тапсырыс бойынша толық ақпарат алу үшін http://#SERVER_NAME#/personal/order/#ORDER_ACCOUNT_NUMBER_ENCODE#/ сайтына өтіңіз

#SITE_NAME#
";
$MESS["SALE_ORDER_DELIVERY_NAME"] = "Тапсырысты жеткізуге рұқсат етілген";
$MESS["SALE_ORDER_DELIVERY_SUBJECT"] = "#SITE_NAME#: N#ORDER_ID# тапсырысын жеткізуге рұқсат етілген";
$MESS["SALE_ORDER_PAID_DESC"] = "#ORDER_ID# - тапсырыс коды
#ORDER_ACCOUNT_NUMBER_ENCODE# - тапсырыс коды (сілтемелер үшін)
#ORDER_REAL_ID# - тапсырыстың нақты ID-і
#ORDER_DATE# - тапсырыс күні
#EMAIL# - пайдаланушының E-Mail-і
#ORDER_PUBLIC_URL# - авторландырусыз тапсырысты қарауға арналған сілтеме (интернет-дүкен модулінде баптау қажет етіледі)
#SALE_EMAIL# - сату бөлімінің E-Mail-ы";
$MESS["SALE_ORDER_PAID_HTML_SUB_TITLE"] = "#ORDER_DATE# күнгі #ORDER_ID# нөмірі бар тапсырыс төленді.";
$MESS["SALE_ORDER_PAID_HTML_TEXT"] = "Тапсырыс бойынша толық ақпарат алу үшін http://#SERVER_NAME#/personal/order/#ORDER_ACCOUNT_NUMBER_ENCODE#/ сайтына өтіңіз";
$MESS["SALE_ORDER_PAID_HTML_TITLE"] = "Сіз #SITE_NAME# сайтында тапсырысты төледіңіз";
$MESS["SALE_ORDER_PAID_MESSAGE"] = "#SITE_NAME# сайтының ақпараттық хабарламасы
------------------------------------------

#ORDER_DATE# күнгі #ORDER_ID# нөмірі бар тапсырыс төленді.

Тапсырыс бойынша толық ақпарат алу үшін http://#SERVER_NAME#/personal/order/#ORDER_ACCOUNT_NUMBER_ENCODE#/ сайтына өтіңіз

#SITE_NAME#
";
$MESS["SALE_ORDER_PAID_NAME"] = "Тапсырыс төленді";
$MESS["SALE_ORDER_PAID_SUBJECT"] = "#SITE_NAME#: N#ORDER_ID# тапсырысы төленді";
$MESS["SALE_ORDER_REMIND_PAYMENT_DESC"] = "#ORDER_ID# - тапсырыс коды
#ORDER_ACCOUNT_NUMBER_ENCODE# - тапсырыс коды (сілтемелер үшін)
#ORDER_REAL_ID# - тапсырыстың нақты ID-і
#ORDER_DATE# - тапсырыс күні
#ORDER_USER# - тапсырыс беруші
#PRICE# - тапсырыс сомасы
#EMAIL# - тапсырыс берушінің E-Mail-ы
#BCC# - жасырын көшірме E-Mail-ы
#ORDER_LIST# - тапсырыс құрамы
#ORDER_PUBLIC_URL# - авторландырусыз тапсырысты қарауға арналған сілтеме (интернет-дүкен модулінде баптау қажет етіледі)
#SALE_EMAIL# - сату бөлімінің E-Mail-ы";
$MESS["SALE_ORDER_REMIND_PAYMENT_HTML_SUB_TITLE"] = "Құрметті #ORDER_USER#,";
$MESS["SALE_ORDER_REMIND_PAYMENT_HTML_TEXT"] = "Сіз #ORDER_DATE# күні #PRICE# сомаға N #ORDER_ID# тапсырысын жасадыңыз.

Өкінішке орай, қазіргі таңда бұл тапсырыс бойынша қаражат бізге түспеді.

Сіз #SITE_NAME# сайтының жеке бөліміне кіру арқылы тапсырысыңыздың орындалуын (орындалудың қай сатысында екенін) бақылай аласыз.

Бұл бөлімге кіру үшін #SITE_NAME# сайт пайдаланушысының логині мен құпиясөзін енгізу қажет екенін ескеріңіз.

Тапсырысты жою үшін #SITE_NAME# сайтының жеке бөлімінде қолжетімді тапсырыстан бас тарту функциясын қолданыңыз.

Өтінеміз, #SITE_NAME# сайтының әкімшілігіне жүгінгенде МІНДЕТТІ ТҮРДЕ тапсырысыңыздың нөмірін көрсетіңіз - #ORDER_ID#.

Сатып алғаныңыз үшін рахмет!
";
$MESS["SALE_ORDER_REMIND_PAYMENT_HTML_TITLE"] = "#SITE_NAME# сайтында тапсырысты төлеу туралы еске саламыз";
$MESS["SALE_ORDER_REMIND_PAYMENT_MESSAGE"] = "#SITE_NAME# сайтының ақпараттық хабарламасы
------------------------------------------

Құрметті #ORDER_USER#,

Сіз #ORDER_DATE# күні #PRICE# сомаға N #ORDER_ID# тапсырысын жасадыңыз.

Өкінішке орай, қазіргі таңда бұл тапсырыс бойынша қаражат бізге түспеді.

Сіз #SITE_NAME# сайтының жеке бөліміне кіру
арқылы тапсырысыңыздың орындалуын (ол орындалудың қай сатысында
екенін) бақылай аласыз. Бұл бөлімге кіру үшін #SITE_NAME#
сайтының логині мен құпиясөзін енгізу қажет
екеніне назар аударыңыз.

Тапсырысты жою үшін #SITE_NAME# сайтының
жеке бөлімінде қолжетімді бас тарту
функциясын қолданыңыз.

Өтінеміз, #SITE_NAME# сайтының әкімшілігіне жүгінгенде
Тапсырыс нөмірін МІНДЕТТІ ТҮРДЕ көрсетіңіз - #ORDER_ID#.

Сатып алғаныңыз үшін рахмет!
";
$MESS["SALE_ORDER_REMIND_PAYMENT_NAME"] = "Тапсырысты төлеу туралы ескерту";
$MESS["SALE_ORDER_REMIND_PAYMENT_SUBJECT"] = "#SITE_NAME#: N#ORDER_ID# тапсырысын төлеу туралы еске салу ";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_HTML_SUB_TITLE"] = "Құрметті #ORDER_USER#,";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_HTML_TEXT"] = "#ORDER_DATE# күнгі № #ORDER_NO# тапсырыс бойынша пошта жөнелтімінің мәртебесі

мәнді \"#STATUS_NAME#\"-ге (#STATUS_DESCRIPTION#) өзгертті.

Жөнелту идентификаторы: #TRACKING_NUMBER#.

Жеткізу қызметінің атауы: #DELIVERY_NAME#.

#DELIVERY_TRACKING_URL##ORDER_DETAIL_URL#
";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_HTML_TITLE"] = "#SITE_NAME# сайтындағы тапсырыстың пошта жөнелтімі өзгерді";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_SUBJECT"] = "#SITE_NAME# сайтындағы тапсырысыңыздың пошта жөнелтімінің мәртебесі өзгерді";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_TYPE_DESC"] = "#SHIPMENT_NO# - жөнелту нөмірі
#SHIPMENT_DATE# - жөнелту күні
#ORDER_NO# - тапсырыс нөмірі
#ORDER_DATE# - тапсырыс күні
#STATUS_NAME# - мәртебе атауы
#STATUS_DESCRIPTION# - мәртебе сипаттамасы
#TRACKING_NUMBER# - пошта жіберілімінің идентификаторы
#EMAIL# - хат кімге жіберіледі
#BCC# - хаттың көшірмесі кімге жіберіледі
#ORDER_USER# - тапсырыс беруші
#DELIVERY_NAME# - жеткізу қызметінің атауы
#DELIVERY_TRACKING_URL# - жеткізу қызметінің сайтындағы сілтеме, онда сіз жөнелту мәртебесі туралы көбірек біле аласыз
#ORDER_ACCOUNT_NUMBER_ENCODE# - тапсырыс коды (сілтемелер үшін)
#ORDER_PUBLIC_URL# - авторландырусыз тапсырысты қарауға арналған сілтеме (интернет-дүкен модулінде баптау қажет етіледі)
#ORDER_DETAIL_URL# - тапсырыс туралы толық ақпаратты көруге арналған сілтеме";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_TYPE_NAME"] = "Пошта жөнелтімінің мәртебесін өзгерту туралы хабарлама";
$MESS["SALE_ORDER_TRACKING_NUMBER_HTML_SUB_TITLE"] = "Құрметті #ORDER_USER#,";
$MESS["SALE_ORDER_TRACKING_NUMBER_HTML_TEXT"] = "#ORDER_DATE# күні N#ORDER_ID# тапсырысы жіберілді.

Жөнелту идентификаторының нөмірі: #ORDER_TRACKING_NUMBER#.

Тапсырыс бойынша толық ақпарат алу үшін http://#SERVER_NAME#/personal/order/detail/#ORDER_ACCOUNT_NUMBER_ENCODE#/ сайтына өтіңіз

E-mail: #SALE_EMAIL#
";
$MESS["SALE_ORDER_TRACKING_NUMBER_HTML_TITLE"] = "#SITE_NAME# сайтындағы тапсырысыңыздың жөнелту идентификаторының нөмірі";
$MESS["SALE_ORDER_TRACKING_NUMBER_MESSAGE"] = "#ORDER_DATE# күні N#ORDER_ID# тапсырысы жіберілді.

Жөнелту идентификаторының нөмірі: #ORDER_TRACKING_NUMBER#.

Тапсырыс бойынша толық ақпарат алу үшін http://#SERVER_NAME#/personal/order/detail/#ORDER_ACCOUNT_NUMBER_ENCODE#/ сайтына өтіңіз

E-mail: #SALE_EMAIL#
";
$MESS["SALE_ORDER_TRACKING_NUMBER_SUBJECT"] = "#SITE_NAME# сайтындағы тапсырысыңыздың жөнелту идентификаторының нөмірі";
$MESS["SALE_ORDER_TRACKING_NUMBER_TYPE_DESC"] = "#ORDER_ID# - тапсырыс коды
#ORDER_ACCOUNT_NUMBER_ENCODE# - тапсырыс коды (сілтемелер үшін)
#ORDER_REAL_ID# - тапсырыстың нақты ID-і
#ORDER_DATE# - тапсырыс күні
#ORDER_USER# - тапсырыс беруші
#ORDER_TRACKING_NUMBER# - пошта жіберілімінің идентификаторы
#ORDER_PUBLIC_URL# - авторландырусыз тапсырысты қарауға арналған сілтеме (интернет-дүкен модулінде баптау қажет етіледі)
#EMAIL# - тапсырыс берушінің E-Mail-ы
#BCC# - жасырын көшірме E-Mail-ы
#SALE_EMAIL# - сату бөлімінің E-Mail-ы";
$MESS["SALE_ORDER_TRACKING_NUMBER_TYPE_NAME"] = "Пошта жөнелтімінің идентификаторын өзгерту туралы хабарлама";
$MESS["SALE_RECURRING_CANCEL_DESC"] = "#ORDER_ID# - тапсырыс коды
#ORDER_ACCOUNT_NUMBER_ENCODE# - тапсырыс коды (сілтемелер үшін)
#ORDER_REAL_ID# - тапсырыстың нақты ID-і
#ORDER_DATE# - тапсырыс күні
#EMAIL# - пайдаланушының E-Mail-і
#CANCELED_REASON# - болдырмау себебі
#SALE_EMAIL# - сату бөлімінің E-Mail-ы";
$MESS["SALE_RECURRING_CANCEL_MESSAGE"] = "#SITE_NAME# сайтының ақпараттық хабарламасы
------------------------------------------

Жазылым тоқтатылды

#CANCELED_REASON#
#SITE_NAME#
";
$MESS["SALE_RECURRING_CANCEL_NAME"] = "Жазылым тоқтатылды";
$MESS["SALE_RECURRING_CANCEL_SUBJECT"] = "#SITE_NAME#: Жазылым тоқтатылды";
$MESS["SALE_SUBSCRIBE_PRODUCT_HTML_SUB_TITLE"] = "Құрметті, #USER_NAME#!";
$MESS["SALE_SUBSCRIBE_PRODUCT_HTML_TEXT"] = "\"#NAME#\" (#PAGE_URL#) тауары қоймаға келіп түсті.

Сіз тапсырыс бере аласыз (http://#SERVER_NAME#/personal/cart/).

Авторландырылуды ұмытпаңыз!

Сіз бұл хабарламаны тауар пайда болған кезде хабарлау туралы өтініш қалдырғандықтан алдыңыз.
Оған жауап бермеңіз - хат автоматты түрде құрылды.

Сатып алғаныңыз үшін рахмет!
";
$MESS["SALE_SUBSCRIBE_PRODUCT_HTML_TITLE"] = "#SITE_NAME# дүкеніне тауардың түскені туралы мәлімдеме";
$MESS["SALE_SUBSCRIBE_PRODUCT_SUBJECT"] = "#SITE_NAME#: Тауардың түскені туралы мәлімдеме";
$MESS["SKGS_STATUS_MAIL_HTML_TITLE"] = "Тапсырыс мәртебесін #SITE_NAME# дүкенінде өзгерту";
$MESS["SMAIL_FOOTER_BR"] = "Құрметпен,<br>";
$MESS["SMAIL_FOOTER_SHOP"] = "Интернет-дүкен әкімшілігі";
$MESS["UP_MESSAGE"] = "#SITE_NAME# сайтының ақпараттық хабарламасы
------------------------------------------

Құрметті, #USER_NAME#!

\"#NAME#\" (#PAGE_URL#) тауары қоймаға келіп түсті.
Сіз тапсырыс бере аласыз (http://#SERVER_NAME#/personal/cart/).

Авторландырылуды ұмытпаңыз!

Сіз бұл хабарламаны тауар пайда болған кезде хабарлау туралы өтініш қалдырғандықтан алдыңыз.
Оған жауап бермеңіз - хат автоматты түрде құрылды.

Сатып алғаныңыз үшін рахмет!
";
$MESS["UP_SUBJECT"] = "#SITE_NAME#: Тауардың түскені туралы мәлімдеме";
$MESS["UP_TYPE_SUBJECT"] = "Тауардың түскені туралы мәлімдеме";
$MESS["UP_TYPE_SUBJECT_DESC"] = "#USER_NAME# - Пайдаланушының аты
#EMAIL# - пайдаланушының email-ы
#NAME# - тауардың атауы
#PAGE_URL# - тауардың егжей-тегжейлі бпарақшасы";
