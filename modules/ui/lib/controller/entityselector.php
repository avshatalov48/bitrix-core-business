<?

namespace Bitrix\UI\Controller;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\Engine\JsonPayload;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class EntitySelector extends JsonController
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

	public function loadAction(JsonPayload $payload)
	{
		$request = $payload->getData();
		$request = is_array($request) ? $request : [];

		$dialog = new Dialog(isset($request['dialog']) && is_array($request['dialog']) ? $request['dialog'] : []);
		$dialog->load();

		return [
			'dialog' => $dialog->getAjaxData()
		];
	}

	public function getChildrenAction(JsonPayload $payload)
	{
		$request = $payload->getData();
		$request = is_array($request) ? $request : [];

		$dialog = new Dialog(isset($request['dialog']) && is_array($request['dialog']) ? $request['dialog'] : []);

		$parentItem = new Item(
			isset($request['parentItem']) && is_array($request['parentItem']) ? $request['parentItem'] : []
		);

		$dialog->getChildren($parentItem);

		return [
			'dialog' => $dialog->getAjaxData()
		];
	}

	public function doSearchAction(JsonPayload $payload)
	{
		$request = $payload->getData();
		$request = is_array($request) ? $request : [];

		$dialog = new Dialog(isset($request['dialog']) && is_array($request['dialog']) ? $request['dialog'] : []);
		$searchQuery = new SearchQuery(
			isset($request['searchQuery']) && is_array($request['searchQuery']) ? $request['searchQuery'] : []
		);

		$dialog->doSearch($searchQuery);

		return [
			'dialog' => $dialog->getAjaxData(),
			'searchQuery' => $searchQuery
		];
	}

	public function saveRecentItemsAction(JsonPayload $payload)
	{
		if (!$GLOBALS['USER']->isAuthorized())
		{
			return;
		}

		$request = $payload->getData();
		$request = is_array($request) ? $request : [];

		$dialog = new Dialog(isset($request['dialog']) && is_array($request['dialog']) ? $request['dialog'] : []);

		if (isset($request['recentItems']) && is_array($request['recentItems']))
		{
			$dialog->saveRecentItems($request['recentItems']);
		}
	}
}
