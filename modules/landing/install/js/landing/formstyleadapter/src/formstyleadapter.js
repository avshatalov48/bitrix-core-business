import {Cache, Dom, Reflection, Runtime, Text, Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {StyleForm} from 'landing.ui.form.styleform';
import {Loc} from 'landing.loc';
import {ColorPickerField} from 'landing.ui.field.colorpickerfield';
import {Backend} from 'landing.backend';
import {Env} from 'landing.env';

import themesMap from './internal/themes-map';

/**
 * @memberOf BX.Landing
 */
export class FormStyleAdapter extends EventEmitter
{
	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Landing.FormStyleAdapter');
		this.options = {...options};
		this.cache = new Cache.MemoryCache();

		this.onDebouncedFormChange = Runtime.debounce(this.onDebouncedFormChange, 500);
	}

	setFormOptions(options)
	{
		this.cache.set('formOptions', {...options});
	}

	getFormOptions()
	{
		return this.cache.get('formOptions');
	}

	load(): Promise<FormStyleAdapter>
	{
		if (Text.capitalize(Env.getInstance().getOptions().params.type) === 'SMN')
		{
			this.setFormOptions(
				{data: {design: Runtime.clone(this.getCrmForm().design)}},
			);

			return Promise.resolve(this);
		}

		return Runtime
			.loadExtension('crm.form.client')
			.then(({FormClient}) => {
				if (FormClient)
				{
					return FormClient
						.getInstance()
						.getOptions(this.options.formId)
						.then((result) => {
							this.setFormOptions(
								Runtime.merge(
									Runtime.clone(result),
									{data: {design: Runtime.clone(this.getCrmForm().design)}},
								),
							);
							return this;
						});
				}

				return null;
			});
	}

	getThemeField(): BX.Landing.UI.Field.Dropdown
	{
		return this.cache.remember('themeField', () => {
			const {theme} = this.getFormOptions().data.design;
			return new BX.Landing.UI.Field.Dropdown({
				selector: 'theme',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_THEME_FIELD_TITLE'),
				content: Type.isString(theme) ? theme.split('-')[0] : '',
				onChange: this.onThemeChange.bind(this),
				items: [
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_THEME_FIELD_ITEM_BUSINESS'),
						value: 'business',
					},
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_THEME_FIELD_ITEM_MODERN'),
						value: 'modern',
					},
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_THEME_FIELD_ITEM_CLASSIC'),
						value: 'classic',
					},
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_THEME_FIELD_ITEM_FUN'),
						value: 'fun',
					},
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_THEME_FIELD_ITEM_PIXEL'),
						value: 'pixel',
					},
				],
			});
		});
	}

	getDarkField(): BX.Landing.UI.Field.Dropdown
	{
		return this.cache.remember('darkField', () => {
			const {theme} = this.getFormOptions().data.design;
			return new BX.Landing.UI.Field.Dropdown({
				selector: 'dark',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_DARK_FIELD_TITLE'),
				content: Type.isString(theme) ? theme.split('-')[1] : '',
				onChange: this.onThemeChange.bind(this),
				items: [
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_DARK_FIELD_ITEM_LIGHT'),
						value: 'light',
					},
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_DARK_FIELD_ITEM_DARK'),
						value: 'dark',
					},
				],
			});
		});
	}

	onThemeChange()
	{
		const themeId = this.getStyleForm().serialize().theme;
		const theme = themesMap.get(themeId);

		if (theme)
		{
			if (Type.isPlainObject(theme.color))
			{
				this.getPrimaryColorField().setValue(theme.color.primary, true);
				this.getPrimaryTextColorField().setValue(theme.color.primaryText, true);
				this.getBackgroundColorField().setValue(theme.color.background);
				this.getTextColorField().setValue(theme.color.text, true);
				this.getFieldBackgroundColorField().setValue(theme.color.fieldBackground, true);
				this.getFieldFocusBackgroundColorField().setValue(theme.color.fieldFocusBackground, true);
				this.getFieldBorderColorField().setValue(theme.color.fieldBorder);
			}

			this.getStyleField().setValue(theme.style);

			if (Type.isBoolean(theme.shadow))
			{
				this.getShadowField().setValue(theme.shadow);
			}

			if (Type.isPlainObject(theme.font))
			{
				const font = {...theme.font};
				if (!Type.isStringFilled(font.family))
				{
					font.family = Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FONT_DEFAULT');
				}

				this.getFontField().setValue(font);
			}

			if (Type.isPlainObject(theme.border))
			{
				const borders = Object.entries(theme.border).reduce((acc, [key, value]) => {
					if (value)
					{
						acc.push(key);
					}

					return acc;
				}, []);

				this.getBorderField().setValue(borders);
			}
		}
	}

	getShadowField(): BX.Landing.UI.Field.Dropdown
	{
		return this.cache.remember('shadow', () => {
			return new BX.Landing.UI.Field.Dropdown({
				selector: 'shadow',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_SHADOW'),
				content: this.getFormOptions().data.design.shadow,
				items: [
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_SHADOW_USE'),
						value: true,
					},
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_SHADOW_NOT_USE'),
						value: false,
					},
				],
			});
		});
	}

	getStyleField()
	{
		return this.cache.remember('styleField', () => {
			return new BX.Landing.UI.Field.Dropdown({
				selector: 'style',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_STYLE_FIELD_TITLE'),
				content: this.getFormOptions().data.design.style,
				items: [
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_STYLE_FIELD_ITEM_STANDARD'),
						value: '',
					},
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_STYLE_FIELD_ITEM_MODERN'),
						value: 'modern',
					},
				],
			});
		});
	}

	getPrimaryColorField(): ColorPickerField
	{
		return this.cache.remember('primaryColorField', () => {
			return new ColorPickerField({
				selector: 'primary',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_PRIMARY_COLOR'),
				value: this.getFormOptions().data.design.color.primary,
			});
		});
	}

	getPrimaryTextColorField(): ColorPickerField
	{
		return this.cache.remember('primaryTextColorField', () => {
			return new ColorPickerField({
				selector: 'primaryText',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_PRIMARY_TEXT_COLOR'),
				value: this.getFormOptions().data.design.color.primaryText,
			});
		});
	}

	getBackgroundColorField(): ColorPickerField
	{
		return this.cache.remember('backgroundColorField', () => {
			return new ColorPickerField({
				selector: 'background',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BACKGROUND_COLOR'),
				value: this.getFormOptions().data.design.color.background,
			});
		});
	}

	getTextColorField(): ColorPickerField
	{
		return this.cache.remember('textColorField', () => {
			return new ColorPickerField({
				selector: 'text',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_TEXT_COLOR'),
				value: this.getFormOptions().data.design.color.text,
			});
		});
	}

	getFieldBackgroundColorField(): ColorPickerField
	{
		return this.cache.remember('fieldBackgroundColorField', () => {
			return new ColorPickerField({
				selector: 'fieldBackground',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FIELD_BACKGROUND_COLOR'),
				value: this.getFormOptions().data.design.color.fieldBackground,
			});
		});
	}

	getFieldFocusBackgroundColorField(): ColorPickerField
	{
		return this.cache.remember('fieldFocusBackgroundColorField', () => {
			return new ColorPickerField({
				selector: 'fieldFocusBackground',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FIELD_FOCUS_BACKGROUND_COLOR'),
				value: this.getFormOptions().data.design.color.fieldFocusBackground,
			});
		});
	}

	getFieldBorderColorField(): ColorPickerField
	{
		return this.cache.remember('fieldBorderColorField', () => {
			return new ColorPickerField({
				selector: 'fieldBorder',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FIELD_BORDER_COLOR'),
				value: this.getFormOptions().data.design.color.fieldBorder,
			});
		});
	}

	getFontField()
	{
		return this.cache.remember('fontField', () => {
			const value = {...this.getFormOptions().data.design.font};
			if (!Type.isStringFilled(value.family))
			{
				value.family = Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FONT_DEFAULT');
			}

			return new BX.Landing.UI.Field.Font({
				selector: 'font',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FONT'),
				headlessMode: true,
				value,
			});
		});
	}

	getBorderField()
	{
		return this.cache.remember('borderField', () => {
			return new BX.Landing.UI.Field.Checkbox({
				selector: 'border',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BORDER'),
				value: (() => {
					const {border} = this.getFormOptions().data.design;
					return Object.entries(border).reduce((acc, [key, value]) => {
						if (value)
						{
							acc.push(key);
						}

						return acc;
					}, []);
				})(),
				items: [
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BORDER_LEFT'),
						value: 'left',
					},
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BORDER_RIGHT'),
						value: 'right',
					},
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BORDER_TOP'),
						value: 'top',
					},
					{
						name: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BORDER_BOTTOM'),
						value: 'bottom',
					},
				],
			});
		});
	}

	getStyleForm(): Array<any>
	{
		return this.cache.remember('styleForm', () => {
			return new StyleForm({
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FORM_TITLE'),
				fields: [
					this.getThemeField(),
					this.getDarkField(),
					this.getStyleField(),
					this.getShadowField(),
					this.getPrimaryColorField(),
					this.getPrimaryTextColorField(),
					this.getBackgroundColorField(),
					this.getTextColorField(),
					this.getFieldBackgroundColorField(),
					this.getFieldFocusBackgroundColorField(),
					this.getFieldBorderColorField(),
					this.getFontField(),
					this.getBorderField(),
				],
				onChange: Runtime.throttle(this.onFormChange.bind(this), 16),
				serializeModifier(value) {
					value.theme = `${value.theme}-${value.dark}`;
					value.dark = value.dark === 'dark';
					value.shadow = Text.toBoolean(value.shadow);

					value.color = {
						primary: value.primary,
						primaryText: value.primaryText,
						text: value.text,
						background: value.background,
						fieldBackground: value.fieldBackground,
						fieldFocusBackground: value.fieldFocusBackground,
						fieldBorder: value.fieldBorder,
					};

					value.border = {
						left: value.border.includes('left'),
						right: value.border.includes('right'),
						top: value.border.includes('top'),
						bottom: value.border.includes('bottom'),
					};

					if (value.font.family === Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FONT_DEFAULT'))
					{
						delete value.font;
					}

					delete value.primary;
					delete value.primaryText;
					delete value.text;
					delete value.background;
					delete value.fieldBackground;
					delete value.fieldFocusBackground;
					delete value.fieldBorder;

					return value;
				},
			});
		});
	}

	getCrmForm()
	{
		const formApp = Reflection.getClass('b24form.App');
		if (formApp)
		{
			if (this.options.instanceId)
			{
				return formApp.get(this.options.instanceId);
			}

			return formApp.list()[0];
		}

		return null;
	}

	onFormChange(event: BaseEvent)
	{
		const currentFormOptions = this.getFormOptions();
		const designOptions = {
			data: {
				design: event.getTarget().serialize(),
			},
		};
		const mergedOptions = Runtime.merge(currentFormOptions, designOptions);
		this.setFormOptions(mergedOptions);
		this.getCrmForm().adjust(mergedOptions.data);

		this.onDebouncedFormChange();
	}

	// eslint-disable-next-line class-methods-use-this
	isCrmFormPage(): boolean
	{
		return Env.getInstance().getOptions().specialType === 'crm_forms';
	}

	saveFormDesign()
	{
		return Runtime
			.loadExtension('crm.form.client')
			.then(({FormClient}) => {
				if (FormClient)
				{
					const formClient = FormClient.getInstance();
					const formOptions = this.getFormOptions();

					formClient.resetCache(formOptions.id);

					return formClient.saveOptions(formOptions);
				}

				return null;
			});
	}

	saveBlockDesign()
	{
		const {currentBlock} = this.options;
		const {design} = this.getFormOptions().data;

		const formNode = currentBlock.node.querySelector('.bitrix24forms');
		Dom.attr(formNode, {
			'data-b24form-design': design,
			'data-b24form-use-style': 'Y',
		});

		Runtime
			.loadExtension('crm.form.client')
			.then(({FormClient}) => {
				if (FormClient)
				{
					const formClient = FormClient.getInstance();
					const formOptions = this.getFormOptions();
					formClient.resetCache(formOptions.id);
				}
			});

		Backend
			.getInstance()
			.action(
				'Landing\\Block::updateNodes',
				{
					block: currentBlock.id,
					data: {
						'.bitrix24forms': {
							attrs: {
								'data-b24form-design': JSON.stringify(design),
								'data-b24form-use-style': 'Y',
							},
						},
					},
					lid: currentBlock.lid,
					siteId: currentBlock.siteId,
				},
				{code: currentBlock.manifest.code},
			);
	}

	onDebouncedFormChange()
	{
		if (this.isCrmFormPage())
		{
			Runtime
				.loadExtension('landing.ui.panel.formsettingspanel')
				.then(({FormSettingsPanel}) => {
					const formSettingsPanel = FormSettingsPanel.getInstance();
					formSettingsPanel.setCurrentBlock(this.options.currentBlock);

					void this.saveFormDesign();

					if (formSettingsPanel.useBlockDesign())
					{
						formSettingsPanel.disableUseBlockDesign();
					}
				});
		}
		else
		{
			this.saveBlockDesign();
		}
	}
}