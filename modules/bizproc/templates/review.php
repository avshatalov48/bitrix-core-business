<?php

IncludeModuleLangFile(__FILE__);

$arFields = Array(
	'AUTO_EXECUTE' => '0',
	'ACTIVE' => 'Y',
	'NAME' => GetMessage("BP_REVW_TITLE_MSGVER_1"),
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
						'MailSubject' => GetMessage("BP_REVW_TASK_MSGVER_1"),
						'MailText' => CBPDocument::_ReplaceTaskURL(GetMessage("BP_REVW_TASK_DESC_MSGVER_1"), $documentType),
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
							'Name' => GetMessage("BP_REVW_REVIEW_MSGVER_1"),
							'Description' => GetMessage("BP_REVW_REVIEW_DESC_MSGVER_1"),
						'Parameters' => '',
						'Users' => Array('Template', 'Voters'),
						'Title' => GetMessage("BP_REVW_TITLE_MSGVER_1")
				)
			),
			Array(
				'Type' => 'MailActivity',
				'Name' => 'A19784_81717_13797_20029',
				'Properties' =>
					Array(
					'MailSubject' => GetMessage("BP_REVW_MAIL_SUBJ_MSGVER_1"),
					'MailText' => GetMessage("BP_REVW_MAIL_TEXT_MSGVER_1"),
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
