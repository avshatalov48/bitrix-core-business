<?
namespace Bitrix\Main\Controller\SidePanel;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\Engine\JsonPayload;
use Bitrix\Main\Error;
use \Bitrix\Main\SidePanel;

class Toolbar extends JsonController
{
	protected function getDefaultPreFilters()
	{
		return [
			new ActionFilter\Authentication(),
			new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
			new ActionFilter\Csrf(),
			new ActionFilter\CloseSession(),
		];
	}

	public function minimizeAction(JsonPayload $payload)
	{
		$toolbar = $this->getToolbar($payload);
		if ($toolbar === null)
		{
			return [];
		}

		$itemOptions = $this->getItemOptions($payload);
		$result = $toolbar->createOrUpdateItem($itemOptions);

		$item = null;
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
		}
		else
		{
			/** @var EO_ToolbarItem */
			$toolbarItem = $result->getData()['item'];
			$item = [
				'title' => $toolbarItem->getTitle(),
				'entityType' => $toolbarItem->getEntityType(),
				'entityId' => $toolbarItem->getEntityId(),
				'url' => $toolbarItem->getUrl(),
			];
		}

		return [
			'item' => $item,
		];
	}

	public function maximizeAction(JsonPayload $payload)
	{
		$this->minimizeAction($payload);
	}

	public function removeAction(JsonPayload $payload)
	{
		[$entityType, $entityId] = $this->getItemId($payload);
		$toolbar = $this->getToolbar($payload);
		$toolbar?->removeItem($entityType, $entityId);
	}

	public function removeAllAction(JsonPayload $payload)
	{
		$toolbar = $this->getToolbar($payload);
		$toolbar?->removeAll();
	}

	public function collapseAction(JsonPayload $payload)
	{
		$toolbar = $this->getToolbar($payload);
		$toolbar?->collapse();
	}

	public function expandAction(JsonPayload $payload)
	{
		$toolbar = $this->getToolbar($payload);
		$toolbar?->expand();
	}

	private function getToolbar(JsonPayload $payload): ?SidePanel\Toolbar
	{
		$request = $payload->getData();
		$request = is_array($request) ? $request : [];

		$context =
			isset($request['toolbar']['context']) && is_string($request['toolbar']['context'])
				? trim($request['toolbar']['context'])
				: null
		;

		if ($context === null || strlen($context) < 1)
		{
			return null;
		}

		return SidePanel\Toolbar::getOrCreate($context);
	}

	private function getItemOptions(JsonPayload $payload): array
	{
		$request = $payload->getData();
		$request = is_array($request) ? $request : [];

		return isset($request['item']) && is_array($request['item']) ? $request['item'] : [];
	}

	private function getItemId(JsonPayload $payload): array
	{
		$options = $this->getItemOptions($payload);
		$entityType =
			isset($options['entityType']) && is_string($options['entityType'])
				? trim($options['entityType'])
				: ''
		;

		$entityId =
			isset($options['entityId']) && is_string($options['entityId'])
				? trim($options['entityId'])
				: ''
		;

		return [$entityType, $entityId];
	}
}
