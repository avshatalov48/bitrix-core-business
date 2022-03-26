<?
IncludeModuleLangFile(__FILE__);

$arFields = Array(
	"AUTO_EXECUTE" => 0,
	'ACTIVE'       => 'Y',
	"NAME"         => GetMessage("BPT_SM_NAME"),
	"DESCRIPTION"  => GetMessage("BPT_SM_DESC"),
	"TEMPLATE"     => Array
	(
		Array
		(
			"Type"       => "SequentialWorkflowActivity",
			"Name"       => "Template",
			"Properties" => Array
			(
				"Title"      => GetMessage("BPT_SM_TITLE1"),
				'Permission' => Array(
					'read' => Array(
						Array('Template', 'Voters'),
						'author'
					),
				),
			),
			"Children"   => Array
			(
				Array
				(
					"Type"       => "MailActivity",
					"Name"       => "A55107_6725_78774_36295",
					"Properties" => Array
					(
						"MailSubject"       => GetMessage("BPT_SM_TASK1_TITLE"),
						"MailText"          => CBPDocument::_ReplaceTaskURL(GetMessage("BPT_SM_TASK1_TEXT"), $documentType),
						"MailMessageType"   => 'plain',
						"MailCharset"       => LANG_CHARSET,
						"MailUserFrom"      => '',
						"MailUserFromArray" => Array
						(
							'user_1'
						),

						"MailUserTo"      => '',
						"MailUserToArray" => Array
						(
							'Template', 'Voters'
						),

						"Title" => GetMessage("BPT_SM_ACT_TITLE")
					)

				),

				Array
				(
					"Type"       => 'ApproveActivity',
					"Name"       => 'ApproveActivity1',
					"Properties" => Array
					(
						"ApproveType"       => 'vote',
						"OverdueDate"       => '',
						"ApproveMinPercent" => '50',
						"ApproveWaitForAll" => 'N',
						"Name"              => GetMessage("BPT_SM_APPROVE_NAME"),
						"Description"       => GetMessage("BPT_SM_APPROVE_DESC"),
						"Parameters"        => '',
						"Users"             => Array
						(
							'Template',
							'Voters',
						),

						"Title" => GetMessage("BPT_SM_APPROVE_TITLE"),
					),

					"Children" => Array
					(
						Array
						(
							"Type"       => 'SequenceActivity',
							"Name"       => 'A66645_60922_64384_78185',
							"Properties" => Array
							(
								"Title" => GetMessage("BPT_SM_ACT_NAME_1"),
							),

							"Children" => Array
							(
								Array
								(
									"Type"       => 'MailActivity',
									"Name"       => 'A52783_8897_80513_17412',
									"Properties" => Array
									(
										"MailSubject"       => GetMessage("BPT_SM_MAIL1_SUBJ"),
										"MailText"          => GetMessage("BPT_SM_MAIL1_TEXT"),
										"MailMessageType"   => 'plain',
										"MailCharset"       => LANG_CHARSET,
										"MailUserFrom"      => '',
										"MailUserFromArray" => Array
										(
											'user_1',
										),

										"MailUserTo"      => '',
										"MailUserToArray" => Array
										(
											Array
											(
												'Template',
												'Voters',
											),

											'author',
										),

										"Title" => GetMessage("BPT_SM_MAIL1_TITLE"),
									)
								),

								Array
								(
									"Type"       => 'SetStateTitleActivity',
									"Name"       => 'A50333_41630_15247_27412',
									"Properties" => Array
									(
										"TargetStateTitle" => GetMessage("BPT_SM_STATUS"),
										"Title"            => GetMessage("BPT_SM_STATUS2"),
									)

								),

								Array
								(
									"Type"       => 'PublishDocumentActivity',
									"Name"       => 'A48746_87842_51629_82911',
									"Properties" => Array
									(
										"Title" => GetMessage("BPT_SM_PUB"),
									),

								),

							)

						),

						Array
						(
							"Type"       => 'SequenceActivity',
							"Name"       => 'A54969_58395_8300_44921',
							"Properties" => Array
							(
								"Title" => GetMessage("BPT_SM_ACT_NAME_1"),
							),

							"Children" => Array
							(
								Array
								(
									"Type"       => 'MailActivity',
									"Name"       => 'A81331_27726_32679_75654',
									"Properties" => Array
									(
										"MailSubject"       => GetMessage("BPT_SM_MAIL2_SUBJ"),
										"MailText"          => GetMessage("BPT_SM_MAIL2_TEXT"),
										"MailMessageType"   => 'plain',
										"MailCharset"       => LANG_CHARSET,
										"MailUserFrom"      => '',
										"MailUserFromArray" => Array
										(
											'user_1',
										),

										"MailUserTo"      => '',
										"MailUserToArray" => Array
										(
											Array
											(
												'Template',
												'Voters'
											),

											'author',
										),

										"Title" => GetMessage("BPT_SM_MAIL2_TITLE"),
									)
								),

								Array
								(
									"Type"       => 'SetStateTitleActivity',
									"Name"       => 'A23308_83481_97609_4879',
									"Properties" => Array
									(
										"TargetStateTitle" => GetMessage("BPT_SM_MAIL2_STATUS"),
										"Title"            => GetMessage("BPT_SM_MAIL2_STATUS2"),
									)

								)

							)

						)

					)

				)

			)

		)

	),

	"PARAMETERS" => Array
	(
		"Voters" => Array
		(
			"Name"        => GetMessage("BPT_SM_PARAM_NAME"),
			"Description" => GetMessage("BPT_SM_PARAM_DESC"),
			"Type"        => 'user',
			"Required"    => 1,
			"Multiple"    => 1,
			"Default"     => ''
		)
	),

	"VARIABLES" => Array
	()
);
?>