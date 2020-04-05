/**
 * @namespace {BX.UI}
 */
export default class IButton
{
	render(): HTMLElement
	{
		throw new Error('BX.UI.IButton: Must be implemented by a subclass');
	}
}