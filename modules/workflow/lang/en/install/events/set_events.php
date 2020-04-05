<?
$MESS["WF_STATUS_CHANGE_NAME"] = "Document status was changed";
$MESS["WF_STATUS_CHANGE_DESC"] = "#ID# - ID
#ADMIN_EMAIL# - Emails of workflow administrators
#BCC# - Emails of the users who have already modified the document at some time or who can modify it
#PREV_STATUS_ID# - ID of previous status of document
#PREV_STATUS_TITLE# - name of previous status of document
#STATUS_ID# - status ID
#STATUS_TITLE# - status name
#DATE_ENTER# - date of document creation
#ENTERED_BY_ID# - ID of the user that created document
#ENTERED_BY_NAME# - name of the user that created document
#ENTERED_BY_EMAIL# - Email of the user that created document
#DATE_MODIFY# - date of document modification
#MODIFIED_BY_ID# - ID of the user that modified document
#MODIFIED_BY_NAME# - name of the user that modified document
#FILENAME# - full file name
#TITLE# - file title
#BODY_HTML# - document contents in HTML format
#BODY_TEXT# - document contents in TEXT format
#BODY# - document's content stored in database
#BODY_TYPE# - type of document contents
#COMMENTS# - comments
";
$MESS["WF_STATUS_CHANGE_SUBJECT"] = "#SITE_NAME#: Status of document # #ID# was changed";
$MESS["WF_STATUS_CHANGE_MESSAGE"] = "Status of document # #ID# was changed at #SITE_NAME#.
---------------------------------------------------------------------------

Now the fields in document have the following values:

File          - #FILENAME#
Title         - #TITLE#
Status        - [#STATUS_ID#] #STATUS_TITLE#; previous - [#PREV_STATUS_ID#] #PREV_STATUS_TITLE#
Created       - #DATE_ENTER#; [#ENTERED_BY_ID#] #ENTERED_BY_NAME#
Modified      - #DATE_MODIFY#; [#MODIFIED_BY_ID#] #MODIFIED_BY_NAME#

Contents (type - #BODY_TYPE#):
---------------------------------------------------------------------------
#BODY_TEXT#
---------------------------------------------------------------------------

Comments:
---------------------------------------------------------------------------
#COMMENTS#
---------------------------------------------------------------------------

To view and edit the document click the link:
http://#SERVER_NAME#/bitrix/admin/workflow_edit.php?lang=en&ID=#ID#

Automatically generated message.
";
$MESS["WF_NEW_DOCUMENT_NAME"] = "New document was created";
$MESS["WF_NEW_DOCUMENT_DESC"] = "#ID# - ID
#ADMIN_EMAIL# - EMails of workflow administrators
#BCC# - Emails of the users which have already modified the document some time or which can modify it
#STATUS_ID# - status ID
#STATUS_TITLE# - status name
#DATE_ENTER# - date of document creation
#ENTERED_BY_ID# - ID of the user that created document
#ENTERED_BY_NAME# - name of the user that created document
#ENTERED_BY_EMAIL# - EMail of the user that created document
#FILENAME# - full file name
#TITLE# - file title
#BODY_HTML# - document contents in HTML format
#BODY_TEXT# - document contents in TEXT format
#BODY# - document's content stored in database
#BODY_TYPE# - type of document contents
#COMMENTS# - comments
";
$MESS["WF_NEW_DOCUMENT_SUBJECT"] = "#SITE_NAME#: New document was created";
$MESS["WF_NEW_DOCUMENT_MESSAGE"] = "New document was created at #SITE_NAME#.
---------------------------------------------------------------------------

Now the fields in document have the following values:

ID            - #ID#
File          - #FILENAME#
Title         - #TITLE#
Status        - [#STATUS_ID#] #STATUS_TITLE#
Created       - #DATE_ENTER#; [#ENTERED_BY_ID#] #ENTERED_BY_NAME#

Contents (type - #BODY_TYPE#):
---------------------------------------------------------------------------
#BODY_TEXT#
---------------------------------------------------------------------------

Comments:
---------------------------------------------------------------------------
#COMMENTS#
---------------------------------------------------------------------------

To view and edit the document visit link:
http://#SERVER_NAME#/bitrix/admin/workflow_edit.php?lang=en&ID=#ID#

Automatically generated message.
";
$MESS["WF_IBLOCK_STATUS_CHANGE_NAME"] = "Infoblock element status was changed";
$MESS["WF_IBLOCK_STATUS_CHANGE_DESC"] = "#ID# - ID
#IBLOCK_ID# - ID of informational block
#IBLOCK_TYPE# - informational block type
#SECTION_ID# - section ID
#ADMIN_EMAIL# - EMails of workflow administrators
#BCC# - Emails of the users which have already modified the element some time or which can modify it
#PREV_STATUS_ID# - ID of previous status of element
#PREV_STATUS_TITLE# - name of previous status of element
#STATUS_ID# - current status ID
#STATUS_TITLE# - current status name
#DATE_CREATE# - date of element creation
#CREATED_BY_ID# - ID of the user that created element
#CREATED_BY_NAME# - name of the user that created element
#CREATED_BY_EMAIL# - EMail of the user that created element
#DATE_MODIFY# - date of element modification
#MODIFIED_BY_ID# - ID of the user that modified element
#MODIFIED_BY_NAME# - name of the user that modified element
#NAME# - element name
#PREVIEW_HTML# - brief description in HTML format
#PREVIEW_TEXT# - brief description in TEXT format
#PREVIEW# - brief description stored in database
#PREVIEW_TYPE# - brief description type (text | html)
#DETAIL_HTML# - full description in HTML format
#DETAIL_TEXT# - full description in TEXT format
#DETAIL# - full description stored in database
#DETAIL_TYPE# - full description type (text | html)
#COMMENTS# - comments
";
$MESS["WF_IBLOCK_STATUS_CHANGE_SUBJECT"] = "#SITE_NAME#: Status of element # #ID# was changed (informational block # #IBLOCK_ID#; type - #IBLOCK_TYPE#)";
$MESS["WF_IBLOCK_STATUS_CHANGE_MESSAGE"] = "#SITE_NAME#: Status of element # #ID# was changed (informational block # #IBLOCK_ID#; type - #IBLOCK_TYPE#)
---------------------------------------------------------------------------

Now the fields of element have the following values:

Name         - #NAME#
Status       - [#STATUS_ID#] #STATUS_TITLE#; previous - [#PREV_STATUS_ID#] #PREV_STATUS_TITLE#
Created      - #DATE_CREATE#; [#CREATED_BY_ID#] #CREATED_BY_NAME#
Modified     - #DATE_MODIFY#; [#MODIFIED_BY_ID#] #MODIFIED_BY_NAME#

Brief description (type - #PREVIEW_TYPE#):
---------------------------------------------------------------------------
#PREVIEW_TEXT#
---------------------------------------------------------------------------

Full description (type - #DETAIL_TYPE#):
---------------------------------------------------------------------------
#DETAIL_TEXT#
---------------------------------------------------------------------------

Comments:
---------------------------------------------------------------------------
#COMMENTS#
---------------------------------------------------------------------------

To view and edit the document visit link:
http://#SERVER_NAME#/bitrix/admin/iblock_element_edit.php?lang=en&WF=Y&PID=#ID#&type=#IBLOCK_TYPE#&IBLOCK_ID=#IBLOCK_ID#&filter_section=#SECTION_ID#

Automatically generated message.
";
$MESS["WF_NEW_IBLOCK_ELEMENT_NAME"] = "New element of information block was created";
$MESS["WF_NEW_IBLOCK_ELEMENT_DESC"] = "#ID# - ID
#IBLOCK_ID# - ID of informational block
#IBLOCK_TYPE# - informational block type
#SECTION_ID# - section ID
#ADMIN_EMAIL# - EMails of workflow administrators
#BCC# - Emails of the users which have already modified the element some time or which can modify it
#STATUS_ID# - current status ID
#STATUS_TITLE# - current status name
#DATE_CREATE# - date of element creation
#CREATED_BY_ID# - ID of the user that created element
#CREATED_BY_NAME# - name of the user that created element
#CREATED_BY_EMAIL# - EMail of the user that created element
#NAME# - element name
#PREVIEW_HTML# - brief description in HTML format
#PREVIEW_TEXT# - brief description in TEXT format
#PREVIEW# - brief description stored in database
#PREVIEW_TYPE# - brief description type (text | html)
#DETAIL_HTML# - full description in HTML format
#DETAIL_TEXT# - full description in TEXT format
#DETAIL# - full description stored in database
#DETAIL_TYPE# - full description type (text | html)
#COMMENTS# - comments
";
$MESS["WF_NEW_IBLOCK_ELEMENT_SUBJECT"] = "#SITE_NAME#: New element was created (informational block # #IBLOCK_ID#; type - #IBLOCK_TYPE#)";
$MESS["WF_NEW_IBLOCK_ELEMENT_MESSAGE"] = "#SITE_NAME#: New element was created (informational block # #IBLOCK_ID#; type - #IBLOCK_TYPE#)
---------------------------------------------------------------------------

Now the fields of element have the following values:

Name         - #NAME#
Status       - [#STATUS_ID#] #STATUS_TITLE#
Created      - #DATE_CREATE#; [#CREATED_BY_ID#] #CREATED_BY_NAME#

Brief description (type - #PREVIEW_TYPE#):
---------------------------------------------------------------------------
#PREVIEW_TEXT#
---------------------------------------------------------------------------

Full description (type - #DETAIL_TYPE#):
---------------------------------------------------------------------------
#DETAIL_TEXT#
---------------------------------------------------------------------------

Comments:
---------------------------------------------------------------------------
#COMMENTS#
---------------------------------------------------------------------------

To view and edit the document visit link:
http://#SERVER_NAME#/bitrix/admin/iblock_element_edit.php?lang=en&WF=Y&PID=#ID#&type=#IBLOCK_TYPE#&IBLOCK_ID=#IBLOCK_ID#&filter_section=#SECTION_ID#

Automatically generated message.
";
?>