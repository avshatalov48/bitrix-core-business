import 'ui.design-tokens';
import 'ui.fonts.opensans';

import {Cache, Dom, Tag, Text, Type} from 'main.core';
import {BaseForm, BaseFormOptions} from 'landing.ui.form.baseform';
import {SmallSwitch} from 'landing.ui.field.smallswitch';
import {BaseEvent} from 'main.core.events';
import {Link} from 'landing.ui.component.link';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';

import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Form
 */
export class FormSettingsForm extends BaseForm
{
	static ToggleableType = {
		Link: 'link',
		Switch: 'switch',
	};

	constructor(
		options: BaseFormOptions | {
			toggleable: boolean,
			toggleableType: $Values<FormSettingsForm.ToggleableType>,
			opened?: boolean,
		},
	)
	{
		super({opened: true, ...options});
		this.setEventNamespace('BX.Landing.UI.Form.FormSettingsForm');
		this.subscribeFromOptions(fetchEventsFromOptions(options));
		Dom.addClass(this.layout, 'landing-ui-form-form-settings');

		this.onFieldChange = this.onFieldChange.bind(this);
		this.onSwitchChange = this.onSwitchChange.bind(this);
		this.cache = new Cache.MemoryCache();

		if (this.options.toggleable)
		{
			this.onSwitchChange(this.options.opened);

			if (
				!this.options.toggleableType
				|| this.options.toggleableType === FormSettingsForm.ToggleableType.Switch
			)
			{
				this.getSwitch().setValue(this.options.opened);
				Dom.prepend(this.getSwitch().getNode(), this.header);
			}

			if (this.options.toggleableType === FormSettingsForm.ToggleableType.Link)
			{
				Dom.clean(this.header);
				Dom.append(this.getLink().getLayout(), this.header);
			}
		}

		if (Type.isPlainObject(this.options.help))
		{
			Dom.append(this.getHelp(this.options.help), this.footer);
		}
	}

	getHelp(options: {href: string, text: string}): HTMLDivElement
	{
		return this.cache.remember('help', () => {
			return Tag.render`
				<div class="landing-ui-form-help">
					<a href="${options.href}" target="_blank">${options.text}</a>
				</div>
			`;
		});
	}

	addField(field: BaseField)
	{
		if (Type.isFunction(field.subscribe))
		{
			field.subscribe('onChange', this.onFieldChange.bind(this));
		}

		super.addField(field);
	}

	replaceField(oldField, newField) {
		if (Type.isFunction(newField.subscribe))
		{
			newField.subscribe('onChange', this.onFieldChange.bind(this));
		}

		super.replaceField(oldField, newField);
	}

	onFieldChange(event: BaseEvent)
	{
		this.emit('onChange', event.getData());
	}

	getSwitch(): SmallSwitch
	{
		return this.cache.remember('switch', () => {
			const switchField = new SmallSwitch({
				value: this.options.opened,
			});
			switchField.subscribe('onChange', (event: BaseEvent) => {
				this.onSwitchChange(event.getTarget().getValue());
			});
			return switchField;
		});
	}

	getLink(): Link
	{
		return this.cache.remember('link', () => {
			return new Link({
				text: this.options.title,
				color: Link.Colors.Grey,
				onClick: () => {
					this.onSwitchChange(Dom.style(this.body, 'display') === 'none');
				},
			});
		});
	}

	onSwitchChange(state: boolean)
	{
		if (!state)
		{
			this.cache.set('isOpened', false);
			Dom.style(this.body, 'display', 'none');
			Dom.style(this.layout, 'margin-bottom', '20px');
		}
		else
		{
			this.cache.set('isOpened', true);
			Dom.style(this.body, 'display', null);
			Dom.style(this.layout, 'margin-bottom', null);
		}

		this.emit('onChange');
	}

	isOpened(): boolean
	{
		return Text.toBoolean(this.cache.get('isOpened'));
	}

	setOffsetTop(offset: number)
	{
		Dom.style(this.getLayout(), 'margin-top', `${offset}px`);
	}

	clear()
	{
		this.fields.forEach((field) => {
			if (Type.isFunction(field.getLayout))
			{
				Dom.remove(field.getLayout());
			}
			else
			{
				Dom.remove(field.layout);
			}

			field.unsubscribeAll('onChange');
		});

		this.fields.clear();
	}
}