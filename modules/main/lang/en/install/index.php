<?php
$MESS["MAIN_ADMIN_GROUP_DESC"] = "Full access.";
$MESS["MAIN_ADMIN_GROUP_NAME"] = "Administrators";
$MESS["MAIN_DEFAULT_LANGUAGE_AM_VALUE"] = "am";
$MESS["MAIN_DEFAULT_LANGUAGE_DAY_MONTH_FORMAT"] = "F j";
$MESS["MAIN_DEFAULT_LANGUAGE_DAY_OF_WEEK_MONTH_FORMAT"] = "l, F j";
$MESS["MAIN_DEFAULT_LANGUAGE_DAY_SHORT_MONTH_FORMAT"] = "M j";
$MESS["MAIN_DEFAULT_LANGUAGE_FORMAT_CHARSET"] = "iso-8859-1";
$MESS["MAIN_DEFAULT_LANGUAGE_FORMAT_DATE"] = "MM/DD/YYYY";
$MESS["MAIN_DEFAULT_LANGUAGE_FORMAT_DATETIME"] = "MM/DD/YYYY H:MI:SS T";
$MESS["MAIN_DEFAULT_LANGUAGE_FORMAT_NAME"] = "#NAME# #LAST_NAME#";
$MESS["MAIN_DEFAULT_LANGUAGE_FULL_DATE_FORMAT"] = "l, F j, Y";
$MESS["MAIN_DEFAULT_LANGUAGE_LONG_DATE_FORMAT"] = "F j, Y";
$MESS["MAIN_DEFAULT_LANGUAGE_LONG_TIME_FORMAT"] = "g:i:s a";
$MESS["MAIN_DEFAULT_LANGUAGE_MEDIUM_DATE_FORMAT"] = "M j, Y";
$MESS["MAIN_DEFAULT_LANGUAGE_NAME"] = "English";
$MESS["MAIN_DEFAULT_LANGUAGE_CODE"] = "en";
$MESS["MAIN_DEFAULT_LANGUAGE_NUMBER_DECIMAL_SEPARATOR"] = ".";
$MESS["MAIN_DEFAULT_LANGUAGE_NUMBER_THOUSANDS_SEPARATOR"] = ",";
$MESS["MAIN_DEFAULT_LANGUAGE_PM_VALUE"] = "pm";
$MESS["MAIN_DEFAULT_LANGUAGE_SHORT_DATE_FORMAT"] = "n/j/Y";
$MESS["MAIN_DEFAULT_LANGUAGE_SHORT_DAY_OF_WEEK_MONTH_FORMAT"] = "D, F j";
$MESS["MAIN_DEFAULT_LANGUAGE_SHORT_DAY_OF_WEEK_SHORT_MONTH_FORMAT"] = "D, M j";
$MESS["MAIN_DEFAULT_LANGUAGE_SHORT_TIME_FORMAT"] = "g:i a";
$MESS["MAIN_DEFAULT_SITE_FORMAT_CHARSET"] = "iso-8859-1";
$MESS["MAIN_DEFAULT_SITE_FORMAT_DATE"] = "MM/DD/YYYY";
$MESS["MAIN_DEFAULT_SITE_FORMAT_DATETIME"] = "MM/DD/YYYY H:MI:SS T";
$MESS["MAIN_DEFAULT_SITE_FORMAT_NAME"] = "#NAME# #LAST_NAME#";
$MESS["MAIN_DEFAULT_SITE_NAME"] = "Default site";
$MESS["MAIN_DESKTOP_CREATEDBY_KEY"] = "Created by";
$MESS["MAIN_DESKTOP_CREATEDBY_VALUE"] = "Bitrix24";
$MESS["MAIN_DESKTOP_EMAIL_KEY"] = "E-mail";
$MESS["MAIN_DESKTOP_EMAIL_VALUE"] = "<a href=\"mailto:info@bitrixsoft.com\">info@bitrixsoft.com</a>";
$MESS["MAIN_DESKTOP_INFO_TITLE"] = "Website info";
$MESS["MAIN_DESKTOP_PRODUCTION_KEY"] = "Released on";
$MESS["MAIN_DESKTOP_PRODUCTION_VALUE"] = "12.12.2011";
$MESS["MAIN_DESKTOP_RESPONSIBLE_KEY"] = "Administrator";
$MESS["MAIN_DESKTOP_RESPONSIBLE_VALUE"] = "John Doe";
$MESS["MAIN_DESKTOP_RSS_TITLE"] = "Bitrix News";
$MESS["MAIN_DESKTOP_URL_KEY"] = "Website URL";
$MESS["MAIN_DESKTOP_URL_VALUE"] = "<a href=\"http://www.bitrixsoft.com\">www.bitrixsoft.com</a>";
$MESS["MAIN_EVENT_MESS_NOTIFICATION"] = "Event log notification: #NAME#";
$MESS["MAIN_EVENT_MESS_NOTIFICATION_TEXT"] = "Log events matching the notification parameters are found:

Event type: #AUDIT_TYPE_ID#
Object: #ITEM_ID#
User: #USER_ID# 
IP address: #REMOTE_ADDR#
Browser: #USER_AGENT#
Page URL: #REQUEST_URI# 

Number of events: #EVENT_COUNT# 

#ADDITIONAL_TEXT#

Go to event log:
http://#SERVER_NAME#/bitrix/admin/event_log.php?set_filter=Y&find_audit_type_id=#AUDIT_TYPE_ID#";
$MESS["MAIN_EVERYONE_GROUP_DESC"] = "All users (including non-authorized users).";
$MESS["MAIN_EVERYONE_GROUP_NAME"] = "All users (with non-authorized users)";
$MESS["MAIN_INSTALL_DB_ERROR"] = "Cannot connect to the database. Please check the parameters.";
$MESS["MAIN_INSTALL_EVENT_MESSAGE_NEW_DEVICE_LOGIN"] = "Hello #NAME#,

a new device has just signed in using your login #LOGIN#.
 
Device: #DEVICE# 
Browser: #BROWSER#
Platform: #PLATFORM#
Location: #LOCATION# (approximate)
Date: #DATE#

We recommend that you change your password immediately if it was not you or the sign-in was not on your behalf.
";
$MESS["MAIN_INSTALL_EVENT_MESSAGE_NEW_DEVICE_LOGIN_SUBJECT"] = "New device signed in";
$MESS["MAIN_INSTALL_EVENT_MESS_USER_CODE_REQUEST"] = "#SITE_NAME#: Request for verification code";
$MESS["MAIN_INSTALL_EVENT_MESS_USER_CODE_REQUEST_MESS"] = "Use the following code to log in:

#CHECKWORD#

Once logged in, you can change your password in your user profile.

Your registration information:

User ID: #USER_ID#
Account status: #STATUS#
Login: #LOGIN#

This message was created automatically.";
$MESS["MAIN_INSTALL_EVENT_TYPE_NEW_DEVICE_LOGIN"] = "New device signed in";
$MESS["MAIN_INSTALL_EVENT_TYPE_NEW_DEVICE_LOGIN_DESC"] = "#USER_ID# - User ID
#EMAIL# - User email:
#LOGIN# - User login
#NAME# - User first name
#LAST_NAME# - User last name
#DEVICE# - Device
#BROWSER# - Browser
#PLATFORM# - Platform
#USER_AGENT# - User agent
#IP# - IP address
#DATE# - Date
#COUNTRY# - Country
#REGION# - Region
#CITY# - City
#LOCATION# - Full location (city, region, country)
";
$MESS["MAIN_INSTALL_EVENT_TYPE_NOTIFICATION"] = "Event log notification";
$MESS["MAIN_INSTALL_EVENT_TYPE_NOTIFICATION_DESC"] = "#EMAIL# - Recipient email
#ADDITIONAL_TEXT# - Action additional text
#NAME# - Notification name
#AUDIT_TYPE_ID# - Event type
#ITEM_ID# - Object
#USER_ID# - User
#REMOTE_ADDR# - IP address
#USER_AGENT# - Browser
#REQUEST_URI# - Page URL
#EVENT_COUNT# - Number of events";
$MESS["MAIN_INSTALL_EVENT_TYPE_NOTIFICATION_DESC_SMS"] = "#PHONE_NUMBER# - Recipient phone number
#ADDITIONAL_TEXT# - Action additional text
#NAME# - Notification name
#AUDIT_TYPE_ID# - Event type
#ITEM_ID# - Object
#USER_ID# - User
#REMOTE_ADDR# - IP address
#USER_AGENT# - Browser
#REQUEST_URI# - Page URL
#EVENT_COUNT# - Number of events";
$MESS["MAIN_INSTALL_EVENT_TYPE_USER_CODE_REQUEST"] = "Request for verification code";
$MESS["MAIN_INSTALL_EVENT_TYPE_USER_CODE_REQUEST_DESC"] = "#USER_ID# - user ID
#STATUS# - Login status
#LOGIN# - Login
#CHECKWORD# - Verification code
#NAME# - First name
#LAST_NAME# - Last name
#EMAIL# - User email
";
$MESS["MAIN_MAIL_CONFIRM_EVENT_TYPE_DESC"] = "

#EMAIL_TO# - confirmation email address
#MESSAGE_SUBJECT# - Message subject
#CONFIRM_CODE# - Confirmation code";
$MESS["MAIN_MAIL_CONFIRM_EVENT_TYPE_NAME"] = "Confirm sender's email address";
$MESS["MAIN_MODULE_DESC"] = "The product kernel ";
$MESS["MAIN_MODULE_NAME"] = "Main module";
$MESS["MAIN_NEW_USER_CONFIRM_EVENT_DESC"] = "Greetings from #SITE_NAME#!
------------------------------------------

Hello,

you have received this message because you (or someone else) used your e-mail to register at #SERVER_NAME#.

Your registration confirmation code: #CONFIRM_CODE#

Please use the link below to verify and activate your registration:
http://#SERVER_NAME#/auth/index.php?confirm_registration=yes&confirm_user_id=#USER_ID#&confirm_code=#CONFIRM_CODE#

Alternatively, open this link in your browser and enter the code manually:
http://#SERVER_NAME#/auth/index.php?confirm_registration=yes&confirm_user_id=#USER_ID#

Attention! Your account will not be activated until you confirm registration.

---------------------------------------------------------------------

Automatically generated message.";
$MESS["MAIN_NEW_USER_CONFIRM_EVENT_NAME"] = "#SITE_NAME#: New user registration confirmation";
$MESS["MAIN_NEW_USER_CONFIRM_TYPE_DESC"] = "

#USER_ID# - User ID
#LOGIN# - Login
#EMAIL# - E-mail
#NAME# - First name
#LAST_NAME# - Last name
#USER_IP# - User IP
#USER_HOST# - User host
#CONFIRM_CODE# - Confirmation code
";
$MESS["MAIN_NEW_USER_CONFIRM_TYPE_NAME"] = "New user registration confirmation";
$MESS["MAIN_NEW_USER_EVENT_DESC"] = "Informational message from #SITE_NAME#
---------------------------------------

New user has been successfully registered on the site #SERVER_NAME#.

User details:
User ID: #USER_ID#

Name: #NAME#
Last Name: #LAST_NAME#
User's E-Mail: #EMAIL#

Login: #LOGIN#

Automatically generated message.";
$MESS["MAIN_NEW_USER_EVENT_NAME"] = "#SITE_NAME#: New user has been registered on the site";
$MESS["MAIN_NEW_USER_TYPE_DESC"] = "

#USER_ID# - User ID
#LOGIN# - Login
#EMAIL# - EMail
#NAME# - Name
#LAST_NAME# - Last Name
#USER_IP# - User IP
#USER_HOST# - User Host
";
$MESS["MAIN_NEW_USER_TYPE_NAME"] = "New user was registered";
$MESS["MAIN_RATING_AUTHORITY_NAME"] = "Authority";
$MESS["MAIN_RATING_NAME"] = "Rating";
$MESS["MAIN_RATING_TEXT_LIKE_D"] = "Like";
$MESS["MAIN_RATING_TEXT_LIKE_N"] = "Unlike";
$MESS["MAIN_RATING_TEXT_LIKE_Y"] = "Like";
$MESS["MAIN_RULE_ADD_GROUP_AUTHORITY_NAME"] = "Enroll in group users allowed to vote for authority";
$MESS["MAIN_RULE_ADD_GROUP_RATING_NAME"] = "Enroll in group users allowed to vote for rating";
$MESS["MAIN_RULE_AUTO_AUTHORITY_VOTE_NAME"] = "Autovote for user authority";
$MESS["MAIN_RULE_REM_GROUP_AUTHORITY_NAME"] = "Remove from group users disallowed to vote for authority";
$MESS["MAIN_RULE_REM_GROUP_RATING_NAME"] = "Remove from group users disallowed to vote for rating";
$MESS["MAIN_SMILE_DEF_SET_NAME"] = "Default set";
$MESS["MAIN_USER_INFO_EVENT_DESC"] = "Informational message from #SITE_NAME#
---------------------------------------

#NAME# #LAST_NAME#,

#MESSAGE#

Your registration info:

User ID: #USER_ID#
Account status: #STATUS#
Login: #LOGIN#

To change your password please visit the link below:
http://#SERVER_NAME#/auth/index.php?change_password=yes&lang=en&USER_CHECKWORD=#CHECKWORD#&USER_LOGIN=#URL_LOGIN#

Automatically generated message.";
$MESS["MAIN_USER_INFO_EVENT_NAME"] = "#SITE_NAME#: Registration info";
$MESS["MAIN_USER_INFO_TYPE_DESC"] = "

#USER_ID# - User ID
#STATUS# - Account status
#MESSAGE# - Message for user
#LOGIN# - Login
#URL_LOGIN# - Encoded login for use in URL
#CHECKWORD# - Check string for password change
#NAME# - Name
#LAST_NAME# - Last Name
#EMAIL# - User E-Mail
";
$MESS["MAIN_USER_INFO_TYPE_NAME"] = "Account Information";
$MESS["MAIN_USER_INVITE_EVENT_DESC"] = "Informational message from site #SITE_NAME#
------------------------------------------
Hello #NAME# #LAST_NAME#!

Administrator has added you to registered site users.

We invite you to visit our site.

Your registration info:

User ID: #ID#
Login: #LOGIN#

We recommend you to change automatically generated password.

To change password please follow the link:
http://#SERVER_NAME#/auth.php?change_password=yes&USER_LOGIN=#URL_LOGIN#&USER_CHECKWORD=#CHECKWORD#";
$MESS["MAIN_USER_INVITE_EVENT_NAME"] = "#SITE_NAME#: Invitation to site";
$MESS["MAIN_USER_INVITE_TYPE_DESC"] = "#ID# - User ID
#LOGIN# - Login
#URL_LOGIN# - Encoded login for use in URL
#EMAIL# - EMail
#NAME# - Name
#LAST_NAME# - Last Name
#PASSWORD# - User password 
#CHECKWORD# - Password check string
#XML_ID# - User ID to link with external data sources

";
$MESS["MAIN_USER_INVITE_TYPE_NAME"] = "Invitation of a new site user";
$MESS["MAIN_USER_PASS_CHANGED_EVENT_DESC"] = "Informational message from #SITE_NAME#
---------------------------------------

#NAME# #LAST_NAME#,

#MESSAGE#

Your registration info:

User ID: #USER_ID#
Account status: #STATUS#
Login: #LOGIN#

Automatically generated message.";
$MESS["MAIN_USER_PASS_CHANGED_EVENT_NAME"] = "#SITE_NAME#: Password Change Confirmation";
$MESS["MAIN_USER_PASS_CHANGED_TYPE_NAME"] = "Password Change Confirmation";
$MESS["MAIN_USER_PASS_REQUEST_EVENT_DESC"] = "Informational message from #SITE_NAME#
---------------------------------------

#NAME# #LAST_NAME#,

#MESSAGE#

To change your password please visit the link below:
http://#SERVER_NAME#/auth/index.php?change_password=yes&lang=en&USER_CHECKWORD=#CHECKWORD#&USER_LOGIN=#URL_LOGIN#

Your registration info:

User ID: #USER_ID#
Account status: #STATUS#
Login: #LOGIN#

Automatically generated message.";
$MESS["MAIN_USER_PASS_REQUEST_EVENT_NAME"] = "#SITE_NAME#: Password Change Request";
$MESS["MAIN_USER_PASS_REQUEST_TYPE_NAME"] = "Password Change Request";
$MESS["MAIN_VOTE_AUTHORITY_GROUP_DESC"] = "Membership for this user group is managed automatically.";
$MESS["MAIN_VOTE_AUTHORITY_GROUP_NAME"] = "Users allowed to vote for authority";
$MESS["MAIN_VOTE_RATING_GROUP_DESC"] = "Membership for this user group is managed automatically.";
$MESS["MAIN_VOTE_RATING_GROUP_NAME"] = "Users allowed to vote for rating";
$MESS["MF_EVENT_DESCRIPTION"] = "#AUTHOR# - Message author
#AUTHOR_EMAIL# - Author's e-mail address
#TEXT# - Message text
#EMAIL_FROM# - Sender's e-mail address
#EMAIL_TO# - Recipient's e-mail address";
$MESS["MF_EVENT_MESSAGE"] = "Notification from #SITE_NAME#
------------------------------------------

A message has been sent to you from the feedback form.

Sent by: #AUTHOR#
Sender's e-mail: #AUTHOR_EMAIL#

Message text:
#TEXT#

This notification has been generated automatically.";
$MESS["MF_EVENT_NAME"] = "Sending a message using a feedback form";
$MESS["MF_EVENT_SUBJECT"] = "#SITE_NAME#: A feedback form message";
$MESS["main_install_sms_event_confirm_descr"] = "#USER_PHONE# - phone number
#CODE# - confirmation code";
$MESS["main_install_sms_event_confirm_name"] = "Verify phone number using SMS";
$MESS["main_install_sms_event_restore_descr"] = "#USER_PHONE# - phone number
#CODE# - recovery confirmation code";
$MESS["main_install_sms_event_restore_name"] = "Recover password using SMS";
$MESS["main_install_sms_template_confirm_mess"] = "Confirmation code: #CODE#";
$MESS["main_install_sms_template_notification_mess"] = "#NAME#: #ADDITIONAL_TEXT# (events: #EVENT_COUNT#)";
$MESS["main_install_sms_template_restore_mess"] = "Code to recover password: #CODE#";
