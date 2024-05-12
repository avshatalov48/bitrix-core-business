// @flow

import {Tag, Type, Dom} from 'main.core';
import LabelColor from './label-color';
import LabelSize from './label-size';
import { Loader } from 'main.loader';
import { Icon, Main } from 'ui.icon-set.api.core';
import 'ui.icon-set.main';
import 'ui.design-tokens';
import './style.css';

type LabelOptions = {
	text: string;
	color: LabelColor;
	size: LabelSize;
	link: string;
	fill: boolean;
	customClass: string;
	icon: Object;
	status: string;
	loader: Loader;
};

export default class Label {

	static Color = LabelColor;
	static Size = LabelSize;

	text: string;
	color: string;
	size: string;
	link: string;
	fill: boolean;
	customClass: string;
	icon: Object;
	status: string;
	loader: Loader;
	node: Object

	constructor(options: LabelOptions)
	{
		this.text = options.text;
		this.color = options.color;
		this.size = options.size;
		this.link = options.link;
		this.icon = options.icon;
		this.fill = !!options.fill ? true : options.fill;
		this.customClass = options.customClass;
		this.classList = "ui-label";
		this.status = options.status;
		this.node = {
			container: null,
			status: null
		};


		this.setText(this.text);
		this.setLink(this.link);
		this.setColor(this.color);
		this.setFill(this.fill);

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

	//region FILL
	setFill(fill: boolean)
	{
		this.fill = !!fill ? true : false;
		this.setClassList();
	}

	getFill()
	{
		return this.fill;
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

	//region LINK
	setLink(link: string)
	{
		this.link = link;
	}

	getLink()
	{
		return this.link;
	}

	// endregion

	//region TEXT
	setText(text: string): this
	{
		this.text = text;
		if (Type.isStringFilled(text))
		{
			this.getTextContainer().textContent = text;
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
			this.textContainer = Tag.render`<span class="ui-label-inner">${this.getText()}</span>`;
		}

		return this.textContainer;
	}

	// endregion

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
		this.classList = "ui-label";

		if(typeof this.getColor() != "undefined")
		{
			this.classList = this.classList + " " + this.color;
		}

		if(typeof this.getSize() != "undefined")
		{
			this.classList = this.classList + " " + this.size;
		}

		if(typeof this.getCustomClass() != "undefined")
		{
			this.classList = this.classList + " " + this.customClass;
		}

		if(this.fill)
		{
			this.classList = this.classList + " ui-label-fill";
		}

		this.updateClassList()
	}

	getClassList()
	{
		return this.classList;
	}

	updateClassList()
	{
		if (!this.node.container)
		{
			this.getContainer()
		}

		this.node.container.setAttribute("class", this.classList);
	}

	getIconAction()
	{
		this.iconNode = Tag.render`<div class="ui-label-icon"></div>`;

		for(let key in this.icon)
		{
			this.iconNode.addEventListener(key, this.icon[key])
		}

		return this.iconNode;
	}

	getLoader() : Loader
	{
		if (!this.loader)
		{
			this.loader = new Loader({
				size: 12,
			});
		}

		return this.loader;
	}

	setStatus(status: string) : void
	{
		if (status)
		{
			this.status = status;
		}

		Dom.clean(this.getContainerStatus());
		this.getContainerStatus().classList.remove('--icon');

		if (this.status.toLocaleUpperCase() === 'LOADING')
		{
			this.getLoader().show(this.getContainerStatus());
		}
		
		if (this.status.toLocaleUpperCase() === 'CHECK')
		{
			let icon = new Icon({
				icon: Main.CHECK,
				size: 10
			});

			this.getContainerStatus().classList.add('--icon');
			this.getContainerStatus().appendChild(icon.render());
		}
	}

	getContainerStatus() : HTMLElement
	{
		if (!this.node.status)
		{
			this.node.status = Tag.render`
				<div class="ui-label-status"></div>
			`;
		}

		return this.node.status;
	}

	// endregion

	getContainer() : HTMLElement
	{
		if (!this.node.container)
		{

			if (this.getLink())
			{
				this.node.container = Tag.render`<a href="${this.link}" class="${this.getClassList()}">
					${this.getContainerStatus()}
					${this.getTextContainer()}
				</a>`;
			}
			else
			{
				this.node.container = Tag.render`<div class="${this.getClassList()}">
					${this.getContainerStatus()}
					${this.getTextContainer()}
				</div>`;
			}

			if (typeof this.icon === 'object')
			{
				this.node.container.appendChild(this.getIconAction());
			}
		}

		return this.node.container;
	}

	render(): HTMLElement
	{
		if (this.status)
		{
			this.setStatus(this.status);
		}

		return this.getContainer();
	}

}