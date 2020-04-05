/**
 * Checks that element contains "Add new Block" button
 * @param {HTMLElement} element
 * @return {boolean}
 */
export default function hasCreateButton(element: HTMLElement): boolean
{
	return !!element && !!element.querySelector('button[data-id="insert_first_block"]');
}