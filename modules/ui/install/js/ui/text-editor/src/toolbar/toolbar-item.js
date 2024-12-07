import { EventEmitter } from 'main.core.events';

export default class ToolbarItem extends EventEmitter
{
	constructor()
	{
		super();
		this.setEventNamespace('BX.UI.TextEditor.ToolbarItem');
	}

	getContainer(): HTMLElement
	{
		throw new Error('You must implement getContainer() method.');
	}

	render(): HTMLElement
	{
		throw new Error('You must implement render() method.');
	}
}
