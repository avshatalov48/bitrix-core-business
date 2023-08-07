<?
$MESS["SONET_NEW_MESSAGE_NAME"] = "You have a new message";
$MESS["SONET_NEW_MESSAGE_DESC"] = "#MESSAGE_ID# - Message ID
#USER_ID# - User ID
#USER_NAME# - User first name
#USER_LAST_NAME# - User last name
#SENDER_ID# - Message sender ID
#SENDER_NAME# - Message sender first name
#SENDER_LAST_NAME# - Message sender last name
#TITLE# - Message title
#MESSAGE# - Message body
#EMAIL_TO# - Recipient e-mail address";
$MESS["SONET_NEW_MESSAGE_SUBJECT"] = "#SITE_NAME#: You have a new message";
$MESS["SONET_NEW_MESSAGE_MESSAGE"] = "Greetings from #SITE_NAME#!
------------------------------------------

Hello #USER_NAME#!

You have a new message from #SENDER_NAME# #SENDER_LAST_NAME#:

------------------------------------------
#MESSAGE#
------------------------------------------

Link to message:

http://#SERVER_NAME#/company/personal/messages/chat/#SENDER_ID#/

This is an automatically generated notification.
";
$MESS["SONET_INVITE_FRIEND_NAME"] = "Invitation to Join Friends";
$MESS["SONET_INVITE_FRIEND_DESC"] = "#RELATION_ID# - Relation ID
#SENDER_USER_ID# - Sender ID
#SENDER_USER_NAME# - Sender first name
#SENDER_USER_LAST_NAME# - Sender last name
#SENDER_EMAIL_TO# - Sender e-mail address
#RECIPIENT_USER_ID# - Recipient ID
#RECIPIENT_USER_NAME# - Recipient first name
#RECIPIENT_USER_LAST_NAME# - Recipient last name
#RECIPIENT_USER_EMAIL_TO# - Recipient e-mail address
#MESSAGE# - Message body";
$MESS["SONET_INVITE_FRIEND_SUBJECT"] = "#SITE_NAME#: Friends Invitation";
$MESS["SONET_INVITE_FRIEND_MESSAGE"] = "Greetings from #SITE_NAME#!
------------------------------------------

Hello #RECIPIENT_USER_NAME#!

#SENDER_USER_NAME# #SENDER_USER_LAST_NAME# invites you to join their friends.

Click the link below to respond to the invitation:

http://#SERVER_NAME##URL#

Message:
------------------------------------------
#MESSAGE#
------------------------------------------

This is an automatically generated notification.";
$MESS["SONET_INVITE_GROUP_NAME"] = "Invitation to Join Group";
$MESS["SONET_INVITE_GROUP_DESC"] = "#RELATION_ID# - Relation ID
#GROUP_ID# - Group ID
#USER_ID# - User ID
#GROUP_NAME# - Group name
#USER_NAME# - User first name
#USER_LAST_NAME# - User last name
#USER_EMAIL# - User e-mail address
#INITIATED_USER_NAME# - Name of sender of invitation
#INITIATED_USER_LAST_NAME# - Last Name of sender of invitation
#URL# - Path to user messages page
#MESSAGE# - Message body";
$MESS["SONET_INVITE_GROUP_SUBJECT"] = "#SITE_NAME#: Invitation to Join Group";
$MESS["SONET_INVITE_GROUP_MESSAGE"] = "Greetings from #SITE_NAME#!
------------------------------------------

Hello #USER_NAME#!

User #INITIATED_USER_NAME# #INITIATED_USER_LAST_NAME# invites you to join the #GROUP_NAME# workgroup.

Click the link below to respond to the invitation:

http://#SERVER_NAME##URL#

Message:
------------------------------------------
#MESSAGE#
------------------------------------------

This is an automatically generated notification.";
$MESS["SONET_AGREE_FRIEND_NAME"] = "Confirmation of Friend Invitation";
$MESS["SONET_AGREE_FRIEND_DESC"] = "#RELATION_ID# - Relation ID
#SENDER_USER_ID# - Sender ID
#SENDER_USER_NAME# - Sender first name
#SENDER_USER_LAST_NAME# - Sender last name
#SENDER_EMAIL_TO# - Sender e-mail address
#RECIPIENT_USER_ID# - Recipient ID
#RECIPIENT_USER_NAME# - Recipient first name
#RECIPIENT_USER_LAST_NAME# - Recipient last name
#RECIPIENT_USER_EMAIL_TO# - Recipient e-mail address
#MESSAGE# - Message body";
$MESS["SONET_AGREE_FRIEND_SUBJECT"] = "#SITE_NAME#: Confirmation of friend invitation";
$MESS["SONET_AGREE_FRIEND_MESSAGE"] = "Greetings from #SITE_NAME#!
------------------------------------------

Hello #RECIPIENT_USER_NAME#!

#SENDER_USER_NAME# #SENDER_USER_LAST_NAME# confirms your invitation to join your friends.

This is an automatically generated notification.";
$MESS["SONET_BAN_FRIEND_NAME"] = "Blacklisting";
$MESS["SONET_BAN_FRIEND_DESC"] = "#RELATION_ID# - Relation ID
#SENDER_USER_ID# - Sender ID
#SENDER_USER_NAME# - Sender first name
#SENDER_USER_LAST_NAME# - Sender last name
#SENDER_EMAIL_TO# - Sender e-mail address
#RECIPIENT_USER_ID# - Recipient ID
#RECIPIENT_USER_NAME# - Recipient first name
#RECIPIENT_USER_LAST_NAME# - Recipient last name
#RECIPIENT_USER_EMAIL_TO# - Recipient e-mail address
#MESSAGE# - Message body";
$MESS["SONET_BAN_FRIEND_SUBJECT"] = "#SITE_NAME#: Blacklisting";
$MESS["SONET_BAN_FRIEND_MESSAGE"] = "Greetings from #SITE_NAME#!
------------------------------------------

Hello #RECIPIENT_USER_NAME#!

#SENDER_USER_NAME# #SENDER_USER_LAST_NAME# has added you to the blacklist.

This is an automatically generated notification.";
$MESS["SONET_NEW_EVENT_GROUP_NAME"] = "New Group Event";
$MESS["SONET_NEW_EVENT_GROUP_DESC"] = "#ENTITY_ID# - Group ID
#LOG_DATE# - Entry date
#TITLE# - Title
#MESSAGE# - Message
#URL# - Address (URL)
#GROUP_NAME# - Group title
#SUBSCRIBER_NAME# - Subscriber's first name
#SUBSCRIBER_LAST_NAME# - Subscriber's last name
#SUBSCRIBER_EMAIL# - Subscriber's e-mail address
#SUBSCRIBER_ID# - Subscriber ID";
$MESS["SONET_NEW_EVENT_GROUP_SUBJECT"] = "#SITE_NAME#: New Group Event";
$MESS["SONET_NEW_EVENT_GROUP_MESSAGE"] = "Greetings from #SITE_NAME#!
------------------------------------------

Hello #SUBSCRIBER_NAME#!

The following changes occurred in the group #GROUP_NAME#:

#TITLE#

------------------------------------------
#MESSAGE#
------------------------------------------

You can open the site by clicking the following link:

http://#SERVER_NAME##URL#

This is an automatically generated notification.";
$MESS["SONET_NEW_EVENT_USER_NAME"] = "New User Event";
$MESS["SONET_NEW_EVENT_USER_DESC"] = "#ENTITY_ID# - Group ID
#LOG_DATE# - Entry date
#TITLE# - Title
#MESSAGE# - Message
#URL# - Address (URL)
#USER_NAME# - User name
#SUBSCRIBER_NAME# - Subscriber's first name
#SUBSCRIBER_LAST_NAME# - Subscriber's last name
#SUBSCRIBER_EMAIL# - Subscriber's e-mail address
#SUBSCRIBER_ID# - Subscriber ID";
$MESS["SONET_NEW_EVENT_USER_SUBJECT"] = "#SITE_NAME#: New User Event";
$MESS["SONET_NEW_EVENT_USER_MESSAGE"] = "Greetings from #SITE_NAME#!
------------------------------------------

Hello #SUBSCRIBER_NAME#!

The user #USER_NAME# has the following new events:

#TITLE#

------------------------------------------
#MESSAGE#
------------------------------------------

You can open the site by clicking the following link:

http://#SERVER_NAME##URL#

This is an automatically generated notification.";
$MESS["SONET_REQUEST_GROUP_DESC"] = "#MESSAGE_ID# - the message ID
#USER_ID# - the request recipient ID
#USER_NAME# - the request recipient first name
#USER_LAST_NAME# - the request recipient last name
#SENDER_ID# - the request sender ID
#SENDER_NAME# - the request sender first name
#SENDER_LAST_NAME# - the request sender last name
#TITLE# - the message title
#MESSAGE# - the message body
#EMAIL_TO# - the message recipient's e-mail";
$MESS["SONET_REQUEST_GROUP_SUBJECT"] = "#TITLE#";
$MESS["SONET_REQUEST_GROUP_NAME"] = "Group Membership Request";
$MESS["SONET_REQUEST_GROUP_MESSAGE"] = "Message from #SITE_NAME#
------------------------------------------

Hello #USER_NAME#!

------------------------------------------
#MESSAGE#
------------------------------------------

This message has been generated automatically.";
$MESS["SONET_NEW_EVENT_NAME"] = "New Event";
$MESS["SONET_NEW_EVENT_MESSAGE"] = "Greetings from #SITE_NAME#!
------------------------------------------

Dear user #SUBSCRIBER_NAME#!

The following updates have occurred since your last visit:

#TITLE#

------------------------------------------
#MESSAGE#
------------------------------------------

Use the following link to view the events:

#URL#

This message has been generated automatically.";
$MESS["SONET_NEW_EVENT_DESC"] = "#ENTITY_ID# - The event source ID
#LOG_DATE# - The date the event was logged
#TITLE# - The title
#MESSAGE# - The message
#URL# - URL
#ENTITY# - The event registration disposition
#SUBSCRIBER_NAME# - The recipient's first name
#SUBSCRIBER_LAST_NAME# - The recipient's last name
#SUBSCRIBER_EMAIL# - The recipient's e-mail address
#SUBSCRIBER_ID# - The recipient's ID";
$MESS["SONET_NEW_EVENT_SUBJECT"] = "#SITE_NAME#: #ENTITY# - New event in #ENTITY_TYPE#";
$MESS["SONET_LOG_NEW_ENTRY_NAME"] = "New message added";
$MESS["SONET_LOG_NEW_ENTRY_DESC"] = "#EMAIL_TO# - message recipient e-mail 
#LOG_ENTRY_ID# - message ID
#RECIPIENT_ID# - recipient ID
#URL_ID# - message view URL
";
$MESS["SONET_LOG_NEW_COMMENT_NAME"] = "New comment added";
$MESS["SONET_LOG_NEW_COMMENT_DESC"] = "#EMAIL_TO# - message recipient e-mail 
#COMMENT_ID# - comment ID
#LOG_ENTRY_ID# - message ID
#RECIPIENT_ID# - recipient ID
#URL_ID# - message view URL
";
?>