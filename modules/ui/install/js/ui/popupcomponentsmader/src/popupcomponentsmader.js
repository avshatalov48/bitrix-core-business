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

				if (Type.isDomNode(item?.html))
				{
					let sectionNode = this.getSection();
					sectionNode.appendChild(this.getSectionItem(item));
					this.contentWrapper.appendChild(sectionNode);
				}

				if (Type.isArray(item?.html))
				{
					let sectionNode = this.getSection();

					item.html.map((itemObj)=> {
						sectionNode.appendChild(this.getSectionItem(itemObj));
					});

					this.contentWrapper.appendChild(sectionNode);
				}
			});
		}

		return this.contentWrapper;
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

	/**
	 * @private
	 */
	getSectionItem(item: Object): HTMLElement
	{
		if (!Type.isDomNode(item?.html))
		{
			return;
		}

		let sectionItemNode = Tag.render`
			<div class="ui-qr-popupcomponentmader__content--section-item">${item.html}</div>
		`;

		if (Type.isBoolean(item?.withoutBackground))
		{
			sectionItemNode.classList.add('--transparent');
		}

		if (Type.isNumber(item?.flex))
		{
			sectionItemNode.style.flex = item?.flex;
		}

		return sectionItemNode;
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