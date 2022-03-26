<?
$MESS["BP_V1ST_NAME"] = "First Approval";
$MESS["BP_V1ST_DESC"] = "Recommended when approval from a single respondent is sufficient. Create a list of persons who will be suggested to take part in voting. Voting is complete when a first vote has been received.";
$MESS["BP_V1ST_SEQ"] = "Sequential Business Process";
$MESS["BP_V1ST_TASK_NAME"] = "Approve document: \"{=Document:NAME}\"";
$MESS["BP_V1ST_TASK_TEXT"] = "You have to approve or reject the document \"{=Document:NAME}\".

Proceed by opening the link: #BASE_HREF##TASK_URL#

Author: {=Document:CREATED_BY_PRINTABLE}";
$MESS["BP_V1ST_MAIL_NAME"] = "email Message";
$MESS["BP_V1ST_TASK_T"] = "Please approve or reject the document.";
$MESS["BP_V1ST_TASK_DESC"] = "You have to approve or reject the document \"{=Document:NAME}\".

Author: {=Document:CREATED_BY_PRINTABLE}";
$MESS["BP_V1ST_VNAME"] = "Respond Concerning a Document";
$MESS["BP_V1ST_S2_1"] = "Sequence of Activities";
$MESS["BP_V1ST_MAIL_SUBJ"] = "Voting on {=Document:NAME}: The document has been approved.";
$MESS["BP_V1ST_MAIL_TEXT"] = "Voting on \"{=Document:NAME}\" has been completed.

The document has been approved.
{=ApproveActivity1:Comments}";
$MESS["BP_V1ST_APPR"] = "Approved";
$MESS["BP_V1ST_APPR_S"] = "Status: Approved";
$MESS["BP_V1ST_T3"] = "Publish Document";
$MESS["BP_V1ST_MAIL2_NA"] = "Voting on {=Document:NAME}: The document has been rejected.";
$MESS["BP_V1ST_MAIL2_NA_TEXT"] = "Voting on \"{=Document:NAME}\" has been completed.

The document has been rejected.
{=ApproveActivity1:Comments}";
$MESS["BP_V1ST_TNA"] = "The document has been rejected.";
$MESS["BP_V1ST_STAT_NA"] = "Rejected";
$MESS["BP_V1ST_STAT_NA_T"] = "Status: Rejected";
$MESS["BP_V1ST_PARAM1"] = "Voting Persons";
$MESS["BP_V1ST_PARAM1_DESC"] = "Users taking part in approval process.";
?>