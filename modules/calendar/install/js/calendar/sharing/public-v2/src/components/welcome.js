import { Tag, Loc, Event, Dom } from 'main.core';
import { EventEmitter } from 'main.core.events';
import 'ui.icons.b24';
import { Member, MembersList } from './layout/members-list';
import { AvatarHexagonGuest } from 'ui.avatar';

type WelcomeOptions = {
	owner: any,
	link: any,
	currentLang: string,
	members: Member[],
}

export default class Welcome
{
	#owner;
	#link;
	#currentLang;
	#name;
	#lastName;
	#photo;
	#layout;
	#members;
	#isGroupContext;

	constructor(options: WelcomeOptions)
	{
		this.#owner = options.owner || null;
		this.#link = options.link || null;
		this.#currentLang = options.currentLang || null;
		this.#name = this.#owner.name || null;
		this.#lastName = this.#owner.lastName || null;
		this.#photo = this.#owner.photo || null;
		this.#layout = {
			wrapper: null,
			button: null,
			label: null,
		};
		this.#members = options.members;
		this.#isGroupContext = this.#link?.type === 'group';

		if (
			this.#link
			&& this.#link.type === 'crm_deal'
			&& this.#link.active === true
			&& this.#link.lastStatus !== 'viewed'
			&& this.#link.lastStatus !== 'notViewed'
		)
		{
			this.#handleTimelineNotify('notViewed');
			this.#link.lastStatus = 'notViewed';
		}
	}

	disableButton()
	{
		Dom.addClass(this.#layout.button, '--disabled');
	}

	enableButton()
	{
		Dom.removeClass(this.#layout.button, '--disabled');
	}

	hideButton()
	{
		Dom.addClass(this.#layout.button, '--hidden');
	}

	handleWelcomePageButtonClick()
	{
		if (Dom.hasClass(this.#layout.button, '--disabled'))
		{
			return;
		}

		if (
			this.#link
			&& this.#link.type === 'crm_deal'
			&& this.#link.active === true
			&& this.#link.lastStatus === 'notViewed'
		)
		{
			this.#handleTimelineNotify('viewed');
			this.#link.lastStatus = 'viewed';
		}

		this.disableButton();
		EventEmitter.emit('showSlotSelector', this);
	}

	#handleTimelineNotify(mode): void
	{
		void BX.ajax.runAction('calendar.api.sharingajax.handleTimelineNotify', {
			data: {
				linkHash: this.#link.hash,
				entityId: this.#link.entityId,
				entityType: this.#link.type,
				notifyType: mode,
			},
		});
	}

	#getNodeButton(): HTMLElement
	{
		if (!this.#layout.button)
		{
			this.#layout.button = Tag.render`
				<div class="calendar-pub-ui__btn">
					<div class="calendar-pub-ui__btn-text">${Loc.getMessage('CALENDAR_SHARING_SELECT_SLOT')}</div>
				</div>
			`;

			Event.bind(this.#layout.button, 'click', () => {
				this.handleWelcomePageButtonClick();
			});

			EventEmitter.subscribe('hideSlotSelector', this.enableButton.bind(this));
		}

		return this.#layout.button;
	}

	#getNodeLabel(): HTMLElement
	{
		if (!this.#layout.label)
		{
			this.#layout.label = Tag.render`
				<div class="calendar-pub__block-label"></div>
			`;

			if (this.#currentLang === 'ru')
			{
				Dom.addClass(this.#layout.label, '--ru');
			}
		}

		return this.#layout.label;
	}

	setAccessDenied(): void
	{
		this.#layout.info = this.#getNodeInfo(true);
	}

	#getNodeInfo(accessDenied = false): HTMLElement
	{
		if (!this.#layout.info)
		{
			this.#layout.infoTitle = Tag.render`
				<div class="calendar-pub-ui__typography-title calendar-pub__welcome-info_title"></div>
			`;
			this.#layout.infoSubTitle = Tag.render`
				<div class="calendar-pub-ui__typography-s calendar-pub__welcome-info_subtitle"></div>
			`;

			this.#layout.info = Tag.render`
				<div class="calendar-pub__welcome-info">
					${this.#layout.infoTitle}
					${this.#layout.infoSubTitle}
					${this.#renderAvatarsSection(this.#members)}
				</div>
			`;
		}

		const titleMessage = this.#isGroupContext
			? 'CALENDAR_SHARING_GROUP_FREE_SLOTS'
			: 'CALENDAR_SHARING_MY_FREE_SLOTS'
		;
		let title = Loc.getMessage(titleMessage);
		const subtitleMessage = this.#isGroupContext
			? 'CALENDAR_SHARING_GROUP_YOU_CAN_CHOOSE_FREE_MEETING_TIME'
			: 'CALENDAR_SHARING_YOU_CAN_CHOOSE_FREE_MEETING_TIME'
		;
		let subTitle = Loc.getMessage(subtitleMessage);
		if (accessDenied)
		{
			title = Loc.getMessage('CALENDAR_SHARING_SLOTS_ACCESS_DENIED');
			subTitle = Loc.getMessage('CALENDAR_SHARING_SLOTS_ACCESS_DENIED_INFO');
		}

		this.#layout.infoTitle.innerText = title;
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
		this.#layout.infoSubTitle.appendChild(Tag.render`<span>${subTitle}</span>`);

		return this.#layout.info;
	}

	#renderAvatarsSection(members): HTMLElement | string
	{
		return new MembersList({
			className: 'calendar-pub-welcome-avatar-section-container',
			textClassName: 'calendar-pub-ui__typography-xs-uppercase',
			avatarSize: 36,
			members,
			linkContext: this.#link?.type,
		}).render();
	}

	render(): HTMLElement
	{
		const node = Tag.render`
			<div class="calendar-pub__block --welcome">
				${this.#getNodeLabel()}
				<div class="calendar-pub__welcome">
					<div class="calendar-pub__welcome-user">
						<div class="calendar-pub__welcome-userpic ui-icon ui-icon-common-user">
							${this.#renderMainAvatar()}
						</div>
						<div class="calendar-pub-ui__typography-m" title="${this.#name || ''} ${this.#lastName || ''}">
							${this.#name || ''} ${this.#lastName || ''} 
						</div>
					</div>
					<div class="calendar-pub__block-separator"></div>
					${this.#getNodeInfo()}
					<div class="calendar-pub__welcome-bottom">
						${this.#getNodeButton()}
					</div>
				</div>
			</div>
		`;

		if (this.#isGroupContext)
		{
			const avatar = new AvatarHexagonGuest({
				size: 64,
				userName: this.#name.toUpperCase(),
				baseColor: '#19CC45',
				userpicPath: this.#photo,
			});

			avatar.renderTo(node.querySelector('.calendar-pub__group-avatar'));
		}

		return node;
	}

	#renderMainAvatar(): string
	{
		if (this.#isGroupContext)
		{
			return '<div class="calendar-pub__group-avatar"></div>';
		}

		const avatarStyle = this.#photo
			? `style="background-image: url(${encodeURI(this.#photo)})"`
			: ''
		;

		return `<i ${avatarStyle}></i>`;
	}
}
