<?
$MESS["BPT_SM_NAME"] = "Simple Approval/Vote";
$MESS["BPT_SM_DESC"] = "Recommended when a decision is to be made by a simple majority of votes. You can assign voting persons and allow them comment. When voting is complete	 all the involved persons are notified about the result.";
$MESS["BPT_SM_TITLE1"] = "Sequential Business Process";
$MESS["BPT_SM_TASK1_TITLE"] = "Approve document: \"{=Document:NAME}\"";
$MESS["BPT_SM_TASK1_TEXT"] = "You have to approve or reject the document \"{=Document:NAME}\".

Proceed by opening the link: #BASE_HREF##TASK_URL#

Author: {=Document:CREATED_BY_PRINTABLE}";
$MESS["BPT_SM_ACT_TITLE"] = "Email Message";
$MESS["BPT_SM_APPROVE_NAME"] = "Please approve or reject the document.";
$MESS["BPT_SM_APPROVE_DESC"] = "You have to approve or reject the document \"{=Document:NAME}\".

Author: {=Document:CREATED_BY_PRINTABLE}";
$MESS["BPT_SM_APPROVE_TITLE"] = "Respond Concerning a Document";
$MESS["BPT_SM_ACT_NAME"] = "Sequence Of Actions";
$MESS["BPT_SM_MAIL1_SUBJ"] = "Voting on \"{=Document:NAME}: The document has passed.";
$MESS["BPT_SM_MAIL1_TEXT"] = "Voting on \"{=Document:NAME}\" has been completed.

The document was accepted by {=ApproveActivity1:ApprovedPercent}% of votes.

Approved: {=ApproveActivity1:ApprovedCount}
Rejected: {=ApproveActivity1:NotApprovedCount}";
$MESS["BPT_SM_MAIL1_TITLE"] = "The document has been approved";
$MESS["BPT_SM_STATUS"] = "Approved";
$MESS["BPT_SM_STATUS2"] = "Status: Approved";
$MESS["BPT_SM_PUB"] = "Publish Document";
$MESS["BPT_SM_MAIL2_SUBJ"] = "Voting on \"{=Document:NAME}: The document has been rejected.";
$MESS["BPT_SM_MAIL2_TEXT"] = "Voting on \"{=Document:NAME}\" has beencompleted.

The document was rejected.

Approved: {=ApproveActivity1:ApprovedCount}
Rejected: {=ApproveActivity1:NotApprovedCount}";
$MESS["BPT_SM_MAIL2_TITLE"] = "The document has been rejected.";
$MESS["BPT_SM_MAIL2_STATUS"] = "Rejected";
$MESS["BPT_SM_MAIL2_STATUS2"] = "Status: Rejected";
$MESS["BPT_SM_PARAM_NAME"] = "Voting Persons";
$MESS["BPT_SM_PARAM_DESC"] = "Users taking part in voting.";
?>