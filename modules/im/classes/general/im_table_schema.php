<?

class CIMTableSchema
{
	public function __construct()
	{
	}

	public static function OnGetTableSchema()
	{
		return array(
			"im" => array(
				"b_im_message" => array(
					"ID" => array(
						"b_im_relation" => "LAST_ID",
						"b_im_relation^" => "LAST_SEND_ID",
						"b_im_relation^^" => "START_ID",
						"b_im_relation^^^" => "UNREAD_ID",
						"b_disk_object" => "LAST_FILE_ID",
						"b_im_chat" => "LAST_MESSAGE_ID",
						"b_im_message_param" => "MESSAGE_ID",
						"b_im_recent" => "ITEM_MID",
					),
					"CHAT_ID" => array(
						"b_im_chat" => "ID",
					),
				),
				"b_im_chat" => array(
					"ID" => array(
						"b_im_message" => "CHAT_ID",
						"b_im_relation" => "CHAT_ID",
						"b_im_recent" => "ITEM_CID",
					),
				),
				"b_im_relation" => array(
					"ID" => array(
						"b_im_recent" => "ITEM_RID",
					),
					"CHAT_ID" => array(
						"b_im_chat" => "ID",
					),
				),
			),
			"main" => array(
				"b_user" => array(
					"ID" => array(
						"b_im_relation" => "USER_ID",
						"b_im_message" => "AUTHOR_ID",
						"b_im_chat" => "AUTHOR_ID",
					),
				),
				"b_module" => array(
					"ID" => array(
						"b_im_message" => "NOTIFY_MODULE",
					),
				),
			),
			"imopelines" => array(
				"b_imopenlines_session" => array(
					"ID" => array(
						"b_im_recent" => "ITEM_OLID",
					),
				),
			),
		);
	}
}

?>
