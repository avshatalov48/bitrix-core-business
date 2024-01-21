import { Dom, Event, Loc, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';

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

		this.#createPopup(params.bindElement);
	}

	show()
	{
		if (this.#popup.isShown())
		{
			this.#popup.close();
		}
		else
		{
			this.#popup.show();
		}
	}

	#createPopup(bindElement: HTMLElement)
	{
		this.#popup = new Popup(
			{
				id: 'sn-post-form',
				bindElement: bindElement,
				content: this.#renderContent(this.#privacyCode),
				autoHide: true,
				angle: false,
				width: 343,
				closeIcon: false,
				closeByEsc: true,
				overlay: true,
				padding: 12,
				animation: 'fading-slide',
			},
		);

		this.#popup.subscribe('onShow', () => this.emit('onShow'));
		this.#popup.subscribe('onAfterClose', () => this.emit('onAfterClose'));
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
							${Loc.getMessage('SN_SPACES_MENU_INFO_VS_OPEN')}
						</div>
						<div class="sn-spaces__popup-menu_item-description">
							${Loc.getMessage('SN_SPACES_MENU_INFO_VS_OPEN_DESC')}
						</div>
					</div>
				</div>
				<div data-id="${closedId}" class="sn-spaces__popup-menu_item ${closedActiveClass}">
					<div class="sn-spaces__popup-menu_item-icon --closed-spaces"></div>
					<div class="sn-spaces__popup-menu_item-info">
						<div class="sn-spaces__popup-menu_item-name">
							${Loc.getMessage('SN_SPACES_MENU_INFO_VS_CLOSED')}
						</div>
						<div class="sn-spaces__popup-menu_item-description">
							${Loc.getMessage('SN_SPACES_MENU_INFO_VS_CLOSED_DESC')}
						</div>
					</div>
				</div>
				<div data-id="${secretId}" class="sn-spaces__popup-menu_item ${secretActiveClass}">
					<div class="sn-spaces__popup-menu_item-icon --secret-spaces"></div>
					<div class="sn-spaces__popup-menu_item-info">
						<div class="sn-spaces__popup-menu_item-name">
							${Loc.getMessage('SN_SPACES_MENU_INFO_VS_SECRET')}
						</div>
						<div class="sn-spaces__popup-menu_item-description">
							${Loc.getMessage('SN_SPACES_MENU_INFO_VS_SECRET_DESC')}
						</div>
					</div>
				</div>
				<div class="sn-spaces__popup-menu_hint">
					${Loc.getMessage('SN_SPACES_MENU_INFO_VS_PROMPT')}
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
		this.#privacyCode = privacyCode;

		Dom.removeClass(this.#contentNode.querySelector('.--active'), '--active');

		const node = this.#contentNode.querySelector(`[data-id='spaces-group-privacy-${privacyCode}']`);
		Dom.addClass(node, '--active');

		this.emit('changePrivacy', this.#privacyCode);

		this.#popup.close();
	}
}
