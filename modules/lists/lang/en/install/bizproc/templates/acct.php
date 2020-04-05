<?
$MESS["LIBTA_NAME"] = "Name";
$MESS["LIBTA_TYPE"] = "Type";
$MESS["LIBTA_TYPE_ADV"] = "Advertising";
$MESS["LIBTA_TYPE_EX"] = "Representation allowance";
$MESS["LIBTA_TYPE_C"] = "Reimbursable expenses";
$MESS["LIBTA_TYPE_D"] = "Other";
$MESS["LIBTA_CREATED_BY"] = "Created by";
$MESS["LIBTA_DATE_CREATE"] = "Created on";
$MESS["LIBTA_FILE"] = "File (invoice copy)";
$MESS["LIBTA_NUM_DATE"] = "Invoice number and date";
$MESS["LIBTA_SUM"] = "Amount";
$MESS["LIBTA_PAID"] = "Paid";
$MESS["LIBTA_PAID_NO"] = "No";
$MESS["LIBTA_PAID_YES"] = "Yes";
$MESS["LIBTA_BDT"] = "Budget item";
$MESS["LIBTA_DATE_PAY"] = "Payment date (provided by bookkeeper)";
$MESS["LIBTA_NUM_PP"] = "Payment order number (provided by bookkeeper)";
$MESS["LIBTA_DOCS"] = "Copies of documents";
$MESS["LIBTA_DOCS_YES"] = "Yes";
$MESS["LIBTA_DOCS_NO"] = "No";
$MESS["LIBTA_APPROVED"] = "Approved";
$MESS["LIBTA_APPROVED_R"] = "Rejected";
$MESS["LIBTA_APPROVED_N"] = "Not approved";
$MESS["LIBTA_APPROVED_Y"] = "Approved";
$MESS["LIBTA_T_PBP"] = "Sequential business process";
$MESS["LIBTA_T_SPA1"] = "Set permissions for: author";
$MESS["LIBTA_T_PDA1"] = "Publish document";
$MESS["LIBTA_STATE1"] = "Being approved";
$MESS["LIBTA_T_SSTA1"] = "Status: being approved";
$MESS["LIBTA_T_ASFA1"] = "Set the document's \"Approved\" field";
$MESS["LIBTA_T_SVWA1"] = "Set supervisor";
$MESS["LIBTA_T_WHILEA1"] = "Approval cycle";
$MESS["LIBTA_T_SA0"] = "Sequence of actions";
$MESS["LIBTA_T_IFELSEA1"] = "Reached supervisors";
$MESS["LIBTA_T_IFELSEBA1"] = "Yes";
$MESS["LIBTA_T_ASFA2"] = "Set the document's \"Approved\" field";
$MESS["LIBTA_T_IFELSEBA2"] = "No";
$MESS["LIBTA_T_GUAX1"] = "Select supervisor";
$MESS["LIBTA_T_SVWA2"] = "Set supervisor";
$MESS["LIBTA_T_SPAX1"] = "Set permissions for: supervisor";
$MESS["LIBTA_SMA_MESSAGE_1"] = "Please approve the invoice
Created by: {=Document:CREATED_BY_PRINTABLE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}
Amount: {=Document:PROPERTY_SUM}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_SMA_MESSAGE_1"] = "Message: invoice approval request";
$MESS["LIBTA_XMA_MESSAGES_1"] = "BIP: Invoice approval";
$MESS["LIBTA_XMA_MESSAGET_1"] = "Please approve the invoice

Created by: {=Document:CREATED_BY_PRINTABLE}
Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}
Invoice number and date: {=Document:PROPERTY_NUM_DATE}
Amount: {=Document:PROPERTY_SUM}
Budget Item: {=Document:PROPERTY_BDT}

{=Variable:Link}{=Document:ID}/


Business process tasks:
{=Variable:TasksLink}";
$MESS["LIBTA_T_XMA_MESSAGES_1"] = "Message: invoice approval";
$MESS["LIBTA_AAQN1"] = "Approve invoice \"{=Document:NAME}\"";
$MESS["LIBTA_AAQD1"] = "You need to approve or decline the invoice

Name: {=Document:NAME}
Created on: {=Document:DATE_CREATE}
Created By: {=Document:CREATED_BY_PRINTABLE}
Type: {=Document:PROPERTY_TYPE}
Invoice number and date: {=Document:PROPERTY_NUM_DATE}
Amount: {=Document:PROPERTY_SUM}
Budget Item: {=Document:PROPERTY_BDT}
File: {=Variable:Domain}{=Document:PROPERTY_FILE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_AAQN1"] = "Approve invoice payment";
$MESS["LIBTA_STATE2"] = "Approved ({=Variable:Approver_printable})";
$MESS["LIBTA_T_SSTA2"] = "Status: approved";
$MESS["LIBTA_STATE3"] = "Not approved ({=Variable:Approver_printable})";
$MESS["LIBTA_T_SSTA3"] = "Status: not approved";
$MESS["LIBTA_T_ASFA3"] = "Set the document's \"Approved\" field";
$MESS["LIBTA_T_IFELSEA2"] = "Invoice approved";
$MESS["LIBTA_T_IFELSEBA3"] = "Yes";
$MESS["LIBTA_SMA_MESSAGE_2"] = "I approve the invoice

Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_SMA_MESSAGE_2"] = "Message: invoice approved";
$MESS["LIBTA_T_SPAX2"] = "Set permissions for: approving manager";
$MESS["LIBTA_SMA_MESSAGE_3"] = "Please approve invoice payment

Approved by: {=Variable:Approver_printable}
Created by: {=Document:CREATED_BY_PRINTABLE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}
Invoice number and date: {=Document:PROPERTY_NUM_DATE}
Amount: {=Document:PROPERTY_SUM}

{=Variable:Link}{=Document:ID}/

Tasks:
{=Variable:TasksLink}";
$MESS["LIBTA_T_SMA_MESSAGE_3"] = "Message: payment confirmation";
$MESS["LIBTA_XMA_MESSAGES_2"] = "BIP: Payment confirmation";
$MESS["LIBTA_XMA_MESSAGET_2"] = "Please confirm invoice payment

Approved by: {=Variable:Approver_printable}
Created by: {=Document:CREATED_BY_PRINTABLE}
Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}
Invoice number and date: {=Document:PROPERTY_NUM_DATE}
Amount: {=Document:PROPERTY_SUM}
Budget item: {=Document:PROPERTY_BDT}

{=Variable:Link}{=Document:ID}/

Tasks:
{=Variable:TasksLink}";
$MESS["LIBTA_T_XMA_MESSAGES_2"] = "Message: Payment confirmation";
$MESS["LIBTA_STATE4"] = "Payment is being confirmed";
$MESS["LIBTA_T_SSTA4"] = "Status: payment is being confirmed";
$MESS["LIBTA_AAQN2"] = "Approve invoice payment \"{=Document:NAME}\"";
$MESS["LIBTA_AAQD2"] = "You need to approve or decline invoice payment

Approved by: {=Variable:Approver_printable}
Created by: {=Document:CREATED_BY_PRINTABLE}
Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}
Invoice Number And Date: {=Document:PROPERTY_NUM_DATE}
Amount: {=Document:PROPERTY_SUM}
Budget item: {=Document:PROPERTY_BDT}
File: {=Variable:Domain}{=Document:PROPERTY_FILE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_AAQN2"] = "Invoice payment confirmation";
$MESS["LIBTA_T_SVWA3"] = "Set variable";
$MESS["LIBTA_STATE5"] = "Payment confirmed";
$MESS["LIBTA_T_SSTA5"] = "Status: payment confirmed";
$MESS["LIBTA_SMA_MESSAGE_4"] = "Invoice payment confirmed

Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_SMA_MESSAGE_4"] = "Message: payment confirmed";
$MESS["LIBTA_T_SPAX3"] = "Set permissions for: payer";
$MESS["LIBTA_SMA_MESSAGE_5"] = "Please pay the invoice

Payment confirmed: {=Variable:PaymentApprover_printable}
Invoice approved: {=Variable:Approver_printable}
Created by: {=Document:CREATED_BY_PRINTABLE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}
Invoice number and date: {=Document:PROPERTY_NUM_DATE}
Amount: {=Document:PROPERTY_SUM}
Budget Item: {=Document:PROPERTY_BDT}

{=Variable:Link}{=Document:ID}/

Tasks:
{=Variable:TasksLink}";
$MESS["LIBTA_T_SMA_MESSAGE_5"] = "Message: invoice";
$MESS["LIBTA_XMA_MESSAGES_3"] = "BIP: Invoice";
$MESS["LIBTA_XMA_MESSAGET_3"] = "Please pay the invoice

Payment confirmed: {=Variable:PaymentApprover_printable}
Invoice approved: {=Variable:Approver_printable}
Created by: {=Document:CREATED_BY_PRINTABLE}
Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}
Invoice number and date: {=Document:PROPERTY_NUM_DATE}
Amount: {=Document:PROPERTY_SUM}
Budget Item: {=Document:PROPERTY_BDT}

{=Variable:Link}{=Document:ID}/

Tasks:
{=Variable:TasksLink}";
$MESS["LIBTA_T_XMA_MESSAGES_3"] = "Message: invoice";
$MESS["LIBTA_STATE6"] = "Payment pending";
$MESS["LIBTA_T_SSTA6"] = "Status: payment pending";
$MESS["LIBTA_T_ASFA4"] = "Edit document";
$MESS["LIBTA_STATE7"] = "Paid";
$MESS["LIBTA_T_SSTA7"] = "Status: invoice paid";
$MESS["LIBTA_SMA_MESSAGE_6"] = "Invoice paid; documentation required.

Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}
";
$MESS["LIBTA_T_SMA_MESSAGE_6"] = "Message: invoice paid";
$MESS["LIBTA_T_SPAX4"] = "Set permissions for: bookkeeper";
$MESS["LIBTA_SMA_MESSAGE_7"] = "All invoicing documentation collected

Payment date: {=Document:PROPERTY_DATE_PAY}
Payment oder number: {=Document:PROPERTY_NUM_PAY}
Created by: {=Document:CREATED_BY_PRINTABLE}
Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}
Invoice number and date: {=Document:PROPERTY_NUM_DATE}
Amount: {=Document:PROPERTY_SUM}

{=Variable:Link}{=Document:ID}/

Tasks:
{=Variable:TasksLink}";
$MESS["LIBTA_T_SMA_MESSAGE_7"] = "Message: invoicing documentation collected";
$MESS["LIBTA_T_ASFA5"] = "Edit document";
$MESS["LIBTA_STATE8"] = "Closed";
$MESS["LIBTA_T_SSTA8"] = "Status:: invoice closed";
$MESS["LIBTA_SMA_MESSAGE_8"] = "Documentation received.

Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_SMA_MESSAGE_8"] = "Message: documentation received";
$MESS["LIBTA_STATE9"] = "Payment declined";
$MESS["LIBTA_T_SSTA9"] = "Status: payment declined";
$MESS["LIBTA_SMA_MESSAGE_9"] = "Payment not confirmed

Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_SMA_MESSAGE_9"] = "Message: payment not confirmed";
$MESS["LIBTA_T_IFELSEBA4"] = "No";
$MESS["LIBTA_SMA_MESSAGE_10"] = "Invoice not approved

Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_SMA_MESSAGE_10"] = "Message: invoice not approved";
$MESS["LIBTA_T_SPAX5"] = "Set permissions: final";
$MESS["LIBTA_V_BK"] = "Accounts department (approval)";
$MESS["LIBTA_V_MNG"] = "Management board";
$MESS["LIBTA_V_APPRU"] = "Supervisor";
$MESS["LIBTA_V_BKP"] = "Accounts department (payment)";
$MESS["LIBTA_V_BKD"] = "Accounts department (documentation)";
$MESS["LIBTA_V_MAPPR"] = "Management board (approval)";
$MESS["LIBTA_V_LINK"] = "Link";
$MESS["LIBTA_V_TLINK"] = "Link to tasks";
$MESS["LIBTA_V_PDATE"] = "Payment date";
$MESS["LIBTA_V_PNUM"] = "Payment order number";
$MESS["LIBTA_V_APPR"] = "Payment approved by";
$MESS["LIBTA_BP_TITLE"] = "Invoices";
$MESS["LIBTA_RIA10_NAME"] = "Pay invoice \"{=Document:NAME}\"";
$MESS["LIBTA_RIA10_DESCR"] = "Pay invoice

Payment confirmed: {=Variable:PaymentApprover_printable}
Invoice approved: {=Variable:Approver_printable}
Created by: {=Document:CREATED_BY_PRINTABLE}
Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}
Invoice number and date: {=Document:PROPERTY_NUM_DATE}
Amount: {=Document:PROPERTY_SUM}
Budget item: {=Document:PROPERTY_BDT}
File: {=Variable:Domain}{=Document:PROPERTY_FILE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_RIA10_R1"] = "Payment date";
$MESS["LIBTA_RIA10_R2"] = "Payment order number";
$MESS["LIBTA_T_RIA10"] = "Pay invoice";
$MESS["LIBTA_RRA15_NAME"] = "Collect documentation on \"{=Document:NAME}\"";
$MESS["LIBTA_RRA15_DESCR"] = "Collect documentation on

Payment confirmed: {=Variable:PaymentApprover_printable}
Invoice approved: {=Variable:Approver_printable}
Created by: {=Document:CREATED_BY_PRINTABLE}
Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}
Invoice number and date: {=Document:PROPERTY_NUM_DATE}
Amount: {=Document:PROPERTY_SUM}
Budget item: {=Document:PROPERTY_BDT}
File: {=Variable:Domain}{=Document:PROPERTY_FILE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_RRA15_SM"] = "Collect documents";
$MESS["LIBTA_RRA15_TASKBUTTON"] = "Documentation collected";
$MESS["LIBTA_T_RRA15"] = "Documentation on";
$MESS["LIBTA_RRA17_NAME"] = "Confirm the receipt of documents on \"{=Document:NAME}\"";
$MESS["LIBTA_RRA17_DESCR"] = "I hereby confirm the receipt of invoice documentation.

Payment date: {=Document:PROPERTY_DATE_PAY}
Payment oder number: {=Document:PROPERTY_NUM_PAY}
Payment confirmed: {=Variable:PaymentApprover_printable}
Invoice approved: {=Variable:Approver_printable}
Created by: {=Document:CREATED_BY_PRINTABLE}
Created on: {=Document:DATE_CREATE}
Title: {=Document:NAME}
Type: {=Document:PROPERTY_TYPE}
Invoice number and date: {=Document:PROPERTY_NUM_DATE}
Amount: {=Document:PROPERTY_SUM}
Budget item: {=Document:PROPERTY_BDT}
File: {=Variable:Domain}{=Document:PROPERTY_FILE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_RRA17_BUTTON"] = "Documentation received";
$MESS["LIBTA_T_RRA17_NAME"] = "Documentation received";
$MESS["LIBTA_V_DOMAIN"] = "Domain";
?>