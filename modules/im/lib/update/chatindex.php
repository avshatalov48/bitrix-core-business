<?
namespace Bitrix\Im\Update;

use Bitrix\Im\Chat;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;


Loc::loadMessages(__FILE__);

final class ChatIndex extends Stepper
{
	const OPTION_NAME = "im_index_chat";

	protected static $moduleId = "im";

	/**
	 * @inheritdoc
	 */
	public function execute(array &$result)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return false;
		}

		$return = false;

		$params = Option::get(self::$moduleId, $this->getOptionName());
		$params = ($params !== "" ? @unserialize($params, ['allowed_classes' => false]) : []);
		$params = (is_array($params) ? $params : []);

		if (empty($params))
		{
			$params = $this->fillNewParams();
		}

		if ($params["count"] <= 0)
		{
			return false;
		}

		$result["title"] = Loc::getMessage("IM_UPDATE_CHAT_INDEX");
		$result["progress"] = 1;
		$result["steps"] = "";
		$result["count"] = $params["count"];

		$cursor = ChatTable::getList([
			'order' => ['ID' => 'DESC'],
			'filter' => [
				'<ID' => $params["lastId"],
				'=TYPE' => $this->getTypes(),
			],
			'select' => ['ID', 'ENTITY_TYPE'],
			'offset' => 0,
			'limit' => 500
		]);

		$found = false;
		while ($row = $cursor->fetch())
		{
			if ($row['ENTITY_TYPE'] !== 'LIVECHAT')
			{
				\CIMChat::index($row['ID']);
			}

			$params["lastId"] = $row['ID'];
			$params["number"]++;
			$found = true;
		}

		if ($found)
		{
			Option::set(self::$moduleId, $this->getOptionName(), serialize($params));
			$return = true;
		}

		$result["progress"] = (int)($params["number"] * 100 / $params["count"]);
		$result["steps"] = $params["number"];

		if ($found === false)
		{
			$this->sendAdminMessage();
		}

		return $return;
	}

	private function fillNewParams(): array
	{
		$lastIdQuery =
			ChatTable::query()
				->addSelect('ID')
				->whereIn('TYPE', $this->getTypes())
				->setOrder(['ID' => 'DESC'])
				->setLimit(1)
				->fetch()
		;

		return [
			"lastId" => (int)$lastIdQuery['ID'] + 1,
			"number" => 0,
			"count" => $this->getChatCount(),
		];
	}

	private function getChatCount(): int
	{
		return ChatTable::getCount([
			'=TYPE' => $this->getTypes(),
		]);
	}

	private function sendAdminMessage(): void
	{
		Option::delete(self::$moduleId, ["name" => $this->getOptionName()]);
		if (!IsModuleInstalled('bitrix24'))
		{
			\CAdminNotify::Add([
				"MESSAGE" => Loc::getMessage(
					'IM_UPDATE_CHAT_INDEX_OPTIMIZE',
					['#B_TAG_START#' => '<b>', '#B_TAG_END#' => '</b>']
				),
				"TAG" => "IM_CHAT_INDEX_OPTIMIZE_1",
				"MODULE_ID" => "IM",
			]);
		}
	}

	private function getTypes(): array
	{
		$types = [];
		$defaultTypes = [Chat::TYPE_OPEN, Chat::TYPE_GROUP];

		foreach ($this->getOuterParams() as $type)
		{
			$types[] = $type;
		}

		return !empty($types) ? $types : $defaultTypes;
	}

	private function getOptionName(): string
	{
		$types = $this->getTypes();

		if (empty($this->getOuterParams()))
		{
			return self::OPTION_NAME;
		}

		return self::OPTION_NAME . '_' . implode('_', $types);
	}
}
