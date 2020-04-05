<?
IncludeModuleLangFile(__FILE__);

$arFields = Array(
	'AUTO_EXECUTE' => '0',
	'ACTIVE' => 'Y',
	'NAME' => GetMessage("BP_REVW_TITLE"),
	'DESCRIPTION' => GetMessage("BP_REVW_DESC"),
	'TEMPLATE' => Array(
		Array(
			'Type' => 'SequentialWorkflowActivity',
			'Name' => 'Template',
			'Properties' =>
				Array(
					'Title' => GetMessage("BP_REVW_T"),
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
					'Name' => 'A56338_78317_67588_59492',
					'Properties' =>
						Array(
						'MailSubject' => GetMessage("BP_REVW_TASK"),
						'MailText' => CBPDocument::_ReplaceTaskURL(GetMessage("BP_REVW_TASK_DESC"), $documentType),
						'MailMessageType' => 'plain',
						'MailCharset' => LANG_CHARSET,
						'MailUserFrom' => '',
						'MailUserFromArray' => Array('user_1'),
						'MailUserTo' => '',
						'MailUserToArray' => Array('Template', 'Voters'),
						'Title' => GetMessage("BP_REVW_MAIL")
					)
				),
				Array(
					'Type' => 'ReviewActivity',
					'Name' => 'A69090_59491_29160_24923',
					'Properties' =>
						Array(
							'OverdueDate' => '',
							'Name' => GetMessage("BP_REVW_REVIEW"),
							'Description' => GetMessage("BP_REVW_REVIEW_DESC"),
						'Parameters' => '',
						'Users' => Array('Template', 'Voters'),
						'Title' => GetMessage("BP_REVW_TITLE")
				)
			),
			Array(
				'Type' => 'MailActivity',
				'Name' => 'A19784_81717_13797_20029',
				'Properties' =>
					Array(
					'MailSubject' => GetMessage("BP_REVW_MAIL_SUBJ"),
					'MailText' => GetMessage("BP_REVW_MAIL_TEXT"),
					'MailMessageType' => 'plain',
					'MailCharset' => LANG_CHARSET,
					'MailUserFrom' => '',
					'MailUserFromArray' => Array('user_1'),
					'MailUserTo' => '',
					'MailUserToArray' => Array(Array('Template', 'Voters'), 'author'),
					'Title' => GetMessage("BP_REVW_MAIL")
					)
			)
		)
		)
	),
	'PARAMETERS' =>
		Array(
		'Voters' =>
		Array(
		'Name' => GetMessage("BP_REVW_PARAM1"),
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