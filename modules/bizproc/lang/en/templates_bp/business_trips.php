<?
$MESS["BPT_TTITLE"] = "Business Trips";
$MESS["BPT_BT_PARAM_OP_READ"] = "Employees Allowed to View All Processes";
$MESS["BPT_BT_PARAM_OP_CREATE"] = "Employees Allowed to Create New Processes";
$MESS["BPT_BT_PARAM_OP_ADMIN"] = "Employees Allowed to Manage Processes";
$MESS["BPT_BT_PARAM_BOSS"] = "Employees to Approve Business Trips";
$MESS["BPT_BT_PARAM_BOOK"] = "Accounting Department";
$MESS["BPT_BT_PARAM_FORM1"] = "Travel Expense Summary form";
$MESS["BPT_BT_PARAM_FORM2"] = "Business Trip Assignment Form";
$MESS["BPT_BT_P_TARGET"] = "Employee";
$MESS["BPT_BT_T_PURPOSE"] = "Purpose";
$MESS["BPT_BT_T_COUNTRY"] = "Destination Country";
$MESS["BPT_BT_T_COUNTRY_DEF"] = "USA";
$MESS["BPT_BT_T_CITY"] = "Destination City";
$MESS["BPT_BT_T_DATE_START"] = "Start Date";
$MESS["BPT_BT_T_DATE_END"] = "Planned End Date";
$MESS["BPT_BT_T_EXP"] = "Planned Expenses";
$MESS["BPT_BT_T_TICKETS"] = "Attached Files";
$MESS["BPT_BT_SWA"] = "Sequential Business Process";
$MESS["BPT_BT_SFA1_NAME"] = "Business Trip  {=Template:TargetUser_printable}, {=Template:COUNTRY}-{=Template:CITY}";
$MESS["BPT_BT_SFA1_TITLE"] = "Save Trip Parameters";
$MESS["BPT_BT_STA1_STATE_TITLE"] = "Project";
$MESS["BPT_BT_STA1_TITLE"] = "Set Status Text";
$MESS["BPT_BT_AA1_NAME"] = "Approve Business Trip {=Template:TargetUser_printable}, {=Template:COUNTRY} - {=Template:CITY}";
$MESS["BPT_BT_AA1_DESCR"] = "A business trip requires approval {=Template:TargetUser_printable}

Mission Country: {=Template:COUNTRY}
Mission City: {=Template:CITY}
Dates: {=Template:date_start} - {=Template:date_end}
Planned Expenditure: {=Template:expenditures}

Purpose:
{=Template:purpose}";
$MESS["BPT_BT_AA1_STATUS_MESSAGE"] = "Awaiting Approval";
$MESS["BPT_BT_AA1_TITLE"] = "Business Trip Approval";
$MESS["BPT_BT_SA1_TITLE"] = "Sequence Of Actions";
$MESS["BPT_BT_SSTA2_STATE_TITLE"] = "Register a business trip";
$MESS["BPT_BT_SSTA2_TITLE"] = "Set Status: Registering";
$MESS["BPT_BT_SNMA1_TEXT"] = "Your business trip has been approved.";
$MESS["BPT_BT_SNMA1_TITLE"] = "Social Network Message";
$MESS["BPT_BT_RA1_NAME"] = "Register a business trip for {=Template:TargetUser_printable}, {=Template:COUNTRY}-{=Template:CITY}";
$MESS["BPT_BT_RA1_DESCR"] = "This business trip has been approved and must be registered by accounting.

Employee: {=Template:TargetUser_printable}

Destination Country: {=Template:COUNTRY}
Destination City: {=Template:CITY}
Dates: {=Template:date_start} - {=Template:date_end}
Planned Expenses: {=Template:expenditures}

Purpose:
{=Template:purpose}

Attached files:
{=Template:tickets_printable}";
$MESS["BPT_BT_RA1_STATUS_MESSAGE"] = "Registration by Accounting";
$MESS["BPT_BT_RA1_TBM"] = "Business trip registered";
$MESS["BPT_BT_RA1_TITLE"] = "Registration by Accounting";
$MESS["BPT_BT_RA2_NAME"] = "Business Trip Documentation";
$MESS["BPT_BT_RA2_DESCR"] = "Before you leave for a business trip:

1. [url={=Variable:ParameterForm1}]Download[/url] the Travel Expenses form.

This form should be filled out at the end of your trip and given to the Accounting Department.

2. Remember that any payment occurring during your business trip must be confirmed by a corresponding bill or receipt.";
$MESS["BPT_BT_RA2_STATUS_MESSAGE"] = "Instructions";
$MESS["BPT_BT_RA2_TBM"] = "Acquainted";
$MESS["BPT_BT_RA2_TITLE1"] = "Business Journey Documentation";
$MESS["BPT_BT_RA2_TITLE"] = "Acquaintance/Approval";
$MESS["BPT_BT_RIA1_NAME"] = "Summary Report (to be completed upon arrival from the business trip)";
$MESS["BPT_BT_RIA1_DESCR"] = "Post-travel report. Provide an expense report to Accounting with the original receipts.  Write a summary in the space provided below and include a link to your full report, if applicable.";
$MESS["BPT_BT_RIA1_DATE_END_REAL"] = "End Date";
$MESS["BPT_BT_RIA1_REPORT"] = "Summary Report";
$MESS["BPT_BT_RIA1_EXP_REAL"] = "Expenses";
$MESS["BPT_BT_RIA1_TITLE"] = "Summary Report";
$MESS["BPT_BT_SSTA3_STATE_TITLE"] = "Summary Report";
$MESS["BPT_BT_SSTA3_TITLE"] = "Set Status Text";
$MESS["BPT_BT_SFA2_TITLE"] = "Save Report";
$MESS["BPT_BT_RA3_NAME"] = "Read Summary Report: {=Template:TargetUser_printable}";
$MESS["BPT_BT_RA3_DESCR"] = "Travel report from {=Template:TargetUser_printable}

Planned Dates: {=Template:date_start} - {=Template:date_end}
Actual travel days: {=Variable:date_end_real}

Expenses:
{=Variable:expenditures_real}

Purpose:
{=Template:purpose}

Post-Travel Report:
{=Variable:report}";
$MESS["BPT_BT_RA3_STATUS_MESSAGE"] = "Report for Management";
$MESS["BPT_BT_RA3_TBM"] = "Acquainted";
$MESS["BPT_BT_RA4_NAME"] = "Summary Report {=Template:TargetUser_printable}";
$MESS["BPT_BT_RA4_DESCR"] = "The post-travel report for the below business trip has been approved and must be registered by Accounting. 

Travel report from {=Template:TargetUser_printable}

Actual travel days: {=Template:date_start} - {=Variable:date_end_real}

Total Expenses:
{=Variable:expenditures_real}

Summary Report:
{=Variable:report}";
$MESS["BPT_BT_RA4_STATUS_MESSAGE"] = "Registration in the Accounting Department";
$MESS["BPT_BT_RA4_TMB"] = "Registered";
$MESS["BPT_BT_RA4_TITLE"] = "Registration in the Accounting Department";
$MESS["BPT_BT_SSTA4_STATE_TITLE"] = "Completed";
$MESS["BPT_BT_SSTA4_TITLE"] = "Set Status Text";
$MESS["BPT_BT_SA3_TITLE"] = "Sequence of Actions";
$MESS["BPT_BT_SSTA5_STATE_TITLE"] = "Rejected by Management";
$MESS["BPT_BT_SSTA5_TITLE"] = "Set Status: Rejected";
$MESS["BPT_BT_SNMA2_TEXT"] = "Your business trip has not been confirmed by management.";
$MESS["BPT_BT_SNMA2_TITLE"] = "Social Network Message";
$MESS["BPT_BT_DF_CITY"] = "Destination City";
$MESS["BPT_BT_DF_COUNTRY"] = "Destination Country";
$MESS["BPT_BT_DF_TICKETS"] = "Attached Files";
$MESS["BPT_BT_DF_DATE_END_REAL"] = "Actually Completed On";
$MESS["BPT_BT_DF_EXP_REAL"] = "Expenses";
$MESS["BPT_BT_AA7_TITLE"] = "Absence Chart";
$MESS["BPT_BT_AA7_NAME"] = "Business Trip";
$MESS["BPT_BT_AA7_DESCR"] = "Purpose: {=Template:purpose}";
$MESS["BPT_BT_AA7_STATE"] = "Business Trip";
$MESS["BPT_BT_AA7_FSTATE"] = "Works";
?>