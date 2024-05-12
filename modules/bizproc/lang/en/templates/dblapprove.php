<?php
$MESS["BP_DBLA_APP"] = "Approved";
$MESS["BP_DBLA_APPROVE2_MSGVER_1"] = "Please approve or reject the workflow element.";
$MESS["BP_DBLA_APPROVE2_TEXT_MSGVER_1"] = "You have to approve or reject the workflow element \"{=Document:NAME}\".

Author: {=Document:CREATED_BY_PRINTABLE}";
$MESS["BP_DBLA_APPROVE2_TITLE_MSGVER_1"] = "Workflow element approval: stage 2";
$MESS["BP_DBLA_APPROVE_MSGVER_1"] = "Please approve or reject the workflow element.";
$MESS["BP_DBLA_APPROVE_TEXT_MSGVER_1"] = "You have to approve or reject the workflow element \"{=Document:NAME}\".
		
Author: {=Document:CREATED_BY_PRINTABLE}";
$MESS["BP_DBLA_APPROVE_TITLR_MSGVER_1"] = "Workflow element approval: stage 1";
$MESS["BP_DBLA_APP_S"] = "Status: Approved";
$MESS["BP_DBLA_DESC_MSGVER_1"] = "Recommended when a workflow element requires expert evaluation before it is approved or rejected. During the first stage, the workflow element is evaluated by a designated expert. If the expert rejects the workflow element, the latter is returned for revision. Otherwise, it is passed on for final approval by a group of employees, on a simple majority basis. If the final vote fails, the workflow element is returned for revision and the approval procedure starts from the beginning.";
$MESS["BP_DBLA_M"] = "Email Message";
$MESS["BP_DBLA_MAIL2_SUBJ_MSGVER_1"] = "Please approve or reject \"{=Document:NAME}\"";
$MESS["BP_DBLA_MAIL2_TEXT_MSGVER_1"] = "You have to approve or reject the workflow element \"{=Document:NAME}\".

Proceed by following the link: #BASE_HREF##TASK_URL#

Author: {=Document:CREATED_BY_PRINTABLE}";
$MESS["BP_DBLA_MAIL3_SUBJ_MSGVER_1"] = "Voting on \"{=Document:NAME}\": The workflow element has been approved.";
$MESS["BP_DBLA_MAIL3_TEXT_MSGVER_1"] = "Voting on \"{=Document:NAME}\" has finished.

The workflow element was approved by {=ApproveActivity2:ApprovedPercent}% of votes.

Approved: {=ApproveActivity2:ApprovedCount}																									
Rejected: {=ApproveActivity2:NotApprovedCount}

{=ApproveActivity2:Comments}";
$MESS["BP_DBLA_MAIL4_SUBJ_MSGVER_1"] = "Voting on {=Document:NAME}: The workflow element has been rejected.";
$MESS["BP_DBLA_MAIL4_TEXT_MSGVER_1"] = "The first stage of voting on \"{=Document:NAME}\" has finished.

The workflow element has been rejected.

{=ApproveActivity1:Comments}";
$MESS["BP_DBLA_MAIL_SUBJ_MSGVER_1"] = "Workflow element has passed stage 1";
$MESS["BP_DBLA_MAIL_TEXT_MSGVER_1"] = "The workflow element \"{=Document:NAME}\" has passed the first stage of approval.											

The workflow element has been approved.																										

{=ApproveActivity1:Comments}";
$MESS["BP_DBLA_NAME"] = "Two-stage Approval";
$MESS["BP_DBLA_NAPP_DRAFT"] = "Sent for revision";
$MESS["BP_DBLA_NAPP_DRAFT_S"] = "Status: Sent for revision";
$MESS["BP_DBLA_NAPP_MSGVER_1"] = "Voting on \"{=Document:NAME}\": The workflow element has been rejected.";
$MESS["BP_DBLA_NAPP_TEXT_MSGVER_1"] = "Voting on \"{=Document:NAME}\" has finished.
						
The workflow element has been rejected.

Approved: {=ApproveActivity2:ApprovedCount}																										
Rejected: {=ApproveActivity2:NotApprovedCount}

{=ApproveActivity2:Comments}";
$MESS["BP_DBLA_PARAM1"] = "Stage 1 Voting Persons";
$MESS["BP_DBLA_PARAM2"] = "Stage 2 Voting Persons";
$MESS["BP_DBLA_PUB_TITLE_MSGVER_1"] = "Publish workflow element";
$MESS["BP_DBLA_S_1"] = "Sequence Of Activities";
$MESS["BP_DBLA_T"] = "Sequential Business Process";
$MESS["BP_DBLA_TASK_DESC_MSGVER_1"] = "You have to approve or reject the workflow element \"{=Document:NAME}\".

Proceed by following the link: #BASE_HREF##TASK_URL#

Author: {=Document:CREATED_BY_PRINTABLE}";
$MESS["BP_DBLA_TASK_MSGVER_1"] = "Approve workflow element \"{=Document:NAME}\"";
