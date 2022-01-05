<?php
$MESS["SALE_CHECK_PRINT_ERROR_HTML_SUB_TITLE"] = "Hallo!";
$MESS["SALE_CHECK_PRINT_ERROR_HTML_TEXT"] = "
Der Kassenzettel ##CHECK_ID# für die Bestellung ##ORDER_ACCOUNT_NUMBER# vom #ORDER_DATE# kann nicht gedruckt werden.

Klicken Sie hier, um das Problem zu lösen:
http://#SERVER_NAME#/bitrix/admin/sale_order_view.php?ID=#ORDER_ID#
";
$MESS["SALE_CHECK_PRINT_ERROR_HTML_TITLE"] = "Fehler beim Drucken des Kassenzettels";
$MESS["SALE_CHECK_PRINT_ERROR_SUBJECT"] = "Fehler beim Drucken des Kassenzettels";
$MESS["SALE_CHECK_PRINT_ERROR_TYPE_DESC"] = "#ORDER_ACCOUNT_NUMBER# - ID der Bestellung
#ORDER_DATE# - Bestelldatum
#ORDER_ID# - ID der Bestellung
#CHECK_ID# - ID des Kassenzettels
";
$MESS["SALE_CHECK_PRINT_ERROR_TYPE_NAME"] = "Benachrichtigung über einen Fehler beim Ausdrucken vom Kassenzettel";
$MESS["SALE_CHECK_PRINT_HTML_SUB_TITLE"] = "Liebe(r) #ORDER_USER#,";
$MESS["SALE_CHECK_PRINT_HTML_TEXT"] = "
Ihre Zahlung wurde verarbeitet und ein entsprechender Kassenzettel wurde erstellt. Um den Kassenzettel anzuzeigen, nutzen Sie den Link:

#CHECK_LINK#

Um weitere Details zu Ihrer Bestellung ##ORDER_ID# vom #ORDER_DATE# zu bekommen, nutzen Sie bitte folgenden Link: http://#SERVER_NAME#/personal/order/detail/#ORDER_ACCOUNT_NUMBER_ENCODE#/
";
$MESS["SALE_CHECK_PRINT_HTML_TITLE"] = "Ihre Zahlung für Bestellung mit #SITE_NAME#";
$MESS["SALE_CHECK_PRINT_SUBJECT"] = "Link zum Kassenzettel";
$MESS["SALE_CHECK_PRINT_TYPE_DESC"] = "#ORDER_ID# - ID der Bestellung
#ORDER_DATE# - Bestelldatum
#ORDER_USER# - Kunde
#ORDER_ACCOUNT_NUMBER_ENCODE# - Bestell-ID zur Nutzung in Links
#ORDER_PUBLIC_URL# - Link zur Bestellansicht für nicht autorisierte Nutzer (Konfiguration in den Einstellungen des Moduls Onlineshop ist erforderlich)
#CHECK_LINK# - Link zum Kassenzettel
";
$MESS["SALE_CHECK_PRINT_TYPE_NAME"] = "Benachrichtigungen über Ausdrucken von Kassenzetteln";
$MESS["SALE_CHECK_VALIDATION_ERROR_HTML_SUB_TITLE"] = "Guten Tag";
$MESS["SALE_CHECK_VALIDATION_ERROR_HTML_TEXT"] = "
Es gab ein Problem beim Erstellen des Kassenbons für die Bestellung ##ORDER_ACCOUNT_NUMBER# vom #ORDER_DATE#!

Folgen Sie diesem Link, um das Problem zu beseitigen:
#LINK_URL#
";
$MESS["SALE_CHECK_VALIDATION_ERROR_HTML_TITLE"] = "Fehler bei der Kassenbonerstellung";
$MESS["SALE_CHECK_VALIDATION_ERROR_SUBJECT"] = "Fehler bei der Kassenbonerstellung";
$MESS["SALE_CHECK_VALIDATION_ERROR_TYPE_DESC"] = "#ORDER_ACCOUNT_NUMBER# - Bestellung Nr.
#ORDER_DATE# - Bestelldatum
#ORDER_ID# - ID der Bestellung";
$MESS["SALE_CHECK_VALIDATION_ERROR_TYPE_NAME"] = "Benachrichtigung über den Fehler der Kassenbonerstellung";
$MESS["SALE_MAIL_EVENT_TEMPLATE"] = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
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
$MESS["SALE_NEW_ORDER_DESC"] = "#ORDER_ID# - ID der Bestellung
#ORDER_ACCOUNT_NUMBER_ENCODE# - ID der Bestellung (für URL)
#ORDER_REAL_ID# - wirkliche ID der Bestellung
#ORDER_DATE# - Bestelldatum
#ORDER_USER# - Kunde
#PRICE# - Bestellbetrag
#EMAIL# - E-Mail des Kunden
#BCC# - BCC der E-Mail
#ORDER_LIST# - Inhalte der Bestellung
#ORDER_PUBLIC_URL# - Link zur Bestellansicht für nicht autorisierte Nutzer (Konfiguration in den Einstellungen des Moduls Onlineshop ist erforderlich)
#SALE_EMAIL# - E-Mail der Vertriebsabteilung
";
$MESS["SALE_NEW_ORDER_HTML_SUB_TITLE"] = "Liebe(r) #ORDER_USER#,";
$MESS["SALE_NEW_ORDER_HTML_TEXT"] = "Wir haben Ihre Bestellung ##ORDER_ID# vom #ORDER_DATE# erhalten.

Gesamtbetrag der Bestellung: #PRICE#.

Bestellte Produkte:
#ORDER_LIST#

Sie können den Status Ihrer Bestellung nach erfolgtem Login auf #SITE_NAME# in Ihrem persönlichen Kundenbereich jederzeit einsehen. Sie werden dabei den Login und das Passwort angeben müssen, mit denen Sie sich auf #SITE_NAME# angemeldet haben.

Falls Sie diese Bestellung stornieren möchten, können Sie dies ebenfalls im persönlichen Kundenbereich auf #SITE_NAME# tun.

Halten Sie Ihre Bestellnummer (##ORDER_ID#) bereit, wenn Sie uns bezüglich Ihrer Bestellung kontaktieren.

Vielen Dank für Ihre Bestellung!
";
$MESS["SALE_NEW_ORDER_HTML_TITLE"] = "Sie haben Ihre Bestellung auf #SITE_NAME# platziert";
$MESS["SALE_NEW_ORDER_MESSAGE"] = "Informationsnachricht von #SITE_NAME#
------------------------------------------

Sehr geehrte(r) #ORDER_Nutzer#,

Ihre Bestellung Nr. #ORDER_ID# vom #ORDER_DATE# ist bei uns eingegangen.

Summe: #PRICE#.

Ihre Bestellung:
#ORDER_LIST#

In Ihrem persönlichen Bereich auf der Seite #SITE_NAME# können Sie 
den Status Ihrer Bestellung verfolgen.

Um in Ihren persönlichen Bereich zu gelangen, müssen Sie sich 
mit Ihrem Loginnamen und Passwort auf der Seite #SITE_NAME# autorisieren.

Um die Bestellung zu stornieren, benutzen Sie die Funktionen
in Ihrem persönlichen Bereich auf der Seite #SITE_NAME#.

Wir bitten Sie bei allen Fragen die Bestellnummer #ORDER_ID# anzugeben.

Vielen Dank für Ihre Bestellung!";
$MESS["SALE_NEW_ORDER_NAME"] = "Neue Bestellung";
$MESS["SALE_NEW_ORDER_RECURRING_DESC"] = "#ORDER_ID# - ID der Bestellung
#ORDER_ACCOUNT_NUMBER_ENCODE# - ID der Bestellung (für URL)
#ORDER_REAL_ID# - wirkliche ID der Bestellung
#ORDER_DATE# - Bestelldatum
#ORDER_USER# - Kunde
#PRICE# - Bestellbetrag
#EMAIL# - E-Mail des Kunden
#BCC# - BCC der E-Mail
#ORDER_LIST# - Inhalte der Bestellung
#SALE_EMAIL# - E-Mail der Vertriebsabteilung
";
$MESS["SALE_NEW_ORDER_RECURRING_MESSAGE"] = "Informationsnachricht von #SITE_NAME#
------------------------------------------

Sehr geehrte(r) #ORDER_Nutzer#,

Ihre Bestellung Nr. #ORDER_ID# vom #ORDER_DATE# ist bei uns eingegangen.

Summe: #PRICE#.

Ihre Bestellung:
#ORDER_LIST#

In Ihrem persönlichen Bereich auf der Seite #SITE_NAME# können Sie 
den Status Ihrer Bestellung verfolgen.

Um in Ihren persönlichen Bereich zu gelangen, müssen Sie sich 
mit Ihrem Loginnamen und Passwort auf der Seite #SITE_NAME# autorisieren.

Um die Bestellung zu stornieren, benutzen Sie die Funktionen
in Ihrem persönlichen Bereich auf der Seite #SITE_NAME#.

Wir bitten Sie bei allen Fragen die Bestellnummer #ORDER_ID# anzugeben.

Vielen Dank für Ihre Bestellung!";
$MESS["SALE_NEW_ORDER_RECURRING_NAME"] = "Neue Bestellung für Verlängerung des Abonnements";
$MESS["SALE_NEW_ORDER_RECURRING_SUBJECT"] = "#SITE_NAME#: Neue Bestellung Nr. #ORDER_ID# für die Verlängerung des Abonnements";
$MESS["SALE_NEW_ORDER_SUBJECT"] = "#SITE_NAME#: Neue Bestellung Nr. #ORDER#";
$MESS["SALE_ORDER_CANCEL_DESC"] = "#ORDER_ID# - ID der Bestellung
#ORDER_ACCOUNT_NUMBER_ENCODE# - ID der Bestellung (für URL)
#ORDER_REAL_ID# - wirkliche ID der Bestellung
#ORDER_DATE# - Bestelldatum
#EMAIL# - E-Mail des Kunden
#ORDER_LIST# - Inhalte der Bestellung
#ORDER_CANCEL_DESCRIPTION# - Grund für Stornierung
#ORDER_PUBLIC_URL# - Link zur Bestellansicht für nicht autorisierte Nutzer (Konfiguration in den Einstellungen des Moduls Onlineshop ist erforderlich)
#SALE_EMAIL# - E-Mail der Vertriebsabteilung
";
$MESS["SALE_ORDER_CANCEL_HTML_SUB_TITLE"] = "Bestellung ##ORDER_ID# vom #ORDER_DATE# wurde storniert.";
$MESS["SALE_ORDER_CANCEL_HTML_TEXT"] = "#ORDER_CANCEL_DESCRIPTION#

Um Details der Bestellung anzuzeigen, klicken Sie bitte hier: http://#SERVER_NAME#/personal/order/#ORDER_ID#/
";
$MESS["SALE_ORDER_CANCEL_HTML_TITLE"] = "#SITE_NAME#: Bestellung ##ORDER_ID# stornieren";
$MESS["SALE_ORDER_CANCEL_MESSAGE"] = "Informationsnachricht von #SITE_NAME#
------------------------------------------

Die Bestellung Nr. #ORDER_ID# vom #ORDER_DATE# wurde storniert.

#ORDER_CANCEL_DESCRIPTION#

#SITE_NAME#
";
$MESS["SALE_ORDER_CANCEL_NAME"] = "Bestellung stornieren";
$MESS["SALE_ORDER_CANCEL_SUBJECT"] = "#SITE_NAME#: Die Bestellung Nr. #ORDER# wurde storniert";
$MESS["SALE_ORDER_DELIVERY_DESC"] = "#ORDER_ID# - ID der Bestellung
#ORDER_ACCOUNT_NUMBER_ENCODE# - ID der Bestellung (für URL)
#ORDER_REAL_ID# - wirkliche ID der Bestellung
#ORDER_DATE# - Bestelldatum
#EMAIL# - E-Mail des Kunden
#ORDER_PUBLIC_URL# - Link zur Bestellansicht für nicht autorisierte Nutzer (Konfiguration in den Einstellungen des Moduls Onlineshop ist erforderlich)
#SALE_EMAIL# - E-Mail der Vertriebsabteilung
";
$MESS["SALE_ORDER_DELIVERY_HTML_SUB_TITLE"] = "Die Lieferung der Bestellung ##ORDER_ID# vom #ORDER_DATE# wurde freigegeben.";
$MESS["SALE_ORDER_DELIVERY_HTML_TEXT"] = "Um Details anzuzeigen, klicken Sie bitte hier: http://#SERVER_NAME#/personal/order/#ORDER_ID#/";
$MESS["SALE_ORDER_DELIVERY_HTML_TITLE"] = "Die Lieferung Ihrer Bestellung von #SITE_NAME# wurde freigegeben.";
$MESS["SALE_ORDER_DELIVERY_MESSAGE"] = "Informationsnachricht von #SITE_NAME#
------------------------------------------

Der Versand der Bestellung Nr. #ORDER_ID# vom #ORDER_DATE# wurde freigegeben.

#SITE_NAME#
";
$MESS["SALE_ORDER_DELIVERY_NAME"] = "Der Versand wurde freigegeben";
$MESS["SALE_ORDER_DELIVERY_SUBJECT"] = "#SITE_NAME#: Der Versand der Bestellug Nr. #ORDER# wurde freigegeben";
$MESS["SALE_ORDER_PAID_DESC"] = "#ORDER_ID# - ID der Bestellung
#ORDER_ACCOUNT_NUMBER_ENCODE# - ID der Bestellung (für URL)
#ORDER_REAL_ID# - wirkliche ID der Bestellung
#ORDER_DATE# - Bestelldatum
#EMAIL# - E-Mail des Kunden
#ORDER_PUBLIC_URL# - Link zur Bestellansicht für nicht autorisierte Nutzer (Konfiguration in den Einstellungen des Moduls Onlineshop ist erforderlich)
#SALE_EMAIL# - E-Mail der Vertriebsabteilung
";
$MESS["SALE_ORDER_PAID_HTML_SUB_TITLE"] = "Ihre Bestellung ##ORDER_ID# vom #ORDER_DATE# wurde bezahlt.";
$MESS["SALE_ORDER_PAID_HTML_TEXT"] = "Um Details der Bestellung anzuzeigen, klicken Sie bitte hier: http://#SERVER_NAME#/personal/order/#ORDER_ID#/";
$MESS["SALE_ORDER_PAID_HTML_TITLE"] = "Ihre Bezahlung für die Bestellung auf #SITE_NAME#";
$MESS["SALE_ORDER_PAID_MESSAGE"] = "Informationsnachricht von #SITE_NAME#
------------------------------------------

Die Bestellung Nr.#ORDER_ID# vom #ORDER_DATE# wurde bezahlt.

#SITE_NAME#
";
$MESS["SALE_ORDER_PAID_NAME"] = "Die Bestellung wurde bezahlt";
$MESS["SALE_ORDER_PAID_SUBJECT"] = "#SITE_NAME#: Die Bestellung Nr. #ORDER# wurde bezahlt";
$MESS["SALE_ORDER_REMIND_PAYMENT_DESC"] = "#ORDER_ID# - ID der Bestellung
#ORDER_ACCOUNT_NUMBER_ENCODE# - ID der Bestellung (für URL)
#ORDER_REAL_ID# - wirkliche ID der Bestellung
#ORDER_DATE# - Bestelldatum
#ORDER_USER# - Kunde
#PRICE# - Bestellbetrag
#EMAIL# - E-Mail des Kunden
#BCC# - BCC der E-Mail
#ORDER_LIST# - Inhalte der Bestellung
#ORDER_PUBLIC_URL# - Link zur Bestellansicht für nicht autorisierte Nutzer (Konfiguration in den Einstellungen des Moduls Onlineshop ist erforderlich)
#SALE_EMAIL# - E-Mail der Vertriebsabteilung
";
$MESS["SALE_ORDER_REMIND_PAYMENT_HTML_SUB_TITLE"] = "Liebe(r) #ORDER_USER#,";
$MESS["SALE_ORDER_REMIND_PAYMENT_HTML_TEXT"] = "Sie haben eine Bestellung ##ORDER_ID# für #PRICE# am #ORDER_DATE# platziert.

Leider haben wir Ihre Bezahlung noch nicht erhalten. 

Sie können den Status Ihrer Bestellung nach erfolgtem Login auf #SITE_NAME# in Ihrem persönlichen Kundenbereich jederzeit einsehen. Sie werden dabei den Login und das Passwort angeben müssen, mit denen Sie sich auf #SITE_NAME# angemeldet haben.


Falls Sie Ihre Bestellung stornieren möchten, können Sie dies ebenfalls im persönlichen Kundenbereich auf #SITE_NAME# tun.

Halten Sie Ihre Bestellnummer (##ORDER_ID#) bereit, wenn Sie uns bezüglich Ihrer Bestellung kontaktieren.

Vielen Dank für Ihre Bestellung!
";
$MESS["SALE_ORDER_REMIND_PAYMENT_HTML_TITLE"] = "Vergessen Sie bitte nicht, Ihre Bestellung auf #SITE_NAME# zu bezahlen";
$MESS["SALE_ORDER_REMIND_PAYMENT_MESSAGE"] = "Informationsnachricht von #SITE_NAME#
------------------------------------------

Sehr geehrte(r) #ORDER_Nutzer#,

Ihre Bestellung Nr. #ORDER_ID# vom #ORDER_DATE# für #PRICE# ist bei uns eingegangen.

In Ihrem persönlichen Bereich auf der Seite #SITE_NAME# können Sie 
den Status Ihrer Bestellung verfolgen.

Um in Ihren persönlichen Bereich zu gelangen, müssen Sie sich 
mit Ihrem Loginnamen und Passwort auf der Seite #SITE_NAME# autorisieren.

Um die Bestellung zu stornieren, benutzen Sie die Funktionen
in Ihrem persönlichen Bereich auf der Seite #SITE_NAME#.

Wir bitten Sie bei allen Fragen die Bestellnummer #ORDER_ID# anzugeben.

Vielen Dank für Ihre Bestellung!";
$MESS["SALE_ORDER_REMIND_PAYMENT_NAME"] = "Erinnerung an die Bezahlung";
$MESS["SALE_ORDER_REMIND_PAYMENT_SUBJECT"] = "#SITE_NAME#: Erinnerung an die Bezahlung der Bestellung Nr. #ORDER_ID# ";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_HTML_SUB_TITLE"] = "Liebe(r)  #ORDER_USER#,";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_HTML_TEXT"] = "Status Ihrer Lieferung für Bestellung ##ORDER_NO# vom #ORDER_DATE# wurde aktualisiert auf 

\"#STATUS_NAME#\" (#STATUS_DESCRIPTION#).

Nummer zum Verfolgen: #TRACKING_NUMBER#.

Geliefert mit: #DELIVERY_NAME#.

#DELIVERY_TRACKING_URL##ORDER_DETAIL_URL#
";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_HTML_TITLE"] = "Information zum Verfolgen Ihrer Lieferung von #SITE_NAME# wurde aktualisiert";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_SUBJECT"] = "Status Ihrer Lieferung von #SITE_NAME# wurde aktualisiert";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_TYPE_DESC"] = "#SHIPMENT_NO# - ID der Lieferung
#SHIPMENT_DATE# - Geliefert am
#ORDER_NO# - Bestellung #
#ORDER_DATE# - Bestelldatum
#STATUS_NAME# - Statusname
#STATUS_DESCRIPTION# - Statusbeschreibung
#TRACKING_NUMBER# - Nummer zum Verfolgen
#EMAIL# - E-Mail-Adresse benachrichtigen
#BCC# - Kopie senden an Adresse
#ORDER_USER# - Kunde
#DELIVERY_NAME# - Name des Lieferservices
#DELIVERY_TRACKING_URL# - Website des Lieferservices für weitere Informationen zum Verfolgen
#ORDER_ACCOUNT_NUMBER_ENCODE# - ID der Bestellung (für Links)
#ORDER_PUBLIC_URL# - Link zur Bestellansicht für nicht autorisierte Nutzer (Konfiguration in den Einstellungen des Moduls Onlineshop ist erforderlich)
#ORDER_DETAIL_URL# - URL der Bestelldetails
";
$MESS["SALE_ORDER_SHIPMENT_STATUS_CHANGED_TYPE_NAME"] = "Aktualisierung des Verpackungsstatus";
$MESS["SALE_ORDER_TRACKING_NUMBER_HTML_SUB_TITLE"] = "Liebe(r) #ORDER_USER#,";
$MESS["SALE_ORDER_TRACKING_NUMBER_HTML_TEXT"] = "Ihre Bestellung #ORDER_ID# vom #ORDER_DATE# wurde ausgeliefert.

Die Auftragsnummer ist: #ORDER_TRACKING_NUMBER#.

Detaillierte Informationen über die Bestellung finden Sie hier: http://#SERVER_NAME#/personal/order/detail/#ORDER_ID#/

E-Mail: #SALE_EMAIL#
";
$MESS["SALE_ORDER_TRACKING_NUMBER_HTML_TITLE"] = "Die Lieferscheinnummer für Ihre Bestellung auf #SITE_NAME#";
$MESS["SALE_ORDER_TRACKING_NUMBER_MESSAGE"] = "Bestellung Nr. #ORDER_ID# vom #ORDER_DATE# wurde per Post ausgeliefert.

Die Auftragsnummer ist: #ORDER_TRACKING_NUMBER#.

Informationen über Ihre Bestellung finden Sie hier: http://#SERVER_NAME#/personal/order/detail/#ORDER_ID#/

E-Mail: #SALE_EMAIL#
";
$MESS["SALE_ORDER_TRACKING_NUMBER_SUBJECT"] = "Auftragsnummer für Ihre Bestellung auf #SITE_NAME#";
$MESS["SALE_ORDER_TRACKING_NUMBER_TYPE_DESC"] = "#ORDER_ID# - ID der Bestellung
#ORDER_ACCOUNT_NUMBER_ENCODE# - ID der Bestellung (für URL)
#ORDER_REAL_ID# - wirkliche ID der Bestellung
#ORDER_DATE# - Bestelldatum
#ORDER_USER# - Kunde
#ORDER_TRACKING_NUMBER# - Nummer zum Verfolgen
#ORDER_PUBLIC_URL# - Link zur Bestellansicht für nicht autorisierte Nutzer (Konfiguration in den Einstellungen des Moduls Onlineshop ist erforderlich)
#EMAIL# - E-Mail des Kunden
#BCC# - BCC der E-Mail
#SALE_EMAIL# - E-Mail der Vertriebsabteilung
";
$MESS["SALE_ORDER_TRACKING_NUMBER_TYPE_NAME"] = "Benachrichtigung über Änderung in der Auftragsnummer ";
$MESS["SALE_RECURRING_CANCEL_DESC"] = "#ORDER_ID# - ID der Bestellung
#ORDER_ACCOUNT_NUMBER_ENCODE# - ID der Bestellung (für URL)
#ORDER_REAL_ID# - wirkliche ID der Bestellung
#ORDER_DATE# - Bestelldatum
#EMAIL# - E-Mail des Kunden
#CANCELED_REASON# - Grund für Stornierung
#SALE_EMAIL# - E-Mail der Vertriebsabteilung
";
$MESS["SALE_RECURRING_CANCEL_MESSAGE"] = "Informationsnachricht von #SITE_NAME#
------------------------------------------

Die Wiederkehrende Zahlung wurde storniert.

#CANCELED_REASON#
#SITE_NAME#
";
$MESS["SALE_RECURRING_CANCEL_NAME"] = "Das Abonnement wurde abgemeldet";
$MESS["SALE_RECURRING_CANCEL_SUBJECT"] = "#SITE_NAME#: Das Abonnement wurde abbestellt";
$MESS["SALE_SUBSCRIBE_PRODUCT_HTML_SUB_TITLE"] = "Liebe(r) #USER_NAME#!";
$MESS["SALE_SUBSCRIBE_PRODUCT_HTML_TEXT"] = "\"#NAME#\" (#PAGE_URL#) ist wieder vorrätig.

Klicken Sie bitte hier, um jetzt zu bestellen: http://#SERVER_NAME#/personal/cart/

Vergessen Sie bitte nicht, sich vor der Bestellung anzumelden.

Sie haben diese Nachricht erhalten, weil Sie uns gebeten haben, Sie zu informieren.
Diese Nachricht wurde automatisch erzeugt, bitte antworten Sie nicht darauf.

Vielen Dank für Ihre Bestellung!
";
$MESS["SALE_SUBSCRIBE_PRODUCT_HTML_TITLE"] = "Produkt ist auf #SITE_NAME# wieder vorrätig.";
$MESS["SALE_SUBSCRIBE_PRODUCT_SUBJECT"] = "#SITE_NAME#: Produkt ist wieder vorrätig";
$MESS["SKGS_STATUS_MAIL_HTML_TITLE"] = "Bestellung aktualisiert auf #SITE_NAME#";
$MESS["SMAIL_FOOTER_BR"] = "Mit freundlichen Grüßen,<br />Support-Team.";
$MESS["SMAIL_FOOTER_SHOP"] = "Online-Shop";
$MESS["UP_MESSAGE"] = "Meldung von #SITE_NAME#
------------------------------------------

Liebe(r) #USER_NAME#,

das Produkt, für welches Sie sich interessiert haben, \"#NAME#\" (#PAGE_URL#) ist jetzt wieder vorrätig.
Wir empfehlen Ihnen, Ihre Bestellung (http://#SERVER_NAME#/personal/cart/) so schnell wie möglich zu senden.

Sie erhalten diese Nachricht, weil Sie benachrichtigt werden wollten, wenn das Produkt wieder verfügbar ist.

Mit freundlichen Grüßen

Kundenservice von #SITE_NAME#
";
$MESS["UP_SUBJECT"] = "#SITE_NAME#: Produkt ist wieder vorrätig";
$MESS["UP_TYPE_SUBJECT"] = "Benachrichtigung über Wieder-Vorrätig-Status";
$MESS["UP_TYPE_SUBJECT_DESC"] = "#USER_NAME# - Nutzername
#EMAIL# - Nutzer-E-Mail 
#NAME# - Produktname
#PAGE_URL# - Seite mit Details zum Produkt
";
