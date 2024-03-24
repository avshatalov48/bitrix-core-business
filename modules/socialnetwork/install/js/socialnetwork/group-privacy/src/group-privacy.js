import { Dom, Event, Loc, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import './group-privacy.css';

type Params = {
	bindElement: HTMLElement,
	privacyCode: 'open' | 'closed' | 'secret',
}

export class GroupPrivacy extends EventEmitter
{
	#popup: Popup;
	#contentNode: HTMLElement;
	#privacyCode: string;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Group.Privacy');

		this.#privacyCode = params.privacyCode;
	}

	show(bindElement)
	{
		this.#getPopup(bindElement).show();
	}

	#getPopup(bindElement: HTMLElement): Popup
	{
		this.#popup ??= this.#createPopup(bindElement);
		return this.#popup;
	}

	#createPopup(bindElement: HTMLElement): Popup
	{
		const popup = new Popup(
			{
				id: 'sn-post-form',
				bindElement,
				content: this.#renderContent(this.#privacyCode),
				autoHide: true,
				angle: false,
				width: 343,
				closeIcon: false,
				closeByEsc: true,
				overlay: true,
				padding: 0,
				animation: 'fading-slide',
			},
		);

		popup.subscribe('onShow', () => this.emit('onShow'));
		popup.subscribe('onAfterClose', () => this.emit('onAfterClose'));

		return popup;
	}

	#renderContent(privacyCode: 'open' | 'closed' | 'secret'): HTMLElement
	{
		const openActiveClass = privacyCode === 'open' ? '--active' : '';
		const closedActiveClass = privacyCode === 'closed' ? '--active' : '';
		const secretActiveClass = privacyCode === 'secret' ? '--active' : '';

		const openId = 'spaces-group-privacy-open';
		const closedId = 'spaces-group-privacy-closed';
		const secretId = 'spaces-group-privacy-secret';

		this.#contentNode = Tag.render`
			<div class="sn-spaces__popup-menu">
				<div data-id="${openId}" class="sn-spaces__popup-menu_item ${openActiveClass}">
					<div class="sn-spaces__popup-menu_item-icon --open-spaces"></div>
					<div class="sn-spaces__popup-menu_item-info">
						<div class="sn-spaces__popup-menu_item-name">
							${Loc.getMessage('SN_SPACES_GROUP_PRIVACY_OPEN')}
						</div>
						<div class="sn-spaces__popup-menu_item-description">
							${Loc.getMessage('SN_SPACES_GROUP_PRIVACY_OPEN_DESC')}
						</div>
					</div>
				</div>
				<div data-id="${closedId}" class="sn-spaces__popup-menu_item ${closedActiveClass}">
					<div class="sn-spaces__popup-menu_item-icon --closed-spaces"></div>
					<div class="sn-spaces__popup-menu_item-info">
						<div class="sn-spaces__popup-menu_item-name">
							${Loc.getMessage('SN_SPACES_GROUP_PRIVACY_CLOSED')}
						</div>
						<div class="sn-spaces__popup-menu_item-description">
							${Loc.getMessage('SN_SPACES_GROUP_PRIVACY_CLOSED_DESC')}
						</div>
					</div>
				</div>
				<div data-id="${secretId}" class="sn-spaces__popup-menu_item ${secretActiveClass}">
					<div class="sn-spaces__popup-menu_item-icon --secret-spaces"></div>
					<div class="sn-spaces__popup-menu_item-info">
						<div class="sn-spaces__popup-menu_item-name">
							${Loc.getMessage('SN_SPACES_GROUP_PRIVACY_SECRET')}
						</div>
						<div class="sn-spaces__popup-menu_item-description">
							${Loc.getMessage('SN_SPACES_GROUP_PRIVACY_SECRET_DESC')}
						</div>
					</div>
				</div>
				<div class="sn-spaces__popup-menu_hint">
					${Loc.getMessage('SN_SPACES_GROUP_PRIVACY_PROMPT')}
				</div>
			</div>
		`;

		Event.bind(
			this.#contentNode.querySelector(`[data-id='${openId}']`),
			'click',
			() => this.#changePrivacy('open'),
		);
		Event.bind(
			this.#contentNode.querySelector(`[data-id='${closedId}']`),
			'click',
			() => this.#changePrivacy('closed'),
		);
		Event.bind(
			this.#contentNode.querySelector(`[data-id='${secretId}']`),
			'click',
			() => this.#changePrivacy('secret'),
		);

		return this.#contentNode;
	}

	#changePrivacy(privacyCode: 'open' | 'closed' | 'secret')
	{
		this.setPrivacy(privacyCode);

		this.emit('changePrivacy', this.#privacyCode);

		this.#popup.close();
	}

	setPrivacy(privacyCode: 'open' | 'closed' | 'secret')
	{
		this.#privacyCode = privacyCode;

		if (this.#contentNode)
		{
			Dom.removeClass(this.#contentNode.querySelector('.--active'), '--active');

			const node = this.#contentNode.querySelector(`[data-id='spaces-group-privacy-${privacyCode}']`);
			Dom.addClass(node, '--active');
		}
	}

	getLabel(): string
	{
		return Loc.getMessage(`SN_SPACES_GROUP_PRIVACY_${this.#privacyCode.toUpperCase()}`);
	}
}
