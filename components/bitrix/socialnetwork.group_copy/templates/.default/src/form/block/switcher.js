import {Dom, Event, Tag, Text} from "main.core";

export class Switcher
{
	constructor(options)
	{
		options = {...{
			title: "Title"
		}, ...options};

		this.title = options.title;

		this.container = null;
		this.options = [];

		this.ids = new Map([
			["switcherId", "social-group-copy-switcher"],
		]);
		this.classes = new Map([
			["switcher", "social-group-copy-switcher"],
			["switcherMore", "social-group-copy-switcher-more"],
			["switcherPromo", "social-group-copy-switcher-promo"],
			["opened", "opened"],
			["hide", "hide"],
			["easingIn", "fade-in"],
			["easingOut", "fade-out"],
		]);
	}

	addOption(option)
	{
		this.options.push(option);
	}

	onAppendToParent(optionsContainer)
	{
		this.optionsContainer = optionsContainer;
		this.container = document.getElementById(this.ids.get("switcherId"));
		Event.bind(this.container, "click", this.onClick.bind(this));
	}

	isOpened()
	{
		return Dom.hasClass(this.container, this.classes.get("opened"));
	}

	/**
	 * @returns {HTMLElement}
	 */
	render()
	{
		this.title = Text.encode(this.title);

		const options = this.options.map((option) => {
			return option.renderPromo();
		});

		return Tag.render`
			<div id="${this.ids.get("switcherId")}" class="${this.classes.get("switcher")}">
				<div class="${this.classes.get("switcherMore")}">${this.title}</div>
				<div class="${this.classes.get("switcherPromo")}">
					${options}
				</div>
			</div>
		`;
	}

	onClick(event)
	{
		let target = event.target;
		let targetId = target.getAttribute("id");
		if (!targetId)
		{
			target = event.currentTarget;
			targetId = target.getAttribute("id");
		}

		if (targetId === this.ids.get("switcherId"))
		{
			this.switchOptions();
		}
	}

	switchOptions()
	{
		this.constructor.switchOptions(this);
	}

	static switchOptions(switcher)
	{
		if (Dom.hasClass(switcher.optionsContainer, switcher.classes.get("easingOut")))
		{
			Dom.removeClass(switcher.optionsContainer, switcher.classes.get("hide"));
			Dom.removeClass(switcher.optionsContainer, switcher.classes.get("easingOut"));
			Dom.addClass(switcher.container, switcher.classes.get("opened"));
			Dom.addClass(switcher.optionsContainer, switcher.classes.get("easingIn"));
		}
		else
		{
			Dom.removeClass(switcher.container, switcher.classes.get("opened"));
			Dom.removeClass(switcher.optionsContainer, switcher.classes.get("easingIn"));
			Dom.addClass(switcher.optionsContainer, switcher.classes.get("easingOut"));
		}
	}
}