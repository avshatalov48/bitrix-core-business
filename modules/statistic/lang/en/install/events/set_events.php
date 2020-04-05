<?
$MESS ['STATISTIC_ACTIVITY_EXCEEDING_NAME'] = "Visitor activity limit exceeded";
$MESS ['STATISTIC_ACTIVITY_EXCEEDING_DESC'] = "#ACTIVITY_TIME_LIMIT# - test time period (sec.)
#ACTIVITY_HITS# - number of hits for test period
#ACTIVITY_HITS_LIMIT# - max number of hits for test period
#ACTIVITY_EXCEEDING# - activity exceeding
#CURRENT_TIME# - time of blocking (server time)
#DELAY_TIME# - period of blocking
#USER_AGENT# - UserAgent
#SESSION_ID# - session ID
#SESSION_LINK# - link to the session report
#SERACHER_ID# - search engine ID
#SEARCHER_NAME# - search engine name
#SEARCHER_LINK# - link to search engine hit list
#VISITOR_ID# - visitor ID
#VISITOR_LINK# - link to the visitor profile
#STOPLIST_LINK# - link for adding the visitor to the stop-list";
$MESS ['STATISTIC_DAILY_REPORT_NAME'] = "Site statistics daily report";
$MESS ['STATISTIC_DAILY_REPORT_DESC'] = "#EMAIL_TO# - site administrator email
#SERVER_TIME# - server time during report sending
#HTML_HEADER# - HTML header
#HTML_COMMON# - table of site traffic (hits, sessions, hosts, guests, events) (HTML)
#HTML_ADV# - table of advertising campaigns (TOP 10) (HTML)
#HTML_EVENTS# - table of event typies (TOP 10) (HTML)
#HTML_REFERERS# - table of referring sites (TOP 10) (HTML)
#HTML_PHRASES# - table of search phrases (TOP 10) (HTML)
#HTML_SEARCHERS# - table of site indexing (TOP 10) (HTML)
#HTML_FOOTER# - HTML footer
";
$MESS ['STATISTIC_DAILY_REPORT_SUBJECT'] = "#SERVER_NAME#: Statistics of site (#SERVER_TIME#)";
$MESS ['STATISTIC_DAILY_REPORT_MESSAGE'] = "#HTML_HEADER#
<font class='h2'>Summarized statistics for <font color='#A52929'>#SITE_NAME#</font> site<br>
Data on <font color='#0D716F'>#SERVER_TIME#</font></font>
<br><br>
<a class='tablebodylink' href='http://#SERVER_NAME#/bitrix/admin/stat_list.php?lang=#LANGUAGE_ID#'>http://#SERVER_NAME#/bitrix/admin/stat_list.php?lang=#LANGUAGE_ID#</a>
<br>
<hr><br>
#HTML_COMMON#
<br>
#HTML_ADV#
<br>
#HTML_REFERERS#
<br>
#HTML_PHRASES#
<br>
#HTML_SEARCHERS#
<br>
#HTML_EVENTS#
<br>
<hr>
<a class='tablebodylink' href='http://#SERVER_NAME#/bitrix/admin/stat_list.php?lang=#LANGUAGE_ID#'>http://#SERVER_NAME#/bitrix/admin/stat_list.php?lang=#LANGUAGE_ID#</a>
#HTML_FOOTER#
";
$MESS ['STATISTIC_ACTIVITY_EXCEEDING_SUBJECT'] = "#SERVER_NAME#: Activity limit exceeded";
$MESS ['STATISTIC_ACTIVITY_EXCEEDING_MESSAGE'] = "Activity limit was exceeded by visitor on the site #SERVER_NAME#.

Starting from #CURRENT_TIME# the visitor was blocked for #DELAY_TIME# sec.

Activity  - #ACTIVITY_HITS# hits per #ACTIVITY_TIME_LIMIT# sec. (limit - #ACTIVITY_HITS_LIMIT#)
Visitor   - #VISITOR_ID#
Session   - #SESSION_ID#
Searcher  - [#SERACHER_ID#] #SEARCHER_NAME#
UserAgent - #USER_AGENT#

>===============================================================
Use the following link to add to the stop list:
http://#SERVER_NAME##STOPLIST_LINK#
Use the following link to view the session:
http://#SERVER_NAME##SESSION_LINK#
Use the following link to view the visitor profile:
http://#SERVER_NAME##VISITOR_LINK#
Use the following link to view searcher hits:
http://#SERVER_NAME##SEARCHER_LINK#";
?>