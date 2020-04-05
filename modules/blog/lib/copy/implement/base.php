<?php
namespace Bitrix\Blog\Copy\Implement;

use Bitrix\Main\Copy\CopyImplementer;

abstract class Base extends CopyImplementer
{
	/**
	 * Updates entity.
	 *
	 * @param int $entityId Entity id.
	 * @param array $fields List entity fields.
	 * @return bool
	 */
	abstract public function update($entityId, array $fields);

	public function updateAttachedIdsInText(int $id, array $attachedIds, callable $callback): void
	{
		list($field, $text) = $this->getText($id);

		$detailText = call_user_func_array($callback, [
			$text,
			$this->ufEntityObject,
			$id,
			$this->ufDiskFileField,
			$attachedIds
		]);

		$this->update($id, [$field => $detailText]);
	}
}