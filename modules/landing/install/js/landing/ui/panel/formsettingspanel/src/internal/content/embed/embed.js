import {Dom} from 'main.core';
import {Loc} from 'landing.loc';
import {HeaderCard} from 'landing.ui.card.headercard';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {RadioButtonField} from 'landing.ui.field.radiobuttonfield';
import {BaseEvent} from 'main.core.events';
import {PresetField} from 'landing.ui.field.presetfield';
import {MessageCard} from 'landing.ui.card.messagecard';
import {VariablesField} from 'landing.ui.field.variablesfield';

import {EmbedField} from './internal/embedfield/embedfield';
import WidgetField from './internal/widgetfield/widgetfield';
import CopyLinkField from './internal/copylinkfield/copylinkfield';
import {PositionField} from './internal/positionfield/positionfield';

import type1icon from './images/type1.svg';
import type2icon from './images/type2.svg';
import type3icon from './images/type3.svg';
import type4icon from './images/type4.svg';
import type5icon from './images/type5.svg';
import type6icon from './images/type6.svg';

import './css/style.css';

export default class EmbedContent extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.EmbedContent');

		Dom.addClass(this.getLayout(), 'landing-ui-embed-content-wrapper');

		this.addItem(this.getHeader());
		this.addItem(this.getTypeButtons());
	}

	getHeader(): HeaderCard
	{
		return this.cache.remember('header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_EMBED_TITLE'),
			});
		});
	}

	getTypeButtons(): RadioButtonField
	{
		return this.cache.remember('typeButtons', () => {
			return new RadioButtonField({
				title: Loc.getMessage('LANDING_FORM_EMBED_TYPE_FIELD_TITLE'),
				items: [1, 3, 4, 2, 5, 6].map((item) => {
					return {
						id: `type${item}`,
						title: Loc.getMessage(`LANDING_FORM_EMBED_TYPE_${item}`),
						icon: `landing-ui-form-embed-type${item}`,
						soon: [2, 5, 6].includes(item),
						disabled: [2, 5, 6].includes(item),
					};
				}),
				onChange: this.onTypeChange.bind(this),
				selectable: false,
			});
		});
	}

	getTypeDropdown(): PresetField
	{
		return this.cache.remember('typeDropdown', () => {
			const field = new PresetField({
				events: {
					onClick: () => {
						this.clear();
						this.addItem(this.getHeader());
						this.addItem(this.getTypeButtons());
					},
				},
			});
			field.setTitle(Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_DROPDOWN_TITLE'));

			return field;
		});
	}

	getType1Message(): MessageCard
	{
		return this.cache.remember('type1Message', () => {
			return new MessageCard({
				header: Loc.getMessage('LANDING_FORM_EMBED_TYPE_1_MESSAGE_TITLE'),
				description: Loc.getMessage('LANDING_FORM_EMBED_TYPE_1_MESSAGE_TEXT'),
				angle: false,
				closeable: false,
				hideActions: true,
			});
		});
	}

	getType3Message(): MessageCard
	{
		return this.cache.remember('type3Message', () => {
			return new MessageCard({
				header: Loc.getMessage('LANDING_FORM_EMBED_TYPE_3_MESSAGE_TITLE'),
				description: Loc.getMessage('LANDING_FORM_EMBED_TYPE_3_MESSAGE_TEXT'),
				angle: false,
				closeable: false,
				hideActions: true,
			});
		});
	}

	getType4Message(): MessageCard
	{
		return this.cache.remember('type4Message', () => {
			return new MessageCard({
				header: Loc.getMessage('LANDING_FORM_EMBED_TYPE_4_MESSAGE_TITLE'),
				description: Loc.getMessage('LANDING_FORM_EMBED_TYPE_4_MESSAGE_TEXT'),
				angle: false,
				closeable: false,
				hideActions: true,
			});
		});
	}

	getType5Message(): MessageCard
	{
		return this.cache.remember('type5Message', () => {
			return new MessageCard({
				header: Loc.getMessage('LANDING_FORM_EMBED_TYPE_4_MESSAGE_TITLE'),
				description: Loc.getMessage('LANDING_FORM_EMBED_TYPE_4_MESSAGE_TEXT'),
				angle: false,
				closeable: false,
				hideActions: true,
			});
		});
	}

	getType8Message(): MessageCard
	{
		return this.cache.remember('type8Message', () => {
			return new MessageCard({
				header: Loc.getMessage('LANDING_FORM_EMBED_TYPE_8_MESSAGE_TITLE'),
				description: Loc.getMessage('LANDING_FORM_EMBED_TYPE_8_MESSAGE_TEXT'),
				angle: false,
				closeable: false,
				hideActions: true,
			});
		});
	}

	getEmbedField(): EmbedField
	{
		return this.cache.remember('embedField', () => {
			return new EmbedField({

			});
		});
	}

	getType2Header(): HeaderCard
	{
		return this.cache.remember('type2header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_EMBED_LINK_SETTINGS_HEADER'),
				level: 2,
			});
		});
	}

	getType3Header(): HeaderCard
	{
		return this.cache.remember('type3header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_EMBED_CLICK_SETTINGS_HEADER'),
				level: 2,
				description: Loc.getMessage('LANDING_FORM_EMBED_SHOW_SETTINGS_DESCRIPTION'),
			});
		});
	}

	getType4Header(): HeaderCard
	{
		return this.cache.remember('type4header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_EMBED_AUTO_SHOW_SETTINGS_HEADER'),
				level: 2,
				description: Loc.getMessage('LANDING_FORM_EMBED_SHOW_SETTINGS_DESCRIPTION'),
			});
		});
	}

	getLinkTextField()
	{
		return this.cache.remember('linkTextField', () => {
			return new VariablesField({
				title: Loc.getMessage('LANDING_FORM_EMBED_LINK_TEXT_SETTINGS_FIELD_TITLE'),
				variables: [
					{name: 'Test', value: 'test'},
				],
			});
		});
	}

	getType4Checkbox(): BX.Landing.UI.Field.Radio
	{
		return this.cache.remember('type4checkbox', () => {
			const field = new BX.Landing.UI.Field.Radio({
				items: [
					{
						name: Loc.getMessage('LANDING_FORM_EMBED_SHOW_AFTER_PAGE_LOADED'),
						value: 'pageLoaded',
					},
					{
						html: Loc.getMessage('LANDING_FORM_EMBED_SHOW_AFTER_TIME'),
						value: 'afterTime',
					},
					// {
					// 	name: Loc.getMessage('LANDING_FORM_EMBED_SHOW_AFTER_SCROLL_TO_ANCHOR'),
					// 	value: 'scrollToAnchor',
					// },
				],
				value: (() => {
					if (this.options.values.views.auto.delay > 0)
					{
						return ['afterTime'];
					}

					return ['pageLoaded'];
				})(),
			});

			Dom.replace(
				field.layout.querySelector('.delay_time'),
				this.getDelayDropdown().getLayout(),
			);

			return field;
		});
	}

	getWidgetField(): WidgetField
	{
		return this.cache.remember('widgetField', () => {
			return new WidgetField({
				title: Loc.getMessage('LANDING_FORM_EMBED_WIDGET_FIELD_TITLE'),
				placeholder: Loc.getMessage('LANDING_FORM_EMBED_WIDGET_FIELD_PLACEHOLDER'),
			});
		});
	}

	getCopyLinkField(): CopyLinkField
	{
		return this.cache.remember('copyLinkField', () => {
			return new CopyLinkField({
				link: 'https://bitrix24.io/pub/my/form/link',
			});
		});
	}

	getDelayDropdown(): BX.Landing.UI.Field.DropdownInline
	{
		return this.cache.remember('delayDropdown', () => {
			return new BX.Landing.UI.Field.DropdownInline({
				selector: 'showDelay',
				content: this.options.values.views.auto.delay,
				items: [
					{
						name: '5c',
						value: '5',
					},
					{
						name: '10c',
						value: '10',
					},
					{
						name: '15c',
						value: '15',
					},
				],
				onChange: this.onChange.bind(this),
				skipInitialEvent: true,
			});
		});
	}

	getType3PositionField()
	{
		return this.cache.remember('type3PositionField', () => {
			return new PositionField({
				title: Loc.getMessage('LANDING_FORM_EMBED_POSITION_FIELD_TITLE'),
				value: {
					vertical: this.options.values.views.click.vertical,
					horizontal: this.options.values.views.click.position,
				},
			});
		});
	}

	getType4PositionField()
	{
		return this.cache.remember('type4PositionField', () => {
			return new PositionField({
				title: Loc.getMessage('LANDING_FORM_EMBED_POSITION_FIELD_TITLE'),
				value: {
					vertical: this.options.values.views.auto.vertical,
					horizontal: this.options.values.views.auto.position,
				},
			});
		});
	}

	getType3ShowTypeField()
	{
		return this.cache.remember('type3ShowTypeField', () => {
			return new BX.Landing.UI.Field.Dropdown({
				title: Loc.getMessage('LANDING_FORM_EMBED_SHOW_TYPE'),
				selector: 'type',
				items: [
					{name: Loc.getMessage('LANDING_FORM_EMBED_SHOW_POPUP'), value: 'popup'},
					{name: Loc.getMessage('LANDING_FORM_EMBED_SHOW_SLIDER'), value: 'panel'},
				],
				content: this.options.values.views.click.type,
			});
		});
	}

	getType4ShowTypeField()
	{
		return this.cache.remember('type4ShowTypeField', () => {
			return new BX.Landing.UI.Field.Dropdown({
				title: Loc.getMessage('LANDING_FORM_EMBED_SHOW_TYPE'),
				selector: 'type',
				items: [
					{name: Loc.getMessage('LANDING_FORM_EMBED_SHOW_POPUP'), value: 'popup'},
					{name: Loc.getMessage('LANDING_FORM_EMBED_SHOW_SLIDER'), value: 'panel'},
				],
				content: this.options.values.views.auto.type,
			});
		});
	}

	onTypeChange(event: BaseEvent)
	{
		const data = event.getData();
		const typeDropdown = this.getTypeDropdown();

		if (/type[1-6]$/.test(data.item.id))
		{
			this.clear();
			this.addItem(this.getHeader());
			this.addItem(typeDropdown);
		}

		typeDropdown.setLinkText(data.item.title.replace(/&nbsp;/, ' '));

		const embedField = this.getEmbedField();

		if (data.item.id === 'type1')
		{
			typeDropdown.setIcon(type1icon);
			this.addItem(this.getType1Message());

			embedField.setValue(this.options.values.scripts.inline.text);
			this.addItem(embedField);
		}

		if (data.item.id === 'type2')
		{
			typeDropdown.setIcon(type2icon);
			// this.addItem(this.getType2Header());
			// this.addItem(this.getLinkTextField());

			embedField.setValue(this.options.values.scripts.click.text);
			this.addItem(embedField);
		}

		if (data.item.id === 'type3')
		{
			typeDropdown.setIcon(type3icon);
			// this.addItem(this.getLinkTextField());

			embedField.setValue(this.options.values.scripts.click.text);
			const positionField = this.getType3PositionField();

			positionField.setValue({
				vertical: this.options.values.views.click.vertical,
				horizontal: this.options.values.views.click.position,
			});

			this.addItem(this.getType3Message());
			this.addItem(this.getType3Header());
			this.addItem(positionField);
			this.addItem(this.getType3ShowTypeField());
			this.addItem(embedField);
		}

		if (data.item.id === 'type4')
		{
			typeDropdown.setIcon(type4icon);
			this.addItem(this.getType4Message());
			this.addItem(this.getType4Header());
			this.addItem(this.getType4Checkbox());

			const positionField = this.getType4PositionField();
			positionField.setValue({
				vertical: this.options.values.views.auto.vertical,
				horizontal: this.options.values.views.auto.position,
			});
			this.addItem(positionField);
			this.addItem(this.getType4ShowTypeField());

			embedField.setValue(this.options.values.scripts.auto.text);
			this.addItem(embedField);
		}

		if (data.item.id === 'type5')
		{
			typeDropdown.setIcon(type5icon);
			this.addItem(this.getType5Message());
			this.addItem(this.getWidgetField());

			embedField.setValue(this.options.values.scripts.auto.text);
			this.addItem(embedField);
		}

		if (data.item.id === 'type6')
		{
			typeDropdown.setIcon(type6icon);
			this.addItem(this.getCopyLinkField());
		}
	}

	onChange(event: BaseEvent)
	{
		this.emit('onChange', {skipPrepare: true});
	}

	getValue(): {[p: string]: any}
	{
		const type3positionValue = this.getType3PositionField().getValue();
		const type4positionValue = this.getType4PositionField().getValue();

		return {
			embedding: {
				views: {
					auto: {
						delay: (() => {
							if (this.getType4Checkbox().getValue().includes('pageLoaded'))
							{
								return 0;
							}

							return this.getDelayDropdown().getValue();
						})(),
						position: type4positionValue.horizontal,
						vertical: type4positionValue.vertical,
						type: this.getType4ShowTypeField().getValue(),
					},
					click: {
						position: type3positionValue.horizontal,
						vertical: type3positionValue.vertical,
						type: this.getType3ShowTypeField().getValue(),
					},
				},
			},
		};
	}
}