<?php
$MESS["SALE_CHECK_PRINT_ERROR_HTML_SUB_TITLE"] = "Вітаю!";
$MESS["SALE_CHECK_PRINT_ERROR_HTML_TEXT"] = "
З якоїсь причини чек №#CHECK_ID# по замовленню №#ORDER_ACCOUNT_NUMBER# від #ORDER_DATE# не вдалося роздрукувати!

Перейдіть за посиланням, щоб усунути причину цієї ситуації:
#LINK_URL#";
$MESS["SALE_CHECK_PRINT_ERROR_HTML_TITLE"] = "Помилка при друку чека";
$MESS["SALE_CHECK_PRINT_ERROR_SUBJECT"] = "Помилка при друку чека";
$MESS["SALE_CHECK_PRINT_ERROR_TYPE_DESC"] = "#ORDER_ACCOUNT_NUMBER# - код замовлення
#ORDER_DATE# - дата замовлення
#ORDER_ID# - ID замовлення
#CHECK_ID# - номер чека";
$MESS["SALE_CHECK_PRINT_ERROR_TYPE_NAME"] = "Сповіщення про помилку при друку чека";
$MESS["SALE_CHECK_PRINT_HTML_SUB_TITLE"] = "Шановний #ORDER_USER#,";
$MESS["SALE_CHECK_PRINT_HTML_TEXT"] = "Згідно з вимогами закону ФЗ-54 про фіскальні чеки на ваше замовлення здійснено оплату та сформований фіскальний касовий чек, який ви можете переглянути за посиланням: #CHECK_LINK#  Для отримання докладної інформації по замовленню №#ORDER_ID# от #ORDER_DATE# пройдіть на сайт http://#SERVER_NAME#/personal/order/detail/#ORDER_ACCOUNT_NUMBER_ENCODE#/";
$MESS["SALE_CHECK_PRINT_HTML_TITLE"] = "Ви сплатили замовлення на сайті #SITE_NAME#";
$MESS["SALE_CHECK_PRINT_SUBJECT"] = "Посилання на чек";
$MESS["SALE_CHECK_PRINT_TYPE_DESC"] = "#ORDER_ID# - код замовлення #ORDER_DATE# - дата замовлення #ORDER_USER# - замовник #ORDER_ACCOUNT_NUMBER_ENCODE# - код замовлення (для посилань) #CHECK_LINK# - посилання на чек";
$MESS["SALE_CHECK_PRINT_TYPE_NAME"] = "Сповіщення про друк чека";
$MESS["SALE_CHECK_VALIDATION_ERROR_HTML_SUB_TITLE"] = "Вітаємо!";
$MESS["SALE_CHECK_VALIDATION_ERROR_HTML_TEXT"] = "
З якоїсь причини чек на замовлення № #ORDER_ACCOUNT_NUMBER# від #ORDER_DATE# не був сформований!

Перейдіть за посиланням, щоб усунути причину ситуації, що виникла:
#LINK_URL#";
$MESS["SALE_CHECK_VALIDATION_ERROR_HTML_TITLE"] = "Помилка при формуванні чека";
$MESS["SALE_CHECK_VALIDATION_ERROR_SUBJECT"] = "Помилка при формуванні чека";
$MESS["SALE_CHECK_VALIDATION_ERROR_TYPE_DESC"] = "#ORDER_ACCOUNT_NUMBER# – код замовлення
#ORDER_DATE# – дата замовлення
#ORDER_ID# – ID замовлення ";
$MESS["SALE_CHECK_VALIDATION_ERROR_TYPE_NAME"] = "Сповіщення про помилку при формуванні чека";
$MESS["SALE_MAIL_EVENT_TEMPLATE"] = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"ru\" lang=\"ru\">
<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html;charset=#SITE_CHARSET#\"/>
	<style>
		body
		{
			font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
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
$MESS["SALE_NEW_ORDER_DESC"] = "#ORDER_ID# — код замовлення
#ORDER_DATE# — дата замовлення
#ORDER_USER# — замовник
#PRICE# — сумма замовлення
#EMAIL# — E-mail замовника
#BCC# — E-mail прихованої копії
#ORDER_LIST# — склад замовлення
#SALE_EMAIL# — E-mail відділу продажів";
$MESS["SALE_NEW_ORDER_HTML_SUB_TITLE"] = "Шановний #ORDER_USER#,";
$MESS["SALE_NEW_ORDER_HTML_TEXT"] = "Ваше замовлення номер #ORDER_ID# від #ORDER_DATE# прийняте.

Вартість замовлення: #PRICE#.

Склад замовлення:
#ORDER_LIST#

Ви можете слідкувати за виконанням свого замовлення (на якій стадії виконання він знаходиться), увійшовши у Ваш персональний розділ сайту #SITE_NAME#.

Зверніть увагу, що для входу в цей розділ Вам необхідно буде ввести логін і пароль користувача сайту #SITE_NAME#.

Для того, щоб анулювати замовлення, скористайтеся функцією відміни замовлення, яка доступна у Вашому персональному розділі сайту #SITE_NAME#.

Будь ласка, при зверненні до адміністрації сайту #SITE_NAME# ОБОВ'ЯЗКОВО вказуйте номер Вашого замовлення - #ORDER_ID#.

Дякуємо за покупку!
";
$MESS["SALE_NEW_ORDER_HTML_TITLE"] = "Вами оформлено замовлення в магазині #SITE_NAME#";
$MESS["SALE_NEW_ORDER_MESSAGE"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------

Шановний #ORDER_USER#,

Ваше замовлення номер #ORDER_ID# от #ORDER_DATE# прийнято.

Вартість замовлення: #PRICE#.

Склад замовлення:
#ORDER_LIST#

Ви можете слідкувати за виконанням свого замовлення (на якій
стадії виконання він знаходиться), якщо увійдете у Ваш персональний
розділ сайту #SITE_NAME#. Зверніть увагу, що для входу
до цього розділу Вам необхідно буде ввести логін та пароль
користувача сайту #SITE_NAME#.

Для того, щоб анулювати замовлення, скористуйтеся функцією
скасування замовлення, яка доступна у Вашому персональному
розділі сайту #SITE_NAME#.

Будь ласка, при зверненні до адміністрації сайту #SITE_NAME#
ОБОВ'ЯЗКОВО вказуйте номер Вашого замовлення — #ORDER_ID#.

Дякуємо за покупку!
";
$MESS["SALE_NEW_ORDER_NAME"] = "Нове замовлення";
$MESS["SALE_NEW_ORDER_RECURRING_DESC"] = "#ORDER_ID# — код замовлення
#ORDER_DATE# — дата замовлення
#ORDER_USER# — замовник
#PRICE# — сума замовлення
#EMAIL# — e-mail замовника
#BCC# — e-mail прихованої копії
#ORDER_LIST# — склад замовлення
#SALE_EMAIL# — e-mail відділу продажів";
$MESS["SALE_NEW_ORDER_RECURRING_MESSAGE"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------

Шановний #ORDER_USER#,

Ваше замовлення номер #ORDER_ID# від #ORDER_DATE# на подовження передплати прийнято.

Вартість замовлення: #PRICE#.

Склад замовлення:
#ORDER_LIST#

Ви можете слідкувати за виконанням свого замовлення (на якій
стадії виконання він знаходиться), якщо увійдете у Ваш персональний
розділ сайту #SITE_NAME#. Зверніть увагу, що для входу
до цього розділу Вам необхідно буде ввести логін та пароль
користувача сайту #SITE_NAME#.

Для того, щоб анулювати замовлення, скористайтеся функцією
скасування замовлення, яка доступна в Вашому персональному
розділі сайта #SITE_NAME#.

Будь ласка, при зверненні до адміністрації сайту #SITE_NAME#
ОБОВ'ЯЗКОВО вказуйте номер Вашого замовлення — #ORDER_ID#.

Дякуємо за покупку!
";
$MESS["SALE_NEW_ORDER_RECURRING_NAME"] = "Нове замовлення на подовження передплати";
$MESS["SALE_NEW_ORDER_RECURRING_SUBJECT"] = "#SITE_NAME#: Нове замовлення N#ORDER_ID# на подовження передплати";
$MESS["SALE_NEW_ORDER_SUBJECT"] = "#SITE_NAME#: Нове замовлення N#ORDER_ID#";
$MESS["SALE_ORDER_CANCEL_DESC"] = "#ORDER_ID# — код замовлення
#ORDER_DATE# — дата замовлення
#EMAIL# — e-mail користувача
#ORDER_CANCEL_DESCRIPTION# — причина скасування
#SALE_EMAIL# — e-mail відділу продажів";
$MESS["SALE_ORDER_CANCEL_HTML_SUB_TITLE"] = "Замовлення номер #ORDER_ID# від #ORDER_DATE# скасоване.";
$MESS["SALE_ORDER_CANCEL_HTML_TEXT"] = "#ORDER_CANCEL_DESCRIPTION#

Для отримання детальної інформації з замовленням пройдіть на сайт http://#SERVER_NAME#/personal/order/#ORDER_ID#/
";
$MESS["SALE_ORDER_CANCEL_HTML_TITLE"] = "#SITE_NAME#: Скасування замовлення N #ORDER_ID#";
$MESS["SALE_ORDER_CANCEL_MESSAGE"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------

Замовлення номер #ORDER_ID# від #ORDER_DATE# скасовано.

#ORDER_CANCEL_DESCRIPTION#

Для отримання докладної інформації по замовленню пройдіть на сайт http://#SERVER_NAME#/personal/order/#ORDER_ACCOUNT_NUMBER_ENCODE#/

#SITE_NAME#";
$MESS["SALE_ORDER_CANCEL_NAME"] = "Скасування замовлення";
$MESS["SALE_ORDER_CANCEL_SUBJECT"] = "#SITE_NAME#: Скасування замовлення N#ORDER_ID#";
$MESS["SALE_ORDER_DELIVERY_DESC"] = "#ORDER_ID# — код замовлення
#ORDER_DATE# — дата замовлення
#EMAIL# — e-mail користувача
#SALE_EMAIL# — e-mail відділу продажів";
$MESS["SALE_ORDER_DELIVERY_HTML_SUB_TITLE"] = "Доставка замовлення номер #ORDER_ID# від #ORDER_DATE# дозволена.";
$MESS["SALE_ORDER_DELIVERY_HTML_TEXT"] = "Для отримання докладної інформації за замовленням пройдіть на сайт http://#SERVER_NAME#/personal/order/#ORDER_ID#/
";
$MESS["SALE_ORDER_DELIVERY_HTML_TITLE"] = "Доставка вашого замовлення з сайту #SITE_NAME# дозволена";
$MESS["SALE_ORDER_DELIVERY_MESSAGE"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------

Доставка замовлення номер #ORDER_ID# від #ORDER_DATE# дозволена.

Для отримання докладної інформації по замовленню пройдіть на сайт http://#SERVER_NAME#/personal/order/#ORDER_ACCOUNT_NUMBER_ENCODE#/

#SITE_NAME#
";
$MESS["SALE_ORDER_DELIVERY_NAME"] = "Доставка замовлення дозволена";
$MESS["SALE_ORDER_DELIVERY_SUBJECT"] = "#SITE_NAME#: Доставка замовлення N #ORDER_ID# дозволена";
$MESS["SALE_ORDER_PAID_DESC"] = "#ORDER_ID# — код замовлення
#ORDER_DATE# — дата замовлення
#EMAIL# — e-mail користувача
#SALE_EMAIL# — e-mail відділу продажів";
$MESS["SALE_ORDER_PAID_HTML_SUB_TITLE"] = "Замовлення номер #ORDER_ID# від #ORDER_DATE# оплачене.";
$MESS["SALE_ORDER_PAID_HTML_TEXT"] = "Для отримання детальної інформації щодо замовлення пройдіть на сайт http://#SERVER_NAME#/personal/order/#ORDER_ID#/";
$MESS["SALE_ORDER_PAID_HTML_TITLE"] = "Ви оплатили замовлення на сайті #SITE_NAME#";
$MESS["SALE_ORDER_PAID_MESSAGE"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------

Замовлення номер #ORDER_ID# від #ORDER_DATE# сплачено.

Для отримання докладної інформації по замовленню пройдіть на сайт  http://#SERVER_NAME#/personal/order/#ORDER_ACCOUNT_NUMBER_ENCODE#/

#SITE_NAME#
";
$MESS["SALE_ORDER_PAID_NAME"] = "Замовлення сплачено";
$MESS["SALE_ORDER_PAID_SUBJECT"] = "#SITE_NAME#: Замовлення  N#ORDER_ID# сплачено";
$MESS["SALE_ORDER_REMIND_PAYMENT_DESC"] = "#ORDER_ID# — код замовлення
#ORDER_DATE# — дата замовлення
#ORDER_USER# — замовник
#PRICE# — сума замовлення
#EMAIL# — e-mail замовника
#BCC# — e-mail прихованої копії
#ORDER_LIST# — склад замовлення
#SALE_EMAIL# — e-mail відділу продажів";
$MESS["SALE_ORDER_REMIND_PAYMENT_HTML_SUB_TITLE"] = "Шановний #ORDER_USE#,";
$MESS["SALE_ORDER_REMIND_PAYMENT_HTML_TEXT"] = "Вами було оформлене замовлення N #ORDER_ID# від #ORDER_DATE# на суму #PRICE#.

На жаль, на сьогоднішній день кошти по цьому замовленню не надійшли до нас.

Ви можете слідкувати за виконанням свого замовлення (на якій стадії виконання він знаходиться), увійшовши у Ваш персональний розділ сайту #SITE_NAME#.

Зверніть увагу, що для входу в цей розділ Вам необхідно буде ввести логін і пароль користувача сайту #SITE_NAME#.

Для того, щоб анулювати замовлення, скористайтеся функцією відміни замовлення, яка доступна у Вашому персональному розділі сайту #SITE_NAME#.

Будь ласка, при зверненні до адміністрації сайту #SITE_NAME# ОБОВ'ЯЗКОВО вказуйте номер Вашого замовлення - #ORDER_ID#.

Дякуємо за покупку!
";
$MESS["SALE_ORDER_REMIND_PAYMENT_HTML_TITLE"] = "Нагадуємо вам про оплату замовлення на сайті #SITE_NAME#";
$MESS["SALE_ORDER_REMIND_PAYMENT_MESSAGE"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------

Шановний #ORDER_USER#,

Вами було оформлене замовлення N #ORDER_ID# від #ORDER_DATE# на суму #PRICE#.

На жаль, на сьогоднішній день кошти за цим замовленням не надійшли до нас. 

Ви можете слідкувати за виконанням свого замовлення (на якій
стадії виконання він знаходиться), якщо увійдете у Ваш персональний
розділ сайту #SITE_NAME#. Зверніть увагу, що для входу
до цього розділу Вам необхідно буде ввести логін та пароль
користувача сайту #SITE_NAME#.

Для того, щоб анулювати замовлення, скористайтеся функцією
скасування замовлення, яка доступна в Вашому персональному
розділі сайта #SITE_NAME#.

Будь ласка, при зверненні до адміністрації сайту #SITE_NAME#
ОБОВ'ЯЗКОВО вказуйте номер Вашого замовлення — #ORDER_ID#.

Дякуємо за покупку!";
$MESS["SALE_ORDER_REMIND_PAYMENT_NAME"] = "Нагадування про оплату замовлення";
$MESS["SALE_ORDER_REMIND_PAYMENT_SUBJECT"] = "#SITE_NAME#: Нагадування про оплату замовлення N #ORDER_ID# ";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_HTML_SUB_TITLE"] = "Шановний #ORDER_USER#,";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_HTML_TEXT"] = "Статус поштового відправлення на замовлення № #ORDER_NO# від #ORDER_DATE#  змінив значення на \"#STATUS_NAME#\" (#STATUS_DESCRIPTION#).  Ідентифікатор відправлення: #TRACKING_NUMBER#.  Найменування служби доставки: #DELIVERY_NAME#.  #DELIVERY_TRACKING_URL##ORDER_DETAIL_URL#";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_HTML_TITLE"] = "Змінився статус поштового відправлення замовлення на сайті #SITE_NAME#";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_SUBJECT"] = "Статус поштового відправлення вашого замовлення на сайті #SITE_NAME# змінився";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_TYPE_DESC"] = "#SHIPMENT_NO# - номер відвантаження  #SHIPMENT_DATE# - дата відвантаження #ORDER_NO# - номер замовлення  #ORDER_DATE# - дата замовлення  #STATUS_NAME# - назва статусу #STATUS_DESCRIPTION# - опис статусу #TRACKING_NUMBER# - ідентифікатор поштового відправлення #EMAIL# - кому буде відправлено лист #BCC# - кому буде відправлена копія листа #ORDER_USER# - замовник #DELIVERY_NAME# - найменування служби доставки #DELIVERY_TRACKING_URL# - посилання на сайті служби доставки, де можна докладніше дізнатися про статус відправлення  #ORDER_ACCOUNT_NUMBER_ENCODE# - код замовлення (для посилань) #ORDER_DETAIL_URL# - посилання для перегляду детальної інформації про замовлення";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_TYPE_NAME"] = "Сповіщення про зміну статусу поштового відправлення";
$MESS["SALE_ORDER_TRACKING_NUMBER_HTML_SUB_TITLE"] = "Шановний #ORDER_USER#,";
$MESS["SALE_ORDER_TRACKING_NUMBER_HTML_TEXT"] = "Сталася поштова відправка замовлення N#ORDER_ID# від #ORDER_DATE#.

Номер ідентифікатора відправлення: #ORDER_TRACKING_NUMBER#.

Для отримання детальної інформації по замовленню пройдіть на сайт http: //#SERVER_NAME#/personal/order/detail/#ORDER_ID#/

E-mail:#SALE_EMAIL#
";
$MESS["SALE_ORDER_TRACKING_NUMBER_HTML_TITLE"] = "Номер ідентифікатора відправлення вашого замовлення на сайті #SITE_NAME#";
$MESS["SALE_ORDER_TRACKING_NUMBER_MESSAGE"] = "Сталася поштова відправка замовлення N#ORDER_ID# від #ORDER_DATE#.

Номер ідентифікатора відправлення: #ORDER_TRACKING_NUMBER#.

Для отримання детальної інформації по замовленню пройдіть на сайт http: //#SERVER_NAME#/personal/order/detail/#ORDER_ID#/

E-mail:#SALE_EMAIL#
";
$MESS["SALE_ORDER_TRACKING_NUMBER_SUBJECT"] = "Номер ідентифікатора відправлення вашого замовлення на сайті #SITE_NAME#";
$MESS["SALE_ORDER_TRACKING_NUMBER_TYPE_DESC"] = "#ORDER_ID#- код замовлення
#ORDER_DATE#- дата замовлення
#ORDER_USER#- замовник
#ORDER_TRACKING_NUMBER#- ідентифікатор поштового відправлення
#EMAIL#- E-Mail замовника
#BCC#- E-Mail прихованої копії
#SALE_EMAIL#- E-Mail відділу продажів
";
$MESS["SALE_ORDER_TRACKING_NUMBER_TYPE_NAME"] = "Сповіщення про зміну ідентифікатора поштового відправлення";
$MESS["SALE_RECURRING_CANCEL_DESC"] = "#ORDER_ID# — код замовлення
#ORDER_DATE# — дата замовлення
#EMAIL# — e-mail користувача
#CANCELED_REASON# — причина скасування
#SALE_EMAIL# — e-mail відділу продажів";
$MESS["SALE_RECURRING_CANCEL_MESSAGE"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------

Підписку скасовано

#CANCELED_REASON#
#SITE_NAME#
";
$MESS["SALE_RECURRING_CANCEL_NAME"] = "Передплата скасована";
$MESS["SALE_RECURRING_CANCEL_SUBJECT"] = "#SITE_NAME#: Передплата скасована";
$MESS["SALE_SUBSCRIBE_PRODUCT_HTML_SUB_TITLE"] = "Шановний, #USER_NAME#!";
$MESS["SALE_SUBSCRIBE_PRODUCT_HTML_TEXT"] = "Товар \"#NAME#\" (#PAGE_URL#) надійшов на склад.

Ви можете Оформити замовлення (http://#SERVER_NAME#/personal/cart/).

Не забудьте авторизуватися!

Ви отримали це повідомлення на Ваше прохання сповістити при появі товару.
Не відповідайте на нього - лист сформований автоматично.

Дякуємо за покупку!";
$MESS["SALE_SUBSCRIBE_PRODUCT_HTML_TITLE"] = "Сповіщення про надходження товару в магазин #SITE_NAME#";
$MESS["SALE_SUBSCRIBE_PRODUCT_SUBJECT"] = "#SITE_NAME#: Сповіщення про надходження товару";
$MESS["SKGS_STATUS_MAIL_HTML_TITLE"] = "Зміна статусу замовлення в магазині #SITE_NAME#";
$MESS["SMAIL_FOOTER_BR"] = "З повагою, <br/> адміністрація";
$MESS["SMAIL_FOOTER_SHOP"] = "Інтернет-магазину";
$MESS["UP_MESSAGE"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------

Шановний, #USER_NAME#!

Товар \"#NAME#\" (#PAGE_URL#) надійшов на склад.
Ви можете Оформити замовлення (http://#SERVER_NAME#/personal/cart/).

Не забудьте авторизуватися!

Ви отримали це повідомлення на Ваше прохання сповістити при появі товару.
Не відповідайте на нього - лист сформований автоматично.

Дякуємо за покупку!";
$MESS["UP_SUBJECT"] = "Даний функціонал не входить у Вашу редакцію продукту.";
$MESS["UP_TYPE_SUBJECT"] = "Сповіщення про надходження товару";
$MESS["UP_TYPE_SUBJECT_DESC"] = "Робота з рахунком заблокована на 1:00";
