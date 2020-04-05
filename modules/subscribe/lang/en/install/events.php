<?
$MESS ['SUBSCRIBE_CONFIRM_NAME'] = "Confirmation of subscription";
$MESS ['SUBSCRIBE_CONFIRM_DESC'] = "#ID# - subscription ID
#EMAIL# - subscription email
#CONFIRM_CODE# - confirmation code
#SUBSCR_SECTION# - section with subscription edit page (specifies in the settings)
#USER_NAME# - subscriber's name (optional)
#DATE_SUBSCR# - date of adding/change of address
";
$MESS ['SUBSCRIBE_CONFIRM_SUBJECT'] = "#SITE_NAME#: Subscription confirmation
";
$MESS ['SUBSCRIBE_CONFIRM_MESSAGE'] = "Informational message from #SITE_NAME#
---------------------------------------

Hello,

You have received this message because a subscription request was made for your address for news from #SERVER_NAME#.

Here is detailed info about your subscription:

Subscription email .............. #EMAIL#
Date of email adding/editing .... #DATE_SUBSCR#

Your confirmation code: #CONFIRM_CODE#

Please click on the link provided in this letter to confirm your subscription.
http://#SERVER_NAME##SUBSCR_SECTION#subscr_edit.php?ID=#ID#&CONFIRM_CODE=#CONFIRM_CODE#

Or go to this page and enter your confirmaton code manually:
http://#SERVER_NAME##SUBSCR_SECTION#subscr_edit.php?ID=#ID#

You will not receive any message until you send us your confirmation.

---------------------------------------------------------------------
Please save this message because it contains information for authorization.
Using the confirmation code, you can change subscription parameters or
unsubscribe.

Edit parameters:
http://#SERVER_NAME##SUBSCR_SECTION#subscr_edit.php?ID=#ID#&CONFIRM_CODE=#CONFIRM_CODE#

Unsubscribe:
http://#SERVER_NAME##SUBSCR_SECTION#subscr_edit.php?ID=#ID#&CONFIRM_CODE=#CONFIRM_CODE#&action=unsubscribe
---------------------------------------------------------------------

This is an automatically generated message.
";
?>