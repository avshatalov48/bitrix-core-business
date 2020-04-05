<?
if(!CModule::IncludeModule('support'))
{
	return ;
}

$rsSites = CSite::GetList($v1, $v2, array('ACTIVE' => 'Y'));
while ($arSite = $rsSites->Fetch())
{
	
	IncludeModuleLangFile(__FILE__, $arSite['LANGUAGE_ID']);
	
	$SLA_ID = CTicketSLA::Set(
		array(
			'NAME' => GetMessage('SUP_DEF_SLA_NAME'),
			'PRIORITY' => 0,
			'RESPONSE_TIME_UNIT' => 'hour',
			'NOTICE_TIME_UNIT' => 'hour',
			'CREATED_GUEST_ID' => 0,
			'MODIFIED_GUEST_ID' => 0,
			'TIMETABLE_ID' => 0,
			
			'arGROUPS' => array(2),
			'arSITES' => array($arSite['ID']),
			'arCATEGORIES' => array(0),
			'arCRITICALITIES' => array(0),
			'arMARKS' => array(0),
			
			'arSHEDULE' => array(
				0 => array('OPEN_TIME' => '24H'),
				1 => array('OPEN_TIME' => '24H'),
				2 => array('OPEN_TIME' => '24H'),
				3 => array('OPEN_TIME' => '24H'),
				4 => array('OPEN_TIME' => '24H'),
				5 => array('OPEN_TIME' => '24H'),
				6 => array('OPEN_TIME' => '24H'),
			),
		),
		0, false
	);
	if (!$SLA_ID) {$DD_ERROR = true; return ;}
	
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_BUGS'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'C',
			'C_SORT'	=> 100,
			'EVENT1'	=> 'ticket',
			'EVENT2'	=> 'bugs',
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_ORDER_PAYMENT'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'C',
			'C_SORT'	=> 200,
			'EVENT1'	=> 'ticket',
			'EVENT2'	=> 'pay',
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_ORDER_SHIPPING'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'C',
			'C_SORT'	=> 300,
			'EVENT1'	=> 'ticket',
			'EVENT2'	=> 'delivery',
	));
	if (!$e) {$DD_ERROR = true; return ;}
	
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_LOW'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'K',
			'C_SORT'	=> 100,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_MIDDLE'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'K',
			'C_SORT'	=> 200,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_HIGH'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'K',
			'C_SORT'	=> 300,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_REQUEST_ACCEPTED'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'S',
			'C_SORT'	=> 100,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_PROBLEM_SOLVING_IN_PROGRESS'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'S',
			'C_SORT'	=> 200,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_COULD_NOT_BE_SOLVED'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'S',
			'C_SORT'	=> 300,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_SUCCESSFULLY_SOLVED'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'S',
			'C_SORT'	=> 400,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_ANSWER_SUITS_THE_NEEDS'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'M',
			'C_SORT'	=> 100,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_ANSWER_IS_NOT_COMPLETE'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'M',
			'C_SORT'	=> 200,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_ANSWER_DOES_NOT_SUIT'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'M',
			'C_SORT'	=> 300,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_E_MAIL'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'SR',
			'C_SORT'	=> 100,
			'SID'		=> 'email',
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_PHONE'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'SR',
			'C_SORT'	=> 200,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_FORUM'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'SR',
			'C_SORT'	=> 300,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_EASY'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'D',
			'C_SORT'	=> 100,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_MEDIUM'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'D',
			'C_SORT'	=> 200,
	));
	if (!$e) {$DD_ERROR = true; return ;}
	$e = CTicketDictionary::Add(array(
			'NAME'		=> GetMessage('SUP_DEF_HARD'),
			'arrSITE'	=> array($arSite['ID']),
			'C_TYPE'  	=> 'D',
			'C_SORT'	=> 300,
	));
	if (!$e) {$DD_ERROR = true; return ;}
}
?>