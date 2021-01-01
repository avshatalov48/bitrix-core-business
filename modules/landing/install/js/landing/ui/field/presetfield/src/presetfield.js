import {BaseField} from 'landing.ui.field.basefield';
import {Cache, Dom, Tag} from 'main.core';
import {Loc} from 'landing.loc';

import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Field
 */
export class PresetField extends BaseField
{
	constructor(options = {})
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.PresetField');
		this.subscribeFromOptions(options.events);
		Dom.addClass(this.layout, 'landing-ui-field-preset');
		this.setTitle(Loc.getMessage('LANDING_PRESET_FIELD_TITLE'));

		this.cache = new Cache.MemoryCache();
		this.onLinkClick = this.onLinkClick.bind(this);

		Dom.replace(this.layout, this.getLayout());
		this.layout = this.getLayout();
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-field-preset-layout">
					<div class="landing-ui-field-preset-left">
						${this.getIcon()}
					</div>
					<div class="landing-ui-field-preset-right">
						${this.header}
						${this.getLink()}
					</div>
				</div>
			`;
		});
	}

	getIcon(): HTMLSpanElement
	{
		return this.cache.remember('icon', () => {
			return Tag.render`<span class="landing-ui-field-preset-icon landing-ui-field-preset-icon-default"></span>`;
		});
	}

	setIcon(icon: string)
	{
		Dom.style(this.getIcon(), 'background-image', `url(${icon})`);
	}

	getLink(): HTMLSpanElement
	{
		return this.cache.remember('link', () => {
			return Tag.render`
				<div class="landing-ui-field-preset-link" onclick="${this.onLinkClick}">
					${Loc.getMessage('LANDING_PRESET_DEFAULT_CASE_TITLE')}
				</div>
			`;
		});
	}

	setLinkText(text: string)
	{
		this.getLink().textContent = text;
	}

	onLinkClick(event: MouseEvent)
	{
		event.preventDefault();

		this.emit('onClick');
	}
}