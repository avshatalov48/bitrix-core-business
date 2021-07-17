<?

namespace Bitrix\UI\Controller;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\Engine\JsonPayload;

class Feedback extends JsonController
{
	protected function getDefaultPreFilters()
	{
		return [
			new ActionFilter\Authentication(),
			new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
			new ActionFilter\Csrf(),
			new ActionFilter\CloseSession()
		];
	}

	public function loadDataAction(JsonPayload $payload)
	{
		$request = $payload->getData();
		$request = is_array($request) ? $request : [];


		$feedbackForm = new \Bitrix\UI\Form\FeedbackForm($request['id']);

		$feedbackForm->setFormParams($request['forms']??[]);

		$feedbackForm->setPresets(is_array($request['presets']) ? $request['presets'] : []);
		if (isset($request['title']))
		{
			$feedbackForm->setTitle($request['title']);
		}

		if (isset($request['portalUri']))
		{
			$feedbackForm->setPortalUri($request['portalUri']);
		}

		if (!$feedbackForm->getFormParams() && !isset($request['defaultForm']))
		{
			return [];
		}

		if (!$feedbackForm->getFormParams() && isset($request['defaultForm']))
		{
			$feedbackForm->setFormParamsDirectly($request['defaultForm']);
		}

		return [
			'form' => $feedbackForm->getFormParams(),
			'presets' => $feedbackForm->getPresets(),
			'title' => $feedbackForm->getTitle(),
			'portalUri' => $feedbackForm->getPortalUri(),
			'params' => $feedbackForm->getJsObjectParams(),
		];
	}
}
