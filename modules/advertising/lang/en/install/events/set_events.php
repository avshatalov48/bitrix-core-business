<?
$MESS ['ADV_BANNER_STATUS_CHANGE_NAME'] = "Banner status was changed";
$MESS ['ADV_BANNER_STATUS_CHANGE_DESC'] = "#EMAIL_TO# - EMail of message receiver (#OWNER_EMAIL#)
#ADMIN_EMAIL# - EMail of the users with \"banners manager\" and \"administrator\" roles
#ADD_EMAIL# - EMail of banner managers
#STAT_EMAIL# - EMail of users who have permissions to view banner statistics
#EDIT_EMAIL# - EMail of users who have permissions to modify some contract fields
#OWNER_EMAIL# - EMail of users who have any permissions on contract
#BCC# - copy (#ADMIN_EMAIL#)
#ID# - banner ID
#CONTRACT_ID# - contract ID
#CONTRACT_NAME# - contract title
#TYPE_SID# - type ID
#TYPE_NAME# - type title
#STATUS# - status
#STATUS_COMMENTS# - comments for status
#NAME# - banner title
#GROUP_SID# - banner group
#ACTIVE# - banner activity flag [Y | N]
#INDICATOR# - is banner shown on the site ?
#SITE_ID# - language part of the site for banner showing
#WEIGHT# - weight (priority)
#MAX_SHOW_COUNT# - max number of banner shows
#SHOW_COUNT# - number of banner shows
#MAX_CLICK_COUNT# - max number of clicks on banner
#CLICK_COUNT# - number of clicks on banner
#DATE_LAST_SHOW# - date of last banner showing
#DATE_LAST_CLICK# - date of last banner click
#DATE_SHOW_FROM# - start date of banner showing period
#DATE_SHOW_TO# - end date of banner showing period
#IMAGE_LINK# - image link
#IMAGE_ALT# - image tooltip text
#URL# - URL on image
#URL_TARGET# - where to open URL
#CODE# - banner code
#CODE_TYPE# - type of banner code (text | html)
#COMMENTS# - banner comments
#DATE_CREATE# - banner creation date
#CREATED_BY# - banner creator
#DATE_MODIFY# - banner modification date
#MODIFIED_BY# - who has modified the banner
";
$MESS ['ADV_BANNER_STATUS_CHANGE_SUBJECT'] = "[BID##ID#] #SITE_NAME#: Banner status was changed - [#STATUS#]";
$MESS ['ADV_BANNER_STATUS_CHANGE_MESSAGE'] = "Status of banner # #ID# was changed to [#STATUS#].

>==================== Banner settings ================================

Banner   - [#ID#] #NAME#
Contract - [#CONTRACT_ID#] #CONTRACT_NAME#
Type     - [#TYPE_SID#] #TYPE_NAME#
Group    - #GROUP_SID#

----------------------------------------------------------------------

Activity: #INDICATOR#

Show period - [#DATE_SHOW_FROM# - #DATE_SHOW_TO#]
Shown       - #SHOW_COUNT# / #MAX_SHOW_COUNT# [#DATE_LAST_SHOW#]
Clicked     - #CLICK_COUNT# / #MAX_CLICK_COUNT# [#DATE_LAST_CLICK#]
Act. flag   - [#ACTIVE#]
Status      - [#STATUS#]
Comments:
#STATUS_COMMENTS#
----------------------------------------------------------------------

Image - [#IMAGE_ALT#] #IMAGE_LINK#
URL   - [#URL_TARGET#] #URL#

Code: [#CODE_TYPE#]
#CODE#

>=====================================================================

Created  - #CREATED_BY# [#DATE_CREATE#]
Modified - #MODIFIED_BY# [#DATE_MODIFY#]

To view the banner settings visit link:
http://#SERVER_NAME#/bitrix/admin/adv_banner_edit.php?ID=#ID#&CONTRACT_ID=#CONTRACT_ID#&lang=#LANGUAGE_ID#

Automatically generated message.
";
$MESS ['ADV_CONTRACT_INFO_NAME'] = "Advertising contract settings";
$MESS ['ADV_CONTRACT_INFO_DESC'] = "#ID# - contract ID
#MESSAGE# - message
#EMAIL_TO# - EMail of message receiver
#ADMIN_EMAIL# - EMail of the users with \"banners manager\" and \"administrator\" roles
#ADD_EMAIL# - EMail of banner managers
#STAT_EMAIL# - EMail of users who have permissions to view banner statistics
#EDIT_EMAIL# - EMail of users who have permissions to modify some contract fields
#OWNER_EMAIL# - EMail of users who have any permissions on contract
#BCC# - copy
#INDICATOR# - is contract banners shown on the site ?
#ACTIVE# - contract activity flag [Y | N]
#NAME# - contract title
#DESCRIPTION# - contract description
#MAX_SHOW_COUNT# - max number of all contract banners shows
#SHOW_COUNT# - number of all contract banners shows
#MAX_CLICK_COUNT# - max number of all contract banners clicks
#CLICK_COUNT# - number of all contract banners clicks
#BANNERS# - number of contract banners
#DATE_SHOW_FROM# - start date of banner showing period
#DATE_SHOW_TO# - end date of banner showing period
#DATE_CREATE# - contract creation date
#CREATED_BY# - contract creator
#DATE_MODIFY# - contract modification date
#MODIFIED_BY# - who has modified the contract
";
$MESS ['ADV_CONTRACT_INFO_SUBJECT'] = "[CID##ID#] #SITE_NAME#: Advertising contract settings";
$MESS ['ADV_CONTRACT_INFO_MESSAGE'] = "#MESSAGE#
Contract: [#ID#] #NAME#
#DESCRIPTION#
>=================== Contract settings ==============================

Activity: #INDICATOR#

Period    - [#DATE_SHOW_FROM# - #DATE_SHOW_TO#]
Shown     - #SHOW_COUNT# / #MAX_SHOW_COUNT#
Clicked   - #CLICK_COUNT# / #MAX_CLICK_COUNT#
Act. flag - [#ACTIVE#]

Banners   - #BANNERS#
>=====================================================================

Created  - #CREATED_BY# [#DATE_CREATE#]
Changed  - #MODIFIED_BY# [#DATE_MODIFY#]

To view the contract settings visit link:
http://#SERVER_NAME#/bitrix/admin/adv_contract_edit.php?ID=#ID#&lang=#LANGUAGE_ID#

Automatically generated message.
";
?>