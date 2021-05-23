<?define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$arJsonData = array();

if (
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& check_bitrix_sessid()
)
{
	if (
		$_POST['popupType'] == 'VIDEO_TRANSFORM'
		&& isset($_POST['popupType'])
		&& isset($_POST['action'])
		&& $_POST['action'] == 'PUBLISH'
		&& isset($_POST['postId'])
		&& intval($_POST['postId']) > 0
		&& \Bitrix\Main\Loader::includeModule('socialnetwork')
	)
	{
		\Bitrix\Socialnetwork\ComponentHelper::setBlogPostLimitedViewStatus(array(
			'postId' => intval($_POST['postId']),
			'show' => true,
			'notifyAuthor' => false
		));
	}

	if (
		isset($_POST['closePopup'])
		&& $_POST['closePopup'] == 'Y'
	)
	{
		$type = (!empty($_POST['popupType']) ? $_POST['popupType'] : 'EXPERT_MODE');

		switch($type)
		{
			case 'EXPERT_MODE':
				$optionName = "~log_expertmode_popup_show";
				break;
			case 'VIDEO_TRANSFORM':
				$optionName = "~log_videotransform_popup_show";
				break;
			default:
				$optionName = "";
		}
		if (!empty($optionName))
		{
			CUserOptions::setOption("socialnetwork", $optionName, "N");
		}
		$arJsonData['SUCCESS'] = 'Y';
	}
}

echo \Bitrix\Main\Web\Json::encode($arJsonData);
?>
