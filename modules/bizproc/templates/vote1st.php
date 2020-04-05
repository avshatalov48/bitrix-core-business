<?
IncludeModuleLangFile(__FILE__);

$arFields = Array(
	'AUTO_EXECUTE' => '0',
	'ACTIVE' 		=> 'Y',
	'NAME' => GetMessage("BP_V1ST_NAME"),
	'DESCRIPTION' => GetMessage("BP_V1ST_DESC"),
'TEMPLATE' => Array(
Array(
'Type' => 'SequentialWorkflowActivity',
'Name' => 'Template',
'Properties' =>
Array(
'Title' => GetMessage("BP_V1ST_SEQ"),
'Permission' => Array(
	'read' => Array(
		Array('Template', 'Voters'),
		'author'
	),
),
),
'Children' => Array(
Array(
'Type' => 'MailActivity',
'Name' => 'A71936_98620_60725_95722',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_V1ST_TASK_NAME"),
'MailText' => CBPDocument::_ReplaceTaskURL(GetMessage("BP_V1ST_TASK_TEXT"), $documentType),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array('Template', 'Voters'),
'Title' => GetMessage("BP_V1ST_MAIL_NAME")
)
),
Array(
'Type' => 'ApproveActivity',
'Name' => 'ApproveActivity1',
'Properties' =>
Array(
'ApproveType' => 'any',
'OverdueDate' => '',
'ApproveMinPercent' => '50',
'ApproveWaitForAll' => 'N',
'Name' => GetMessage("BP_V1ST_TASK_T"),
'Description' => GetMessage("BP_V1ST_TASK_DESC"),
'Parameters' => '',
'Users' => Array('Template', 'Voters'),
'Title' => GetMessage("BP_V1ST_VNAME")
),
'Children' => Array(
Array(
'Type' => 'SequenceActivity',
'Name' => 'A25099_16832_64072_25637',
'Properties' =>
Array(
'Title' => GetMessage("BP_V1ST_S2")
),
'Children' => Array(
Array(
'Type' => 'MailActivity',
'Name' => 'A29301_24557_63118_91259',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_V1ST_MAIL_SUBJ"),
'MailText' => GetMessage("BP_V1ST_MAIL_TEXT"),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array(Array('Template', 'Voters'), 'author'),
'Title' => GetMessage("BP_V1ST_MAIL_NAME")
)
),
Array(
'Type' => 'SetStateTitleActivity',
'Name' => 'A93341_94449_30890_54582',
'Properties' =>
Array(
'TargetStateTitle' => GetMessage("BP_V1ST_APPR"),
'Title' => GetMessage("BP_V1ST_APPR_S")
)
),
Array(
'Type' => 'PublishDocumentActivity',
'Name' => 'A50491_99266_37083_78593',
'Properties' =>
Array(
'Title' => GetMessage("BP_V1ST_T3")
)
))
),
Array(
'Type' => 'SequenceActivity',
'Name' => 'A28091_6558_3951_66191',
'Properties' =>
Array(
'Title' => GetMessage("BP_V1ST_S2")
),
'Children' => Array(
Array(
'Type' => 'MailActivity',
'Name' => 'A7429_66097_71801_19761',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_V1ST_MAIL2_NA"),
'MailText' => GetMessage("BP_V1ST_MAIL2_NA_TEXT"),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array(Array('Template', 'Voters'), 'author'),
'Title' => GetMessage("BP_V1ST_TNA")
)
),
Array(
'Type' => 'SetStateTitleActivity',
'Name' => 'A81366_56581_39764_20787',
'Properties' =>
Array(
'TargetStateTitle' => GetMessage("BP_V1ST_STAT_NA"),
'Title' => GetMessage("BP_V1ST_STAT_NA_T")
)
))
))
))
)),
'PARAMETERS' =>
Array(
'Voters' =>
Array(
'Name' => GetMessage("BP_V1ST_PARAM1"),
'Description' => GetMessage("BP_V1ST_PARAM1_DESC"),
'Type' => 'user',
'Required' => '1',
'Multiple' => '1',
'Default' => ''
)
),
'VARIABLES' => Array(),
);
?>