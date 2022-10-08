import 'ui.design-tokens';
import 'ui.fonts.opensans';

import {EventEmitter} from 'main.core.events';
import {Cache, Dom, Tag, Text, Type} from 'main.core';
import type {Options} from 'crm.form';
import {Loc} from 'landing.loc';
import {TextCrop} from 'ui.textcrop';

import './css/preset.css';

type PresetOptions = {
	id: string,
	title: string,
	category: string,
	description?: string,
	icon?: string,
	items?: Array<string>,
	formOptions?: Options,
	disabled?: boolean,
	soon?: boolean,
	defaultSection?: string,
};

const defaultOptions: PresetOptions = {
	disabled: false,
	soon: false,
};

/**
 * @memberOf BX.Landing.UI.Panel.BasePresetPanel
 */
export default class Preset extends EventEmitter
{
	options: PresetOptions;

	constructor(options: PresetOptions)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.BasePresetPanel.Preset');

		this.options = {...defaultOptions, ...options};
		this.cache = new Cache.MemoryCache();
	}

	getTextCrop(): TextCrop
	{
		return this.cache.remember('textCrop', () => {
			return new TextCrop({
				rows: 2,
				target: this.getDescriptionNode(),
			});
		});
	}

	getIconNode(): HTMLDivElement
	{
		return this.cache.remember('iconNode', () => {
			return Tag.render`
				<div
					class="landing-ui-panel-preset-icon"
					style="background-image: url(${this.options.icon}?v2)"
				></div>
			`;
		});
	}

	getTitleNode(): HTMLDivElement
	{
		return this.cache.remember('titleNode', () => {
			return Tag.render`
				<div
					class="landing-ui-panel-preset-text-title"
					title="${Text.encode(this.options.title)}"
				>${this.options.title}</div>
			`;
		});
	}

	getDescriptionNode(): HTMLDivElement
	{
		return this.cache.remember('descriptionNode', () => {
			return Tag.render`
				<div
					class="landing-ui-panel-preset-text-description"
					title="${Text.encode(this.options.description)}"
				>${this.options.description}</div>
			`;
		});
	}

	activate()
	{
		Dom.addClass(this.getLayout(), 'landing-ui-panel-preset-active');
	}

	deactivate()
	{
		Dom.removeClass(this.getLayout(), 'landing-ui-panel-preset-active');
	}

	isActive(): boolean
	{
		return Dom.hasClass(this.getLayout(), 'landing-ui-panel-preset-active');
	}

	getSoonLabel(): HTMLDivElement
	{
		return this.cache.remember('soonLabel', () => {
			return Tag.render`
				<div class="landing-ui-panel-preset-soon-label">
					${Loc.getMessage('LANDING_UI_BASE_PRESET_PANEL_SOON_LABEL')}
				</div>
			`;
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			const onLayoutClick = (event: MouseEvent) => {
				event.preventDefault();
				if (this.options.openable)
				{
					this.activate();
				}

				this.emit('onClick');
			};

			const additionalClass = this.options.active ? ' landing-ui-panel-preset-active' : '';
			const disabledClass = this.options.disabled ? ' landing-ui-disabled' : '';

			return Tag.render`
				<div class="landing-ui-panel-preset${additionalClass}${disabledClass}" onclick="${onLayoutClick}">
					${Type.isStringFilled(this.options.icon) ? this.getIconNode() : ''}
					<div class="landing-ui-panel-preset-text">
						${Type.isStringFilled(this.options.title) ? this.getTitleNode() : ''}
						${Type.isStringFilled(this.options.description) ? this.getDescriptionNode() : ''}
					</div>
					${this.options.soon ? this.getSoonLabel() : ''}
				</div>
			`;
		});
	}
}