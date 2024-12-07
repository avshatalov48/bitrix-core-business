import { Dom, Tag, Text, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { PopupComponentsMaker } from 'ui.popupcomponentsmaker';
import { PopupHeader } from './popup-header';
import { Popup } from 'main.popup';
import { BaseTemplate } from 'ui.popup-with-header';
import './styles.css';
import { Skeleton } from './skeleton';

export class PopupWithHeader extends PopupComponentsMaker
{
	headerWrapper: ?HTMLElement;
	#popupOptions: Object;

	constructor(options)
	{
		super(options);
		this.header = (options.header instanceof PopupHeader) ? options.header : null;
		this.template = options.template instanceof BaseTemplate ? options.template : null;
		this.asyncData = (options.asyncData instanceof BX.Promise || options.asyncData instanceof Promise) ? options.asyncData : null;
		this.animationTemplate = options.animationTemplate ?? true;
		this.skeletonSize = options.skeletonSize ?? 473;
		this.analyticsCallback = Type.isFunction(options.analyticsCallback) ? options.analyticsCallback : null;
		this.#popupOptions = Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};
	}

	getPopup(): Popup
	{
		if (!this.popup)
		{
			const popupWidth = this.width ? this.width : 344;
			const popupId = this.id ? `${this.id}-popup` : null;
			let content = [];

			if (!this.asyncData)
			{
				content = Tag.render`
					<div>
						${this.getHeaderWrapper()}
					<div>
				`;
				if (this.content.length > 0)
				{
					content.append(Tag.render`<div style="padding: 0 ${this.padding}px ${this.padding}px ${this.padding}px">${this.getContentWrapper()}</div>`);
				}
			}

			this.popup = new Popup(popupId, this.target, ({
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
				padding: 0,
				animation: 'fading-slide',
				content,
				cacheable: this.cacheable,
				...this.#popupOptions,
			}));

			if (this.asyncData)
			{
				const container = this.popup.getContentContainer();
				Dom.clean(container);
				Dom.append(this.getSkeleton(), container);
				this.preparePopupAngly(container);

				if (Type.isDomNode(container.parentNode))
				{
					Dom.addClass(container.parentNode, '--with-header');
				}

				this.asyncData.then((response) => {
					Dom.clean(container);
					response.data.header.analyticsCallback = this.analyticsCallback;
					this.header = PopupHeader.createByJson(popupId, response.data.header);
					content = Tag.render`
						<div>
							${this.getHeaderWrapper()}
						<div>
					`;

					let hasContent = response.data.items && this.template;

					if (hasContent)
					{
						this.template.setOptions({
							items: response.data.items,
							analyticsCallback: this.analyticsCallback,
						});
						this.content = this.template.getContent();
						this.contentWrapper = null;

						if (Dom.hasClass(this.getHeaderWrapper(), '--empty-content'))
						{
							Dom.removeClass(this.getHeaderWrapper(), '--empty-content');
						}

						if (
							!this.getHeaderWrapper().querySelector('.ui-popupcomponentsmaker__round-player-box')
							&& !Dom.hasClass(this.getHeaderWrapper(), '--without-video')
						)
						{
							Dom.addClass(this.getHeaderWrapper(), '--without-video');
						}

						if (this.content.length > 0)
						{
							content.append(Tag.render`<div class="ui-popupcomponentmaker__content-wrap">${this.getContentWrapper()}</div>`);
						}
						else
						{
							hasContent = false;
						}
					}

					Dom.append(content, container);

					if (hasContent)
					{
						if (this.popup.isShown())
						{
							this.#prepareItemsContent(content);
						}
						else
						{
							this.popup.subscribeOnce('onShow', () => {
								this.#prepareItemsContent(content);
							});
						}
					}

					this.popup.adjustPosition({ forceBindPosition: true, position: this.popup.isBottomAngle() ? 'top' : 'bottom' });
				});
			}

			this.popup.getContentContainer().style.overflowX = null;
		}

		return this.popup;
	}

	#prepareItemsContent(content: HTMLElement): void
	{
		Dom.addClass(this.getContentWrapper(), 'ui-popup-with-header__content');
		content.append(Tag.render`<div class="ui-popupcomponentmaker__content-wrap">${this.getContentWrapper()}</div>`);

		if (this.popup.isBottomAngle() || !this.animationTemplate)
		{
			Dom.style(this.getContentWrapper(), 'transition', 'none');
		}

		if (this.getContentWrapper().scrollHeight > 287 && !Dom.hasClass(this.getContentWrapper(), '--active-scroll'))
		{
			Dom.style(this.getContentWrapper(), 'height', '287px');
			Dom.style(this.getContentWrapper(), 'overflow-y', 'scroll');
			Dom.addClass(content, 'active-scroll');
		}
		else
		{
			Dom.style(this.getContentWrapper(), 'height', `${this.getContentWrapper().scrollHeight}px`);
		}
	}

	getSkeleton(): HTMLElement
	{
		if (!this.skeleton)
		{
			this.skeleton = (new Skeleton(this.skeletonSize)).get();

			const theme = this.#getThemePicker()?.getAppliedTheme();
			if (!theme)
			{
				return this.skeleton;
			}

			const headerContainer = this.skeleton.querySelector('.popup-with-header-skeleton__header');
			this.#applyTheme(headerContainer, theme);

			EventEmitter.subscribe(
				'BX.Intranet.Bitrix24:ThemePicker:onThemeApply',
				(event) =>
				{
					this.#applyTheme(headerContainer, event.data.theme);
				},
			);
		}

		return this.skeleton;
	}

	preparePopupAngly(popupContainer: HTMLElement): void
	{
		const angly = popupContainer?.parentNode?.querySelector('.popup-window-angly--arrow');

		if (Type.isDomNode(angly))
		{
			const theme = this.#getThemePicker()?.getAppliedTheme();
			if (theme)
			{
				this.#applyTheme(angly, theme);

				EventEmitter.subscribe(
					'BX.Intranet.Bitrix24:ThemePicker:onThemeApply',
					(event) =>
					{
						this.#applyTheme(angly, event.data.theme);
					},
				);
			}

			Dom.style(angly, 'background-position', 'center top');
			Dom.addClass(popupContainer?.parentNode, '--with-header');
		}
	}

	#getThemePicker(): ?BX.Intranet.Bitrix24.ThemePicker
	{
		return BX.Intranet?.Bitrix24?.ThemePicker.Singleton ?? top.BX.Intranet?.Bitrix24?.ThemePicker.Singleton;
	}

	#applyTheme(container, theme): void
	{
		const previewImage = `url('${Text.encode(theme.previewImage)}')`;
		Dom.style(container, 'backgroundImage', previewImage);
		Dom.removeClass(container, 'bitrix24-theme-default bitrix24-theme-dark bitrix24-theme-light');
		let themeClass = 'bitrix24-theme-default';

		if (theme.id !== 'default')
		{
			themeClass = String(theme.id).indexOf('dark:') === 0 ? 'bitrix24-theme-dark' : 'bitrix24-theme-light';
		}

		Dom.addClass(container, themeClass);
	}

	getHeaderWrapper(): ?HTMLElement
	{
		if (!this.header)
		{
			return null;
		}

		if (!this.headerWrapper)
		{
			this.headerWrapper = Tag.render`
				<div class="ui-popupcomponentmaker__header-content"></div>
			`;

			if (this.content.length <= 0)
			{
				this.headerWrapper.classList.add('--empty-content');
			}

			const sectionNode = this.getSection();

			if (this.header?.marginBottom)
			{
				Type.isNumber(this.header.marginBottom)
					? sectionNode.style.marginBottom = `${this.header.marginBottom}px`
					: null;
			}

			if (this.header?.className)
			{
				Dom.addClass(sectionNode, this.header.className);
			}

			if (Type.isDomNode(this.header?.html))
			{
				sectionNode.appendChild(this.getItem(this.header).getContainer());
				this.headerWrapper.appendChild(sectionNode);
			}

			if (Type.isFunction(this.header?.html?.then))
			{
				this.adjustPromise(this.header, sectionNode);
				this.headerWrapper.appendChild(sectionNode);
			}
		}

		return this.headerWrapper;
	}
}
