import {Tag, Cache, Loc, Type, Dom, Text} from 'main.core';
import {Button} from 'ui.buttons';

import './css/style.css';

type HeaderOptions = {
	contact: {
		label: string,
		href: string,
	},
	contactsList: Array<any>,
};

export default class Header
{
	cache = new Cache.MemoryCache();

	constructor(options)
	{
		this.setOptions(options);
	}

	setOptions(options: HeaderOptions)
	{
		this.cache.set('options', {...options});
	}

	getOptions(): HeaderOptions
	{
		return this.cache.get('options', {});
	}

	setValue(value: string)
	{
		if (Type.isString(value) || Type.isNumber(value))
		{
			this.getValueLayout().textContent = value;
		}
	}

	getValueLayout(): HTMLDivElement
	{
		return this.cache.remember('valueLayout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-header-text-value">
					<span title="${Text.encode(this.getOptions().contact.label)}">${Text.encode(this.getOptions().contact.label)}</span>
				</div>
			`;
		});
	}

	getChangeContactButton(): Button
	{
		return this.cache.remember('changeContactButton', () => {
			return new Button({
				text: Loc.getMessage('UI_STAMP_UPLOADER_HEADER_CHANGE_CONTACT_BUTTON_LABEL'),
				size: Button.Size.EXTRA_SMALL,
				color: Button.Color.LIGHT_BORDER,
				round: true,
			});
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('headerLayout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-header">
					<div class="ui-stamp-uploader-header-icon">
						<div class="ui-stamp-uploader-header-icon-image"></div>
					</div>
					<div class="ui-stamp-uploader-header-text">
						<div class="ui-stamp-uploader-header-text-label">
							${Loc.getMessage('UI_STAMP_UPLOADER_HEADER_TITLE')}
						</div>
						${this.getValueLayout()}
					</div>
					<div class="ui-stamp-uploader-header-action">
						
					</div>
				</div>
			`;
		});
	}

	appendTo(target: HTMLElement)
	{
		if (Type.isDomNode(target))
		{
			Dom.append(this.getLayout(), target);
		}
	}

	prependTo(target: HTMLElement)
	{
		if (Type.isDomNode(target))
		{
			Dom.prepend(this.getLayout(), target);
		}
	}

	renderTo(target: HTMLElement)
	{
		this.appendTo(target);
	}
}