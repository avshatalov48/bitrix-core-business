<?php
namespace Bitrix\Landing\Controller;

use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Note\Target;
use \Bitrix\Landing\Note\Source;
use \Bitrix\Landing\Site\Type;
use \Bitrix\Main\Engine\Controller;
use \Bitrix\Main\Error;

class Note extends Controller
{
	public function getDefaultPreFilters()
	{
		return [];
	}

	/**
	 * Returns true if current user have 'edit' right.
	 * @return bool
	 */
	protected static function canCreateNew(): bool
	{
		Type::setScope(Type::SCOPE_CODE_KNOWLEDGE);
		return Rights::hasAdditionalRight(Rights::ADDITIONAL_RIGHTS['create']);
	}

	/**
	 * Returns available entities short list for note creating.
	 * @return array
	 */
	public static function getTargetsAction(): array
	{
		return [
			'list' => Target::getShortList(),
			'canCreateNew' => self::canCreateNew()
		];
	}

	/**
	 * Creates new knowledge page and returns info about it.
	 * @param int $kbId Knowledge id.
	 * @param string $sourceType Source type.
	 * @param int $sourceId Source id.
	 * @param string $scope Scope.
	 * @return array|null
	 */
	public function createNoteAction(int $kbId, string $sourceType, int $sourceId, ?string $scope = null): ?array
	{
		$result = null;

		if ($scope)
		{
			Type::setScope($scope);
		}
		if (check_bitrix_sessid())
		{
			$result = Source::createFromSource($kbId, $sourceType, $sourceId);
		}
		if (is_array($result))
		{
			return $result;
		}

		$this->addError(new Error('Error occurred during note creating.'));
		return null;
	}
}