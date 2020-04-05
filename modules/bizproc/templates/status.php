<?
IncludeModuleLangFile(__FILE__);

$arFields = Array(
	"AUTO_EXECUTE" => 0,
	"ACTIVE"       => "Y",
	"NAME"         => GetMessage("BPT_ST_NAME"),
	"DESCRIPTION"  => GetMessage("BPT_ST_DESC"),
	"PARAMETERS"   => Array
	(
		'Creators' => Array
		(
			'Name'        => GetMessage("BPT_ST_CREATORS"),
			'Description' => '',
			'Type'        => 'user',
			'Required'    => 1,
			'Multiple'    => 1,
			'Default'     => GetMessage("BPT_ST_AUTHOR")
		),

		'Approvers' => Array
		(
			'Name'        => GetMessage("BPT_ST_APPROVERS"),
			'Description' => '',
			'Type'        => 'user',
			'Required'    => 1,
			'Multiple'    => 1,
			'Default'     => ''
		)

	),

	"VARIABLES" => Array(),
	"TEMPLATE"  => Array
	(
		Array
		(
			'Type'       => 'StateMachineWorkflowActivity',
			'Name'       => 'Template',
			'Properties' => Array
			(
				'Title'            => GetMessage("BPT_ST_BP_NAME"),
				'InitialStateName' => 'A24311_90344_46263_93603'
			),

			'Children' => Array
			(
				Array
				(
					'Type'       => 'StateActivity',
					'Name'       => 'A24311_90344_46263_93603',
					'Properties' => Array
					(
						'Permission' => Array
						(
							'read' => Array
							(
								Array('Template', 'Creators'),
								'author'
							),

							'write' => Array
							(
								Array('Template', 'Creators'),
								'author'
							)

						),
						'Title'      => GetMessage("BPT_ST_ST_DRAFT")
					),
					'Children'   => Array
					(
						Array
						(
							'Type'       => 'EventDrivenActivity',
							'Name'       => 'A60358_33256_48109_7232',
							'Properties' => Array
							(
								'Title' => GetMessage("BPT_ST_F")
							),

							'Children' => Array
							(
								Array
								(
									'Type'       => 'HandleExternalEventActivity',
									'Name'       => 'A51140_77423_9962_89571',
									'Properties' => Array
									(
										'Permission' => Array('author'),
										'Title'      => GetMessage("BPT_ST_CMD_APPR")
									)
								),

								Array
								(
									'Type'       => 'SetStateActivity',
									'Name'       => '6415d1f824b3c901092dca75de4f63ff',
									'Properties' => Array
									(
										'TargetStateName' => 'A21096_75379_71462_5314',
										'Title'           => GetMessage("BPT_ST_SETSTATE")
									)

								)

							)

						),

						Array
						(
							'Type'       => 'EventDrivenActivity',
							'Name'       => 'A29936_73709_64127_66887',
							'Properties' => Array
							(
								'Title' => GetMessage("BPT_ST_F")
							),

							'Children' => Array
							(
								Array
								(
									'Type'       => 'HandleExternalEventActivity',
									'Name'       => 'A94196_7496_26093_25274',
									'Properties' => Array
									(
										'Permission' => Array
										(
											1
										),

										'Title' => GetMessage("BPT_ST_CMD_PUBLISH")
									)

								),

								Array
								(
									'Type'       => 'SetStateActivity',
									'Name'       => 'fe7994c3ccaf844dd7931cb0f6dea9b1',
									'Properties' => Array
									(
										'TargetStateName' => 'A63574_17349_63919_63247',
										'Title'           => GetMessage("BPT_ST_SETSTATE")
									)

								)

							)

						)

					)

				),

				Array
				(
					'Type'       => 'StateActivity',
					'Name'       => 'A21096_75379_71462_5314',
					'Properties' => Array
					(
						'Permission' => Array
						(
							'read' => Array
							(
								Array
								(
									'Template',
									'Approvers'
								),

								Array
								(
									'Template',
									'Creators'
								),

								'author',
								1
							),

							'write' => Array
							(
								Array
								(
									'Template',
									'Approvers'
								),

								1
							)

						),

						'Title' => GetMessage("BPT_ST_CMD_APP")
					),

					'Children' => Array
					(
						Array
						(
							'Type'       => 'StateInitializationActivity',
							'Name'       => 'A21398_19376_97560_43971',
							'Properties' => Array
							(
								'Title' => GetMessage("BPT_ST_INIT"),
							),

							'Children' => Array
							(
								Array
								(
									'Type'       => 'MailActivity',
									'Name'       => 'A43038_42133_40786_25817',
									'Properties' => Array
									(
										'MailSubject'       => GetMessage("BPT_ST_SUBJECT"),
										'MailText'          => CBPDocument::_ReplaceTaskURL(GetMessage("BPT_ST_TEXT"), $documentType),
										'MailMessageType'   => 'plain',
										'MailCharset'       => LANG_CHARSET,
										'MailUserFrom'      => '',
										'MailUserFromArray' => Array
										(
											'user_1'
										),

										'MailUserTo'      => '',
										'MailUserToArray' => Array
										(
											'Template',
											'Approvers'
										),

										'Title' => GetMessage("BPT_ST_MAIL_TITLE")
									)

								),

								Array
								(
									'Type'       => 'ApproveActivity',
									'Name'       => 'A67683_71359_97848_39571',
									'Properties' => Array
									(
										'ApproveType' => 'any',
										'OverdueDate' => '',
										'Name'        => GetMessage("BPT_ST_APPROVE_NAME"),
										'Description' => GetMessage("BPT_ST_APPROVE_DESC"),
										'Parameters'  => '',
										'Users'       => Array
										(
											'Template',
											'Approvers'
										),

										'Title' => GetMessage("BPT_ST_APPROVE_TITLE")
									),

									'Children' => Array
									(
										Array
										(
											'Type'       => 'SequenceActivity',
											'Name'       => 'A59613_65289_48965_36944',
											'Properties' => Array
											(
												'Title' => GetMessage("BPT_ST_SEQ"),
											),

											'Children' => Array
											(
												Array
												(
													'Type'       => 'SetStateActivity',
													'Name'       => 'A55952_79788_55492_180',
													'Properties' => Array
													(
														'TargetStateName' => 'A63574_17349_63919_63247',
														'Title'           => GetMessage("BPT_ST_SET_PUB")
													)

												)

											)

										),

										Array
										(
											'Type'       => 'SequenceActivity',
											'Name'       => 'A33350_1503_71725_10015',
											'Properties' => Array
											(
												'Title' => GetMessage("BPT_ST_SEQ")
											),

											'Children' => Array
											(
												Array
												(
													'Type'       => 'SetStateActivity',
													'Name'       => 'A50289_31807_90287_91538',
													'Properties' => Array
													(
														'TargetStateName' => 'A24311_90344_46263_93603',
														'Title'           => GetMessage("BPT_ST_SET_DRAFT")
													)

												)

											)

										)

									)

								)

							)

						)

					)

				),

				Array
				(
					'Type'       => 'StateActivity',
					'Name'       => 'A63574_17349_63919_63247',
					'Properties' => Array
					(
						'Permission' => Array
						(
							'read' => Array
							(
								Array
								(
									'Template',
									'Creators'
								),

								'author',
								1
							),

							'write' => Array
							(
								1
							)

						),

						'Title' => GetMessage("BPT_ST_TP")
					),

					'Children' => Array
					(
						Array
						(
							'Type'       => 'StateInitializationActivity',
							'Name'       => 'A54581_85798_93933_67509',
							'Properties' => Array
							(
								'Title' => GetMessage('BPT_ST_INS')
							),

							'Children' => Array
							(
								Array
								(
									'Type'       => 'SaveHistoryActivity',
									'Name'       => 'A25642_16907_65069_1784',
									'Properties' => Array
									(
										'Title'  => GetMessage('BPT_ST_SAVEH'),
										'UserId' => '{=Document:MODIFIED_BY}'
									)

								),

								Array
								(
									'Type'       => 'PublishDocumentActivity',
									'Name'       => 'A95261_51340_58180_66962',
									'Properties' => Array
									(
										'Title' => GetMessage('BPT_ST_PUBDC')
									)

								)

							)

						)

					)

				)

			)

		)

	)
);
?>