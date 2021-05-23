import {Event, Cache, Dom, Type} from 'main.core';
import {SliderHacks} from 'landing.sliderhacks';

const onEditButtonClick = Symbol('onEditButtonClick');
const onBackButtonClick = Symbol('onBackButtonClick');
const onForwardButtonClick = Symbol('onForwardButtonClick');

export class TopPanel
{
	static cache = new Cache.MemoryCache();

	constructor()
	{
		Event.bind(TopPanel.getEditButton(), 'click', this[onEditButtonClick]);
		Event.bind(TopPanel.getBackButton(), 'click', this[onBackButtonClick]);
		Event.bind(TopPanel.getForwardButton(), 'click', this[onForwardButtonClick]);

		TopPanel.pushHistory(window.location.toString());
		TopPanel.checkNavButtonsActivity();
	}

	static getLayout(): HTMLDivElement
	{
		return TopPanel.cache.remember('layout', () => {
			return document.querySelector('.landing-pub-top-panel');
		});
	}

	static getEditButton(): HTMLAnchorElement
	{
		return TopPanel.cache.remember('editButton', () => {
			return TopPanel.getLayout().querySelector('.landing-pub-top-panel-edit-button');
		});
	}

	[onEditButtonClick](event)
	{
		event.preventDefault();

		const href = Dom.attr(event.currentTarget, 'href');
		const landingId = Dom.attr(event.currentTarget, 'data-landingId');

		if (Type.isString(href) && href !== '')
		{
			TopPanel.openSlider(href, landingId);
		}
	}

	static openSlider(url, landingId)
	{
		BX.SidePanel.Instance.open(url, {
			cacheable: false,
			customLeftBoundary: 240,
			allowChangeHistory: false,
			events: {
				onClose() {
					void SliderHacks.reloadSlider(
						window.location.toString().split('#')[0] + '#landingId' + landingId
					);
				},
			},
		});
	}

	// HISTORY save
	static history = [];
	static historyState;

	static pushHistory(url)
	{
		if (!Type.isNumber(TopPanel.historyState))
		{
			TopPanel.historyState = -1; // will increase later
		}

		if (TopPanel.historyState < TopPanel.history.length - 1)
		{
			TopPanel.history.splice(TopPanel.historyState + 1);
		}

		TopPanel.history.push(url);
		TopPanel.historyState++;
	}

	static checkNavButtonsActivity()
	{
		Dom.removeClass(TopPanel.getForwardButton(), 'ui-btn-disabled');
		Dom.removeClass(TopPanel.getBackButton(), 'ui-btn-disabled');

		if (
			!Type.isArrayFilled(TopPanel.history)
			|| !Type.isNumber(TopPanel.historyState)
			|| TopPanel.history.length === 1
		)
		{
			Dom.addClass(TopPanel.getForwardButton(), 'ui-btn-disabled');
			Dom.addClass(TopPanel.getBackButton(), 'ui-btn-disabled');
			return;
		}

		if (TopPanel.historyState === 0)
		{
			Dom.addClass(TopPanel.getBackButton(), 'ui-btn-disabled');
		}

		if (TopPanel.historyState >= TopPanel.history.length - 1)
		{
			Dom.addClass(TopPanel.getForwardButton(), 'ui-btn-disabled');
		}
	}

	static getBackButton(): HTMLAnchorElement
	{
		return TopPanel.cache.remember('backButton', () => {
			const layout = TopPanel.getLayout();
			return layout ? layout.querySelector('.landing-pub-top-panel-back') : null;
		});
	}

	static getForwardButton(): HTMLAnchorElement
	{
		return TopPanel.cache.remember('forwardButton', () => {
			const layout = TopPanel.getLayout();
			return layout ? layout.querySelector('.landing-pub-top-panel-forward') : null;
		});
	}

	[onBackButtonClick](event)
	{
		event.preventDefault();
		if (
			Type.isArrayFilled(TopPanel.history)
			&& Type.isNumber(TopPanel.historyState)
			&& TopPanel.historyState > 0
		)
		{
			void SliderHacks.reloadSlider(TopPanel.history[--TopPanel.historyState]);
			TopPanel.checkNavButtonsActivity();
		}
	}

	[onForwardButtonClick](event)
	{
		event.preventDefault();

		if (
			Type.isArrayFilled(TopPanel.history)
			&& Type.isNumber(TopPanel.historyState)
			&& (TopPanel.historyState < TopPanel.history.length - 1)
		)
		{
			void SliderHacks.reloadSlider(TopPanel.history[++TopPanel.historyState]);
			TopPanel.checkNavButtonsActivity();
		}
	}
}