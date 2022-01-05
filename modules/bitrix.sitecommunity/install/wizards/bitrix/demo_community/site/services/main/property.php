<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (WIZARD_INSTALL_DEMO_DATA)	
{
	$arProperties = Array(

		'UF_TWITTER' => array(
			'ENTITY_ID' => 'USER',
			'FIELD_NAME' => 'UF_TWITTER',
			'USER_TYPE_ID' => 'string_formatted',
			'XML_ID' => 'UF_TWITTER',
			'SORT' => 100,
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'SHOW_IN_LIST' => 'Y',
			'EDIT_IN_LIST' => 'Y',
			'IS_SEARCHABLE' => 'Y',
			'SETTINGS' => array('PATTERN' => '<img src="/upload/twitter.gif" class="ico_info"> <a href="http://twitter.com/#VALUE#/" target="_blank">#VALUE#</a>'),
		),

		'UF_SKYPE' => array(
			'ENTITY_ID' => 'USER',
			'FIELD_NAME' => 'UF_SKYPE',
			'USER_TYPE_ID' => 'string_formatted',
			'XML_ID' => 'UF_SKYPE',
			'SORT' => 200,
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'SHOW_FILTER' => 'S',
			'SHOW_IN_LIST' => 'Y',
			'EDIT_IN_LIST' => 'Y',
			'IS_SEARCHABLE' => 'Y',
			'SETTINGS' => array('PATTERN' => '<a href="callto://#VALUE#">#VALUE#</a>'),
		),

	);
	
	if(LANGUAGE_ID == 'ru'){
		
		$arPropertiesRu = array(
				'UF_KONTAKT' => array(
				'ENTITY_ID' => 'USER',
				'FIELD_NAME' => 'UF_KONTAKT',
				'USER_TYPE_ID' => 'string_formatted',
				'XML_ID' => 'UF_KONTAKT',
				'SORT' => 300,
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'S',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
				'SETTINGS' => array('PATTERN' => '<img src="/upload/vkontakte.gif" class="ico_info"> <a href="http://vkontakte.ru/id#VALUE#" target="_blank">#VALUE#</a>'),
			),
			'UF_MOYMIR' => array(
				'ENTITY_ID' => 'USER',
				'FIELD_NAME' => 'UF_MOYMIR',
				'USER_TYPE_ID' => 'string_formatted',
				'XML_ID' => 'UF_MOYMIR',
				'SORT' => 400,
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'S',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
				'SETTINGS' => array('PATTERN' => '<img src="/upload/mymail.gif" class="ico_info"> <a href="http://my.mail.ru/mail/#VALUE#" target="_blank">#VALUE#</a>'),
			),
			'UF_MYYANDEX' => array(
				'ENTITY_ID' => 'USER',
				'FIELD_NAME' => 'UF_MYYANDEX',
				'USER_TYPE_ID' => 'string_formatted',
				'XML_ID' => 'UF_MYYANDEX',
				'SORT' => 500,
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'S',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
				'SETTINGS' => array('PATTERN' => '<img src="/upload/yaru.gif" class="ico_info"> <a href="http://#VALUE#.ya.ru" target="_blank">#VALUE#</a>'),
			),
		);
		
		$arProperties = array_merge($arProperties, $arPropertiesRu);
	}
	
	$arLanguages = Array();
	$rsLanguage = CLanguage::GetList();
	while($arLanguage = $rsLanguage->Fetch())
		$arLanguages[] = $arLanguage["LID"];

	foreach ($arProperties as $arProperty)
	{
		$dbRes = CUserTypeEntity::GetList(Array(), Array("ENTITY_ID" => $arProperty["ENTITY_ID"], "FIELD_NAME" => $arProperty["FIELD_NAME"]));
		if ($dbRes->Fetch())
			continue;

		$arLabelNames = Array();
		foreach($arLanguages as $languageID)
		{
			WizardServices::IncludeServiceLang("property_names.php", $languageID);
			$arLabelNames[$languageID] = GetMessage($arProperty["FIELD_NAME"]);
		}

		$arProperty["EDIT_FORM_LABEL"] = $arLabelNames;
		$arProperty["LIST_COLUMN_LABEL"] = $arLabelNames;
		$arProperty["LIST_FILTER_LABEL"] = $arLabelNames;

		$userType = new CUserTypeEntity();
		$success = (bool)$userType->Add($arProperty);

	}
}
?>