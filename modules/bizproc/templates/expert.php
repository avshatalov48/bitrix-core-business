<?
IncludeModuleLangFile(__FILE__);

$arFields = Array(
'AUTO_EXECUTE' => '0',
'ACTIVE' => 'Y',
'NAME' => GetMessage("BP_EXPR_NAME"),
'DESCRIPTION' => GetMessage("BP_EXPR_DESC"),
'TEMPLATE' => Array(
Array(
'Type' => 'SequentialWorkflowActivity',
'Name' => 'Template',
'Properties' =>
Array(
'Title' => GetMessage("BP_EXPR_S"),
'Permission' => Array(
	'read' => Array(
		Array('Template', 'Reviewers'),
		Array('Template', 'Approvers'),
		'author'
	),
),
),
'Children' => Array(
Array(
'Type' => 'MailActivity',
'Name' => 'A94662_69963_83390_76732',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_EXPR_TASK1"),
'MailText' => CBPDocument::_ReplaceTaskURL(GetMessage("BP_EXPR_TASK1_MAIL"), $documentType),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array('Template', 'Reviewers'),
'Title' => GetMessage("BP_EXPR_M")
)
),
Array(
'Type' => 'ApproveActivity',
'Name' => 'ApproveActivity1',
'Properties' =>
Array(
'ApproveType' => 'vote',
'OverdueDate' => '',
'ApproveMinPercent' => '50',
'ApproveWaitForAll' => 'Y',
'Name' => GetMessage("BP_EXPR_APPR1"),
'Description' => GetMessage("BP_EXPR_APPR1_DESC"),
'Parameters' => '',
'Users' => Array('Template', 'Reviewers'),
'Title' => GetMessage("BP_EXPR_NAME")
),
'Children' => Array(
Array(
'Type' => 'SequenceActivity',
'Name' => 'A64611_46822_29561_87975',
'Properties' =>
Array(
'Title' => GetMessage("BP_EXPR_ST")
)
),
Array(
'Type' => 'SequenceActivity',
'Name' => 'A68833_77308_5845_73466',
'Properties' =>
Array(
'Title' => GetMessage("BP_EXPR_ST")
)
))
),
Array(
'Type' => 'MailActivity',
'Name' => 'A96367_17945_13820_95972',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_EXPR_MAIL2_SUBJ"),
'MailText' => CBPDocument::_ReplaceTaskURL(GetMessage("BP_EXPR_MAIL2_TEXT"), $documentType),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array('Template', 'Approvers'),
'Title' => GetMessage("BP_EXPR_M")
)
),
Array(
'Type' => 'ApproveActivity',
'Name' => 'ApproveActivity2',
'Properties' =>
Array(
'ApproveType' => 'all',
'OverdueDate' => '',
'ApproveMinPercent' => '50',
'ApproveWaitForAll' => 'N',
'Name' => GetMessage("BP_EXPR_APP2_TEXT"),
'Description' => GetMessage("BP_EXPR_APP2_DESC"),
'Parameters' => '',
'Users' => Array('Template', 'Approvers'),
'Title' => GetMessage("BP_EXPR_TAPP")
),
'Children' => Array(
Array(
'Type' => 'SequenceActivity',
'Name' => 'A22532_2878_32169_11537',
'Properties' =>
Array(
'Title' => GetMessage("BP_EXPR_ST")
),
'Children' => Array(
Array(
'Type' => 'MailActivity',
'Name' => 'A83133_30611_25503_24252',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_EXPR_MAIL3_SUBJ"),
'MailText' => GetMessage("BP_EXPR_MAIL3_TEXT"),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array('author', Array('Template', 'Reviewers'), Array('Template', 'Approvers')),
'Title' => GetMessage("BP_EXPR_M")
)
),
Array(
'Type' => 'SetStateTitleActivity',
'Name' => 'A74836_68856_62794_73135',
'Properties' =>
Array(
'TargetStateTitle' => GetMessage("BP_EXPR_ST3_T"),
'Title' => GetMessage("BP_EXPR_ST3_TIT")
)
),
Array(
'Type' => 'PublishDocumentActivity',
'Name' => 'A18564_33518_5981_62692',
'Properties' =>
Array(
'Title' => GetMessage("BP_EXPR_PUB")
)
))
),
Array(
'Type' => 'SequenceActivity',
'Name' => 'A51489_50805_51908_16085',
'Properties' =>
Array(
'Title' => GetMessage("BP_EXPR_ST")
),
'Children' => Array(
Array(
'Type' => 'MailActivity',
'Name' => 'A79990_95953_26064_13430',
'Properties' =>
Array(
'MailSubject' => GetMessage("BP_EXPR_MAIL4_SUBJ"),
'MailText' => GetMessage("BP_EXPR_MAIL4_TEXT"),
'MailMessageType' => 'plain',
'MailCharset' => LANG_CHARSET,
'MailUserFrom' => '',
'MailUserFromArray' => Array('user_1'),
'MailUserTo' => '',
'MailUserToArray' => Array('author', Array('Template', 'Reviewers'), Array('Template', 'Approvers')),
'Title' => GetMessage("BP_EXPR_M")
)
),
Array(
'Type' => 'SetStateTitleActivity',
'Name' => 'A92731_73187_56429_18444',
'Properties' =>
Array(
'TargetStateTitle' => GetMessage("BP_EXPR_NA"),
'Title' => GetMessage("BP_EXPR_NA_ST")
)
))
))
))
)),
'PARAMETERS' =>
Array(
'Reviewers' =>
Array(
'Name' => GetMessage("BP_EXPR_PARAM2"),
'Description' => GetMessage("BP_EXPR_PARAM2_DESC"),
'Type' => 'user',
'Required' => '1',
'Multiple' => '1',
'Default' => ''
),
'Approvers' =>
Array(
'Name' => GetMessage("BP_EXPR_PARAM1"),
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