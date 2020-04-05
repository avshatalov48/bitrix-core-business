<?
namespace Bitrix\Lists\Entity;

interface Controllable
{
	/**
	 * Adds an entity.
	 *
	 * @return int|bool
	 */
	public function add();

	/**
	 * Returns a list of entity data.
	 *
	 * @param array $navData Navigation data.
	 *
	 * @return array
	 */
	public function get(array $navData = []);

	/**
	 * Updates an entity.
	 *
	 * @return bool
	 */
	public function update();

	/**
	 * Deletes an entity.
	 *
	 * @return bool
	 */
	public function delete();
}
