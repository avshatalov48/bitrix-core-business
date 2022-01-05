import { Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

export class ThemePicker
{
	constructor(
		params: {
			container: Element,
			theme: object,
		}
	)
	{
		this.container = params.container;
		this.theme = params.theme;
		this.draw(this.theme);

		const previewImageNode = this.getNode('image');
		if (previewImageNode)
		{
			previewImageNode.addEventListener('click', this.open);
		}

		const titleNode = this.getNode('title');
		if (titleNode)
		{
			titleNode.addEventListener('click', this.open);
		}

		const deleteNode = this.getNode('delete');
		if (deleteNode)
		{
			deleteNode.addEventListener('click', () => {
				this.select({});
			});
		}

		EventEmitter.subscribe('Intranet.ThemePicker:onSave', (event: BaseEvent) => {
			const [ data ] = event.getData();
			this.select(data);
		});
	}

	select(data)
	{
		const theme = (Type.isPlainObject(data.theme) ? data.theme : {});
		this.draw(theme);
	}

	draw(theme)
	{
		const previewImageNode = this.getNode('image');
		if (previewImageNode)
		{
			previewImageNode.style.backgroundImage = (Type.isStringFilled(theme.previewImage) ? `url('${theme.previewImage}')` : '');
			previewImageNode.style.backgroundColor = (Type.isStringFilled(theme.previewColor) ? theme.previewColor : 'transparent');
		}

		const titleNode = this.getNode('title');
		if (titleNode)
		{
			titleNode.innerHTML = (Type.isStringFilled(theme.title) ? theme.title : '');
		}

		const inputNode = this.getNode('id');
		if (inputNode)
		{
			inputNode.value = (Type.isStringFilled(theme.id) ? theme.id : '');
		}
	}

	open(event)
	{
		BX.Intranet.Bitrix24.ThemePicker.Singleton.showDialog(true);

		event.preventDefault();
	}

	getNode(name)
	{
		const result = null;
		if (!Type.isStringFilled(name))
		{
			return result;
		}

		return this.container.querySelector(`[bx-group-edit-theme-node="${name}"]`);
	}

	getContainer()
	{
		return this.container;
	}
}
