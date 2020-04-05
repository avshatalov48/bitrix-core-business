import {Cache, Dom, Runtime, Text} from 'main.core';
import {Loader} from 'main.loader';
import {Content} from 'landing.ui.panel.content';
import {Loc} from 'landing.loc';
import {PageObject} from 'landing.pageobject';
import './css/style.css';

const showPseudoContent = Symbol('showPseudoContent');
const hidePseudoContent = Symbol('hidePseudoContent');
const disableEditorPointerEvents = Symbol('disableEditorPointerEvents');
const enableEditorPointerEvents = Symbol('enableEditorPointerEvents');

/**
 * @memberOf BX.Landing.UI.Panel
 */
export class StylePanel extends Content
{
	shouldAdjustTopPanelControls = false;

	constructor(options = {})
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.StylePanel');

		this.setTitle(Loc.getMessage('LANDING_DESIGN_PANEL_HEADER'));
		this.pseudoContent = Runtime.clone(this.content);
		this.loader = new Loader({
			target: this.pseudoContent,
			offset: {top: '-10%'},
		});
		this.lsCache = new Cache.LocalStorageCache();
		this.cache = new Cache.MemoryCache();
		this.switcher = this.getSwitcher();

		Dom.addClass(this.layout, 'landing-ui-panel-style');
		Dom.addClass(this.overlay, 'landing-ui-panel-style-overlay');
		Dom.attr(this.layout, 'hidden', 'true');

		Dom.clean(this.pseudoContent);
		Dom.style(this.pseudoContent, 'margin-left', '20px');
		Dom.append(this.pseudoContent, this.body);

		Dom.append(this.switcher.layout, this.footer);
		Dom.prepend(this.layout, this.getViewContainer());

		if (window.localStorage)
		{
			const state = window.localStorage.getItem('selectGroup') === 'true';
			this.lsCache.set('selectGroup', state.toString());
		}
	}

	static getInstance(): StylePanel
	{
		const rootWindow = PageObject.getRootWindow();

		if (!rootWindow.BX.Landing.UI.Panel.StylePanel.instance && !StylePanel.instance)
		{
			rootWindow.BX.Landing.UI.Panel.StylePanel.instance = new StylePanel();
		}

		return (rootWindow.BX.Landing.UI.Panel.StylePanel.instance || StylePanel.instance);
	}

	getSwitcher(): BX.Landing.UI.Field.Switch
	{
		return this.cache.remember('switcher', () => {
			return new BX.Landing.UI.Field.Switch({
				title: Loc.getMessage('LANDING_STYLE_PANEL_SELECT_GROUP_SWITCH'),
				onValueChange: () => {
					if (window.localStorage)
					{
						window.localStorage.setItem('selectGroup', this.switcher.getValue().toString());
					}
					this.lsCache.set('selectGroup', this.switcher.getValue().toString());
				},
				value: Text.toBoolean(this.lsCache.get('selectGroup')),
			});
		});
	}

	getViewContainer(): HTMLDivElement
	{
		return this.cache.remember('viewContainer', () => {
			return PageObject.getRootWindow().document.querySelector('.landing-ui-view-container');
		});
	}

	getViewWrapper(): HTMLDivElement
	{
		return this.cache.remember('viewWrapper', () => {
			return this.getViewContainer().querySelector('.landing-ui-view-wrapper');
		});
	}

	[showPseudoContent]()
	{
		Dom.attr(this.content, 'hidden', true);
		Dom.attr(this.pseudoContent, 'hidden', null);
	}

	[hidePseudoContent]()
	{
		Dom.attr(this.content, 'hidden', null);
		Dom.attr(this.pseudoContent, 'hidden', true);
	}

	static [enableEditorPointerEvents]()
	{
		Dom.style(document.body, 'pointer-events', null);
	}

	static [disableEditorPointerEvents]()
	{
		Dom.style(document.body, 'pointer-events', 'none');
	}

	show(): Promise<StylePanel>
	{
		this[showPseudoContent]();
		StylePanel[disableEditorPointerEvents]();

		return super.show()
			.then(() => {
				this.loader.show();

				setTimeout(() => {
					this[hidePseudoContent]();
					StylePanel[enableEditorPointerEvents]();
				}, 300);

				Dom.style(this.getViewWrapper(), 'width', 'calc(100% - 320px)');
				Dom.addClass(document.body, 'landing-ui-collapsed');

				BX.onCustomEvent('BX.Landing.Style:enable', []);
				this.emit('enable', {panel: this});

				return this;
			});
	}

	hide(): Promise<StylePanel>
	{
		StylePanel[disableEditorPointerEvents]();
		Dom.style(this.getViewWrapper(), 'width', null);

		return super.hide()
			.then(() => {
				StylePanel[enableEditorPointerEvents]();
				Dom.addClass(document.body, 'landing-ui-collapsed');

				BX.onCustomEvent('BX.Landing.Style:disable', []);
				this.emit('disable', {panel: this});

				return this;
			});
	}
}