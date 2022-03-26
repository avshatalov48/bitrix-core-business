<?
$MESS["BP_EXPR_NAME"] = "Expert Opinion";
$MESS["BP_EXPR_DESC"] = "Recommended for situations when a person who is to approve or reject a document needs expert comments on it. This process creates a group of experts each of which expresses their opinion on the document. Then	 the opinions are passed over to the person who makes the final decision.";
$MESS["BP_EXPR_S"] = "Sequential Business Process";
$MESS["BP_EXPR_TASK1"] = "The document \"{=Document:NAME}\" requires your comments on it.";
$MESS["BP_EXPR_TASK1_MAIL"] = "Your opinion is required to make decision on the document \"{=Document:NAME}\".

Please proceed by opening the link: #BASE_HREF##TASK_URL#";
$MESS["BP_EXPR_M"] = "email Message";
$MESS["BP_EXPR_APPR1"] = "The document \"{=Document:NAME}\" requires your comments on it.";
$MESS["BP_EXPR_APPR1_DESC"] = "Your opinion is required to make decision on the document \"{=Document:NAME}\".";
$MESS["BP_EXPR_ST_1"] = "Sequence of Activities";
$MESS["BP_EXPR_MAIL2_SUBJ"] = "Approve document: \"{=Document:NAME}\"";
$MESS["BP_EXPR_MAIL2_TEXT"] = "All the appointed persons have examined the document and expressed their opinion.																										
You now need to approve or reject the document.																										
																										
Please proceed by opening the link: #BASE_HREF##TASK_URL#
																										
{=ApproveActivity1:Comments}";
$MESS["BP_EXPR_APP2_TEXT"] = "Approve document: \"{=Document:NAME}\"";
$MESS["BP_EXPR_APP2_DESC"] = "All the appointed persons have examined the document and expressed their opinion.																										
																										
{=ApproveActivity1:Comments}																										
																										
You now need to approve or reject the document.";
$MESS["BP_EXPR_TAPP"] = "Approve Document";
$MESS["BP_EXPR_MAIL3_SUBJ"] = "Approval of \"{=Document:NAME}\": the document has passed.";
$MESS["BP_EXPR_MAIL3_TEXT"] = "Debate on \"{=Document:NAME}\" has completed; the document has been approved.																										
																										
{=ApproveActivity2:Comments}";
$MESS["BP_EXPR_ST3_T"] = "Approved";
$MESS["BP_EXPR_ST3_TIT"] = "Status: Approved";
$MESS["BP_EXPR_PUB"] = "Publish Document";
$MESS["BP_EXPR_MAIL4_SUBJ"] = "Discussion on \"{=Document:NAME}\": the document has been rejected.";
$MESS["BP_EXPR_MAIL4_TEXT"] = "discussions on \"{=Document:NAME}\" have completed; the document has been rejected.																										
																										
{=ApproveActivity2:Comments}";
$MESS["BP_EXPR_NA"] = "Rejected";
$MESS["BP_EXPR_NA_ST"] = "Status: Rejected";
$MESS["BP_EXPR_PARAM2"] = "Experts";
$MESS["BP_EXPR_PARAM2_DESC"] = "An expert group whose members can express their opinion on a document.";
$MESS["BP_EXPR_PARAM1"] = "Approving Person";
?>