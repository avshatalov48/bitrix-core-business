<?

namespace Bitrix\SocialNetwork\Controller;

use Bitrix\Main\Engine\Controller;

class VideoRecorder extends Controller
{
	public function onStopRecordAction()
	{
		return true;
	}

	public function onSaveAction()
	{
		return true;
	}
}