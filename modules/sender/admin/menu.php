<?
IncludeModuleLangFile(__FILE__);

if($APPLICATION->GetGroupRight("sender")!="D")
{
	$arSiteMailing = array();
	$arSiteMailingTrig = array();
	if(CModule::IncludeModule('sender'))
	{
		$mailingListDb = \Bitrix\Sender\MailingTable::getList(array('filter' => array()));
		while ($mailing = $mailingListDb->fetch())
		{
			if($mailing['IS_TRIGGER'] == 'Y')
			{
				$mailingMenu = array(
					"text" => htmlspecialcharsbx($mailing['NAME']),
					"title" => GetMessage("mnu_sender_site_mailing_trig_one_alt"),
					"items_id" => "menu_sender_mailing_" . $mailing['ID'],
					"items" => array(
						array(
							"text" => GetMessage("mnu_sender_site_mailing_trig_chain"),
							"url" => "sender_mailing_trig_edit.php?ID=" . $mailing['ID'] . "&lang=" . LANGUAGE_ID,
							"title" => GetMessage("mnu_sender_site_mailing_chain_trig_alt"),
							"more_url" => array("sender_mailing_trig_edit.php?ID=" . $mailing['ID']),
						),
						array(
							"text" => GetMessage("mnu_sender_site_mailing_trig_stat"),
							"url" => "sender_trig_statistics.php?MAILING_ID=" . $mailing['ID'] . "&lang=" . LANGUAGE_ID,
							"title" => GetMessage("mnu_sender_site_mailing_trig_stat_alt")
						),
						array(
							"text" => GetMessage("mnu_sender_site_mailing_addr"),
							"url" => "sender_mailing_recipient_admin.php?MAILING_ID=" . $mailing['ID'] . "&lang=" . LANGUAGE_ID,
							"title" => GetMessage("mnu_sender_site_mailing_addr_alt")
						),
					)
				);
				$arSiteMailingTrig[] = $mailingMenu;
			}

			else
			{
				$mailingMenu = array(
					"text" => htmlspecialcharsbx($mailing['NAME']),
					"title" => GetMessage("mnu_sender_site_mailing_one_alt"),
					"items_id" => "menu_sender_mailing_" . $mailing['ID'],
					"items" => array(
						array(
							"text" => GetMessage("mnu_sender_site_mailing_chain"),
							"url" => "sender_mailing_chain_admin.php?MAILING_ID=" . $mailing['ID'] . "&lang=" . LANGUAGE_ID,
							"title" => GetMessage("mnu_sender_site_mailing_chain_alt"),
							"more_url" => array("sender_mailing_chain_edit.php?MAILING_ID=" . $mailing['ID']),
						),
						array(
							"text" => GetMessage("mnu_sender_site_mailing_stat"),
							"url" => "sender_mailing_stat.php?MAILING_ID=" . $mailing['ID'] . "&lang=" . LANGUAGE_ID,
							"title" => GetMessage("mnu_sender_site_mailing_stat_alt")
						),
						array(
							"text" => GetMessage("mnu_sender_site_mailing_addr"),
							"url" => "sender_mailing_recipient_admin.php?MAILING_ID=" . $mailing['ID'] . "&lang=" . LANGUAGE_ID,
							"title" => GetMessage("mnu_sender_site_mailing_addr_alt")
						),
					)
				);
				$arSiteMailing[] = $mailingMenu;
			}
		}
	}
	$aMenu = array();
	$aMenu[] = array(
		"parent_menu" => "global_menu_marketing",
		"section" => "sender",
		"sort" => 600,
		"text" => GetMessage("mnu_sender_sect"),
		"title" => GetMessage("mnu_sender_sect_title"),
		"icon" => "sender_menu_icon",
		"page_icon" => "sender_page_icon",
		"items_id" => "menu_sender",
		"items" => array(
			array(
				"text" => GetMessage("mnu_sender_stat"),
				"url" => "sender_statistics.php?lang=".LANGUAGE_ID,
				"title" => GetMessage("mnu_sender_stat_alt"),
			),
			array(
				"text" => GetMessage("mnu_sender_mailing_admin"),
				"url" => "sender_mailing_admin.php?lang=".LANGUAGE_ID,
				"title" => GetMessage("mnu_sender_mailing_admin_alt"),
				"more_url" => array("sender_mailing_edit.php", "sender_mailing_wizard.php?IS_TRIGGER=N"),
			),

			array(
				"text" => GetMessage("mnu_sender_group"),
				"url" => "sender_group_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("sender_group_edit.php"),
				"title" => GetMessage("mnu_sender_group_alt")
			),

			array(
				"text" => GetMessage("mnu_sender_contact_admin"),
				"url" => "sender_contact_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("sender_contact_import.php", "sender_list_admin.php", "sender_contact_edit.php"),
				"title" => GetMessage("mnu_sender_contact_admin_alt")
			),
			array(
				"text" => GetMessage("mnu_sender_template_admin"),
				"url" => "sender_template_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("sender_template_edit.php"),
				"title" => GetMessage("mnu_sender_template_admin_alt")
			),

			array(
				"text" => GetMessage("mnu_sender_site_mailing"),
				"title" => GetMessage("mnu_sender_site_mailing_alt"),
				"dynamic" => true,
				"module_id" => "sender",
				"items_id" => "menu_sender_mailing_list",
				'items' => $arSiteMailing
			)
		)
	);

	$aMenu[] = array(
		"parent_menu" => "global_menu_marketing",
		"section" => "sender",
		"sort" => 650,
		"text" => GetMessage("mnu_sender_mailing_trig"),
		"title" => GetMessage("mnu_sender_mailing_trig_alt"),
		"icon" => "sender_trig_menu_icon",
		"page_icon" => "sender_trig_page_icon",
		"items_id" => "menu_sender_trig",
		"items" => array(
			array(
				"text" => GetMessage("mnu_sender_mailing_admin"),
				"url" => "sender_mailing_trig_admin.php?lang=".LANGUAGE_ID,
				"title" => GetMessage("mnu_sender_mailing_admin_alt"),
				"more_url" => array("sender_mailing_wizard.php?IS_TRIGGER=Y"),
			),
			array(
				"text" => GetMessage("mnu_sender_site_mailing"),
				"title" => GetMessage("mnu_sender_site_mailing_alt"),
				"dynamic" => true,
				"module_id" => "sender_trig",
				"items_id" => "menu_sender_trig_mailing_list",
				'items' => $arSiteMailingTrig
			)
		)
	);

	return $aMenu;
}
return false;
?>