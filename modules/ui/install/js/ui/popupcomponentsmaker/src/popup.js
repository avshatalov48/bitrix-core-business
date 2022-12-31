import {Type, Tag, Dom} from 'main.core';
import {Popup} from 'main.popup';

import PopupComponentsMakerItem from './popup.item';

import 'ui.fonts.opensans';
import 'ui.design-tokens';
import './style.css';

class PopupComponentsMaker
{
	constructor({ id, target, content, width, cacheable })
	{
		this.id = Type.isString(id) ? id : null;
		this.target = Type.isElementNode(target) ? target : null;
		this.content = content || null;
		this.contentWrapper = null;
		this.popup = null;
		this.loader = null;
		this.items = [];
		this.width = Type.isNumber(width) ? width : null;
		this.cacheable = Type.isBoolean(cacheable) ? cacheable : true;
	}

	getItems()
	{
		return this.items;
	}

	getItem(item): PopupComponentsMakerItem
	{
		if (item instanceof PopupComponentsMakerItem)
		{
			return item;
		}

		item = new PopupComponentsMakerItem(item);

		if (this.items.indexOf(item) === -1)
		{
			this.items.push(item);
		}

		return item;
	}

	getPopup(): Popup
	{
		if (!this.popup)
		{
			const popupWidth = this.width ? this.width : 350;

			const popupId = this.id ? this.id + '-popup' : null;

			this.popup = new Popup(popupId, this.target, {
				className: 'ui-popupcomponentmaker',

				contentBackground: 'transparent',
				angle: {
					offset: (popupWidth / 2) - 16
				},
				width: popupWidth,
				offsetLeft: -(popupWidth / 2) + (this.target ? this.target.offsetWidth / 2 : 0) + 40,
				autoHide: true,
				closeByEsc: true,
				padding: 13,
				animation: 'fading-slide',
				content: this.getContentWrapper(),
				cacheable: this.cacheable
			});

			this.popup.getContentContainer().style.overflowX = null;
		}

		return this.popup;
	}

	isShown()
	{
		return this.getPopup().isShown();
	}

	/**
	 * @private
	 */
	getContentWrapper(): HTMLElement
	{
		if (!this.contentWrapper)
		{
			this.contentWrapper = Tag.render`
				<div class="ui-popupcomponentmaker__content"></div>
			`;

			if (!this.content)
			{
				return;
			}

			this.content.map((item)=> {
				let sectionNode = this.getSection()

				if (item?.marginBottom)
				{
					Type.isNumber(item.marginBottom)
						? sectionNode.style.marginBottom = item.marginBottom + 'px'
						: null;
				}
				if (item?.className)
				{
					Dom.addClass(sectionNode, item.className);
				}

				if (item?.attrs)
				{
					Dom.adjust(sectionNode, {attrs: item.attrs});
				}

				if (Type.isDomNode(item?.html))
				{
					sectionNode.appendChild(this.getItem(item).getContainer());
					this.contentWrapper.appendChild(sectionNode);
				}

				if (Type.isArray(item?.html))
				{
					let innerSection = Tag.render`
						<div class="ui-popupcomponentmaker__content--section-item --flex-column --transparent"></div>
					`;

					item.html.map((itemObj)=> {

						if (itemObj?.html?.then)
						{
							this.adjustPromise(itemObj, sectionNode);
							Type.isNumber(itemObj?.marginBottom)
								? sectionNode.style.marginBottom = itemObj.marginBottom + 'px'
								: null;
						}
						else
						{
							if (Type.isArray(itemObj?.html))
							{
								itemObj.html.map((itemInner)=> {
									innerSection.appendChild(this.getItem(itemInner).getContainer());
								});
								sectionNode.appendChild(innerSection);
							}
							else
							{
								sectionNode.appendChild(this.getItem(itemObj).getContainer());
							}
						}
					});
					this.contentWrapper.appendChild(sectionNode);
				}

				if (Type.isFunction(item?.html?.then))
				{
					this.adjustPromise(item, sectionNode);
					this.contentWrapper.appendChild(sectionNode);
				}
			});
		}

		return this.contentWrapper;
	}

	adjustPromise(item: Object, sectionNode: HTMLElement)
	{
		item.awaitContent = true;
		let itemObj = this.getItem(item);

		if (sectionNode)
		{
			sectionNode.appendChild(itemObj.getContainer());
			item?.html?.then((node) => {
				if (Type.isDomNode(node))
				{
					itemObj.stopAwait();
					itemObj.updateContent(node);
				}
			})
		}
	}

	/**
	 * @private
	 */
	getSection(): HTMLElement
	{
		return Tag.render`
			<div class="ui-popupcomponentmaker__content--section"></div>
		`;
	}

	show()
	{
		if (!Type.isDomNode(this.target))
		{
			return;
		}

		this.getPopup().show();
	}

	close()
	{
		this.getPopup().close();
	}
}
export {PopupComponentsMakerItem, PopupComponentsMaker};