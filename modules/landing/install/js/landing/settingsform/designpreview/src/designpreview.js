import { Dom, Tag } from 'main.core';
import { Control } from './internal/control';

import './css/style.css';

type Controls = {
	theme: {
		use: ?Control,
		baseColor: ?Control,
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
		picture: ?Control,
		position: ?Control,
		color: ?Control,
	},
}

export class DesignPreview
{
	static DEFAULT_FONT_SIZE = 14;

	controls: Controls;

	constructor(form: HTMLElement, options: Object = {}, phrase: Object = {})
	{
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
		this.fonts = DesignPreview.loadFonts();
		this.styleNode = document.createElement("style");
		Dom.append(this.styleNode, this.layout);
		Dom.append(this.layout, this.form);
		Dom.append(this.fonts, this.form);

		const paramsObserver = {
			threshold: 1
		}
		const observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
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
				if (group === 'background' && key === 'picture')
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

		BX.addCustomEvent('BX.Landing.ColorPicker:onSelectColor', this.onApplyStyles.bind(this));
		BX.addCustomEvent('BX.Landing.ColorPicker:onClearColorPicker', this.onApplyStyles.bind(this));
		BX.addCustomEvent('BX.Landing.UI.Field.Image:onChangeImage', this.onApplyStyles.bind(this));
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
		let setColors = BX('set-colors');
		let colorPickerElement = BX('colorpicker-theme');
		let activeColorNode = setColors.querySelector('.active');
		let isActiveColorPickerElement = colorPickerElement.classList.contains('active');

		if (activeColorNode)
		{
			colorPrimary = activeColorNode.dataset.value;
		}
		if (isActiveColorPickerElement)
		{
			colorPrimary = colorPickerElement.dataset.value;
		}
		if (colorPrimary[0] !== '#')
		{
			colorPrimary = '#' + colorPrimary;
		}
		//for 'design page', if use not checked, use color from 'design site'
		if (this.controls.theme.use.node)
		{
			if (this.controls.theme.use.node.checked === false)
			{
				colorPrimary = this.controls.theme.corporateColor.defaultValue;
			}
		}
		css += `--design-preview-primary: ${colorPrimary};`;

		return css;
	}

	getCSSPart2(css)
	{
		let textColor = this.controls.typo.textColor.node.value;
		let font = this.convertFont(this.controls.typo.textFont.node.value);
		let hFont = this.convertFont(this.controls.typo.hFont.node.value);
		let textSize = Math.round(this.controls.typo.textSize.node.value * DesignPreview.DEFAULT_FONT_SIZE) + 'px';
		let fontWeight = this.controls.typo.textWeight.node.value;
		let fontLineHeight = this.controls.typo.textLineHeight.node.value;
		let hColor = this.controls.typo.hColor.node.value;
		let hWeight = this.controls.typo.hWeight.node.value;

		if (this.controls.typo.use.node)
		{
			if (this.controls.typo.use.node.checked === false)
			{
				textColor = this.controls.typo.textColor.defaultValue;
				font = this.convertFont(this.controls.typo.textFont.defaultValue);
				hFont = this.convertFont(this.controls.typo.hFont.defaultValue);
				textSize = Math.round(this.controls.typo.textSize.defaultValue
					* DesignPreview.DEFAULT_FONT_SIZE) + 'px';
				fontWeight = this.controls.typo.textWeight.defaultValue;
				fontLineHeight = this.controls.typo.textLineHeight.defaultValue;
				hColor = this.controls.typo.hColor.defaultValue;
				hWeight = this.controls.typo.hWeight.defaultValue;
			}
		}

		css += `--design-preview-color: ${textColor};`;
		css += `--design-preview-font: ${font};`;
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
		if (this.controls.typo.hFont.node.value)
		{
			css += `--design-preview-font-h: ${hFont};`;
		}
		else
		{
			css += `--design-preview-font-h: ${font};`;
		}

		return css;
	}

	getCSSPart3(css)
	{
		let bgColor = this.controls.background.color.node.value;
		let bgFieldNode = BX('landing-form-background-field');
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
					bgPicture = this.controls.background.picture.defaultValue;
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

	onApplyStyles()
	{
		this.applyStyles();
	}

	applyStyles()
	{
		this.styleNode.innerHTML = this.generateCss();
	}

	convertFont(font)
	{
		switch (font)
		{
			case 'g-font-open-sans':
				font = '"Open Sans", Helvetica, Arial, sans-serif';
				break;
			case 'g-font-roboto':
				font = '"Roboto", Arial, sans-serif';
				break;
			case 'g-font-roboto-slab':
				font = '"Roboto Slab", Helvetica, Arial, sans-serif';
				break;
			case 'g-font-montserrat':
				font = '"Montserrat", Arial, sans-serif';
				break;
			case 'g-font-alegreya-sans':
				font = '"Alegreya Sans", sans-serif';
				break;
			case 'g-font-cormorant-infant':
				font = '"Cormorant Infant", serif';
				break;
			case 'g-font-pt-sans-caption':
				font = '"PT Sans Caption", sans-serif';
				break;
			case 'g-font-pt-sans-narrow':
				font = '"PT Sans Narrow", sans-serif';
				break;
			case 'g-font-pt-sans':
				font = '"PT Sans", sans-serif';
				break;
			case 'g-font-lobster':
				font = '"Lobster", cursive';
				break;
			default:
				font = '"Montserrat", Arial, sans-serif';
		}
		return font;
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

	static loadFonts(): HTMLDivElement
	{
		return Tag.render`
			<div>
				<link
					rel="stylesheet"
					href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700,900"
				>
				<link
					rel="stylesheet"
					href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,600,700,900"
				>
				<link
					rel="stylesheet"
					href="https://fonts.googleapis.com/css?family=Roboto+Slab:300,400,500,600,700,900"
				>
				<link
					rel="stylesheet"
					href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,900"
				>
				<link
					rel="stylesheet"
					href="https://fonts.googleapis.com/css?family=Alegreya+Sans:300,400,500,600,700,900"
				>
				<link
					rel="stylesheet"
					href="https://fonts.googleapis.com/css?family=Cormorant+Infant:300,400,500,600,700,900"
				>
				<link
					rel="stylesheet"
					href="https://fonts.googleapis.com/css?family=PT+Sans+Caption:300,400,500,600,700,900"
				>
				<link
					rel="stylesheet"
					href="https://fonts.googleapis.com/css?family=PT+Sans+Narrow:300,400,500,600,700,900"
				>
				<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=PT+Sans:300,400,500,600,700,900">
				<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lobster:300,400,500,600,700,900">
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
}