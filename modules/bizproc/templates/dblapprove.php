<?
IncludeModuleLangFile(__FILE__);

$arFields = Array(
'AUTO_EXECUTE' => '0',
'ACTIVE' => 'Y',
'NAME' => GetMessage("BP_DBLA_NAME"),
'DESCRIPTION' => GetMessage("BP_DBLA_DESC"),
'TEMPLATE' => Array(
Array(
'Type' => 'SequentialWorkflowActivity',
'Name' => 'Template',
'Properties' =>
Array(
'Title' => GetMessage("BP_DBLA_T"),
'Permission' => Array(
	'read' => Array(
		Array('Template', 'Voters1'),
		Array('Template', 'Voters2'),
		'author'
	),
),
),
'Children' => Array(
Array(
'Type' => 'MailActivity',
'Name' => 'A28652_8343_148_31493',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_DBLA_TASK"),
'MailText' => CBPDocument::_ReplaceTaskURL(GetMessage("BP_DBLA_TASK_DESC"), $documentType),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array('Template', 'Voters1'),
'Title' => GetMessage("BP_DBLA_M")
)
),
Array(
'Type' => 'ApproveActivity',
'Name' => 'ApproveActivity1',
'Properties' =>
Array(
'ApproveType' => 'all',
'OverdueDate' => '',
'ApproveMinPercent' => '50',
'ApproveWaitForAll' => 'N',
'Name' => GetMessage("BP_DBLA_APPROVE"),
'Description' => GetMessage("BP_DBLA_APPROVE_TEXT"),
'Parameters' => '',
'Users' => Array('Template', 'Voters1'),
'Title' => GetMessage("BP_DBLA_APPROVE_TITLR")
),
'Children' => Array(
Array(
'Type' => 'SequenceActivity',
'Name' => 'A17547_32223_79545_47398',
'Properties' =>
Array(
'Title' => GetMessage("BP_DBLA_S_1")
),
'Children' => Array(
Array(
'Type' => 'MailActivity',
'Name' => 'A27867_13545_12971_17663',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_DBLA_MAIL_SUBJ"),
'MailText' => GetMessage("BP_DBLA_MAIL_TEXT"),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array(Array('Template', 'Voters1'), 'author'),
'Title' => GetMessage("BP_DBLA_M")
)
),
Array(
'Type' => 'MailActivity',
'Name' => 'A18214_65247_45761_70900',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_DBLA_MAIL2_SUBJ"),
'MailText' => CBPDocument::_ReplaceTaskURL(GetMessage("BP_DBLA_MAIL2_TEXT"), $documentType),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array('Template', 'Voters2'),
'Title' => GetMessage("BP_DBLA_M")
)
),
Array(
'Type' => 'ApproveActivity',
'Name' => 'ApproveActivity2',
'Properties' =>
Array(
'ApproveType' => 'vote',
'OverdueDate' => '',
'ApproveMinPercent' => '50',
'ApproveWaitForAll' => 'N',
'Name' => GetMessage("BP_DBLA_APPROVE2"),
'Description' => GetMessage("BP_DBLA_APPROVE2_TEXT"),
'Parameters' => '',
'Users' => Array('Template', 'Voters2'),
'Title' => GetMessage("BP_DBLA_APPROVE2_TITLE")
),
'Children' => Array(
Array(
'Type' => 'SequenceActivity',
'Name' => 'A66252_54392_98992_85416',
'Properties' =>
Array(
'Title' => GetMessage("BP_DBLA_S_1")
),
'Children' => Array(
Array(
'Type' => 'MailActivity',
'Name' => 'A53284_91445_14224_61949',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_DBLA_MAIL3_SUBJ"),
'MailText' => GetMessage("BP_DBLA_MAIL3_TEXT"),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array(Array('Template', 'Voters2'), 'author'),
'Title' => GetMessage("BP_DBLA_M")
)
),
Array(
'Type' => 'SetStateTitleActivity',
'Name' => 'A7968_41299_59793_3782',
'Properties' =>
Array(
'TargetStateTitle' => GetMessage("BP_DBLA_APP"),
'Title' => GetMessage("BP_DBLA_APP_S")
)
),
Array(
'Type' => 'PublishDocumentActivity',
'Name' => 'A67943_28837_6285_74924',
'Properties' =>
Array(
'Title' => GetMessage("BP_DBLA_PUB_TITLE")
)
))
),
Array(
'Type' => 'SequenceActivity',
'Name' => 'A12994_12953_66343_68057',
'Properties' =>
Array(
'Title' => GetMessage("BP_DBLA_S_1")
),
'Children' => Array(
Array(
'Type' => 'MailActivity',
'Name' => 'A91433_23054_53017_48385',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_DBLA_NAPP"),
'MailText' => GetMessage("BP_DBLA_NAPP_TEXT"),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array(Array('Template', 'Voters2'), 'author'),
'Title' => GetMessage("BP_DBLA_M")
)
),
Array(
'Type' => 'SetStateTitleActivity',
'Name' => 'A52669_22760_20977_24814',
'Properties' =>
Array(
'TargetStateTitle' => GetMessage("BP_DBLA_NAPP_DRAFT"),
'Title' => GetMessage("BP_DBLA_NAPP_DRAFT_S")
)
))
))
))
),
Array(
'Type' => 'SequenceActivity',
'Name' => 'A38247_89879_46019_89829',
'Properties' =>
Array(
'Title' => GetMessage("BP_DBLA_S_1")
),
'Children' => Array(
Array(
'Type' => 'MailActivity',
'Name' => 'A48868_5097_265_85128',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_DBLA_MAIL4_SUBJ"),
'MailText' => GetMessage("BP_DBLA_MAIL4_TEXT"),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array(Array('Template', 'Voters1'), 'author'),
'Title' => GetMessage("BP_DBLA_M")
)
),
Array(
'Type' => 'SetStateTitleActivity',
'Name' => 'A11966_38248_36340_23189',
'Properties' =>
Array(
'TargetStateTitle' => GetMessage("BP_DBLA_NAPP_DRAFT"),
'Title' => GetMessage("BP_DBLA_NAPP_DRAFT_S")
)
))
))
))
)),
'PARAMETERS' =>
Array(
'Voters1' =>
Array(
'Name' => GetMessage("BP_DBLA_PARAM1"),
'Description' => '',
'Type' => 'user',
'Required' => '1',
'Multiple' => '1',
'Default' => ''
),
'Voters2' =>
Array(
'Name' => GetMessage("BP_DBLA_PARAM2"),
'Description' => '',
'Type' => 'user',
'Required' => '1',
'Multiple' => '1',
'Default' => ''
)
),
'VARIABLES' => Array(),
);
?>