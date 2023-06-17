import { Tag, Loc, Dom } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import DialogQr from './dialog-qr';

import 'main.qrcode';
import 'ui.design-tokens';
import {Util} from "calendar.util";

type DialogOptions = {
	bindElement: HTMLElement,
	dialogQr: DialogQr,
	sharingUrl: string,
	context: 'calendar' | 'crm',
}

export default class DialogNew
{
	HELP_DESK_CODE_CALENDAR = 17198666;
	HELP_DESK_CODE_CRM = 17502612;
	#popup;
	#layout;
	#dialogQr;
	#context;

	constructor(options: DialogOptions)
	{
		this.#popup = null;
		this.#dialogQr = null;
		this.#layout = {
			wrapper: null,
			contentTop: null,
			contentBody: null,
			contentBottom: null,
			buttonCopy: null
		};
		this.#context = options.context;

		this.bindElement = options.bindElement || null;
		this.sharingUrl = options.sharingUrl || null;
	}

	/**
	 *
	 * @returns {Popup}
	 */
	getPopup()
	{
		if (!this.#popup)
		{
			this.#popup = new Popup({
				bindElement: this.bindElement,
				className: 'calendar-sharing__dialog',
				closeByEsc: true,
				autoHide: true,
				padding: 0,
				width: 470,
				angle: {
					offset: this.bindElement.offsetWidth / 2 + 16
				},
				autoHideHandler: (event) => { return this.autoHideHandler(event)},
				content: this.getPopupWrapper(),
				animation: 'fading-slide',
				events: {
					onPopupShow: () => this.bindElement.classList.add('ui-btn-hover'),
					onPopupClose: () => this.bindElement.classList.remove('ui-btn-hover')
				}
			});
		}

		return this.#popup;
	}

	autoHideHandler(event)
	{
		return !this.#layout.wrapper.contains(event.target) && !this.#dialogQr?.isShown();
	}

	/**
	 *
	 * @returns {DialogQr}
	 */
	getDialogQr()
	{
		if (!this.#dialogQr)
		{
			this.#dialogQr = new DialogQr({
				sharingUrl: this.sharingUrl,
				context: this.#context,
			});
		}

		return this.#dialogQr;
	}

	/**
	 *
	 * @returns {HTMLElement}
	 */
	getPopupWrapper()
	{
		if (!this.#layout.wrapper)
		{
			this.#layout.wrapper = Tag.render`
				<div class="calendar-sharing__dialog-wrapper">
					${this.getPopupContentTop()}
					<div class="calendar-sharing__dialog-body">
						<div class="calendar-sharing__dialog-userpic"></div>
						<div class="calendar-sharing__dialog-notify">
							<div class="calendar-sharing__dialog-notify_content">
								${Loc.getMessage('SHARING_INFO_POPUP_CONTENT_4', {'#LINK#': this.sharingUrl})}
							</div>
						</div>
					</div>
					${this.getPopupContentBottom()}
				</div>
			`;
		}

		return this.#layout.wrapper;
	}

	/**
	 *
	 * @returns {HTMLElement}
	 */
	getPopupCopyLinkButton()
	{
		if (!this.#layout.buttonCopy)
		{
			this.#layout.buttonCopy = Tag.render`
				<span onclick="${this.adjustSave.bind(this)}" class="ui-btn ui-btn-success ui-btn-round ui-btn-no-caps">${Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_COPY_LINK_BUTTON')}</span>
			`;
		}

		return this.#layout.buttonCopy;
	}

	adjustSave()
	{
		if (this.copyLink())
		{
			this.onSuccessfulCopyingLink();
		}
	}

	copyLink()
	{
		let result = false;
		if (this.sharingUrl)
		{
			result = BX.clipboard.copy(this.sharingUrl);
		}
		if (result)
		{
			Util.showNotification(Loc.getMessage('SHARING_COPY_LINK_NOTIFICATION'));
			EventEmitter.emit('CalendarSharing:LinkCopied');
		}
		return result;
	}

	onSuccessfulCopyingLink()
	{
		this.getPopup().close();
	}

	/**
	 *
	 * @returns {HTMLElement}
	 */
	getPopupContentBottom()
	{
		if (!this.#layout.contentBottom)
		{
			const adjustClick = () => {
				this.getDialogQr().show();
			};

			this.#layout.contentBottom = Tag.render`
				<div class="calendar-sharing__dialog-bottom">
					${this.getPopupCopyLinkButton()}
					<span onclick="${adjustClick}" class="calendar-sharing__dialog-link">${Loc.getMessage('SHARING_INFO_POPUP_WHAT_SEE_USERS')}</span>
				</div>
			`;
		}

		return this.#layout.contentBottom;
	}

	/**
	 *
	 * @returns {HTMLElement}
	 */
	getPopupContentTop()
	{
		if (!this.#layout.contentTop)
		{
			const openHelpDesk = () => {
				top.BX.Helper.show('redirect=detail&code=' + this.getHelpDeskCodeDependsOnContext());
			};

			this.#layout.contentTop = Tag.render`
				<div class="calendar-sharing__dialog-top">
					<div class="calendar-sharing__dialog-title">
						<span>${Loc.getMessage('SHARING_BUTTON_TITLE')}</span>
						<span onclick="${openHelpDesk}" class="calendar-sharing__dialog-title-help"  title="${Loc.getMessage('SHARING_INFO_POPUP_HOW_IT_WORK')}"></span>
					</div>
					<div class="calendar-sharing__dialog-info">${this.getPhraseDependsOnContext('SHARING_INFO_POPUP_CONTENT_3') + ' '}</div>
				</div>
			`;

			const infoNotify = this.#layout.contentTop.querySelector('[ data-role="calendar-sharing_popup-open-link"]');

			if (infoNotify)
			{
				let infoNotifyHint;
				let timer;
				infoNotify.addEventListener('mouseenter', () => {
					timer = setTimeout(()=> {
						if (!infoNotifyHint)
						{
							infoNotifyHint = new Popup({
								bindElement: infoNotify,
								angle: {
									offset: infoNotify.offsetWidth / 2 + 16
								},
								width: 410,
								darkMode: true,
								content: Loc.getMessage('SHARING_INFO_POPUP_SLOT_DESC'),
								animation: 'fading-slide',
							});
						}
						infoNotifyHint.show()
					}, 1000);
				});

				infoNotify.addEventListener('mouseleave', () => {
					clearTimeout(timer);
					if (infoNotifyHint)
					{
						infoNotifyHint.close();
					}
				});
			}
		}

		return this.#layout.contentTop;
	}

	isShown()
	{
		return this.getPopup().isShown();
	}

	show(): void
	{
		if (!this.bindElement)
		{
			console.warn('BX.Calendar.Sharing: "bindElement" is not defined');
			return;
		}
		this.getPopup().show();
	}

	destroy(): void
	{
		this.getPopup().destroy();
		this.getDialogQr().destroy();
	}

	getPhraseDependsOnContext(code: string)
	{
		return Loc.getMessage(code + '_' + this.#context.toUpperCase())
	}

	getHelpDeskCodeDependsOnContext()
	{
		let code = 0;
		switch (this.#context)
		{
			case 'calendar':
			{
				code = this.HELP_DESK_CODE_CALENDAR;
				break;
			}
			case 'crm':
			{
				code = this.HELP_DESK_CODE_CRM;
				break;
			}
		}

		return code;
	}
}