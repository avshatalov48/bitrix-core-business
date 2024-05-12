import { Dom, Tag, Loc, Event } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import { Analytics } from 'calendar.sharing.analytics';
import DialogQr from './dialog-qr';

import 'main.qrcode';
import 'ui.design-tokens';
import { Util } from 'calendar.util';
import Settings from './rule/settings';
import UserSelector from "./joint/user-selector";
import List from './link-list/list';

type DialogOptions = {
	bindElement: HTMLElement,
	dialogQr: DialogQr,
	userInfo: { name: string, avatar: ?string, id: number } | null,
	sharingUrl: string,
	linkHash: string,
	sharingRule: any,
	calendarSettings: any,
	context: 'calendar' | 'crm',
	readOnly: boolean,
	settingsCollapsed: boolean,
	sortJointLinksByFrequentUse: boolean,
}

export default class DialogNew
{
	// eslint-disable-next-line unicorn/numeric-separators-style
	HELP_DESK_CODE_CALENDAR = 17198666;
	// eslint-disable-next-line unicorn/numeric-separators-style
	HELP_DESK_CODE_CRM = 17502612;
	#options: DialogOptions;
	#popup;
	#layout;
	#dialogQr;
	#context;
	#settings;
	#calendarContext = 'calendar';
	#crmContext = 'crm';
	#settingsControl;
	#userSelectorControl;
	#linkList;

	constructor(options: DialogOptions)
	{
		this.#options = options;
		this.#popup = null;
		this.#dialogQr = null;
		this.#layout = {
			wrapper: null,
			contentWrapper: null,
			contentTop: null,
			contentBody: null,
			contentBottom: null,
			listWrapper: null,
			buttonCopy: null,
			buttonHistory: null,
			buttonWhatSeeUsers: null,
		};
		this.#context = options.context;

		this.#settingsControl = null;
		this.#userSelectorControl = null;
		this.#linkList = null;

		const weekHolidays = new Set(options.calendarSettings.weekHolidays.map((day) => Util.getIndByWeekDay(day)));

		this.#settings = {
			readOnly: options.readOnly ?? false,
			rule: {
				slotSize: options.sharingRule.slotSize ?? 60,
				ranges: options.sharingRule.ranges ?? [],
			},
			weekStart: Util.getIndByWeekDay(options.calendarSettings.weekStart),
			workDays: [0, 1, 2, 3, 4, 5, 6].filter((day) => !weekHolidays.has(day)),
			workTimeStart: options.calendarSettings.workTimeStart,
			workTimeEnd: options.calendarSettings.workTimeEnd,
			collapsed: options.settingsCollapsed,
		};

		this.bindElement = options.bindElement || null;
		this.sharingUrl = options.sharingUrl || null;
		this.linkHash = options.linkHash;
		this.userInfo = options.userInfo || null;

		this.onPopupClose = this.onPopupClose.bind(this);
		this.onCopyButtonClick = this.onCopyButtonClick.bind(this);

		EventEmitter.subscribe('CalendarSharing:onJointLinkCopy', (event) => {
			const shortUrl = event.data.shortUrl;

			if (this.copyLink(shortUrl))
			{
				this.onSuccessfulCopyingLink();
			}

			Analytics.sendLinkCopiedList(this.#context, {
				peopleCount: event.data.members.length + 1,
				ruleChanges: this.#settingsControl.getChanges(),
			});
		});

		this.bindEvents();
	}

	bindEvents()
	{
		Event.bind(window, 'beforeunload', this.saveSharingRule.bind(this));
	}

	/**
	 *
	 * @returns {Popup}
	 */
	getPopup(): Popup
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
					offset: this.bindElement.offsetWidth / 2 + 16,
				},
				autoHideHandler: (event) => this.canBeClosed(event),
				content: this.getPopupWrapper(),
				animation: 'fading-slide',
				events: {
					onPopupShow: this.onPopupShow.bind(this),
					onPopupClose: this.onPopupClose.bind(this),
				},
			});
		}

		return this.#popup;
	}

	onPopupShow()
	{
		Dom.addClass(this.bindElement, 'ui-btn-hover');

		Analytics.sendPopupOpened(this.#context);
	}

	onPopupClose()
	{
		Dom.removeClass(this.bindElement, 'ui-btn-hover');
		this.saveSharingRule();
		this.clearSelectedUsers();
		this.closeLinkList();
	}

	canBeClosed(event): boolean
	{
		const isClickInside = this.#layout.wrapper.contains(event.target);
		const isQrDialogShown = this.#dialogQr?.isShown();
		const isSettingsPopupShown = this.#settingsControl.settingPopupShown();
		const isUserSelectorDialogOpened = this.#userSelectorControl?.isUserSelectorDialogOpened();
		const isListItemPopupOpened = this.#linkList?.isOpenListItemPopup();
		const checkTopSlider = (this.#context === this.#calendarContext)
			? Util.getBX().SidePanel.Instance.getTopSlider()
			: false
		;

		return !isClickInside
			&& !isQrDialogShown
			&& !isSettingsPopupShown
			&& !isUserSelectorDialogOpened
			&& !isListItemPopupOpened
			&& !checkTopSlider
		;
	}

	/**
	 *
	 * @returns {DialogQr}
	 */
	getDialogQr(): DialogQr
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
	getPopupWrapper(): HTMLElement
	{
		if (!this.#layout.wrapper)
		{
			this.#layout.wrapper = Tag.render`
				<div class="calendar-sharing__dialog-wrapper">
					${this.getPopupContentMain()}
					${this.getPopupContentList()}
				</div>
			`;
		}

		return this.#layout.wrapper;
	}

	getPopupContentMain()
	{
		if (!this.#layout.contentWrapper)
		{
			this.#layout.contentWrapper = Tag.render`
				<div class="calendar-sharing__dialog-content-wrapper --show">
					${this.getPopupContentTop()}
						<div class="calendar-sharing__dialog-body">
							<div class="calendar-sharing__dialog-message">
								<div class="calendar-sharing__dialog-info-icon-container">
									<div class="calendar-sharing__dialog-info-icon"></div>
								</div>
								<div class="calendar-sharing__dialog-notify" onclick="${this.onOpenLink.bind(this)}">
									${Loc.getMessage('SHARING_INFO_POPUP_CONTENT_4_V3', { '#LINK#': this.sharingUrl })}
								</div>
							</div>
							${this.getSettingsNode()}
							${this.getUserSelectorNode()}
						</div>
					${this.getPopupContentBottom()}
				</div>
			`;
		}

		return this.#layout.contentWrapper;
	}

	getPopupContentList()
	{
		if (this.#context === this.#crmContext)
		{
			return;
		}

		if (!this.#linkList)
		{
			this.#linkList = new List({
				userInfo: this.userInfo,
				onLinkListClose: this.onLinkListClose.bind(this),
				sortJointLinksByFrequentUse: this.#options.sortJointLinksByFrequentUse,
			});
		}

		return this.#linkList.render();
	}

	/**
	 *
	 * @returns {HTMLElement}
	 */
	getPopupCopyLinkButton(): HTMLElement
	{
		if (!this.#layout.buttonCopy)
		{
			this.#layout.buttonCopy = Tag.render`
				<span class="ui-btn ui-btn-success ui-btn-round ui-btn-no-caps calendar-sharing__dialog-copy">
					${Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_COPY_LINK_BUTTON')}
				</span>
			`;

			Event.bind(this.#layout.buttonCopy, 'click', this.onCopyButtonClick);
		}

		return this.#layout.buttonCopy;
	}

	getPopupLinkHistoryButton(): HTMLElement
	{
		if (this.#context === this.#crmContext)
		{
			return;
		}

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

			Event.bind(this.#layout.buttonHistory, 'click', this.openLinkList.bind(this));
		}

		return this.#layout.buttonHistory;
	}

	getPopupWhatSeeUsersButton()
	{
		if (this.#context === this.#calendarContext)
		{
			return;
		}

		if (!this.#layout.buttonWhatSeeUsers)
		{
			const adjustClick = () => {
				this.saveSharingRule();
				this.getDialogQr().show();
			};

			this.#layout.buttonWhatSeeUsers = Tag.render`
				<span onclick="${adjustClick}" class="calendar-sharing__dialog-link">
					${Loc.getMessage('SHARING_INFO_POPUP_WHAT_SEE_USERS')}
				</span>
			`;

			Event.bind(this.#layout.buttonWhatSeeUsers, 'click', adjustClick);
		}

		return this.#layout.buttonWhatSeeUsers;
	}

	onCopyButtonClick()
	{
		const params = {
			peopleCount: this.#userSelectorControl?.getPeopleCount() ?? 1,
			ruleChanges: this.#settingsControl.getChanges(),
		};

		if (this.#userSelectorControl && this.#userSelectorControl.hasChanges())
		{
			Analytics.sendLinkCopied(this.#context, Analytics.linkTypes.multiple, params);

			this.saveJointLink();
		}
		else if (this.copyLink(this.sharingUrl))
		{
			Analytics.sendLinkCopied(this.#context, Analytics.linkTypes.solo, params);

			if (this.#context === this.#calendarContext)
			{
				BX.ajax.runAction('calendar.api.sharingajax.increaseFrequentUse', {
					data: {
						hash: this.linkHash,
					},
				});
			}

			this.onSuccessfulCopyingLink();
		}
	}

	async saveJointLink()
	{
		if (this.#layout.buttonCopy && Dom.hasClass(this.#layout.buttonCopy, 'ui-btn-clock'))
		{
			return;
		}

		Dom.addClass(this.#layout.buttonCopy, 'ui-btn-clock');

		const memberIds = this.#userSelectorControl.getSelectedUserIdList();

		const response = await BX.ajax.runAction('calendar.api.sharingajax.generateUserJointSharingLink', {
			data: {
				memberIds,
			},
		});

		if (response && response.data)
		{
			Dom.removeClass(this.#layout.buttonCopy, 'ui-btn-clock');

			const { url } = response.data;

			if (this.copyLink(url))
			{
				this.onSuccessfulCopyingLink();
			}

			this.#linkList?.getLinkListInfo();
		}
	}

	clearSelectedUsers()
	{
		if (this.#userSelectorControl)
		{
			this.#userSelectorControl.clearSelectedUsers();
		}
	}

	openLinkList()
	{
		if (this.#layout.contentWrapper && this.#linkList)
		{
			Dom.removeClass(this.#layout.contentWrapper, '--show');
			this.#linkList.show(this.#layout.contentWrapper.offsetHeight);
		}
	}

	closeLinkList()
	{
		if (this.#linkList)
		{
			setTimeout(() => {
				this.#linkList.close();
			}, 200);
		}
	}

	onLinkListClose()
	{
		if (this.#layout.contentWrapper)
		{
			Dom.addClass(this.#layout.contentWrapper, '--show');
		}
	}

	copyLink(linkUrl): boolean
	{
		if (!linkUrl)
		{
			return;
		}

		const result = BX.clipboard.copy(linkUrl);

		if (result)
		{
			Util.showNotification(Loc.getMessage('SHARING_COPY_LINK_NOTIFICATION'));
			EventEmitter.emit('CalendarSharing:LinkCopied');
		}

		return result;
	}

	async onOpenLink()
	{
		await this.saveSharingRule();
		window.open(this.sharingUrl, '_blank').focus();
	}

	onSuccessfulCopyingLink()
	{
		this.getPopup().close();
	}

	getSettingsNode(): HTMLElement
	{
		this.#settingsControl = new Settings(this.#settings);
		this.#settings.rule = this.#settingsControl.getRule();

		return this.#settingsControl.render();
	}

	getUserSelectorNode(): HTMLElement
	{
		if (this.#context === this.#crmContext)
		{
			return;
		}

		this.#userSelectorControl = new UserSelector({
			userInfo: this.userInfo,
			onMembersAdded: () => Analytics.sendMembersAdded(this.#context, this.#userSelectorControl.getPeopleCount()),
		});

		return this.#userSelectorControl.render();
	}

	/**
	 *
	 * @returns {HTMLElement}
	 */
	getPopupContentBottom(): HTMLElement
	{
		if (!this.#layout.contentBottom)
		{
			this.#layout.contentBottom = Tag.render`
				<div class="calendar-sharing__dialog-bottom">
					${this.getPopupCopyLinkButton()}
					${this.getPopupLinkHistoryButton()}
					${this.getPopupWhatSeeUsersButton()}
				</div>
			`;
		}

		return this.#layout.contentBottom;
	}

	/**
	 *
	 * @returns {HTMLElement}
	 */
	getPopupContentTop(): HTMLElement
	{
		if (!this.#layout.contentTop)
		{
			const openHelpDesk = () => {
				top.BX.Helper.show(`redirect=detail&code=${this.getHelpDeskCodeDependsOnContext()}`);
			};

			this.#layout.contentTop = Tag.render`
				<div class="calendar-sharing__dialog-top">
					<div class="calendar-sharing__dialog-title">
						<span>${Loc.getMessage('SHARING_BUTTON_TITLE')}</span>
						<span onclick="${openHelpDesk}" class="calendar-sharing__dialog-title-help" title="${Loc.getMessage('SHARING_INFO_POPUP_HOW_IT_WORK')}"></span>
					</div>
					<div class="calendar-sharing__dialog-info">${`${this.getPhraseDependsOnContext('SHARING_INFO_POPUP_CONTENT_3')} `}</div>
				</div>
			`;

			const infoNotify = this.#layout.contentTop.querySelector('[ data-role="calendar-sharing_popup-open-link"]');

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

		return this.#layout.contentTop;
	}

	isShown(): boolean
	{
		return this.getPopup().isShown();
	}

	show(): void
	{
		if (!this.bindElement)
		{
			// eslint-disable-next-line no-console
			console.warn('BX.Calendar.Sharing: "bindElement" is not defined');

			return;
		}

		this.#settingsControl?.sortRanges();
		this.#settingsControl?.updateRanges();
		this.#settingsControl?.renderRanges();

		this.getPopup().show();
	}

	destroy(): void
	{
		this.getPopup().destroy();
		this.getDialogQr().destroy();
	}

	getPhraseDependsOnContext(code: string): string
	{
		return Loc.getMessage(`${code}_${this.#context.toUpperCase()}`);
	}

	getHelpDeskCodeDependsOnContext(): number
	{
		let code;
		switch (this.#context)
		{
			case this.#calendarContext:
			{
				code = this.HELP_DESK_CODE_CALENDAR;
				break;
			}

			case this.#crmContext:
			{
				code = this.HELP_DESK_CODE_CRM;
				break;
			}

			default:
				code = 0;
		}

		return code;
	}

	saveSharingRule()
	{
		if (!this.#settingsControl.isDifferentFrom(this.#settings.rule))
		{
			return;
		}

		const changes = this.#settingsControl.getChanges();
		Analytics.sendRuleUpdated(this.#context, changes);

		const newRule = this.#settingsControl.getRule();
		BX.ajax.runAction('calendar.api.sharingajax.saveLinkRule', {
			data: {
				linkHash: this.linkHash,
				ruleArray: newRule,
			},
		}).then(() => {
			this.#settings.rule = newRule;
			EventEmitter.emit('CalendarSharing:RuleUpdated');
		}, (error) => {
			// eslint-disable-next-line no-console
			console.error(error);
		});
	}

	getSettingsControlRule(): any
	{
		return this.#settingsControl?.getRule();
	}
}
