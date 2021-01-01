import {BaseEvent, EventEmitter} from 'main.core.events';
import {Cache, Dom, Tag} from 'main.core';
import {HeaderCard} from 'landing.ui.card.headercard';
import {MessageCard} from 'landing.ui.card.messagecard';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {BaseCollection} from 'landing.collection.basecollection';
import {BaseForm} from 'landing.ui.form.baseform';

export default class ContentWrapper extends EventEmitter
{
	items: BaseCollection<FormSettingsForm | HeaderCard | MessageCard>;

	constructor(options: any)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.ContentWrapper');
		this.options = {...options};
		this.cache = new Cache.MemoryCache();
		this.items = new BaseCollection();
		this.onChange = this.onChange.bind(this);
	}

	addItem(item: FormSettingsForm | HeaderCard | MessageCard)
	{
		if (!this.items.includes(item))
		{
			this.items.add(item);
			item.subscribe('onChange', this.onChange);
		}

		Dom.append(item.getLayout(), this.getLayout());
	}

	insertBefore(current: FormSettingsForm | HeaderCard | MessageCard, target)
	{
		if (!this.items.includes(current))
		{
			this.items.add(current);
			current.subscribe('onChange', this.onChange);
		}

		Dom.insertBefore(current.getLayout(), target.getLayout());
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('wrapper', () => {
			return Tag.render`<div class="landing-ui-panel-form-settings-content-wrapper"></div>`;
		});
	}

	getValue(): {[key: string]: any}
	{
		const value = this.items.reduce((acc, item) => {
			if (item instanceof BaseForm && item.getLayout().parentElement)
			{
				return {...acc, ...item.serialize()};
			}

			return acc;
		}, {});

		return this.valueReducer(value);
	}

	// eslint-disable-next-line class-methods-use-this
	valueReducer(value: {[key: string]: any}): {[key: string]: any}
	{
		return value;
	}

	onChange(event: BaseEvent)
	{
		this.emit('onChange', event.getData());
	}

	clear()
	{
		Dom.clean(this.getLayout());
	}
}