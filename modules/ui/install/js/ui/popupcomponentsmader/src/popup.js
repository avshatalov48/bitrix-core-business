import PopupComponentsMaderItem from './popup.item';
import { Type, Tag } from 'main.core';
import { Popup } from 'main.popup';

export class PopupComponentsMader
{
	constructor(options = {})
	{
		this.target = Type.isElementNode(options.target) ? options.target : null;
		this.content = options.content || null;
		this.contentWrapper = null;
		this.popup = null;
		this.loader = null;
	}

	getItem(item)
	{
		if (item instanceof PopupComponentsMaderItem)
		{
			return item;
		}

		return new PopupComponentsMaderItem(item);
	}

	addItem(item, sectionNode: HTMLElement): PopupComponentsMaderItem
	{
		if (!(item instanceof PopupComponentsMaderItem))
		{
			item = this.getItem(item);
		}

		if (Type.isDomNode(sectionNode))
		{
			sectionNode.appendChild(item.getContainer());
		}
	}

	getPopup(): Popup
	{
		if (!this.popup)
		{
			let popupWidth = 350;

			this.popup = new Popup(null, this.target, {
				className: 'ui-qr-popupcomponentmader',
				background: 'rgba(255,255,255,.88)',
				contentBackground: 'transparent',
				angle: {
					offset: (popupWidth / 2) - 16
				},
				maxWidth: popupWidth,
				offsetLeft: -(popupWidth / 2) + (this.target.offsetWidth / 2) + 40,
				autoHide: true,
				closeByEsc: true,
				padding: 13,
				animation: 'fading-slide',
				content: this.getContentWrapper()
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
				<div class="ui-qr-popupcomponentmader__content"></div>
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
						: sectionNode.style.marginBottom = item.marginBottom;
				}

				if (Type.isDomNode(item?.html))
				{
					sectionNode.appendChild(this.getItem(item).getContainer());
					this.contentWrapper.appendChild(sectionNode);
				}

				if (Type.isArray(item?.html))
				{
					item.html.map((itemObj)=> {
						itemObj?.html?.then
							? this.adjustPromise(itemObj, sectionNode)
							: sectionNode.appendChild(this.getItem(itemObj).getContainer());
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
			<div class="ui-qr-popupcomponentmader__content--section"></div>
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