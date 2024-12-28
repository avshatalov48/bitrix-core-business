import { Dom, Event, Loc, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import { Analytics } from 'calendar.sharing.analytics';
import { Util } from 'calendar.util';
import Settings from './rule/settings';
import UserSelector from './joint/user-selector';
import List from './link-list/list';

import { SettingsModel } from '../model/index';

export type LayoutParams = {
	readOnly: boolean,
	settingsModel: SettingsModel,
	externalIcon: HTMLElement,
};

export class Layout
{
	HELP_DESK_CODE_CALENDAR = 17198666;
	HELP_DESK_CODE_CRM = 17502612;

	CONTEXT = {
		CRM: 'crm',
		CALENDAR: 'calendar',
	};

	#params: LayoutParams;

	#layout: {
		wrap: HTMLElement,
		main: HTMLElement,
		mainTop: HTMLElement,
		mainBottom: HTMLElement,
		buttonHistory: HTMLElement,
		buttonCopy: HTMLElement,
		buttonWhatSeeUsers: HTMLElement,
	};

	#settingsControl: Settings;
	#userSelectorControl: UserSelector;
	#linkList: List;

	constructor(params: LayoutParams)
	{
		this.#params = params;
		this.#layout = {};
		this.#bindEvents();
		this.isGroupContext = params.settingsModel.getCalendarContext()?.sharingObjectType === 'group';
	}

	get #settingsModel(): SettingsModel
	{
		return this.#params.settingsModel;
	}

	#bindEvents(): void
	{
		Event.bind(window, 'beforeunload', () => this.#settingsModel.save());
		EventEmitter.subscribe('CalendarSharing:onJointLinkCopy', async (event) => {
			const shortUrl = event.data.shortUrl;
			const linkHash = event.data.hash;

			await this.copyLink(shortUrl, linkHash);

			Analytics.sendLinkCopiedList(this.#settingsModel.getContext(), {
				peopleCount: event.data.members.length + 1,
				ruleChanges: this.#settingsModel.getChanges(),
			});
		});
	}

	reset(): void
	{
		void this.#settingsModel.save();
		this.#userSelectorControl?.clearSelectedUsers();
		setTimeout(() => this.#linkList?.close(), 200);
	}

	hasShownPopups(): boolean
	{
		const isSettingsPopupShown = this.#settingsControl.hasShownPopups();
		const isUserSelectorDialogOpened = this.#userSelectorControl?.isUserSelectorDialogOpened();
		const isListItemPopupOpened = this.#linkList?.isOpenListItemPopup();

		return isSettingsPopupShown || isUserSelectorDialogOpened || isListItemPopupOpened;
	}

	render(): HTMLElement
	{
		this.#layout.wrap = Tag.render`
			<div class="calendar-sharing__dialog-wrapper">
				${this.#renderMain()}
				${this.isGroupContext ? null : this.#renderLinkList()}
			</div>
		`;

		return this.#layout.wrap;
	}

	#renderMain(): HTMLElement
	{
		this.#layout.main ??= Tag.render`
			<div class="calendar-sharing__dialog-content-wrapper --show">
				${this.#renderTop()}
				<div class="calendar-sharing__dialog-body">
					${this.#renderDialogMessage()}
					${this.#renderSettings()}
					${this.#renderMembers()}
				</div>
				${this.#renderMainBottom()}
			</div>
		`;

		return this.#layout.main;
	}

	#renderDialogMessage(): HTMLElement | string
	{
		if (
			this.#settingsModel.getContext() === this.CONTEXT.CRM
			|| this.isGroupContext
		)
		{
			return '';
		}

		return Tag.render`
			<div class="calendar-sharing__dialog-message">
				<div class="calendar-sharing__dialog-info-icon-container">
					<div class="calendar-sharing__dialog-info-icon"></div>
				</div>
				<div class="calendar-sharing__dialog-notify" onclick="${this.#onOpenLink.bind(this)}">
					${Loc.getMessage('SHARING_INFO_POPUP_CONTENT_4_V3', { '#LINK#': this.#settingsModel.getSharingUrl() })}
				</div>
			</div>
		`;
	}

	async #onOpenLink(): Promise
	{
		await this.#settingsModel.save();
		window.open(this.#settingsModel.getSharingUrl(), '_blank').focus();
	}

	#renderTop(): HTMLElement
	{
		if (!this.#layout.mainTop)
		{
			this.#layout.mainTop = Tag.render`
				<div class="calendar-sharing__dialog-top">
					<div class="calendar-sharing__dialog-title">
						<span>${Loc.getMessage('SHARING_BUTTON_TITLE')}</span>
						${this.#renderHowDoesItWorkIcon()}
						${this.#params.externalIcon ?? ''}
					</div>
					<div class="calendar-sharing__dialog-info">
						${this.#getSharingInfoMessage()}
					</div>
				</div>
			`;

			const howDoesItWork = this.#layout.mainTop.querySelector('[data-role="calendar-sharing-how-does-it-work"]');
			Event.bind(howDoesItWork, 'click', this.#openHelpDesk.bind(this));

			const infoNotify = this.#layout.mainTop.querySelector('[data-role="calendar-sharing_popup-open-link"]');

			if (infoNotify)
			{
				let infoNotifyHint;
				let timer;
				Event.bind(infoNotify, 'mouseenter', () => {
					timer = setTimeout(() => {
						if (!infoNotifyHint)
						{
							infoNotifyHint = new Popup({
								bindElement: infoNotify,
								angle: {
									offset: infoNotify.offsetWidth / 2 + 16,
								},
								width: 410,
								darkMode: true,
								content: Loc.getMessage('SHARING_INFO_POPUP_SLOT_DESC'),
								animation: 'fading-slide',
							});
						}
						infoNotifyHint.show();
					}, 1000);
				});

				Event.bind(infoNotify, 'mouseleave', () => {
					clearTimeout(timer);
					if (infoNotifyHint)
					{
						infoNotifyHint.close();
					}
				});
			}
		}

		return this.#layout.mainTop;
	}

	#renderHowDoesItWorkIcon(): HTMLElement | string
	{
		if (this.#settingsModel.getContext() === this.CONTEXT.CRM)
		{
			return '';
		}

		const howDoesItWork = Tag.render`
			<span
				class="calendar-sharing__dialog-title-help"
				title="${Loc.getMessage('SHARING_INFO_POPUP_HOW_IT_WORK')}"
			></span>
		`;

		Event.bind(howDoesItWork, 'click', this.#openHelpDesk.bind(this));

		return howDoesItWork;
	}

	#openHelpDesk(): void
	{
		top.BX.Helper.show(`redirect=detail&code=${this.#getContextHelpDeskCode()}`);
	}

	#getSharingInfoMessage(): string
	{
		switch (this.#settingsModel.getContext())
		{
			case this.CONTEXT.CALENDAR:
				return Loc.getMessage('SHARING_INFO_POPUP_CONTENT_3_CALENDAR');
			case this.CONTEXT.CRM:
				return Loc.getMessage('SHARING_INFO_POPUP_CONTENT_3_CRM_MSGVER_2');
			default:
				return '';
		}
	}

	#getContextHelpDeskCode(): number
	{
		switch (this.#settingsModel.getContext())
		{
			case this.CONTEXT.CALENDAR:
				return this.HELP_DESK_CODE_CALENDAR;

			case this.CONTEXT.CRM:
				return this.HELP_DESK_CODE_CRM;

			default:
				return 0;
		}
	}

	#renderSettings(): HTMLElement
	{
		this.#settingsControl = new Settings({
			readOnly: this.#params.readOnly,
			model: this.#settingsModel,
		});

		return this.#settingsControl.render();
	}

	#renderMembers(): HTMLElement
	{
		this.#userSelectorControl = new UserSelector({
			model: this.#settingsModel,
			onMembersAdded: () => Analytics.sendMembersAdded(
				this.#settingsModel.getContext(),
				this.#userSelectorControl.getPeopleCount(),
			),
		});

		return this.#userSelectorControl.render();
	}

	#renderMainBottom(): HTMLElement
	{
		if (this.#settingsModel.getContext() === this.CONTEXT.CRM)
		{
			return '';
		}

		this.#layout.mainBottom ??= Tag.render`
			<div class="calendar-sharing__dialog-bottom">
				${this.#renderCopyLinkButton()}
				${this.isGroupContext ? null : this.#renderLinkHistoryButton()}
			</div>
		`;

		return this.#layout.mainBottom;
	}

	#renderCopyLinkButton(): HTMLElement
	{
		if (!this.#layout.buttonCopy)
		{
			this.#layout.buttonCopy = Tag.render`
				<span class="ui-btn ui-btn-success ui-btn-round ui-btn-no-caps calendar-sharing__dialog-copy">
					${Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_COPY_LINK_BUTTON')}
				</span>
			`;

			Event.bind(this.#layout.buttonCopy, 'click', this.#onButtonCopyClick.bind(this));
		}

		return this.#layout.buttonCopy;
	}

	async #onButtonCopyClick(): void
	{
		const params = {
			peopleCount: this.#userSelectorControl?.getPeopleCount() ?? 1,
			ruleChanges: this.#settingsModel.getChanges(),
		};

		if (this.#userSelectorControl && this.#userSelectorControl.hasChanges())
		{
			Analytics.sendLinkCopied(this.#settingsModel.getContext(), Analytics.linkTypes.multiple, params);

			void this.saveJointLink();
		}
		else if (await this.copyLink(this.#settingsModel.getSharingUrl()))
		{
			Analytics.sendLinkCopied(this.#settingsModel.getContext(), Analytics.linkTypes.solo, params);

			if (!this.isGroupContext)
			{
				this.#settingsModel.increaseFrequentUse();
			}
		}
	}

	async saveJointLink(): Promise
	{
		if (this.#layout.buttonCopy && Dom.hasClass(this.#layout.buttonCopy, 'ui-btn-clock'))
		{
			return;
		}

		Dom.addClass(this.#layout.buttonCopy, 'ui-btn-clock');

		const link = await this.#settingsModel.saveJointLink();

		Dom.removeClass(this.#layout.buttonCopy, 'ui-btn-clock');

		await this.copyLink(link.url, link.hash);

		this.#linkList?.getLinkListInfo();
	}

	#renderLinkHistoryButton(): HTMLElement
	{
		if (!this.#layout.buttonHistory)
		{
			this.#layout.buttonHistory = Tag.render`
				<span
					class="ui-btn ui-btn-round ui-btn-light ui-btn-no-caps calendar-sharing__dialog-people"
					data-id="calendar-sharing-history-btn"
				>
					${Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_JOINT_SLOTS_BUTTON')}
				</span>
			`;

			Event.bind(this.#layout.buttonHistory, 'click', this.#openLinkList.bind(this));
		}

		return this.#layout.buttonHistory;
	}

	#renderLinkList(): HTMLElement | null
	{
		if (this.#settingsModel.getContext() === this.CONTEXT.CRM)
		{
			return null;
		}

		return this.#getLinkList().render();
	}

	#getLinkList(): List
	{
		this.#linkList ??= new List({
			model: this.#settingsModel,
			onLinkListClose: this.#closeLinkList.bind(this),
		});

		return this.#linkList;
	}

	#openLinkList(): void
	{
		Dom.removeClass(this.#layout.main, '--show');
		this.#linkList.show(this.#layout.main.offsetHeight);
	}

	#closeLinkList(): void
	{
		Dom.addClass(this.#layout.main, '--show');
	}

	async copyLink(url: string, hash: string): Promise<boolean>
	{
		if (!url)
		{
			return false;
		}

		try
		{
			await this.#copyToClipboard(url);
		}
		catch
		{
			return false;
		}

		Util.showNotification(Loc.getMessage('SHARING_COPY_LINK_NOTIFICATION'));
		EventEmitter.emit('CalendarSharing:LinkCopied', { url, hash });

		return true;
	}

	async #copyToClipboard(textToCopy: string): Promise<void>
	{
		if (!Type.isString(textToCopy))
		{
			return Promise.reject();
		}

		// navigator.clipboard defined only if window.isSecureContext === true
		// so or https should be activated, or localhost address
		if (navigator.clipboard)
		{
			// safari not allowed clipboard manipulation as result of ajax request
			// so timeout is hack for this, to prevent "not have permission"
			return new Promise((resolve, reject) => {
				setTimeout(() => (
					navigator.clipboard
						.writeText(textToCopy)
						.then(() => resolve())
						.catch((e) => reject(e))
				), 0);
			});
		}

		return BX.clipboard?.copy(textToCopy) ? Promise.resolve() : Promise.reject();
	}
}
