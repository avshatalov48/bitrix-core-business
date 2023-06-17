import { Tag, Loc, Event, Dom } from 'main.core';
import { EventEmitter } from 'main.core.events';
import 'ui.icons.b24';

type WelcomeOptions = {
	owner: any,
	link: any,
	currentLang: string,
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

		if (this.#link && this.#link.type === 'crm_deal' && this.#link.active === true)
		{
			this.#handleTimelineNotify('notViewed');
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

		if (this.#link && this.#link.type === 'crm_deal' && this.#link.active === true)
		{
			this.#handleTimelineNotify('viewed');
		}

		this.disableButton();
		EventEmitter.emit('showSlotSelector', this);
	}

	#handleTimelineNotify(mode)
	{
		BX.ajax.runAction('calendar.api.sharingajax.handleTimelineNotify', {
			data: {
				linkHash: this.#link.hash,
				entityId: this.#link.entityId,
				entityType: this.#link.type,
				notifyType: mode
			}
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

			Event.bind(this.#layout.button, 'click', ()=> {
				this.handleWelcomePageButtonClick();
			});

			EventEmitter.subscribe('hideSlotSelector', this.enableButton.bind(this));
		}

		return this.#layout.button;
	}

	#getNodeLabel()
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

	setAccessDenied()
	{
		this.#layout.info = this.#getNodeInfo(true);
	}

	#getNodeInfo(accessDenied = false)
	{
		if (!this.#layout.info)
		{
			this.#layout.infoTitle = Tag.render`
				<div class="calendar-pub-ui__typography-title calendar-pub__welcome-info_title"></div>
			`;
			this.#layout.infoSubTitle = Tag.render`
				<div class="calendar-pub-ui__typography-s"></div>
			`;

			this.#layout.info = Tag.render`
				<div class="calendar-pub__welcome-info">
					<div class="calendar-pub-ui__typography-title calendar-pub__welcome-info_title">${this.#layout.infoTitle}</div>
					<div class="calendar-pub-ui__typography-s">${this.#layout.infoSubTitle}</div>
				</div>
			`;
		}

		let title = Loc.getMessage('CALENDAR_SHARING_MY_FREE_SLOTS');
		let subTitle = Loc.getMessage('CALENDAR_SHARING_YOU_CAN_CHOOSE_FREE_MEETING_TIME');
		if (accessDenied)
		{
			title = Loc.getMessage('CALENDAR_SHARING_SLOTS_ACCESS_DENIED');
			subTitle = Loc.getMessage('CALENDAR_SHARING_SLOTS_ACCESS_DENIED_INFO');
		}

		this.#layout.infoTitle.innerText = title;
		this.#layout.infoSubTitle.innerText = subTitle;

		return this.#layout.info;
	}

	render()
	{
		return Tag.render`
			<div class="calendar-pub__block --welcome">
				${this.#getNodeLabel()}
				<div class="calendar-pub__welcome">
					<div class="calendar-pub__welcome-user">
						<div class="calendar-pub__welcome-userpic ui-icon ui-icon-common-user">
							<i ${this.#photo ? `style="background-image: url(${this.#photo})"` : ''}></i>
						</div>
						<div class="calendar-pub-ui__typography-m">
							${this.#name ? this.#name : ''} ${this.#lastName ? this.#lastName : ''} 
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
	}
}
