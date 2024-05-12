import { Dom, Tag, Text, Type, Event } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { PopupComponentsMaker } from 'ui.popupcomponentsmaker';
import { PopupHeader } from './popup-header';
import { Popup } from 'main.popup';
import { BaseTemplate } from 'ui.popup-with-header';
import './styles.css';

export class PopupWithHeader extends PopupComponentsMaker
{
	headerWrapper: ?HTMLElement;

	constructor(options)
	{
		super(options);
		this.header = (options.header instanceof PopupHeader) ? options.header : null;
		this.template = options.template instanceof BaseTemplate ? options.template : null;
		this.asyncData = options.asyncData instanceof BX.Promise ? options.asyncData : null;
		this.analyticsCallback = Type.isFunction(options.analyticsCallback) ? options.analyticsCallback : null;
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
				padding: 0,
				animation: 'fading-slide',
				content: content,
				cacheable: this.cacheable,
			});

			if (this.blurBlackground)
			{
				Dom.addClass(this.popup.getPopupContainer(), 'popup-with-radius');
				this.setBlurBackground();
				EventEmitter.subscribe(
					EventEmitter.GLOBAL_TARGET,
					'BX.Intranet.Bitrix24:ThemePicker:onThemeApply',
					() => {
						setTimeout(() => {
							this.setBlurBackground();
						}, 200);
					},
				);
			}

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
					this.popup.show();
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
						Dom.addClass(this.getContentWrapper(), 'ui-popup-with-header__content');

						if (this.popup.isBottomAngle())
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

						this.popup.adjustPosition({ forceBindPosition: true, position: this.popup.isBottomAngle() ? 'top' : 'bottom' });
					}
					else
					{
						this.popup.adjustPosition({forceBindPosition: true, position: this.popup.isBottomAngle() ? 'top' : 'bottom' });
					}
				});
			}

			this.popup.getContentContainer().style.overflowX = null;
		}

		return this.popup;
	}

	getSkeleton(): HTMLElement
	{
		if (!this.skeleton)
		{
			this.skeleton = Tag.render`
				<div class="popup-with-header-skeleton__wrap">
					<div class="popup-with-header-skeleton__header">
						<div class="popup-with-header-skeleton__header-top">
							<div class="popup-with-header-skeleton__header-circle">
								<div class="popup-with-header-skeleton__header-circle-inner"></div>
							</div>
							<div style="width: 100%;">
								<div style="margin-bottom: 12px; max-width: 219px; height: 6px; background: rgba(255,255,255,.8);" class="popup-with-header-skeleton__line"></div>
								<div style="max-width: 119px; height: 4px;" class="popup-with-header-skeleton__line"></div>
							</div>
						</div>
						<div class="popup-with-header-skeleton__header-bottom">
							<div class="popup-with-header-skeleton__header-bottom-circle-box">
								<div class="popup-with-header-skeleton__header-bottom-circle"></div>
								<div class="popup-with-header-skeleton__header-bottom-circle-blue"></div>
							</div>
							<div style="width: 100%;">
								<div style="margin-bottom: 9px; max-width: 193px; height: 5px;" class="popup-with-header-skeleton__line"></div>
								<div style="margin-bottom: 15px; max-width: 163px; height: 5px;" class="popup-with-header-skeleton__line"></div>
								<div style="margin-bottom: 9px; max-width: 156px; height: 2px;" class="popup-with-header-skeleton__line"></div>
								<div style="margin-bottom: 9px; max-width: 93px; height: 2px;" class="popup-with-header-skeleton__line"></div>
							</div>
						</div>
					</div>
					<div class="popup-with-header-skeleton__bottom">
						<div class="popup-with-header-skeleton__bottom-inner">
							<div class="popup-with-header-skeleton__bottom-left">
								<div style="margin-bottom: 11px; max-width: 193px; height: 5px;" class="popup-with-header-skeleton__line"></div>
								<div style="margin-bottom: 17px; max-width: 163px; height: 5px;" class="popup-with-header-skeleton__line"></div>
								<div style="margin-bottom: 9px; max-width: 168px; height: 3px; background: rgba(149,156,164,.23);" class="popup-with-header-skeleton__line --dark-animation"></div>
								<div style="margin-bottom: 9px; max-width: 131px; height: 3px; background: rgba(149,156,164,.23);" class="popup-with-header-skeleton__line --dark-animation"></div>
								<div style="margin-bottom: 9px; max-width: 150px; height: 3px; background: rgba(149,156,164,.23);" class="popup-with-header-skeleton__line --dark-animation"></div>
								<div style="margin-bottom: 9px; max-width: 56px; height: 5px; background: rgba(32,102,176,.23);" class="popup-with-header-skeleton__line"></div>
							</div>
							<div class="popup-with-header-skeleton__bottom-right">
								<div class="popup-with-header-skeleton-btn"></div>
								<div style="margin: 0 auto; max-width: 36px; height: 3px; background: #d9d9d9;" class="popup-with-header-skeleton__line"></div>
							</div>
						</div>
					</div>
				</div>
			`;

			const theme = this.#getThemePicker().getAppliedTheme();
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
			const theme = this.#getThemePicker().getAppliedTheme()
			this.#applyTheme(angly, theme);

			EventEmitter.subscribe(
				'BX.Intranet.Bitrix24:ThemePicker:onThemeApply',
				(event) =>
				{
					this.#applyTheme(angly, event.data.theme);
				},
			);

			Dom.style(angly, 'background-position', 'center top');
			Dom.addClass(popupContainer?.parentNode, '--with-header');
		}
	}

	#getThemePicker(): BX.Intranet.Bitrix24.ThemePicker
	{
		return BX.Intranet.Bitrix24.ThemePicker.Singleton ?? top.BX.Intranet.Bitrix24.ThemePicker.Singleton;
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
