<?
$MESS ['LDAP_USER_CONFIRM_TYPE_NAME'] = "Registration Confirmation";
$MESS ['LDAP_USER_CONFIRM_TYPE_DESC'] = "#USER_ID# - User ID
#EMAIL# - E-mail
#LOGIN# - Login
#XML_ID# - External ID
#BACK_URL# - Return URL
";
$MESS ['LDAP_USER_CONFIRM_EVENT_NAME'] = "#SITE_NAME#: Registration confirmation";
$MESS ['LDAP_USER_CONFIRM_EVENT_DESC'] = "Greetings from #SITE_NAME#!
------------------------------------------
Hello,

you have received this message because you (or someone else) used your e-mail to register at #SERVER_NAME#.
To confirm registration, click the following link and enter your name and password you use in the local network:

http://#SERVER_NAME#/bitrix/admin/ldap_user_auth.php?ldap_user_id=#XML_ID#&back_url=#BACK_URL#

This is an automated message.";
?>