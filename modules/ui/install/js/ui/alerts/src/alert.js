// @flow

import {Dom, Tag, Type} from 'main.core';
import AlertColor from './alert-color';
import AlertSize from './alert-size';
import AlertIcon from './alert-icon';

type AlertOptions = {
	text: string;
	color: AlertColor;
	size: AlertSize;
	icon: AlertIcon;
	customClass: string;
	closeBtn: boolean;
	animated: boolean;
};

export default class Alert {

	static Color = AlertColor;
	static Size = AlertSize;
	static Icon = AlertIcon;

	text: string;
	color: string;
	size: string;
	icon: string;
	closeBtn: boolean;
	animated: boolean;
	customClass: string;

	constructor(options: AlertOptions)
	{
		this.text = options.text;
		this.color = options.color;
		this.size = options.size;
		this.icon = options.icon;
		this.closeBtn = !!options.closeBtn ? true : options.closeBtn;
		this.animated = !!options.animated ? true : options.animated;
		this.customClass = options.customClass;

		this.setText(this.text);
		this.setSize(this.size);
		this.setIcon(this.icon);
		this.setColor(this.color);
		this.setCloseBtn(this.closeBtn);
		this.setCustomClass(this.customClass);
	}

	//region COLOR
	setColor(color: string)
	{
		this.color = color;
		this.setClassList();
	}

	getColor()
	{
		return this.color;
	}

	// endregion

	//region SIZE
	setSize(size: string)
	{
		this.size = size;
		this.setClassList();
	}

	getSize()
	{
		return this.size;
	}

	// endregion

	//region ICON
	setIcon(icon: string)
	{
		this.icon = icon;
		this.setClassList();
	}

	getIcon()
	{
		return this.icon;
	}

	// endregion

	//region TEXT
	setText(text: string): this
	{
		this.text = text;
		if (Type.isStringFilled(text))
		{
			this.getTextContainer().innerHTML = text;
		}
	}

	getText()
	{
		return this.text;
	}

	getTextContainer()
	{
		if (!this.textContainer)
		{
			this.textContainer =  BX.create('span', {
				props: {
					className: 'ui-alert-message'
				},
				html: this.getText()
			});
		}

		return this.textContainer;
	}

	// endregion

	// region CLOSE BTN
	setCloseBtn(closeBtn: boolean)
	{
		this.closeBtn = closeBtn;
	}

	getCloseBtn()
	{
		if (this.closeBtn != true)
		{
			return
		}

		if ((!this.closeNode) && (this.closeBtn === true))
		{
			this.closeNode = BX.create("span", {
				props: {className: "ui-alert-close-btn"},
				events: {
					click: this.handleCloseBtnClick.bind(this)
				}
			})
		}

		return this.closeNode;
	}

	handleCloseBtnClick()
	{
		if (this.animated === true)
		{
			this.animateClosing();
		}
		else
		{
			BX.remove(this.container);
		}
	}

	//endregion

	//region CUSTOM CLASS
	setCustomClass(customClass: string)
	{
		this.customClass = customClass;
		this.updateClassList();
	}

	getCustomClass()
	{
		return this.customClass;
	}

	// endregion

	//region CLASS LIST
	setClassList()
	{
		this.classList = "ui-alert";

		if (typeof this.getColor() != "undefined")
		{
			this.classList = this.classList + " " + this.color;
		}

		if (typeof this.getSize() != "undefined")
		{
			this.classList = this.classList + " " + this.size;
		}

		if (typeof this.getIcon() != "undefined")
		{
			this.classList = this.classList + " " + this.icon;
		}

		if (typeof this.getCustomClass() != "undefined")
		{
			this.classList = this.classList + " " + this.customClass;
		}

		this.updateClassList();
	}

	getClassList()
	{
		return this.classList;
	}

	updateClassList()
	{
		if (!this.container)
		{
			this.getContainer()
		}

		this.container.setAttribute("class", this.classList);
	}

	// endregion

	//region ANIMATION
	animateOpening()
	{
		this.container.style.overflow = "hidden";
		this.container.style.height = 0;
		this.container.style.paddingTop = 0;
		this.container.style.paddingBottom = 0;
		this.container.style.marginBottom = 0;
		this.container.style.opacity = 0;

		setTimeout(
			function () {
				this.container.style.height = this.container.scrollHeight + "px";
				this.container.style.height = "";
				this.container.style.paddingTop = "";
				this.container.style.paddingBottom = "";
				this.container.style.marginBottom = "";
				this.container.style.opacity = "";
			}.bind(this),
			10
		);

		setTimeout(
			function () {
				this.container.style.height = "";
			}.bind(this),
			200
		);
	}

	animateClosing()
	{
		this.container.style.overflow = "hidden";

		var alertWrapPos = BX.pos(this.container);
		this.container.style.height = alertWrapPos.height + "px";

		setTimeout(
			function () {
				this.container.style.height = 0;
				this.container.style.paddingTop = 0;
				this.container.style.paddingBottom = 0;
				this.container.style.marginBottom = 0;
				this.container.style.opacity = 0;
			}.bind(this),
			10
		);

		setTimeout(
			function () {
				BX.remove(this.container);
			}.bind(this),
			260
		);
	}
	
	//endregion

	getContainer()
	{
		this.container = Tag.render`<div class="${this.getClassList()}">${this.getTextContainer()}</div>`;

		if (this.animated === true)
		{
			this.animateOpening();
		}

		if (this.closeBtn === true)
		{
			BX.append(this.getCloseBtn(), this.container);
		}

		return this.container;
	}

	render(): HTMLElement
	{
		return this.getContainer();
	}
}