<?
namespace Bitrix\Lists\Security;

interface RightEntity
{
	/**
	 * Sets the access label that is needed to verify the rights of the entity.
	 *
	 * @param string $listsPermission Access label.
	 */
	public function setListsPermission($listsPermission);
}