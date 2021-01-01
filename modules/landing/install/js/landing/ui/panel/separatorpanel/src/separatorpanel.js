import {Cache, Dom, Type} from 'main.core';
import {Content} from 'landing.ui.panel.content';
import {PageObject} from 'landing.pageobject';
import {Loc} from 'landing.loc';

import solidLinePreview from './images/solid.png';
import dashedLinePreview from './images/dashed.png';
import headerPreview from './images/header.png';
import pagePreview from './images/page.png';
import waveLinePreview from './images/wave.png';

import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Panel
 */
export class SeparatorPanel extends Content
{
	static getInstance(): FormSettingsPanel
	{
		const rootWindow = PageObject.getRootWindow();
		const rootWindowPanel = rootWindow.BX.Landing.UI.Panel.SeparatorPanel;
		if (!rootWindowPanel.instance && !SeparatorPanel.instance)
		{
			rootWindowPanel.instance = new SeparatorPanel();
		}

		return (rootWindowPanel.instance || SeparatorPanel.instance);
	}

	adjustActionsPanels = false;

	constructor()
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Panel.SeparatorPanel');
		this.setLayoutClass('landing-ui-panel-separator');
		this.setOverlayClass('landing-ui-panel-separator-overlay');

		this.setTitle(Loc.getMessage('LANDING_SEPARATOR_PANEL_TITLE'));

		this.cache = new Cache.MemoryCache();
		this.renderTo(this.getViewContainer());

		this.appendCard(
			new BX.Landing.UI.Card.BlockPreviewCard({
				title: Loc.getMessage('LANDING_SEPARATOR_SOLID_LINE'),
				image: solidLinePreview,
				code: 'hr',
				onClick: this.onPreviewClick.bind(this),
			}),
		);

		this.appendCard(
			new BX.Landing.UI.Card.BlockPreviewCard({
				title: Loc.getMessage('LANDING_SEPARATOR_HEADER'),
				image: headerPreview,
				code: 'section',
				onClick: this.onPreviewClick.bind(this),
			}),
		);

		this.appendCard(
			new BX.Landing.UI.Card.BlockPreviewCard({
				title: Loc.getMessage('LANDING_SEPARATOR_PAGE'),
				image: pagePreview,
				code: 'page',
				onClick: this.onPreviewClick.bind(this),
			}),
		);

		[...this.content.children].forEach((item) => {
			Dom.style(item, 'opacity', '1');
		});
	}

	getViewContainer(): HTMLDivElement
	{
		return this.cache.remember('viewContainer', () => {
			const rootWindow = PageObject.getRootWindow();
			return rootWindow.document.querySelector('.landing-ui-view-container');
		});
	}

	show(options): Promise<{type: string}>
	{
		void super.show(options);
		return new Promise((resolve) => {
			this.promiseResolver = resolve;
		});
	}

	onPreviewClick(card)
	{
		void this.hide();

		if (Type.isFunction(this.promiseResolver))
		{
			this.promiseResolver({type: card.code, separatorTitle: card.title});
		}
	}
}