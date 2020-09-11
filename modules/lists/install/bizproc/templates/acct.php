<?
IncludeModuleLangFile(__FILE__);

$acctVersion = CIBlockDocument::GetVersion();

$ar = array(
	array(
		'SORT' => 10,
		'NAME' => GetMessage("LIBTA_NAME"),
		'IS_REQUIRED' => 'N',
		'MULTIPLE' => 'N',
		'TYPE' => 'NAME',
	),
	array(
		'SORT' => 20,
		'CODE'=> 'TYPE',
		'NAME' => GetMessage("LIBTA_TYPE"),
		'IS_REQUIRED' => 'Y',
		'MULTIPLE' => 'N',
		"FILTRABLE" => "Y",
		'TYPE' => 'L',
		'LIST' => array(
			'n0' => array('SORT' => 10, 'VALUE' => GetMessage("LIBTA_TYPE_ADV")),
			'n1' => array('SORT' => 20, 'VALUE' => GetMessage("LIBTA_TYPE_EX")),
			'n2' => array('SORT' => 30, 'VALUE' => GetMessage("LIBTA_TYPE_C")),
			'n3' => array('SORT' => 40, 'VALUE' => GetMessage("LIBTA_TYPE_D")),
		),
	),
	array(
		'SORT' => 30,
		'NAME' => GetMessage("LIBTA_CREATED_BY"),
		'IS_REQUIRED' => 'N',
		'MULTIPLE' => 'N',
		'TYPE' => 'CREATED_BY',
	),
	array(
		'SORT' => 40,
		'NAME' => GetMessage("LIBTA_DATE_CREATE"),
		'IS_REQUIRED' => 'N',
		'MULTIPLE' => 'N',
		'TYPE' => 'DATE_CREATE',
	),
	array(
		'SORT' => 50,
		'CODE'=> 'FILE',
		'NAME' => GetMessage("LIBTA_FILE"),
		'IS_REQUIRED' => 'Y',
		"FILTRABLE" => "N",
		'MULTIPLE' => 'N',
		'TYPE' => 'F',
	),
	array(
		'SORT' => 60,
		'CODE'=> 'NUM_DATE',
		'NAME' => GetMessage("LIBTA_NUM_DATE"),
		'IS_REQUIRED' => 'N',
		"FILTRABLE" => "N",
		'MULTIPLE' => 'N',
		'TYPE' => 'S',
	),
	array(
		'SORT' => 70,
		'CODE'=> 'SUM',
		'NAME' => GetMessage("LIBTA_SUM"),
		'IS_REQUIRED' => 'Y',
		'MULTIPLE' => 'N',
		"FILTRABLE" => "N",
		'TYPE' => 'N',
	),
	array(
		'SORT' => 80,
		'CODE'=> 'PAID',
		'NAME' => GetMessage("LIBTA_PAID"),
		'IS_REQUIRED' => 'N',
		'MULTIPLE' => 'N',
		"FILTRABLE" => "Y",
		'TYPE' => 'L',
		'LIST' => array(
			'n0' => array('SORT' => 10, 'VALUE' => GetMessage("LIBTA_PAID_NO")),
			'n1' => array('SORT' => 20, 'VALUE' => GetMessage("LIBTA_PAID_YES")),
		),
	),
	array(
		'SORT' => 90,
		'CODE'=> 'BDT',
		'NAME' => GetMessage("LIBTA_BDT"),
		'IS_REQUIRED' => 'Y',
		"FILTRABLE" => "N",
		'MULTIPLE' => 'N',
		'TYPE' => 'S',
	),
	array(
		'SORT' => 100,
		'CODE'=> 'DATE_PAY',
		'NAME' => GetMessage("LIBTA_DATE_PAY"),
		'IS_REQUIRED' => 'N',
		"FILTRABLE" => "Y",
		'MULTIPLE' => 'N',
		'TYPE' => 'S:DateTime',
	),
	array(
		'SORT' => 110,
		'CODE'=> 'NUM_PAY',
		'NAME' => GetMessage("LIBTA_NUM_PP"),
		'IS_REQUIRED' => 'N',
		"FILTRABLE" => "N",
		'MULTIPLE' => 'N',
		'TYPE' => 'S',
	),
	array(
		'SORT' => 120,
		'CODE'=> 'DOCS',
		'NAME' => GetMessage("LIBTA_DOCS"),
		'IS_REQUIRED' => 'N',
		'MULTIPLE' => 'N',
		"FILTRABLE" => "Y",
		'TYPE' => 'L',
		'LIST' => array(
			'n0' => array('SORT' => 20, 'VALUE' => GetMessage("LIBTA_DOCS_YES")),
			'n1' => array('SORT' => 10, 'VALUE' => GetMessage("LIBTA_DOCS_NO")),
		),
	),
	array(
		'SORT' => 130,
		'CODE'=> 'IS_APPROVED',
		'NAME' => GetMessage("LIBTA_APPROVED"),
		'IS_REQUIRED' => 'N',
		'MULTIPLE' => 'N',
		"FILTRABLE" => "Y",
		'TYPE' => 'L',
		'LIST' => array(
			'n0' => array('VALUE' => GetMessage("LIBTA_APPROVED_R")),
			'n1' => array('VALUE' => GetMessage("LIBTA_APPROVED_N")),
			'n2' => array('VALUE' => GetMessage("LIBTA_APPROVED_Y")),
		),
	),
);

$iblockId = intval(mb_substr($documentType[2], mb_strlen("iblock_")));

$obList = new CList($iblockId);
foreach ($ar as $value)
	$obList->AddField($value);

$GLOBALS["CACHE_MANAGER"]->ClearByTag("lists_list_".$iblockId);

$arIS_APPROVED = array();
$db = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC", "VALUE"=>"ASC"), array("IBLOCK_ID" => $iblockId, "PROPERTY_ID" => "IS_APPROVED"));
while ($ar = $db->Fetch())
	$arIS_APPROVED[$ar["VALUE"]] = ($acctVersion == 2) ? $ar["XML_ID"] : $ar["ID"];

$arDOCS = array();
$db = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC", "VALUE"=>"ASC"), array("IBLOCK_ID" => $iblockId, "PROPERTY_ID" => "DOCS"));
while ($ar = $db->Fetch())
	$arDOCS[$ar["VALUE"]] = ($acctVersion == 2) ? $ar["XML_ID"] : $ar["ID"];

$arPAID = array();
$db = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC", "VALUE"=>"ASC"), array("IBLOCK_ID" => $iblockId, "PROPERTY_ID" => "PAID"));
while ($ar = $db->Fetch())
	$arPAID[$ar["VALUE"]] = ($acctVersion == 2) ? $ar["XML_ID"] : $ar["ID"];

$arPerms = array(
	"R" => CIBlockRights::LetterToTask('R'),
	"W" => CIBlockRights::LetterToTask('W'),
);



$arFields = array(
	'AUTO_EXECUTE' => '1',
	'ACTIVE' => 'Y',
	'NAME' => GetMessage("LIBTA_BP_TITLE"),
	'DESCRIPTION' => '',
	'TEMPLATE' => array(
		array(
			'Type' => 'SequentialWorkflowActivity',
			'Name' => 'Template',
			'Properties' => array(
				'Title' => GetMessage("LIBTA_T_PBP"),
				'Permission' => array(
					$arPerms['R'] => array('author', array('Variable', 'Manager')),
					$arPerms['W'] => array('author', array('Variable', 'Manager'))
				)
			),
			'Children' => array(
				array(
					'Type' => 'SetPermissionsActivity',
					'Name' => 'A70197_60940_99196_61268',
					'Properties' => array(
						'Permission' => array(
							$arPerms['R'] => array('author', array('Variable', 'Manager')),
							$arPerms['W'] => array(array('Variable', 'Manager')),
						),
						'Title' => GetMessage("LIBTA_T_SPA1")
					)
				),
				array(
					'Type' => 'PublishDocumentActivity',
					'Name' => 'A36563_74965_81599_49650',
					'Properties' => array(
						'Title' => GetMessage("LIBTA_T_PDA1")
					)
				),
				array(
					'Type' => 'SetStateTitleActivity',
					'Name' => 'A11106_90545_49031_22508',
					'Properties' => array(
						'TargetStateTitle' => GetMessage("LIBTA_STATE1"),
						'Title' => GetMessage("LIBTA_T_SSTA1")
					)
				),
				array(
					'Type' => 'SetFieldActivity',
					'Name' => 'A56400_86193_70053_33925',
					'Properties' => array(
						'FieldValue' => array(
							'PROPERTY_PAID' => $arPAID[GetMessage("LIBTA_PAID_NO")],
							'PROPERTY_DOCS' => $arDOCS[GetMessage("LIBTA_DOCS_NO")],
							'PROPERTY_IS_APPROVED' => $arIS_APPROVED[GetMessage("LIBTA_APPROVED_N")],
						),
						'Title' => GetMessage("LIBTA_T_ASFA1")
					)
				),
				array(
					'Type' => 'SetVariableActivity',
					'Name' => 'A1616_10288_22301_25856',
					'Properties' => array(
						'VariableValue' => array(
							'Approver' => array('Document', 'CREATED_BY')
						),
						'Title' => GetMessage("LIBTA_T_SVWA1")
					)
				),
				array(
					'Type' => 'WhileActivity',
					'Name' => 'A99014_21676_67321_91161',
					'Properties' => array(
						'Title' => GetMessage("LIBTA_T_WHILEA1"),
						'fieldcondition' => array(array('PROPERTY_IS_APPROVED', '=', $arIS_APPROVED[GetMessage("LIBTA_APPROVED_N")]))
					),
					'Children' => array(
						array(
							'Type' => 'SequenceActivity',
							'Name' => 'A44262_22192_92537_62808',
							'Properties' => array(
								'Title' => GetMessage("LIBTA_T_SA0")
							),
							'Children' => array(
								array(
									'Type' => 'IfElseActivity',
									'Name' => 'A27506_14464_63914_70168',
									'Properties' => array(
										'Title' => GetMessage("LIBTA_T_IFELSEA1")
									),
									'Children' => array(
										array(
											'Type' => 'IfElseBranchActivity',
											'Name' => 'A73194_18815_56133_13880',
											'Properties' => array(
												'Title' => GetMessage("LIBTA_T_IFELSEBA1"),
												'propertyvariablecondition' => array(array('Approver', 'in', array('Variable', 'ManagerApprover')))
											),
											'Children' => array(
												array(
													'Type' => 'SetFieldActivity',
													'Name' => 'A42632_30934_21795_54480',
													'Properties' => array(
														'FieldValue' => array(
															'PROPERTY_IS_APPROVED' => $arIS_APPROVED[GetMessage("LIBTA_APPROVED_Y")]
														),
														'Title' => GetMessage("LIBTA_T_ASFA2")
													)
												)
											)
										),
										array(
											'Type' => 'IfElseBranchActivity',
											'Name' => 'A99668_49419_49977_64690',
											'Properties' => array(
												'Title' => GetMessage("LIBTA_T_IFELSEBA2"),
												'truecondition' => '1'
											),
											'Children' => array(
												array(
													'Type' => 'GetUserActivity',
													'Name' => 'A62351_13892_65034_70711',
													'Properties' => array(
														'UserType' => 'boss',
														'MaxLevel' => '1',
														'UserParameter' => array(array('Variable', 'Approver')),
														'ReserveUserParameter' => array(array('Variable', 'Manager')),
														'Title' => GetMessage("LIBTA_T_GUAX1")
													)
												),
												array(
													'Type' => 'SetVariableActivity',
													'Name' => 'A19319_19284_94376_40816',
													'Properties' => array(
														'VariableValue' => array(
															'Approver' => array('A62351_13892_65034_70711', 'GetUser')
														),
														'Title' => GetMessage("LIBTA_T_SVWA2")
													)
												),
												array(
													'Type' => 'SetPermissionsActivity',
													'Name' => 'A33518_77010_95258_20619',
													'Properties' => array(
														'Permission' => array(
															$arPerms['R'] => array('author', array('Variable', 'Manager'), array('Variable', 'Approver')),
															$arPerms['W'] => array(array('Variable', 'Manager')),
														),
														'Title' => GetMessage("LIBTA_T_SPAX1")
													)
												),
												array(
													'Type' => 'SocNetMessageActivity',
													'Name' => 'A34008_30179_33603_79039',
													'Properties' => array(
														'MessageText' => GetMessage("LIBTA_SMA_MESSAGE_1"),
														'MessageUserFrom' => array('author'),
														'MessageUserTo' => array(array('Variable', 'Approver')),
														'Title' => GetMessage("LIBTA_T_SMA_MESSAGE_1")
													)
												),
												array(
													'Type' => 'MailActivity',
													'Name' => 'A99761_85585_84103_82472',
													'Properties' => array(
														'MailSubject' => GetMessage("LIBTA_XMA_MESSAGES_1"),
														'MailText' => GetMessage("LIBTA_XMA_MESSAGET_1"),
														'MailMessageType' => 'plain',
														'MailCharset' => 'windows-1251',
														'MailUserFrom' => '',
														'MailUserFromArray' => array('author'),
														'MailUserTo' => '',
														'MailUserToArray' => array(array('Variable', 'Approver')),
														'Title' => GetMessage("LIBTA_T_XMA_MESSAGES_1")
													)
												),
												array(
													'Type' => 'ApproveActivity',
													'Name' => 'A58853_60082_34258_61777',
													'Properties' => array(
														'ApproveType' => 'any',
														'OverdueDate' => '',
														'ApproveMinPercent' => '50',
														'ApproveWaitForAll' => 'N',
														'Name' => GetMessage("LIBTA_AAQN1"),
														'Description' => GetMessage("LIBTA_AAQD1"),
														'Parameters' => '',
														'StatusMessage' => '',
														'SetStatusMessage' => 'N',
														'Users' => array(array('Variable', 'Approver')),
														'TimeoutDuration' => '0',
														'Title' => GetMessage("LIBTA_T_AAQN1")
													),
													'Children' => array(
														array(
															'Type' => 'SequenceActivity',
															'Name' => 'A27147_6731_92080_88258',
															'Properties' => array(
																'Title' => GetMessage("LIBTA_T_SA0")
															),
															'Children' => array(
																array(
																	'Type' => 'SetStateTitleActivity',
																	'Name' => 'A90721_7986_9652_85837',
																	'Properties' => array(
																		'TargetStateTitle' => GetMessage("LIBTA_STATE2"),
																		'Title' => GetMessage("LIBTA_T_SSTA2")
																	)
																)
															)
														),
														array(
															'Type' => 'SequenceActivity',
															'Name' => 'A27802_76371_20200_57131',
															'Properties' => array(
																'Title' => GetMessage("LIBTA_T_SA0")
															),
															'Children' => array(
																array(
																	'Type' => 'SetStateTitleActivity',
																	'Name' => 'A46191_85419_32185_80066',
																	'Properties' => array(
																		'TargetStateTitle' => GetMessage("LIBTA_STATE3"),
																		'Title' => GetMessage("LIBTA_T_SSTA3")
																	)
																),
																array(
																	'Type' => 'SetFieldActivity',
																	'Name' => 'A77467_72171_34395_65026',
																	'Properties' => array(
																		'FieldValue' => array('PROPERTY_IS_APPROVED' => $arIS_APPROVED[GetMessage("LIBTA_APPROVED_R")]),
																		'Title' => GetMessage("LIBTA_T_ASFA3")
																	)
																)
															)
														)
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
				array(
					'Type' => 'IfElseActivity',
					'Name' => 'A21763_99388_53150_1648',
					'Properties' => array(
						'Title' => GetMessage("LIBTA_T_IFELSEA2")
					),
					'Children' => array(
						array(
							'Type' => 'IfElseBranchActivity',
							'Name' => 'A69256_74360_71431_89019',
							'Properties' => array(
								'Title' => GetMessage("LIBTA_T_IFELSEBA3"),
								'fieldcondition' => array(array('PROPERTY_IS_APPROVED', '=', $arIS_APPROVED[GetMessage("LIBTA_APPROVED_Y")]))
							),
							'Children' => array(
								array(
									'Type' => 'SocNetMessageActivity',
									'Name' => 'A23902_56289_44539_820',
									'Properties' => array(
										'MessageText' => GetMessage("LIBTA_SMA_MESSAGE_2"),
										'MessageUserFrom' => array(array('Variable', 'Approver')),
										'MessageUserTo' => array('author'),
										'Title' => GetMessage("LIBTA_T_SMA_MESSAGE_2")
									)
								),
								array(
									'Type' => 'SetPermissionsActivity',
									'Name' => 'A67388_91549_22195_11207',
									'Properties' => array(
										'Permission' => array(
											$arPerms['R'] => array(array('Variable', 'Manager'), 'author', array('Variable', 'Bookkeeper')),
											$arPerms['W'] => array(array('Variable', 'Manager')),
										),
										'Title' => GetMessage("LIBTA_T_SPAX2")
									)
								),
								array(
									'Type' => 'SocNetMessageActivity',
									'Name' => 'A47679_10274_41421_86172',
									'Properties' => array(
										'MessageText' => GetMessage("LIBTA_SMA_MESSAGE_3"),
										'MessageUserFrom' => array(array('Variable', 'Approver')),
										'MessageUserTo' => array(array('Variable', 'Bookkeeper')),
										'Title' => GetMessage("LIBTA_T_SMA_MESSAGE_3")
									)
								),
								array(
									'Type' => 'MailActivity',
									'Name' => 'A44152_75250_83855_58298',
									'Properties' => array(
										'MailSubject' => GetMessage("LIBTA_XMA_MESSAGES_2"),
										'MailText' => GetMessage("LIBTA_XMA_MESSAGET_2"),
										'MailMessageType' => 'plain',
										'MailCharset' => 'windows-1251',
										'MailUserFrom' => '',
										'MailUserFromArray' => array(array('Variable', 'Approver')),
										'MailUserTo' => '',
										'MailUserToArray' => array(array('Variable', 'Bookkeeper')),
										'Title' => GetMessage("LIBTA_T_XMA_MESSAGES_2")
									)
								),
								array(
									'Type' => 'SetStateTitleActivity',
									'Name' => 'A52322_26302_33196_87407',
									'Properties' => array(
										'TargetStateTitle' => GetMessage("LIBTA_STATE4"),
										'Title' => GetMessage("LIBTA_T_SSTA4")
									)
								),
								array(
									'Type' => 'ApproveActivity',
									'Name' => 'A11229_71564_7314_72859',
									'Properties' => array(
										'ApproveType' => 'any',
										'OverdueDate' => '',
										'ApproveMinPercent' => '50',
										'ApproveWaitForAll' => 'N',
										'Name' => GetMessage("LIBTA_AAQN2"),
										'Description' => GetMessage("LIBTA_AAQD2"),
										'Parameters' => '',
										'StatusMessage' => '',
										'SetStatusMessage' => 'N',
										'Users' => array(array('Variable', 'Bookkeeper')),
										'TimeoutDuration' => '0',
										'Title' => GetMessage("LIBTA_T_AAQN2")
									),
									'Children' => array(
										array(
											'Type' => 'SequenceActivity',
											'Name' => 'A80194_76711_19263_21676',
											'Properties' => array(
												'Title' => GetMessage("LIBTA_T_SA0")
											),
											'Children' => array(
												array(
													'Type' => 'SetVariableActivity',
													'Name' => 'A29193_47401_98150_33180',
													'Properties' => array(
														'VariableValue' => array(
															'PaymentApprover' => array('A11229_71564_7314_72859', 'LastApprover')
														),
														'Title' => GetMessage("LIBTA_T_SVWA3")
													)
												),
												array(
													'Type' => 'SetStateTitleActivity',
													'Name' => 'A25637_98522_38985_58818',
													'Properties' => array(
														'TargetStateTitle' => GetMessage("LIBTA_STATE5"),
														'Title' => GetMessage("LIBTA_T_SSTA5")
													)
												),
												array(
													'Type' => 'SocNetMessageActivity',
													'Name' => 'A60579_4147_47619_95911',
													'Properties' => array(
														'MessageText' => GetMessage("LIBTA_SMA_MESSAGE_4"),
														'MessageUserFrom' => array(array('A11229_71564_7314_72859', 'LastApprover')),
														'MessageUserTo' => array('author'),
														'Title' => GetMessage("LIBTA_T_SMA_MESSAGE_4")
													)
												),
												array(
													'Type' => 'SetPermissionsActivity',
													'Name' => 'A65918_88401_61091_54037',
													'Properties' => array(
														'Permission' => array(
															$arPerms['R'] => array('author', array('Variable', 'Manager'), array('Variable', 'BookkeeperPay')),
															$arPerms['W'] => array(array('Variable', 'Manager')),
														),
														'Title' => GetMessage("LIBTA_T_SPAX3")
													)
												),
												array(
													'Type' => 'SocNetMessageActivity',
													'Name' => 'A44719_18754_52824_82329',
													'Properties' => array(
														'MessageText' => GetMessage("LIBTA_SMA_MESSAGE_5"),
														'MessageUserFrom' => array('author'),
														'MessageUserTo' => array(array('Variable', 'BookkeeperPay')),
														'Title' => GetMessage("LIBTA_T_SMA_MESSAGE_5")
													)
												),
												array(
													'Type' => 'MailActivity',
													'Name' => 'A36817_80301_35858_77783',
													'Properties' => array(
														'MailSubject' => GetMessage("LIBTA_XMA_MESSAGES_3"),
														'MailText' => GetMessage("LIBTA_XMA_MESSAGET_3"),
														'MailMessageType' => 'plain',
														'MailCharset' => 'windows-1251',
														'MailUserFrom' => '',
														'MailUserFromArray' => array('author'),
														'MailUserTo' => '',
														'MailUserToArray' => array(array('Variable', 'BookkeeperPay')),
														'Title' => GetMessage("LIBTA_T_XMA_MESSAGES_3")
													)
												),
												array(
													'Type' => 'SetStateTitleActivity',
													'Name' => 'A89468_1651_50411_32935',
													'Properties' => array(
														'TargetStateTitle' => GetMessage("LIBTA_STATE6"),
														'Title' => GetMessage("LIBTA_T_SSTA6")
													)
												),
												array(
													'Type' => 'RequestInformationActivity',
													'Name' => 'A73644_72626_36326_19757',
													'Properties' => array(
														'OverdueDate' => '',
														'Name' => GetMessage("LIBTA_RIA10_NAME"),
														'Description' => GetMessage("LIBTA_RIA10_DESCR"),
														'Parameters' => '',
														'RequestedInformation' => array(
															array(
																'Name' => 'PayDate',
																'Title' => GetMessage("LIBTA_RIA10_R1"),
																'Type' => 'date',
																'Required' => '1',
																'Multiple' => '0',
																'Default' => ''
															),
															array(
																'Name' => 'PayNum',
																'Title' => GetMessage("LIBTA_RIA10_R2"),
																'Type' => 'string',
																'Required' => '1',
																'Multiple' => '0',
																'Default' => ''
															)
														),
														'Users' => array(array('Variable', 'BookkeeperPay')),
														'Title' =>  GetMessage("LIBTA_T_RIA10")
													)
												),
												array(
													'Type' => 'SetFieldActivity',
													'Name' => 'A44711_55976_47536_95701',
													'Properties' => array(
														'FieldValue' => array(
															'PROPERTY_PAID' => $arPAID[GetMessage("LIBTA_PAID_YES")],
															'PROPERTY_DATE_PAY' => '{=Variable:PayDate}',
															'PROPERTY_NUM_PAY' => '{=Variable:PayNum}'
														),
														'Title' => GetMessage("LIBTA_T_ASFA4")
													)
												),
												array(
													'Type' => 'SetStateTitleActivity',
													'Name' => 'A45199_90932_25287_50349',
													'Properties' => array(
														'TargetStateTitle' => GetMessage("LIBTA_STATE7"),
														'Title' => GetMessage("LIBTA_T_SSTA7")
													)
												),
												array(
													'Type' => 'SocNetMessageActivity',
													'Name' => 'A76367_15525_51581_89180',
													'Properties' => array(
														'MessageText' => GetMessage("LIBTA_SMA_MESSAGE_6"),
														'MessageUserFrom' => array(array('Variable', 'BookkeeperPay')),
														'MessageUserTo' => array('author'),
														'Title' => GetMessage("LIBTA_T_SMA_MESSAGE_6")
													)
												),
												array(
													'Type' => 'ReviewActivity',
													'Name' => 'A46159_82367_93285_47305',
													'Properties' => array(
														'ApproveType' => 'all',
														'OverdueDate' => '',
														'Name' => GetMessage("LIBTA_RRA15_NAME"),
														'Description' => GetMessage("LIBTA_RRA15_DESCR"),
														'Parameters' => '',
														'StatusMessage' => GetMessage("LIBTA_RRA15_SM"),
														'SetStatusMessage' => 'Y',
														'TaskButtonMessage' => GetMessage("LIBTA_RRA15_TASKBUTTON"),
														'Users' => array('author'),
														'TimeoutDuration' => '0',
														'Title' => GetMessage("LIBTA_T_RRA15")
													)
												),
												array(
													'Type' => 'SetPermissionsActivity',
													'Name' => 'A43537_96143_38641_40872',
													'Properties' => array(
														'Permission' => array(
															$arPerms['R'] => array('author', array('Variable', 'Manager'), array('Variable', 'BookkeeperDoc')),
															$arPerms['W'] => array(array('Variable', 'Manager')),
														),
														'Title' => GetMessage("LIBTA_T_SPAX4")
													)
												),
												array(
													'Type' => 'SocNetMessageActivity',
													'Name' => 'A37545_56955_2373_59563',
													'Properties' => array(
														'MessageText' => GetMessage("LIBTA_SMA_MESSAGE_7"),
														'MessageUserFrom' => array('author'),
														'MessageUserTo' => array(array('Variable', 'BookkeeperDoc')),
														'Title' => GetMessage("LIBTA_T_SMA_MESSAGE_7")
													)
												),
												array(
													'Type' => 'ReviewActivity',
													'Name' => 'A43748_28266_19411_20456',
													'Properties' => array(
														'ApproveType' => 'any',
														'OverdueDate' => '',
														'Name' => GetMessage("LIBTA_RRA17_NAME"),
														'Description' => GetMessage("LIBTA_RRA17_DESCR"),
														'Parameters' => '',
														'StatusMessage' => '',
														'SetStatusMessage' => 'N',
														'TaskButtonMessage' => GetMessage("LIBTA_RRA17_BUTTON"),
														'Users' => array(array('Variable', 'BookkeeperDoc')),
														'TimeoutDuration' => '0',
														'Title' => GetMessage("LIBTA_T_RRA17_NAME")
													)
												),
												array(
													'Type' => 'SetFieldActivity',
													'Name' => 'A87351_71655_99755_15385',
													'Properties' => array(
														'FieldValue' => array(
															'PROPERTY_DOCS' => $arDOCS[GetMessage("LIBTA_DOCS_YES")]
														),
														'Title' => GetMessage("LIBTA_T_ASFA5")
													)
												),
												array(
													'Type' => 'SetStateTitleActivity',
													'Name' => 'A45199_90932_25287_87864',
													'Properties' => array(
														'TargetStateTitle' => GetMessage("LIBTA_STATE8"),
														'Title' => GetMessage("LIBTA_T_SSTA8")
													)
												),
												array(
													'Type' => 'SocNetMessageActivity',
													'Name' => 'A37972_81289_77366_11898',
													'Properties' => array(
														'MessageText' => GetMessage("LIBTA_SMA_MESSAGE_8"),
														'MessageUserFrom' => array(array('Variable', 'BookkeeperDoc')),
														'MessageUserTo' => array('author'),
														'Title' => GetMessage("LIBTA_T_SMA_MESSAGE_8")
													)
												)
											)
										),
										array(
											'Type' => 'SequenceActivity',
											'Name' => 'A86251_76559_20148_59279',
											'Properties' => array(
												'Title' => GetMessage("LIBTA_T_SA0")
											),
											'Children' => array(
												array(
													'Type' => 'SetStateTitleActivity',
													'Name' => 'A40026_74145_86433_86524',
													'Properties' => array(
														'TargetStateTitle' => GetMessage("LIBTA_STATE9"),
														'Title' => GetMessage("LIBTA_T_SSTA9")
													)
												),
												array(
													'Type' => 'SocNetMessageActivity',
													'Name' => 'A13757_19624_64725_7220',
													'Properties' => array(
														'MessageText' => GetMessage("LIBTA_SMA_MESSAGE_9"),
														'MessageUserFrom' => array(array('A11229_71564_7314_72859', 'LastApprover')),
														'MessageUserTo' => array('author'),
														'Title' => GetMessage("LIBTA_T_SMA_MESSAGE_9")
													)
												)
											)
										)
									)
								)
							)
						),
						array(
							'Type' => 'IfElseBranchActivity',
							'Name' => 'A92121_88692_18191_72652',
							'Properties' => array(
								'Title' => GetMessage("LIBTA_T_IFELSEBA4"),
								'truecondition' => '1'
							),
							'Children' => array(
								array(
									'Type' => 'SocNetMessageActivity',
									'Name' => 'A48721_62923_57576_94352',
									'Properties' => array(
										'MessageText' => GetMessage("LIBTA_SMA_MESSAGE_10"),
										'MessageUserFrom' => array(array('Variable', 'Approver')),
										'MessageUserTo' => array('author'),
										'Title' => GetMessage("LIBTA_T_SMA_MESSAGE_10")
									)
								)
							)
						)
					)
				),
				array(
					'Type' => 'SetPermissionsActivity',
					'Name' => 'A11979_87167_62472_41650',
					'Properties' => array(
						'Permission' => array(
							$arPerms['R'] => array('author', array('Variable', 'Manager')),
							$arPerms['W'] => array(array('Variable', 'Manager')),
						),
						'Title' => GetMessage("LIBTA_T_SPAX5")
					)
				)
			)
		)
	),
	'PARAMETERS' => array(),
	'VARIABLES' => array(
		'Bookkeeper' => array(
			'Name' => GetMessage("LIBTA_V_BK"),
			'Description' => '',
			'Type' => 'user',
			'Required' => '0',
			'Multiple' => '1',
			'Default' => array('1')
		),
		'Manager' => array(
			'Name' => GetMessage("LIBTA_V_MNG"),
			'Description' => '',
			'Type' => 'user',
			'Required' => '0',
			'Multiple' => '1',
			'Default' => array('1')
		),
		'Approver' => array(
			'Name' => GetMessage("LIBTA_V_APPRU"),
			'Description' => '',
			'Type' => 'user',
			'Required' => '0',
			'Multiple' => '0',
			'Default_printable' => '',
			'Default' => ''
		),
		'BookkeeperPay' => array(
			'Name' => GetMessage("LIBTA_V_BKP"),
			'Description' => '',
			'Type' => 'user',
			'Required' => '0',
			'Multiple' => '1',
			'Default' => array('1')
		),
		'BookkeeperDoc' => array(
			'Name' => GetMessage("LIBTA_V_BKD"),
			'Description' => '',
			'Type' => 'user',
			'Required' => '0',
			'Multiple' => '1',
			'Default' => array('1')
		),
		'ManagerApprover' => array(
			'Name' => GetMessage("LIBTA_V_MAPPR"),
			'Description' => '',
			'Type' => 'user',
			'Required' => '0',
			'Multiple' => '1',
			'Default' => array('1')
		),
		'Link' => array(
			'Name' => GetMessage("LIBTA_V_LINK"),
			'Description' => '',
			'Type' => 'string',
			'Required' => '0',
			'Multiple' => '0',
			'Default' => "http://".$_SERVER["HTTP_HOST"].'/services/lists/'.$iblockId.'/element/0/'
		),
		'TasksLink' => array(
			'Name' => GetMessage("LIBTA_V_TLINK"),
			'Description' => '',
			'Type' => 'string',
			'Required' => '0',
			'Multiple' => '0',
			'Default' => "http://".$_SERVER["HTTP_HOST"].'/company/personal/bizproc/'
		),
		'PayDate' => array(
			'Name' => GetMessage("LIBTA_V_PDATE"),
			'Title' => GetMessage("LIBTA_V_PDATE"),
			'Description' => '',
			'Type' => 'date',
			'Required' => '1',
			'Multiple' => '0',
			'Default' => ''
		),
		'PayNum' => array(
			'Name' => GetMessage("LIBTA_V_PNUM"),
			'Title' => GetMessage("LIBTA_V_PNUM"),
			'Description' => '',
			'Type' => 'string',
			'Required' => '1',
			'Multiple' => '0',
			'Default' => ''
		),
		'PaymentApprover' => array(
			'Name' => GetMessage("LIBTA_V_APPR"),
			'Description' => '',
			'Type' => 'user',
			'Required' => '0',
			'Multiple' => '0',
			'Default' => ''
		),
		'Domain' => array(
			'Name' => GetMessage("LIBTA_V_DOMAIN"),
			'Description' => '',
			'Type' => 'string',
			'Required' => '0',
			'Multiple' => '0',
			'Default' => "http://".$_SERVER["HTTP_HOST"]
		)
	),
);
?>