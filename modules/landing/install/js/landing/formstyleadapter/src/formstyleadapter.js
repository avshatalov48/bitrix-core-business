import {Cache, Dom, Reflection, Runtime, Text, Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {StyleForm} from 'landing.ui.form.styleform';
import {Loc} from 'landing.loc';
import {ColorPickerField} from 'landing.ui.field.colorpickerfield';
import {Backend} from 'landing.backend';
import {Env} from 'landing.env';
import {ColorField} from 'landing.ui.field.color';
import {PageObject} from 'landing.pageobject';
import {FormSettingsPanel} from 'landing.ui.panel.formsettingspanel';

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
			const rootWindow = PageObject.getRootWindow();
			return new rootWindow.BX.Landing.UI.Field.Dropdown({
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
			const rootWindow = PageObject.getRootWindow();
			return new rootWindow.BX.Landing.UI.Field.Dropdown({
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
				this.getPrimaryColorField().setValue({
					'--color': FormStyleAdapter.prepareColorFieldValue(theme.color.primary),
				});
				this.getPrimaryTextColorField().setValue({
					'--color': FormStyleAdapter.prepareColorFieldValue(theme.color.primaryText),
				});
				this.getBackgroundColorField().setValue({
					'--color': FormStyleAdapter.prepareColorFieldValue(theme.color.background),
				});
				this.getTextColorField().setValue({
					'--color': FormStyleAdapter.prepareColorFieldValue(theme.color.text),
				});
				this.getFieldBackgroundColorField().setValue({
					'--color': FormStyleAdapter.prepareColorFieldValue(theme.color.fieldBackground),
				});
				this.getFieldFocusBackgroundColorField().setValue({
					'--color': FormStyleAdapter.prepareColorFieldValue(theme.color.fieldFocusBackground),
				});
				this.getFieldBorderColorField().setValue({
					'--color': FormStyleAdapter.prepareColorFieldValue(theme.color.fieldBorder),
				});
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
			const rootWindow = PageObject.getRootWindow();
			return new rootWindow.BX.Landing.UI.Field.Dropdown({
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
			const rootWindow = PageObject.getRootWindow();
			return new rootWindow.BX.Landing.UI.Field.Dropdown({
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

	static prepareColorFieldValue(color: string): string
	{
		return ColorPickerField.toRgba(
			...ColorPickerField.parseHex(color),
		);
	}

	static convertColorFieldValueToHexa(value: string, opacity: string = null): string
	{
		const parsedPrimary = ColorPickerField.parseHex(value);

		if (!Type.isNil(opacity))
		{
			parsedPrimary[3] = opacity;
		}

		return ColorPickerField.toHex(...parsedPrimary);
	}

	getPrimaryColorField(): ColorPickerField
	{
		return this.cache.remember('primaryColorField', () => {
			const rootWindow = PageObject.getRootWindow();
			const field = new rootWindow.BX.Landing.UI.Field.ColorField({
				selector: 'primary',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_PRIMARY_COLOR'),
				subtype: 'color',
			});

			Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));

			field.setValue({
				'--color': FormStyleAdapter.prepareColorFieldValue(this.getFormOptions().data.design.color.primary),
			});

			return field;
		});
	}

	getPrimaryTextColorField(): ColorPickerField
	{
		return this.cache.remember('primaryTextColorField', () => {
			const rootWindow = PageObject.getRootWindow();
			const field = new rootWindow.BX.Landing.UI.Field.ColorField({
				selector: 'primaryText',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_PRIMARY_TEXT_COLOR'),
				subtype: 'color',
			});

			Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));

			field.setValue({
				'--color': FormStyleAdapter.prepareColorFieldValue(this.getFormOptions().data.design.color.primaryText),
			});

			return field;
		});
	}

	getBackgroundColorField(): ColorPickerField
	{
		return this.cache.remember('backgroundColorField', () => {
			const rootWindow = PageObject.getRootWindow();
			const field = new rootWindow.BX.Landing.UI.Field.ColorField({
				selector: 'background',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BACKGROUND_COLOR'),
				subtype: 'color',
			});

			Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));

			field.setValue({
				'--color': FormStyleAdapter.prepareColorFieldValue(this.getFormOptions().data.design.color.background),
			});

			return field;
		});
	}

	getTextColorField(): ColorPickerField
	{
		return this.cache.remember('textColorField', () => {
			const rootWindow = PageObject.getRootWindow();
			const field = new rootWindow.BX.Landing.UI.Field.ColorField({
				selector: 'text',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_TEXT_COLOR'),
				subtype: 'color',
			});

			Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));

			field.setValue({
				'--color': FormStyleAdapter.prepareColorFieldValue(this.getFormOptions().data.design.color.text),
			});

			return field;
		});
	}

	getFieldBackgroundColorField(): ColorPickerField
	{
		return this.cache.remember('fieldBackgroundColorField', () => {
			const rootWindow = PageObject.getRootWindow();
			const field = new rootWindow.BX.Landing.UI.Field.ColorField({
				selector: 'fieldBackground',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FIELD_BACKGROUND_COLOR'),
				subtype: 'color',
			});

			Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));

			field.setValue({
				'--color': FormStyleAdapter.prepareColorFieldValue(this.getFormOptions().data.design.color.fieldBackground),
			});

			return field;
		});
	}

	getFieldFocusBackgroundColorField(): ColorPickerField
	{
		return this.cache.remember('fieldFocusBackgroundColorField', () => {
			const rootWindow = PageObject.getRootWindow();
			const field = new rootWindow.BX.Landing.UI.Field.ColorField({
				selector: 'fieldFocusBackground',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FIELD_FOCUS_BACKGROUND_COLOR'),
				value: this.getFormOptions().data.design.color.fieldFocusBackground,
				subtype: 'color',
			});

			Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));

			field.setValue({
				'--color': FormStyleAdapter.prepareColorFieldValue(this.getFormOptions().data.design.color.fieldFocusBackground),
			});

			return field;
		});
	}

	getFieldBorderColorField(): ColorPickerField
	{
		return this.cache.remember('fieldBorderColorField', () => {
			const rootWindow = PageObject.getRootWindow();
			const field = new rootWindow.BX.Landing.UI.Field.ColorField({
				selector: 'fieldBorder',
				title: Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FIELD_BORDER_COLOR'),
				value: this.getFormOptions().data.design.color.fieldBorder,
				subtype: 'color',
			});

			Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));

			field.setValue({
				'--color': FormStyleAdapter.prepareColorFieldValue(this.getFormOptions().data.design.color.fieldBorder),
			});

			return field;
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

	getStyleForm(collapsed = true): Array<any>
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
				serializeModifier: (value) => {
					value.theme = `${value.theme}-${value.dark}`;
					value.dark = value.dark === 'dark';
					value.shadow = Text.toBoolean(value.shadow);

					value.color = {
						primary: FormStyleAdapter.convertColorFieldValueToHexa(
							value.primary.getHex(),
							value.primary.getOpacity(),
						),
						primaryText: FormStyleAdapter.convertColorFieldValueToHexa(
							value.primaryText.getHex(),
							value.primaryText.getOpacity(),
						),
						text: FormStyleAdapter.convertColorFieldValueToHexa(
							value.text.getHex(),
							value.text.getOpacity(),
						),
						background: FormStyleAdapter.convertColorFieldValueToHexa(
							value.background.getHex(),
							value.background.getOpacity(),
						),
						fieldBackground: FormStyleAdapter.convertColorFieldValueToHexa(
							value.fieldBackground.getHex(),
							value.fieldBackground.getOpacity(),
						),
						fieldFocusBackground: FormStyleAdapter.convertColorFieldValueToHexa(
							value.fieldFocusBackground.getHex(),
							value.fieldFocusBackground.getOpacity(),
						),
						fieldBorder: FormStyleAdapter.convertColorFieldValueToHexa(
							value.fieldBorder.getHex(),
							value.fieldBorder.getOpacity(),
						),
					};

					value.border = {
						left: value.border.includes('left'),
						right: value.border.includes('right'),
						top: value.border.includes('top'),
						bottom: value.border.includes('bottom'),
					};

					if (value.font.family === Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FONT_DEFAULT'))
					{
						value.font.family = '';
						value.font.uri = '';
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
				collapsed: collapsed,
				specialType: 'crm_forms',
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
		this.getCrmForm().design.adjust(mergedOptions.data.design);

		const formSettingsPanel: FormSettingsPanel = FormSettingsPanel.getInstance();
		if (formSettingsPanel.isShown())
		{
			const initialOptions = formSettingsPanel.getInitialFormOptions();
			const currentOptions = formSettingsPanel.getFormOptions();

			initialOptions.data.design = mergedOptions.data.design;
			formSettingsPanel.setInitialFormOptions(initialOptions);

			currentOptions.data.design = mergedOptions.data.design;
			formSettingsPanel.setFormOptions(currentOptions);
		}

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
			)
			.then(BX.Landing.History.getInstance().push());
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