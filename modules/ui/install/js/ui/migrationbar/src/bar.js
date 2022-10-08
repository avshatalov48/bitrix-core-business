import { Tag, Type } from 'main.core';
import { Button } from 'ui.buttons';
import { Popup } from 'main.popup';
import './style.css';

export default class MigrationBar
{
	constructor({target, title, cross, items, buttons, link, hint, width, height, minWidth, minHeight})
	{
		this.target = Type.isDomNode(target) ? target : null;
		this.title = Type.isString(title) || Type.isObject(title) ? title : null;
		this.cross = Type.isBoolean(cross) ? cross : true;
		this.items = Type.isArray(items) ? items : [];
		this.buttons = Type.isArray(buttons) ? buttons : null;
		this.link = Type.isObject(link) ? link : null;
		this.hint = Type.isString(hint) ? hint : null;
		this.width = Type.isNumber(width) ? width : null;
		this.height = Type.isNumber(height) ? height : null;
		this.minWidth = Type.isNumber(minWidth) ? minWidth : null;
		this.minHeight = Type.isNumber(minHeight) ? minHeight : null;

		this.layout = {
			wrapper: null,
			container: null,
			items: null,
			title: null,
			text: null,
			link: null,
			remove: null,
			buttons: null
		}

		this.popupHint = null;
	}

	getWrapper()
	{
		if (!this.layout.wrapper)
		{
			this.layout.wrapper = Tag.render`
				<div class="ui-migration-bar__wrap"></div>
			`;
		}

		return this.layout.wrapper;
	}

	getContainer()
	{
		if (!this.layout.container)
		{
			this.layout.container = Tag.render`
				<div class="ui-migration-bar__container ui-migration-bar__scope --show">
					${this.cross ? this.getCross() : ''}
					<div class="ui-migration-bar__content">
						${this.title ? this.getTitle() : ''}
						${this.getItemContainer()}
					</div>
					${this.getButtonsContainer()}
				</div>
			`;

			this.layout.container.addEventListener('animationend', () => {
				this.layout.container.classList.remove('--show');
			}, { once: true });

			if (this.width)
			{
				this.layout.container.style.setProperty('width', this.width + 'px');
			}

			if (this.height)
			{
				this.layout.container.style.setProperty('height', this.height + 'px')
			}

			if (this.minWidth)
			{
				this.layout.container.style.setProperty('min-width', this.minWidth + 'px');
			}

			if (this.minHeight)
			{
				this.layout.container.style.setProperty('min-height', this.minHeight + 'px')
			}

		}

		return this.layout.container;
	}

	getTitle()
	{
		if (!this.layout.title)
		{
			const isTitleObject = Type.isObject(this.title);
			const titleText = isTitleObject ? this.title?.text : this.title;
			const alignTitle =  isTitleObject ? this.title?.align : null;

			this.layout.title = Tag.render`
				<div class="ui-migration-bar__title ${alignTitle ? '--align-' + alignTitle : ''}">
					${titleText}
					${this.hint ? this.getHint() : ''}
				</div>
			`;
		}

		return this.layout.title;
	}

	getCross()
	{
		if (!this.layout.remove)
		{
			this.layout.remove = Tag.render`
				<div class="ui-migration-bar__remove">
					<div class="ui-migration-bar__remove-icon"></div>
				</div>
			`;

			this.layout.remove.addEventListener('click', () => this.remove());
		}

		return this.layout.remove;
	}

	getButtonsContainer()
	{
		if (!this.layout.buttons)
		{
			this.layout.buttons = Tag.render`
				<div class="ui-migration-bar__btn-container"></div>
			`;
		}

		return this.layout.buttons;
	}

	getItemContainer()
	{
		if (!this.layout.items)
		{
			this.layout.items = Tag.render`
				<div class="ui-migration-bar__item-container"></div>
			`;
		}

		return this.layout.items;
	}

	getImage()
	{
		return this.items;
	}

	getLink()
	{
		if (!this.layout.link)
		{
			const linkNode = this.link?.href ? 'a' : 'div';

			this.layout.link = Tag.render`
				<${linkNode} class="ui-migration-bar__link">${this.link.text}</${linkNode}>
			`;

			const setCursorPointerMode = () => {
				this.layout.link.classList.add('--cursor-pointer')
			};


			if (this.link.href)
			{
				setCursorPointerMode();
				this.layout.link.href = this.link.href;
			}

			if (this.link.target)
			{
				this.layout.link.target = this.link.target;
			}

			if (this.link.events)
			{
				setCursorPointerMode();
				const eventKeys = Object.keys(this.link.events);
				eventKeys.forEach(event => {
					this.layout.link.addEventListener(event, () => {
						this.link.events[event]()
					});
				});
			}

		}

		return this.layout.link;
	}

	getHint()
	{
		if (!this.layout.hint)
		{
			this.layout.hint = Tag.render`
				<div class="ui-migration-bar__hint">
					<div class="ui-migration-bar__hint-icon"></div>
				</div>
			`;

			const popupHintWidth = 200;
			const hintIconWidth = 20;

			this.popupHint = new Popup(null, this.layout.hint, {
				darkMode: true,
				content: this.hint,
				angle: {
					offset: (popupHintWidth / 2) - 16
				},
				width: popupHintWidth,
				offsetLeft: -(popupHintWidth / 2) + (hintIconWidth / 2) + 40,
				animation: 'fading-slide'
			});

			this.layout.hint.addEventListener('mouseover', () => { this.popupHint.show() });
			this.layout.hint.addEventListener('mouseleave', () => { this.popupHint.close() });
		}

		return this.layout.hint;
	}

	adjustItemData()
	{
		this.items = this.items.map((item) => {
			return {
				id: item.id ? item.id : null,
				src: item.src ? item.src : null,
				events: item.events ? item.events : null,
			}
		})
	}

	setButtons()
	{
		if (this.buttons.length > 0)
		{
			this.buttons.forEach(button => {
				const option = Object.assign({}, button);
				button = new Button(option);
				this.getButtonsContainer().appendChild(button.render());
			});
		}
	}

	render()
	{
		if (this.target)
		{
			this.getWrapper().style.setProperty('height', this.target.offsetHeight + 'px');
			this.target.appendChild(this.getWrapper());
			this.getWrapper().appendChild(this.getContainer());
		}

		if (this.items.length > 0)
		{
			this.items.forEach(item => {
				let itemNode = item;
				itemNode = Tag.render`
					<img class="ui-migration-bar__item">
				`;

				this.getItemContainer().appendChild(itemNode);

				const itemKeys = Object.keys(item);
				for (let i = 0; i < itemKeys.length; i++)
				{
					const event = itemKeys[i];
					itemNode.setAttribute(event, item[event]);
				}
			});
		}

		if (this.link?.text)
		{
			this.getItemContainer().appendChild(this.getLink());
		}
	}

	remove()
	{
		this.getContainer().classList.add('--close');
		this.getContainer().addEventListener('animationend', () => {
			this.getContainer().classList.remove('--close');
			this.getContainer().remove();
			this.getWrapper().remove();
		}, { once: true });
	}

	show()
	{
		this.adjustItemData();
		this.setButtons();
		this.render();
	}
}
