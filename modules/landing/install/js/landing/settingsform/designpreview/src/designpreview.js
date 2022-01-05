import { Dom, Event, Tag } from 'main.core';
import { Control } from './internal/control';
import { BaseEvent, EventEmitter } from 'main.core.events';

import './css/style.css';

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

	constructor(form: HTMLElement, options: Object = {}, phrase: Object = {})
	{
		super();
		this.setEventNamespace('BX.Landing.SettingsForm.DesignPreview');

		this.form = form;
		this.phrase = phrase;

		this.initControls(options);
		this.initLayout();
		this.applyStyles();
		this.onApplyStyles = this.applyStyles.bind(this);
	}

	initLayout()
	{
		this.layout = DesignPreview.createLayout(this.phrase);
		this.styleNode = document.createElement("style");
		Dom.append(this.styleNode, this.layout);
		Dom.append(this.layout, this.form);

		const paramsObserver = {
			threshold: 1
		}
		const observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				const availableHeight = document.documentElement.clientHeight - DesignPreview.HEIGHT_PAGE_TITLE_WRAP;
				if (entry.target.getBoundingClientRect().height <= availableHeight)
				{
					if (entry.isIntersecting)
					{
						if (!this.hasOwnProperty('defaultIntersecting'))
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
						if (!this.hasOwnProperty('defaultIntersecting'))
						{
							this.defaultIntersecting = false;
						}
						if (this.defaultIntersecting)
						{
							this.fixElement();
						}
					}
				}
			})
		}, paramsObserver)
		let elementDesignPreview = document.querySelector('.landing-design-preview-wrap');
		observer.observe(elementDesignPreview);
	}

	initControls(options: Object)
	{
		this.controls = {};
		for (let group in options)
		{
			if (!options.hasOwnProperty(group))
			{
				continue;
			}
			for (let key in options[group])
			{
				if (!options[group].hasOwnProperty(key))
				{
					continue;
				}
				if (!this.controls[group])
				{
					this.controls[group] = {};
				}

				const control = new Control(options[group][key]['control']);
				control.setChangeHandler(this.applyStyles.bind(this));
				if (group === 'theme' && key !== 'use')
				{
					control.setClickHandler(this.applyStyles.bind(this));
				}
				if (group === 'background' && key === 'field')
				{
					control.setClickHandler(this.applyStyles.bind(this));
				}
				
				this.controls[group][key] = control;
			}
		}

		// parents and default
		for (let group in this.controls)
		{
			if (!this.controls.hasOwnProperty(group))
			{
				continue;
			}
			for (let key in this.controls[group])
			{
				if (!this.controls[group].hasOwnProperty(key))
				{
					continue;
				}
				if (key !== 'use' && this.controls[group]['use'])
				{
					this.controls[group][key].setParent(this.controls[group]['use']);
					if (options[group][key]['defaultValue'])
					{
						this.controls[group][key].setDefaultValue(options[group][key]['defaultValue']);
					}
				}
			}
		}
		
		if (this.controls.theme.corporateColor.node)
		{
			this.controls.theme.corporateColor.node.subscribe('onSelectColor', this.onApplyStyles.bind(this));
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

		this.panel = BX.Landing.UI.Panel.GoogleFonts.getInstance();
		Dom.append(this.panel.layout, document.body);

		const fieldCode = this.controls.typo.textFont.node;
		const fieldCodeH = this.controls.typo.hFont.node;
		if (fieldCode && fieldCodeH)
		{
			fieldCode.setAttribute("value", this.convertFont(fieldCode.value));
			fieldCodeH.setAttribute("value", this.convertFont(fieldCodeH.value));
			Event.bind(fieldCode, 'click', this.onCodeClick.bind(this));
			Event.bind(fieldCodeH, 'click', this.onCodeClick.bind(this));
		}
	}

	onCodeClick(event: BaseEvent)
	{
		this.panel.show({
			hideOverlay: true,
			context: window,
		}).then((font) => {
			const element = event.target;
			element.setAttribute("value", font.family);
			this.onApplyStyles();
		});
	}

	onApplyStyles()
	{
		this.applyStyles();
	}

	applyStyles()
	{
		this.styleNode.innerHTML = this.generateCss();
	}

	generateSelectorStart(className)
	{
		return '.' + className + ' {';
	}

	generateSelectorEnd(selector)
	{
		return selector + ' }';
	}

	getCSSPart1(css)
	{
		let colorPrimary;
		let setColors = this.controls.theme.baseColors.node;
		let colorPickerElement;
		if (this.controls.theme.corporateColor.node)
		{
			colorPickerElement = this.controls.theme.corporateColor.node.element;
		}

		let activeColorNode;
		if (setColors)
		{
			activeColorNode = setColors.querySelector('.active');
		}
		let isActiveColorPickerElement;
		if (colorPickerElement)
		{
			isActiveColorPickerElement = colorPickerElement.classList.contains('active');
		}

		if (activeColorNode)
		{
			colorPrimary = activeColorNode.dataset.value;
		}
		if (isActiveColorPickerElement)
		{
			colorPrimary = colorPickerElement.dataset.value;
		}
		//for 'design page', if use not checked, use color from 'design site'
		if (this.controls.theme.use.node)
		{
			if (this.controls.theme.use.node.checked === false)
			{
				colorPrimary = this.controls.theme.corporateColor.defaultValue;
			}
		}
		if (colorPrimary)
		{
			if (colorPrimary[0] !== '#')
			{
				colorPrimary = '#' + colorPrimary;
			}
			css += `--design-preview-primary: ${colorPrimary};`;
		}

		return css;
	}

	getCSSPart2(css)
	{
		let textColor;
		let textFont;
		let hFont;
		let textSize;
		let fontWeight;
		let fontLineHeight;
		let hColor;
		let hWeight;
		if (this.controls.typo.textColor.node)
		{
			textColor = this.controls.typo.textColor.node.input.value;
		}
		if (this.controls.typo.textFont.node)
		{
			textFont = this.controls.typo.textFont.node.value;
		}
		if (this.controls.typo.hFont.node)
		{
			hFont = this.controls.typo.hFont.node.value;
		}
		if (this.controls.typo.textSize.node)
		{
			textSize = Math.round(this.controls.typo.textSize.node.value * DesignPreview.DEFAULT_FONT_SIZE) + 'px';
		}
		if (this.controls.typo.textWeight.node)
		{
			fontWeight = this.controls.typo.textWeight.node.value;
		}
		if (this.controls.typo.textLineHeight.node)
		{
			fontLineHeight = this.controls.typo.textLineHeight.node.value;
		}
		if (this.controls.typo.hColor.node)
		{
			hColor = this.controls.typo.hColor.node.input.value;
		}
		if (this.controls.typo.hWeight.node)
		{
			hWeight = this.controls.typo.hWeight.node.value;
		}

		if (this.controls.typo.use.node)
		{
			if (this.controls.typo.use.node.checked === false)
			{
				textColor = this.controls.typo.textColor.defaultValue;
				textFont = this.controls.typo.textFont.defaultValue;
				hFont = this.controls.typo.hFont.defaultValue;
				textSize = Math.round(this.controls.typo.textSize.defaultValue
					* DesignPreview.DEFAULT_FONT_SIZE) + 'px';
				fontWeight = this.controls.typo.textWeight.defaultValue;
				fontLineHeight = this.controls.typo.textLineHeight.defaultValue;
				hColor = this.controls.typo.hColor.defaultValue;
				hWeight = this.controls.typo.hWeight.defaultValue;
			}
		}

		let link;
		let linkH;
		if (textFont)
		{
			link = this.createLink(textFont);
			Dom.append(link, this.form);
		}
		if (hFont)
		{
			linkH = this.createLink(hFont);
			Dom.append(linkH, this.form);
		}

		css += `--design-preview-color: ${textColor};`;
		css += `--design-preview-font-theme: ${textFont};`;
		css += `--design-preview-font-size: ${textSize};`;
		css += `--design-preview-font-weight: ${fontWeight};`;
		css += `--design-preview-line-height: ${fontLineHeight};`;
		if (hColor)
		{
			css += `--design-preview-color-h: ${hColor};`;
		}
		else
		{
			css += `--design-preview-color-h: ${textColor};`;
		}
		if (hWeight)
		{
			css += `--design-preview-font-weight-h: ${hWeight};`;
		}
		else
		{
			css += `--design-preview-font-weight-h: ${fontWeight};`;
		}
		if (this.controls.typo.hFont.node)
		{
			css += `--design-preview-font-h-theme: ${hFont};`;
		}
		else
		{
			css += `--design-preview-font-h-theme: ${textFont};`;
		}

		return css;
	}

	createLink(font)
	{
		let link = document.createElement('link');
		link.rel = 'stylesheet';
		link.href = 'https://fonts.googleapis.com/css2?family=';
		link.href += font.replace(' ', '+');
		link.href += ':wght@100;200;300;400;500;600;700;800;900';
		return link;
	}

	getCSSPart3(css)
	{
		let bgColor = this.controls.background.color.node.input.value;
		let bgFieldNode = this.controls.background.field.node;
		let bgPictureElement = bgFieldNode.getElementsByClassName('landing-ui-field-image-hidden');
		let bgPicture = bgPictureElement[0].getAttribute('src');
		let bgPosition = this.controls.background.position.node.value;

		if (this.controls.background.use.node.checked === true)
		{
			css += `--design-preview-bg: ${bgColor};`;
		}
		else
		{
			bgPicture = '';
			if (this.controls.background.useSite)
			{
				if (this.controls.background.useSite.defaultValue === 'Y')
				{
					bgColor = this.controls.background.color.defaultValue;
					bgPicture = this.controls.background.field.defaultValue;
					bgPosition = this.controls.background.position.defaultValue;
					css += `--design-preview-bg: ${bgColor};`;
				}
			}
		}
		if (this.controls.background.position)
		{
			if (bgPosition === 'center')
			{
				css += `background-image: url(${bgPicture});`;
				css += `background-attachment: scroll;`;
				css += `background-position: center;`;
				css += `background-repeat: no-repeat;`;
				css += `background-size: cover;`;
			}
			if (bgPosition === 'repeat')
			{
				css += `background-image: url(${bgPicture});`;
				css += `background-attachment: scroll;`;
				css += `background-position: center;`;
				css += `background-repeat: repeat;`;
				css += `background-size: 50%;`;
			}
			if (bgPosition === 'center_repeat_y')
			{
				css += `background-image: url(${bgPicture});`;
				css += `background-attachment: scroll;`;
				css += `background-position: top;`;
				css += `background-repeat: repeat-y;`;
				css += `background-size: 100%;`;
			}
		}

		return css;
	}

	generateCss()
	{
		let css;
		css = this.generateSelectorStart('landing-design-preview');
		css = this.getCSSPart1(css);
		css = this.getCSSPart2(css);
		css = this.getCSSPart3(css);
		css = this.generateSelectorEnd(css);

		return css;
	}

	static createLayout(phrase): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-design-preview-wrap">
				<div class="landing-design-preview">
					<h2 class="landing-design-preview-title">${phrase.title}</h2>
					<h4 class="landing-design-preview-subtitle">${phrase.subtitle}</h4>
					<p class="landing-design-preview-text">
						${phrase.text1}
					</p>
					<p class="landing-design-preview-text">
						${phrase.text2}
					</p>
					<div class="">
						<a href="#" class="landing-design-preview-button">${phrase.button}</a>
					</div>
				</div>
			</div>
		`;
	}

	fixElement()
	{
		const paddingDesignForm = 20;
		const designForm = document.querySelector('.landing-design-form');
		const designFormPosition = designForm.getBoundingClientRect();
		const designPreview = document.querySelector('.landing-design-preview');
		const designPreviewPosition = designPreview.getBoundingClientRect();
		const bodyWidth = document.body.clientWidth;
		const positionFixedRight = bodyWidth - designFormPosition.right + paddingDesignForm;
		const paddingDesignPreview = 25;
		const designPreviewWrap = document.querySelector('.landing-design-preview-wrap');
		const designPreviewWrapPosition = designPreviewWrap.getBoundingClientRect();
		const maxWidth = designPreviewWrapPosition.width - (paddingDesignPreview * 2);
		if (designFormPosition.height > designPreviewPosition.height)
		{
			let fixedStyle;
			fixedStyle = 'position: fixed; ';
			fixedStyle += 'top: 20px; ';
			fixedStyle += 'margin-top: 0; ';
			fixedStyle += 'right: '+ positionFixedRight + 'px;';
			fixedStyle += 'max-width: '+ maxWidth + 'px;';
			designPreview.setAttribute("style", fixedStyle);
		}
	}

	unFixElement()
	{
		let designPreview = document.querySelector('.landing-design-preview');
		designPreview.setAttribute("style", '');
	}

	convertFont(font)
	{
		font = font.replace('g-font-', '');
		font = font.replaceAll('-', ' ');
		font = font.replace('ibm ', 'IBM ');
		font = font.replace('pt ', 'PT ');
		font = font.replace(/sc(?:(?![a-z]))/i, 'SC');
		font = font.replace(/jp(?:(?![a-z]))/i, 'JP');
		font = font.replace(/kr(?:(?![a-z]))/i, 'KR');
		font = font.replace(/tc(?:(?![a-z]))/i, 'TC');
		font = font.replace(/(^|\s)\S/g, function(firstSymbol) {
			return firstSymbol.toUpperCase()
		})

		return font;
	}
}