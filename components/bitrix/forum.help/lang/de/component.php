<?
$MESS ['F_CONTENT'] = "<b>Wie erstelle ich ein Thema im Forum?</b>
<br />Klicken Sie bitte auf den dafür vorgesehenen Button oder Link in der Liste der Themen oder des konkreten Themas. Bevor Sie einen Beitrag erstellen können, müssen Sie sich registrieren.<br /><b>Kann ich HTML Benutzen?</b><br />Das hängt davon ab, ob der Administrator es erlaubt hat. Wenn HTML erlaubt ist, können nur wenige HTML-Tags benutzt werden. Dies geschieht aus Sicherheitsgründen, weil es Tags gibt, die Probleme verursachen können. Wenn HTML an ist, können Sie es während der Erstellung, für bestimmte Beiträge ausschalten. <br /><b>Wie kann ich meinen Text im Beitrag formatieren, wenn HTML nicht erlaubt ist?</b><br />Vom Administrator können folgende Tags erlaubt werden:<br />
<table class='forum-main'>
	<tr><th><div class=\"forum-bold\">Tag</div></th>
	<th><div class=\"forum-bold\">Beschreibung</div></th>
	<th><div class=\"forum-bold\">Synonyme</div></th>
	<th><div class=\"forum-bold\">Anmerkung</div></th></tr>
	<tr>
		<td>&lt;a href=\"Link\"&gt;</td>
		<td>Link</td><td>[URL]Link[/URL]<br />[URL=Link]</td>
		<td> </td></tr><tr><td>&lt;b&gt;, &lt;u&gt;, &lt;i&gt;</td>
		<td>Fetter, unterstrichener oder kursiver Text</td>
		<td>[b], [u], [i]</td><td>Zu jedem offenen, muss immer auch ein abschließender Tag existieren &lt;/b&gt;, &lt;/u&gt;, &lt;/i&gt;</td></tr>
	<tr><td>&lt;img src=\"Adresse\"&gt;</td><td>Bild</td>
		<td>[img]Adresse[/img]</td>
		<td>Adresse - der volle Link zum Bild auf jeder öffentlichen Seite</td></tr>
	<tr>
		<td>&lt;ul&gt;, &lt;li&gt;</td><td>Nichtnummerierte Listen</td>
		<td>[ul], [li]</td><td> </td></tr><tr><td>&lt;quote&gt;</td>
		<td>Ein extra Tag zum Zitieren</td><td>[quote]</td>
		<td>Zu jedem offenen, muss immer auch ein abschließender Tag existieren &lt;/quote&gt;</td></tr>
	<tr>
		<td>&lt;code&gt;</td>
		<td>Ein extra Tag, um den Text hervorzuheben</td>
		<td>[code]</td>
		<td>Zu jedem offenen, muss immer auch ein abschließender Tag existieren &lt;/code&gt;</td></tr>
	<tr>
		<td>&lt;font color=&gt;, &lt;font size=&gt;</td>
		<td>Änderung der Textfarbe und -größe</td>
		<td>[color=Farbe], [size=Maße]</td>
		<td>Zu jedem offenen, muss immer auch ein abschließender Tag existieren &lt;/font&gt;</td></tr></table>
<br /><div class=\"forum-bold\">Kann ich Bilder hinzufügen?</div><br />Sie können Bilder zu Ihren Beiträgen hinzufügen, wenn es vom Administrator erlaubt ist. Leider gibt es bis jetzt keine Möglichkeit, Bilder direkt ins Forum hoch zu laden.
Sie können einen Link zum Bild, dass auf öffentlich zugänglichen Servern liegt, erstellen. <br /><div class=\"forum-bold\">Was sind Smilies?</div><br />Smileys oder Emoticons - sind kleine Bilder, die für Gefühlsausdrücke verwendet werden können. z.B. :) bedeutet Freude, :( bedeutet Traurig. Die komplette Liste der Smileys können Sie im Forum zum Erstellen der Beiträge sehen. Aber übertreiben Sie nicht mit deren Gebrauch: Sie können den Beitrag unleserlich machen und der Moderator kann ihren Beitrag bearbeiten oder ihn ganz löschen.<br /><div class=\"forum-bold\">Warum muss ich mich registrieren?</div><br />Sie müssen sich nicht registrieren. Alles hängt davon ab, wie der Administrator das Forum eingestellt hat: Ob Sie sich registrieren müssen, wenn sie Beiträge verfassen wollen oder nicht.<br /><div class=\"forum-bold\">Wie ändere ich meine Einstellungen?</div><br />All Ihre Einstellungen befinden sich in der Datenbank (wenn Sie registriert sind). Um sie zu ändern, gehen Sie bitte zum Bereich \"Profil\" (der Link dazu befindet sich im oberen Bereich des Forums). Dort können Sie alle persönlichen Einstellungen ändern. Der Zutritt zum Bereich \"Profil\" ist nur nach der Registrierung möglich.<br />Die Profilseite besteht aus folgenden Bereichen: Registrierungsinformationen, Persönliche Daten und Forumprofil.  <br /><img height=49 src=\"/bitrix/images/forum/help/prof_link1.gif\" width=370 border=0 alt=\"\"/><UL><LI>Im Bereich \"Registrierungsinformationen\" können Sie Ihren Vornamen, Nachnamen, Loginnamen und Passwort ändern</LI><LI>Im Bereich \"Persönliche Daten\" können Sie Ihren Beruf, ICQ Nummer, Geburtsdatum, Fotos, Wohnort und andere persönliche Daten ändern</LI><LI>Im Bereich \"Forumprofil\" können Sie Ihre Vorstellung im Forum ändern:<UL><LI><i>Name anzeigen.</i>Als Name des Autors des Beitrags wird der Vor- und Nachname verwendet, wenn es nicht leer ist. Andernfalls wird der Loginname des Autors verwendet. Dieser Flag verbietet die Benutzung des Vor- und Nachnamens unabhängig von ihrer Belegung;</LI><LI><i>Kommentar</i> - Der Kommentar des Autors, er wird unter dem Namen des Beitrag-Autors angezeigt. Im Kommentar ist es verboten, Wörter und Sätze zu benutzen, die \"Admin\", \"Moderator\", \"Support\" usw. enthalten. Beim Verstoß wird der User ohne Vorwarnung gelöscht;</LI><LI><i>Signatur</i> - die automatische Signatur wird unter jedem Beitrag des Users angezeigt. In der Signatur ist die Verwendung aller, im aktuellen Forum erlaubter Tags, möglich;</LI><LI><i>Avatar</i> - ist ein Bild, das unter dem Namen des Beitrag-Autors erscheint. Das Bild darf die Größe von 10KB und 90x90 Pixel nicht überschreiten.</LI></UL></LI></UL><br /><div class=\"forum-bold\">Ich will über neue Beiträge per E-Mail benachrichtig werden!</div><br />Sie können sich sowohl für neue Beiträge im Forum als auch für neue Beiträge im bestimmten Thema eintragen. Dafür müssen Sie auf dem Server registriert sein. Wenn Sie sich für neue Beiträge im ganzen Forum eintragen wollen, benutzen Sie bitte den Link \"Abonnieren\" im Forummenü (Nummer 1 auf dem Bild). Wenn Sie sich für neue Beiträge im bestimmten Thema eintragen wollen, benutzen Sie bitte den Link \"Abonnieren\" in der rechten Ecke des Themas (Nummer 2 auf dem Bild).<br /><IMG height=104 src=\"/bitrix/images/forum/help/subscr.gif\" width=400 border=0 alt=\"\"/><br /><br />Für die Abonnementverwaltung drücken Sie bitte auf den Button \"Abonnement [ändern]\", der sich in Ihrem Profil befindet.<br /><IMG src=\"/bitrix/images/forum/help/prof_form.gif\" width=400 height=261 border=0 alt=\"\"/>";
$MESS ['F_NO_MODULE'] = "Das Modul \"Forum\" wurde nicht installiert";
$MESS ['F_TITLE'] = "Hilfe";
$MESS ['F_TITLE_NAV'] = "Hilfe";
?>