<?
$MESS["BP_DBLA_NAME"] = "Two-stage Approval";
$MESS["BP_DBLA_DESC"] = "Recommended when a document requires preliminary expert evaluation before being approved. During the first process stage	 a document is attested by an expert. If an expert rejects the document	 the latter is returned to an originator for revision. 	 If the document is approved	 it is conveyed for  final approval by a selected group of employees on a simple majority basis. If the final vote fails,	 the document is returned for revision	 and the approval procedure starts from the beginning.";
$MESS["BP_DBLA_T"] = "Sequential Business Process";
$MESS["BP_DBLA_TASK"] = "Approve document: \"{=Document:NAME}\"";
$MESS["BP_DBLA_TASK_DESC"] = "You have to approve or reject the document \"{=Document:NAME}\".

Proceed by opening the link: #BASE_HREF##TASK_URL#

Author: {=Document:CREATED_BY_PRINTABLE}";
$MESS["BP_DBLA_M"] = "Email Message";
$MESS["BP_DBLA_APPROVE"] = "Please approve or reject the document.";
$MESS["BP_DBLA_APPROVE_TEXT"] = "You have to approve or reject the document \"{=Document:NAME}\".
		
Author: {=Document:CREATED_BY_PRINTABLE}";
$MESS["BP_DBLA_APPROVE_TITLR"] = "Document Approval: Stage 1";
$MESS["BP_DBLA_S_1"] = "Sequence Of Activities";
$MESS["BP_DBLA_MAIL_SUBJ"] = "The document has passed Stage 1";
$MESS["BP_DBLA_MAIL_TEXT"] = "The document \"{=Document:NAME}\" has passed the first stage of approval.											

The document has been approved.																										

{=ApproveActivity1:Comments}";
$MESS["BP_DBLA_MAIL2_SUBJ"] = "Please respond concerning \"{=Document:NAME}\"";
$MESS["BP_DBLA_MAIL2_TEXT"] = "You have to approve or reject the document \"{=Document:NAME}\".

Proceed by opening the link: #BASE_HREF##TASK_URL#

Author: {=Document:CREATED_BY_PRINTABLE}";
$MESS["BP_DBLA_APPROVE2"] = "Please approve or reject the document.";
$MESS["BP_DBLA_APPROVE2_TEXT"] = "You have to approve or reject the document \"{=Document:NAME}\".

Author: {=Document:CREATED_BY_PRINTABLE}";
$MESS["BP_DBLA_APPROVE2_TITLE"] = "Document Approval: Stage 2";
$MESS["BP_DBLA_MAIL3_SUBJ"] = "Voting on \"{=Document:NAME}: The document has been accepted.";
$MESS["BP_DBLA_MAIL3_TEXT"] = "Voting on \"{=Document:NAME}\" has been completed.

The document was accepted by {=ApproveActivity2:ApprovedPercent}% of votes.

Approved: {=ApproveActivity2:ApprovedCount}																										
Rejected: {=ApproveActivity2:NotApprovedCount}

{=ApproveActivity2:Comments}";
$MESS["BP_DBLA_APP"] = "Approved";
$MESS["BP_DBLA_APP_S"] = "Status: Approved";
$MESS["BP_DBLA_PUB_TITLE"] = "Publish Document";
$MESS["BP_DBLA_NAPP"] = "Voting on \"{=Document:NAME}: The document has been rejected.";
$MESS["BP_DBLA_NAPP_TEXT"] = "Voting on \"{=Document:NAME}\" has been completed.
						
The document was rejected.

Approved: {=ApproveActivity2:ApprovedCount}																										
Rejected: {=ApproveActivity2:NotApprovedCount}

{=ApproveActivity2:Comments}";
$MESS["BP_DBLA_NAPP_DRAFT"] = "Sent for revision";
$MESS["BP_DBLA_NAPP_DRAFT_S"] = "Status: Sent for revision";
$MESS["BP_DBLA_MAIL4_SUBJ"] = "Voting on {=Document:NAME}: The document has been rejected.";
$MESS["BP_DBLA_MAIL4_TEXT"] = "The first stage of approving \"{=Document:NAME}\" has completed.

The document was rejected.

{=ApproveActivity1:Comments}";
$MESS["BP_DBLA_PARAM1"] = "Stage 1 Voting Persons";
$MESS["BP_DBLA_PARAM2"] = "Stage 2 Voting Persons";
?>