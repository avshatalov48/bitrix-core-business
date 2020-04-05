import {Event, Cache, Dom, Type} from 'main.core';
import {SliderHacks} from 'landing.sliderhacks';

const onEditButtonClick = Symbol('onEditButtonClick');

export class TopPanel
{
	constructor()
	{
		this.cache = new Cache.MemoryCache();
		this[onEditButtonClick] = this[onEditButtonClick].bind(this);

		Event.bind(this.getEditButton(), 'click', this[onEditButtonClick]);
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return document.querySelector('.landing-pub-top-panel');
		});
	}

	getEditButton(): HTMLAnchorElement
	{
		return this.cache.remember('editButton', () => {
			return this.getLayout().querySelector('.landing-pub-top-panel-edit-button');
		});
	}

	[onEditButtonClick](event)
	{
		event.preventDefault();

		const href = Dom.attr(event.currentTarget, 'href');

		if (Type.isString(href) && href !== '')
		{
			this.openSlider(href);
		}
	}

	openSlider(url)
	{
		BX.SidePanel.Instance.open(url, {
			cacheable: false,
			customLeftBoundary: 240,
			allowChangeHistory: false,
			events: {
				onClose() {
					void SliderHacks.reloadSlider(window.location.toString());
				},
			},
		});
	}
}