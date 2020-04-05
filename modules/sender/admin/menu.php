<?
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Security;
use Bitrix\Sender\Integration;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('sender'))
{
	return false;
}

if (!Security\User::current()->canView())
{
	return false;
}

$aMenu = [];
$aMenu[] = [
	"parent_menu" => "global_menu_marketing",
	"section" => "sender",
	"sort" => 600,
	"text" => GetMessage("mnu_sender_sect"),
	"title" => GetMessage("mnu_sender_sect_title"),
	"icon" => "sender_menu_icon",
	"page_icon" => "sender_page_icon",
	"items_id" => "menu_sender",
	"items" => [
		[
			"text" => GetMessage("mnu_sender_stat"),
			"url" => "sender_statistics.php?lang=".LANGUAGE_ID,
			"more_url" => ["sender_statistics.php"],
			"title" => GetMessage("mnu_sender_stat_alt"),
		],
		[
			"text" => GetMessage("mnu_sender_letters"),
			"url" => "sender_letters.php?lang=".LANGUAGE_ID,
			"more_url" => ["sender_letters.php"],
			"title" => GetMessage("mnu_sender_letters_alt")
		],
		[
			"text" => GetMessage("mnu_sender_segments"),
			"url" => "sender_segments.php?lang=".LANGUAGE_ID,
			"more_url" => ["sender_segments.php"],
			"title" => GetMessage("mnu_sender_group_alt")
		],
		[
			"text" => GetMessage("mnu_sender_campaigns"),
			"url" => "sender_campaign.php?lang=".LANGUAGE_ID,
			"more_url" => ["sender_campaign.php"],
			"title" => GetMessage("mnu_sender_campaigns_alt")
		],
		[
			"text" => GetMessage("mnu_sender_template_admin"),
			"url" => "sender_templates.php?lang=".LANGUAGE_ID,
			"more_url" => ["sender_templates.php"],
			"title" => GetMessage("mnu_sender_template_admin_alt")
		],
		[
			"text" => GetMessage("mnu_sender_blacklist"),
			"url" => "sender_blacklist.php?lang=".LANGUAGE_ID,
			"more_url" => ["sender_blacklist.php"],
			"title" => GetMessage("mnu_sender_blacklist_alt")
		],
		[
			"text" => GetMessage("mnu_sender_contact_admin"),
			"url" => "sender_contacts.php?lang=".LANGUAGE_ID,
			"more_url" => ["sender_contacts.php"],
			"title" => GetMessage("mnu_sender_contact_admin_alt")
		],
	]
];

if (Integration\Seo\Ads\Service::isAvailable())
{
	$aMenu[] = [
		"parent_menu" => "global_menu_marketing",
		"section" => "sender",
		"sort" => 625,
		"text" => GetMessage("mnu_sender_ads"),
		"title" => GetMessage("mnu_sender_ads_alt"),
		"url" => "sender_ads.php?lang=" . LANGUAGE_ID,
		"more_url" => ["sender_ads.php"],
		"icon" => "sender_ads_menu_icon",
		"page_icon" => "sender_ads_page_icon",
	];
}

$aMenu[] = [
	"parent_menu" => "global_menu_marketing",
	"section" => "sender",
	"sort" => 650,
	"text" => GetMessage("mnu_sender_mailing_trig"),
	"title" => GetMessage("mnu_sender_mailing_trig_alt"),
	"url" => "sender_trigger.php?lang=" . LANGUAGE_ID,
	"more_url" => ["sender_trigger.php"],
	"icon" => "sender_trig_menu_icon",
	"page_icon" => "sender_trig_page_icon",
];


return $aMenu;