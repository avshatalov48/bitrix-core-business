<?
namespace Bitrix\Fileman\Controller;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class HtmlEditorAjax
 */
class HtmlEditorAjax extends \Bitrix\Main\Engine\Controller
{
	public function configureActions()
	{
	}

	public function getVideoOembedAction($video_source)
	{
		return \CHTMLEditor::GetVideoOembed($video_source);
	}
}