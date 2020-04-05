/**
 * Checks that element contains block
 * @param {HTMLElement} element
 * @return {boolean}
 */
export default function hasBlock(element: HTMLElement): boolean
{
	return !!element && !!element.querySelector('.block-wrapper');
}