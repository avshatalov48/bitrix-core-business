import { Type, Tag, Dom, Reflection } from 'main.core';
import {Popup} from 'main.popup';
import {EventEmitter} from "main.core.events";
import PopupComponentsMakerItem from './popup.item';

import 'ui.fonts.opensans';
import 'ui.design-tokens';
import './style.css';

export default class PopupComponentsMaker
{
	constructor({
		id,
		target,
		content,
		width,
		cacheable,
		contentPadding,
		padding,
		offsetTop,
		blurBackground,
		useAngle,
	})
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
		this.contentPadding = Type.isNumber(contentPadding) ? contentPadding : 0;
		this.padding = Type.isNumber(padding) ? padding : 13;
		this.offsetTop = Type.isNumber(offsetTop) ? offsetTop : 0;
		this.blurBlackground = Type.isBoolean(blurBackground) ? blurBackground : false;
		this.useAngle = (Type.isUndefined(useAngle) || useAngle !== false);
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
				contentPadding: this.contentPadding,
				angle: this.useAngle
					? {
						offset: (popupWidth / 2) - 16,
					}
					: false,
				offsetTop: this.offsetTop,
				width: popupWidth,
				offsetLeft: -(popupWidth / 2) + (this.target ? this.target.offsetWidth / 2 : 0) + 40,
				autoHide: true,
				closeByEsc: true,
				padding: this.padding,
				animation: 'fading-slide',
				content: this.getContentWrapper(),
				cacheable: this.cacheable,
			});

			if (this.blurBlackground)
			{
				Dom.addClass(this.popup.getPopupContainer(), 'popup-with-radius');
				this.setBlurBackground();
				EventEmitter.subscribe(
					EventEmitter.GLOBAL_TARGET,
					'BX.Intranet.Bitrix24:ThemePicker:onThemeApply', () => {
						setTimeout(() => {
							this.setBlurBackground();
						}, 200)
					}
				);
			}

			this.popup.getContentContainer().style.overflowX = null;
		}

		return this.popup;
	}

	isShown()
	{
		return this.getPopup().isShown();
	}

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
			item?.html?.then((result) => {
				if (Type.isDomNode(result))
				{
					itemObj.stopAwait();
					itemObj.updateContent(result);
				}
				else if (Type.isPlainObject(result) && Type.isDomNode(result.node))
				{
					if (Type.isPlainObject(result.options))
					{
						itemObj.setParams(result.options);
					}
					itemObj.stopAwait();
					itemObj.updateContent(result.node);
				}
			});
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

	setBlurBackground(): void
	{
		const container = this.getPopup().getPopupContainer();
		const windowStyles = window.getComputedStyle(document.body);
		const backgroundImage = windowStyles.backgroundImage;
		const backgroundColor = windowStyles.backgroundColor;

		if (Type.isDomNode(container))
		{
			Dom.addClass(container, 'popup-window-blur');
		}

		let blurStyle = Dom.create('style', {
			attrs: {
				type: 'text/css',
				id: 'styles-widget-blur',
			}
		});

		let styles = '.popup-window-content:after { '
			+ 'background-image: '
			+ backgroundImage
			+ ';'
			+ 'background-color: '
			+ backgroundColor
			+ '} ';

		styles = document.createTextNode(styles);
		blurStyle.appendChild(styles);

		let stylesWithAngle = '.popup-window-angly:after { ' + 'background-color: ' + backgroundColor + '} ';

		stylesWithAngle = document.createTextNode(stylesWithAngle);
		blurStyle.appendChild(stylesWithAngle);
		const headStyle = document.head.querySelector('#styles-widget-blur');

		if (headStyle)
		{
			Dom.replace(headStyle, blurStyle);
		}
		else
		{
			document.head.appendChild(blurStyle);
		}
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
