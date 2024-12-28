<?
IncludeModuleLangFile(__FILE__);

class CSocServDescription
{
	public static function GetDescription()
	{
		$tw_disabled = !function_exists("hash_hmac");

		return array(
			array(
				"ID" => "Bitrix24Net",
				"CLASS" => "CSocServBitrix24Net",
				"NAME" => GetMessage("socserv_bitrix24net_name"),
				"ICON" => "bitrix24",
			),
			array(
				"ID" => "Facebook",
				"CLASS" => "CSocServFacebook",
				"NAME" => "Facebook",
				"ICON" => "facebook",
			),
			array(
				"ID" => "YandexOAuth",
				"CLASS" => "CSocServYandexAuth",
				"NAME" => GetMessage("socserv_openid_yandex"),
				"ICON" => "yandex",
			),
			array(
				"ID" => "MailRu2",
				"CLASS" => "CSocServMailRu2",
				"NAME" => GetMessage("socserv_mailru2_name"),
				"ICON" => "mailru2",
			),
			array(
				"ID" => "MyMailRu",
				"CLASS" => "CSocServMyMailRu",
				"NAME" => GetMessage("socserv_mailru_name"),
				"ICON" => "mymailru",
			),
			array(
				"ID" => "OpenID",
				"CLASS" => "CSocServOpenID",
				"NAME" => "OpenID",
				"ICON" => "openid",
			),
/*
			array(
				"ID" => "YandexOpenID",
				"CLASS" => "CSocServYandex",
				"NAME" => GetMessage("socserv_openid_yandex_openid"),
				"ICON" => "yandex",
			),
*/
			array(
				"ID" => "MailRuOpenID",
				"CLASS" => "CSocServMailRu",
				"NAME" => GetMessage("socserv_mailru_openid_name"),
				"ICON" => "openid-mail-ru",
			),
			array(
				"ID" => "Livejournal",
				"CLASS" => "CSocServLivejournal",
				"NAME" => "Livejournal",
				"ICON" => "livejournal",
			),
			array(
				"ID" => "Liveinternet",
				"CLASS" => "CSocServLiveinternet",
				"NAME" => "Liveinternet",
				"ICON" => "liveinternet",
			),
			array(
				"ID" => "Blogger",
				"CLASS" => "CSocServBlogger",
				"NAME" => "Blogger",
				"ICON" => "blogger",
			),
			array(
				"ID" => "Twitter",
				"CLASS" => "CSocServTwitter",
				"NAME" => "X",
				"ICON" => "twitter",
				"DISABLED" => $tw_disabled,
			),
			array(
				"ID" => "VKontakte",
				"CLASS" => "CSocServVKontakte",
				"NAME" => GetMessage("socserv_vk_name"),
				"ICON" => "vkontakte",
			),
			array(
				"ID" => "GoogleOAuth",
				"CLASS" => "CSocServGoogleOAuth",
				"NAME" => "Google",
				"ICON" => "google",
			),
/*			array(
				"ID" => "GooglePlusOAuth",
				"CLASS" => "CSocServGooglePlusOAuth",
				"NAME" => "Google+",
				"ICON" => "google-plus",
			), */
			array(
				"ID" => "LiveIDOAuth",
				"CLASS" => "CSocServLiveIDOAuth",
				"NAME" => "LiveID",
				"ICON" => "liveid",
			),
			array(
				"ID" => "Office365",
				"CLASS" => "CSocServOffice365OAuth",
				"NAME" => "Office365",
				"ICON" => "office365",
			),
			array(
				"ID" => "Odnoklassniki",
				"CLASS" => "CSocServOdnoklassniki",
				"NAME" => GetMessage("socserv_odnoklassniki_name"),
				"ICON" => "odnoklassniki",
			),
			array(
				"ID" => "Dropbox",
				"CLASS" => "CSocServDropboxAuth",
				"NAME" => "Dropbox",
				"ICON" => "dropbox",
			),
			array(
				"ID" => "Box",
				"CLASS" => "CSocServBoxAuth",
				"NAME" => "Box.com",
				"ICON" => "box",
			),
			array(
				"ID" => "apple",
				"CLASS" => "CSocServApple",
				"NAME" => "Apple Sign In",
				"ICON" => "apple",
			),
			array(
				"ID" => "zoom",
				"CLASS" => "CSocServZoom",
				"NAME" => "Zoom",
				"ICON" => "zoom",
			),
		);
	}
}

AddEventHandler("socialservices", "OnAuthServicesBuildList", array("CSocServDescription", "GetDescription"));