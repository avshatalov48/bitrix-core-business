import { Dom, Event, Tag } from 'main.core';
import { Control } from './internal/control';
import { BaseEvent, EventEmitter } from 'main.core.events';
import 'ui.design-tokens';

import './css/style.css';
import 'main.polyfill.intersectionobserver';

type Controls = {
	theme: {
		use: ?Control,
		baseColors: ?Control,
		corporateColor: ?Control,
	},
	typo: {
		use: ?Control,
		textColor: ?Control,
		textSize: ?Control,
		textFont: ?Control,
		textWeight: ?Control,
		textLineHeight: ?Control,
		hColor: ?Control,
		hFont: ?Control,
		hWeight: ?Control,
	},
	background: {
		use: ?Control,
		useSite: ?Control,
		field: ?Control,
		image: ?Control,
		position: ?Control,
		color: ?Control,
	},
}

export class DesignPreview extends EventEmitter
{
	static DEFAULT_FONT_SIZE = 14;
	static HEIGHT_PAGE_TITLE_WRAP = 74;

	controls: Controls;

	constructor(form: HTMLElement, options: Object = {}, phrase: Object = {}, id = null, type = null)
	{
		super();
		this.setEventNamespace('BX.Landing.SettingsForm.DesignPreview');

		this.form = form;
		this.phrase = phrase;
		this.id = id;
		this.options = options;
		this.type = type;
		window.fontsProxyUrl = window.fontsProxyUrl ?? 'fonts.googleapis.com';

		this.initControls();
		this.initLayout();
		this.applyStyles();
		this.onApplyStyles = this.applyStyles.bind(this);
	}

	initLayout()
	{
		this.createLayout();
		this.styleNode = document.createElement('style');
		Dom.append(this.styleNode, this.layout);
		Dom.append(this.layout, this.form);

		const paramsObserver = {
			threshold: 1,
		};
		const observer = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				const availableHeight = document.documentElement.clientHeight - DesignPreview.HEIGHT_PAGE_TITLE_WRAP;
				if (entry.target.getBoundingClientRect().height <= availableHeight)
				{
					this.toggleIntersectionState(entry);
				}
			});
		}, paramsObserver);
		observer.observe(this.layoutContent.parentNode);
	}

	toggleIntersectionState(entry)
	{
		if (entry.isIntersecting)
		{
			if (!('defaultIntersecting' in this))
			{
				this.defaultIntersecting = true;
			}

			if (this.defaultIntersecting)
			{
				this.unFixElement();
			}
		}
		else
		{
			if (!('defaultIntersecting' in this))
			{
				this.defaultIntersecting = false;
			}

			if (this.defaultIntersecting)
			{
				this.fixElement();
			}
		}
	}

	initControls()
	{
		this.controls = {};
		this.initOptions();

		// parents and default
		const controlsKeys = Object.keys(this.controls);
		for (const group of controlsKeys)
		{
			if (!(group in this.controls))
			{
				continue;
			}

			const keys = Object.keys(this.controls[group]);
			for (const key of keys)
			{
				if (!(key in this.controls[group]))
				{
					continue;
				}

				if (key !== 'use' && this.controls[group].use)
				{
					this.setupControls(group, key);
				}
			}
		}

		this.initSubscribes();

		this.panel = BX.Landing.UI.Panel.GoogleFonts.getInstance();
		Dom.append(this.panel.layout, document.body);

		this.setupFontFields();
	}

	initOptions()
	{
		const optionKeys = Object.keys(this.options);
		for (const group of optionKeys)
		{
			if (!(group in this.options))
			{
				continue;
			}

			const groupKeys = Object.keys(this.options[group]);
			for (const key of groupKeys)
			{
				if (!(key in this.options[group]))
				{
					continue;
				}

				if (!this.controls[group])
				{
					this.controls[group] = {};
				}

				const control = new Control(this.options[group][key].control);
				this.initControlHandlers(control, group, key);

				this.controls[group][key] = control;
			}
		}
	}

	initControlHandlers(control, group, key)
	{
		control.setChangeHandler(this.applyStyles.bind(this));
		if (control.node && key === 'use')
		{
			Event.bind(control.node.parentNode, 'click', this.onCheckboxClick.bind(this, group));
		}

		if (group === 'theme' && key !== 'use')
		{
			control.setClickHandler(this.applyStyles.bind(this));
		}

		if (group === 'background' && key === 'field')
		{
			control.setClickHandler(this.applyStyles.bind(this));
		}
	}

	setupControls(group, key)
	{
		this.controls[group][key].setParent(this.controls[group].use);
		if (this.options[group][key].defaultValue)
		{
			this.controls[group][key].setDefaultValue(this.options[group][key].defaultValue);
		}
	}

	initSubscribes()
	{
		if (this.controls.theme.corporateColor.node)
		{
			this.controls.theme.corporateColor.node.subscribe('onSelectCustomColor', this.applyStyles.bind(this));
		}

		if (this.controls.background.image.node)
		{
			this.controls.background.image.node.subscribe('change', this.onApplyStyles.bind(this));
		}

		if (this.controls.typo.textColor.node)
		{
			EventEmitter.subscribe(this.controls.typo.textColor.node, 'BX.Landing.ColorPicker:onSelectColor', this.onApplyStyles.bind(this));
			EventEmitter.subscribe(this.controls.typo.textColor.node, 'BX.Landing.ColorPicker:onClearColorPicker', this.onApplyStyles.bind(this));
		}

		if (this.controls.typo.hColor.node)
		{
			EventEmitter.subscribe(this.controls.typo.hColor.node, 'BX.Landing.ColorPicker:onSelectColor', this.onApplyStyles.bind(this));
			EventEmitter.subscribe(this.controls.typo.hColor.node, 'BX.Landing.ColorPicker:onClearColorPicker', this.onApplyStyles.bind(this));
		}

		if (this.controls.background.color.node)
		{
			EventEmitter.subscribe(this.controls.background.color.node, 'BX.Landing.ColorPicker:onSelectColor', this.onApplyStyles.bind(this));
			EventEmitter.subscribe(this.controls.background.color.node, 'BX.Landing.ColorPicker:onClearColorPicker', this.onApplyStyles.bind(this));
		}
	}

	setupFontFields()
	{
		if (this.controls.typo.textFont.node && this.controls.typo.hFont.node)
		{
			this.controls.typo.textFont.node.setAttribute(
				'value',
				this.convertFont(this.controls.typo.textFont.node.value),
			);
			this.controls.typo.hFont.node.setAttribute(
				'value',
				this.convertFont(this.controls.typo.hFont.node.value),
			);
			Event.bind(this.controls.typo.textFont.node, 'click', this.onCodeClick.bind(this));
			Event.bind(this.controls.typo.hFont.node, 'click', this.onCodeClick.bind(this));
		}
	}

	onCodeClick(event: BaseEvent)
	{
		this.panel.show({
			hideOverlay: true,
			context: window,
		}).then((font) => {
			const element = event.target;
			element.setAttribute('value', font.family);
			this.onApplyStyles();
		}).catch((error) => {
			console.error(error);
		});
	}

	onApplyStyles()
	{
		this.applyStyles();
	}

	applyStyles()
	{
		this.styleNode.innerHTML = this.generateCss();
		setTimeout(() => {
			let layoutHeight = parseInt(window.getComputedStyle(this.layoutContent.parentNode).height, 10);
			const formHeight = parseInt(window.getComputedStyle(this.form).height, 10);
			if (layoutHeight > formHeight)
			{
				layoutHeight += 20;
				BX.Dom.style(this.form, 'min-height', `${layoutHeight}px`);
				const formSection = this.form.querySelector('.ui-form-section');
				if (formSection)
				{
					BX.Dom.style(formSection, 'min-height', `${layoutHeight}px`);
				}
			}
		}, 1000);
	}

	onCheckboxClick(group)
	{
		this.controls[group].use.node.check = !this.controls[group].use.node.checked;
		this.applyStyles();
	}

	generateSelectorStart(className): string
	{
		return `#${className} {`;
	}

	generateSelectorEnd(selector): string
	{
		return `${selector} }`;
	}

	getCSSPart1(css): string
	{
		let colorPrimary = '';
		const setColors = this.controls.theme.baseColors.node;
		let colorPickerElement = '';
		if (this.controls.theme.corporateColor.node)
		{
			colorPickerElement = this.controls.theme.corporateColor.node.element;
		}

		let activeColorNode = '';
		if (setColors)
		{
			activeColorNode = setColors.querySelector('.active');
		}
		let isActiveColorPickerElement = '';
		if (colorPickerElement)
		{
			isActiveColorPickerElement = Dom.hasClass(colorPickerElement, 'active');
		}

		if (activeColorNode)
		{
			colorPrimary = activeColorNode.dataset.value;
		}

		if (isActiveColorPickerElement)
		{
			colorPrimary = colorPickerElement.dataset.value;
		}

		// for "design page", if you use the unchecked box, use the color from "design site"
		if (this.controls.theme.use.node && this.controls.theme.use.node.check === false)
		{
			colorPrimary = this.controls.theme.corporateColor.defaultValue;
		}

		let preparedCss = css;
		if (colorPrimary)
		{
			if (colorPrimary[0] !== '#')
			{
				colorPrimary = `#${colorPrimary}`;
			}
			preparedCss += `--design-preview-primary: ${colorPrimary};`;
		}

		return preparedCss;
	}

	getCSSPart2(css): string
	{
		let textColor = this.getControlValue(
			this.controls.typo.textColor.node,
			this.controls.typo.textColor.node.input.value,
		);
		let textFont = this.getControlValue(
			this.controls.typo.textFont.node,
			this.controls.typo.textFont.node.value,
		);
		let hFont = this.getControlValue(
			this.controls.typo.hFont.node,
			this.controls.typo.hFont.node.value,
		);
		let fontWeight = this.getControlValue(
			this.controls.typo.textWeight.node,
			this.controls.typo.textWeight.node.value,
		);
		let fontLineHeight = this.getControlValue(
			this.controls.typo.textLineHeight.node,
			this.controls.typo.textLineHeight.node.value,
		);
		let hColor = this.getControlValue(
			this.controls.typo.hColor.node,
			this.controls.typo.hColor.node.input.value,
		);
		let hWeight = this.getControlValue(
			this.controls.typo.hWeight.node,
			this.controls.typo.hWeight.node.value,
		);

		let textSize = '';
		if (this.controls.typo.textSize.node)
		{
			textSize = `${Math.round(this.controls.typo.textSize.node.value * DesignPreview.DEFAULT_FONT_SIZE)}px`;
		}

		if (this.controls.typo.use.node && this.controls.typo.use.node.check === false)
		{
			textColor = this.controls.typo.textColor.defaultValue;
			textFont = this.controls.typo.textFont.defaultValue;
			hFont = this.controls.typo.hFont.defaultValue;
			textSize = `${Math.round(this.controls.typo.textSize.defaultValue
					* DesignPreview.DEFAULT_FONT_SIZE)}px`;
			fontWeight = this.controls.typo.textWeight.defaultValue;
			fontLineHeight = this.controls.typo.textLineHeight.defaultValue;
			hColor = this.controls.typo.hColor.defaultValue;
			hWeight = this.controls.typo.hWeight.defaultValue;
		}

		this.appendFontLinks(textFont);
		this.appendFontLinks(hFont);

		let preparedCss = css;
		preparedCss += `--design-preview-color: ${textColor};`;
		preparedCss += `--design-preview-font-theme: ${textFont};`;
		preparedCss += `--design-preview-font-size: ${textSize};`;
		preparedCss += `--design-preview-font-weight: ${fontWeight};`;
		preparedCss += `--design-preview-line-height: ${fontLineHeight};`;
		if (hColor)
		{
			preparedCss += `--design-preview-color-h: ${hColor};`;
		}
		else
		{
			preparedCss += `--design-preview-color-h: ${textColor};`;
		}

		if (hWeight)
		{
			preparedCss += `--design-preview-font-weight-h: ${hWeight};`;
		}
		else
		{
			preparedCss += `--design-preview-font-weight-h: ${fontWeight};`;
		}

		if (this.controls.typo.hFont.node)
		{
			preparedCss += `--design-preview-font-h-theme: ${hFont};`;
		}
		else
		{
			preparedCss += `--design-preview-font-h-theme: ${textFont};`;
		}

		return preparedCss;
	}

	createFontLink(font: string): string
	{
		const link = document.createElement('link');
		link.rel = 'stylesheet';
		link.href = `https://${window.fontsProxyUrl}/css2?family=`;
		link.href += font.replace(' ', '+');
		link.href += ':wght@100;200;300;400;500;600;700;800;900';

		return link;
	}

	getControlValue(element, value): string
	{
		if (element)
		{
			return value;
		}

		return '';
	}

	appendFontLinks(font)
	{
		if (font)
		{
			Dom.append(this.createFontLink(font), this.form);
		}
	}

	getCSSPart3(css): string
	{
		let preparedCss = css;
		let bgColor = this.controls.background.color.node.input.value;
		const bgFieldNode = this.controls.background.field.node;
		const bgPictureElement = bgFieldNode.getElementsByClassName('landing-ui-field-image-hidden');
		let bgPicture = bgPictureElement[0].getAttribute('src');
		let bgPosition = this.controls.background.position.node.value;

		if (this.controls.background.use.node.check === true)
		{
			preparedCss += `--design-preview-bg: ${bgColor};`;
		}
		else
		{
			bgPicture = '';
			if (this.controls.background.useSite && this.controls.background.useSite.defaultValue === 'Y')
			{
				bgColor = this.controls.background.color.defaultValue;
				bgPicture = this.controls.background.field.defaultValue;
				bgPosition = this.controls.background.position.defaultValue;
				preparedCss += `--design-preview-bg: ${bgColor};`;
			}
		}

		if (this.options.background.image.defaultValue && bgPicture === '')
		{
			bgPicture = this.options.background.image.defaultValue;
		}

		if (bgPicture)
		{
			preparedCss += `background-image: url(${bgPicture});`;
		}

		if (this.controls.background.position)
		{
			if (bgPosition === 'center')
			{
				preparedCss += 'background-attachment: scroll;';
				preparedCss += 'background-position: center;';
				preparedCss += 'background-repeat: no-repeat;';
				preparedCss += 'background-size: cover;';
			}

			if (bgPosition === 'repeat')
			{
				preparedCss += 'background-attachment: scroll;';
				preparedCss += 'background-position: center;';
				preparedCss += 'background-repeat: repeat;';
				preparedCss += 'background-size: 50%;';
			}

			if (bgPosition === 'center_repeat_y')
			{
				preparedCss += 'background-attachment: scroll;';
				preparedCss += 'background-position: top;';
				preparedCss += 'background-repeat: repeat-y;';
				preparedCss += 'background-size: 100%;';
			}
		}

		return preparedCss;
	}

	generateCss(): string
	{
		let css = this.generateSelectorStart(this.id);
		css = this.getCSSPart1(css);
		css = this.getCSSPart2(css);
		css = this.getCSSPart3(css);
		css = this.generateSelectorEnd(css);

		return css;
	}

	createLayout(): HTMLDivElement
	{
		this.layout = Tag.render`<div class="landing-design-preview-wrap"></div>`;
		if (this.type === null)
		{
			this.layoutContent = Tag.render`<div id="${this.id}" class="landing-design-preview"><h2 class="landing-design-preview-title">${this.phrase.title}</h2><h4 class="landing-design-preview-subtitle">${this.phrase.subtitle}</h4><p class="landing-design-preview-text">${this.phrase.text1}</p><p class="landing-design-preview-text">${this.phrase.text2}</p><div class=""><a class="landing-design-preview-button">${this.phrase.button}</a></div></div>`;
		}

		Dom.append(this.layoutContent, this.layout);
	}

	fixElement()
	{
		const designPreviewWrap = this.layoutContent.parentNode;
		const designPreviewWrapPosition = designPreviewWrap.getBoundingClientRect();
		const paddingDesignPreview = 20;
		const maxWidth = designPreviewWrapPosition.width - (paddingDesignPreview * 2);
		const designForm = designPreviewWrap.parentNode;
		const designFormPosition = designForm.getBoundingClientRect();
		const designPreviewPosition = this.layoutContent.getBoundingClientRect();
		const bodyWidth = document.body.clientWidth;
		const paddingDesignForm = 20;
		const positionFixedRight = bodyWidth - designFormPosition.right + paddingDesignForm;
		if (designFormPosition.height > designPreviewPosition.height)
		{
			let fixedStyle = 'position: fixed; ';
			fixedStyle += 'top: 20px; ';
			fixedStyle += 'margin-top: 0; ';
			fixedStyle += `right: ${positionFixedRight}px;`;
			fixedStyle += `max-width: ${maxWidth}px;`;
			this.layoutContent.setAttribute('style', fixedStyle);
		}
	}

	unFixElement()
	{
		this.layoutContent.setAttribute('style', '');
	}

	convertFont(font): string
	{
		let convertFont = font;
		convertFont = convertFont
			.replace('g-font-', '')
			.replaceAll('-', ' ')
			.replace('ibm ', 'IBM ')
			.replace('pt ', 'PT ')
			.replace(/sc(?![a-z])/i, 'SC')
			.replace(/jp(?![a-z])/i, 'JP')
			.replace(/kr(?![a-z])/i, 'KR')
			.replace(/tc(?![a-z])/i, 'TC')
			.replaceAll(/(^|\s)\S/g, (firstSymbol) => {
				return firstSymbol.toUpperCase();
			});

		return convertFont;
	}
}
