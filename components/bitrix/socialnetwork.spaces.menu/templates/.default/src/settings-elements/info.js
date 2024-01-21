import { Dom, Event, Loc, Tag, Text } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Perms } from '../group-settings';
import { Logo } from '../logo';
import { GroupPrivacy } from './group-privacy';

type Params = {
	title: number,
	logo: Logo,
	privacyCode: string,
	actions: Perms,
}

export class Info extends EventEmitter
{
	#title: string;
	#logo: Logo;
	#privacyCode: string;
	#privacyPopup: ?GroupPrivacy;
	#actions: Perms;

	#node: HTMLElement;
	#titleNode: HTMLElement;
	#editNode: HTMLElement;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Info');

		this.#title = params.title;
		this.#logo = params.logo;
		this.#privacyCode = params.privacyCode;
		this.#actions = params.actions;
	}

	render(): HTMLElement
	{
		const moreBtnId = 'spaces-settings-space-info-btn';

		this.#node = Tag.render`
			<div class="sn-spaces__popup-item --main">
				<div class="sn-spaces__popup-settings_logo">
					<div class="sn-spaces__list-item_icon ${this.#logo.getClass()}">
						${this.#logo.render()}
					</div>
				</div>
				<div class="sn-spaces__popup-settings_info">
					${this.#renderTitle()}
					${this.#renderEdit()}
					${this.#renderPrivacy()}
				</div>
				<div
					style="display: none;"
					data-id="${moreBtnId}"
					class="ui-popupcomponentmaker__btn --large --border sn-spaces__popup-settings_btn"
				>${Loc.getMessage('SN_SPACES_MENU_INFO_MORE_BTN')}</div>
			</div>
		`;

		Event.bind(
			this.#node.querySelector(`[data-id='${moreBtnId}']`),
			'click',
			() => this.emit('more'),
		);

		return this.#node;
	}

	#showPrivacy(event)
	{
		if (!this.#privacyPopup)
		{
			this.#privacyPopup = new GroupPrivacy({
				bindElement: event.target,
				privacyCode: this.#privacyCode,
			});

			this.#privacyPopup.subscribe('onShow', () => this.emit('setAutoHide', false));
			this.#privacyPopup.subscribe('onAfterClose', () => this.emit('setAutoHide', true));
			this.#privacyPopup.subscribe('changePrivacy', this.#changePrivacy.bind(this));
		}

		this.#privacyPopup.show();
	}

	#startEditTitle()
	{
		Dom.addClass(this.#titleNode, '--hidden');
		Dom.removeClass(this.#editNode, '--hidden');

		const input = this.#editNode.querySelector('input');

		input.focus();
		input.setSelectionRange(input.value.length, input.value.length);
	}

	#stopEditTitle()
	{
		Dom.addClass(this.#editNode, '--hidden');
		Dom.removeClass(this.#titleNode, '--hidden');
	}

	#setTitle(value: string)
	{
		this.#title = value;

		const node = this.#titleNode.querySelector('.sn-spaces__popup-settings_name');
		node.textContent = Text.encode(this.#title);
	}

	#renderTitle(): HTMLElement
	{
		this.#titleNode = Tag.render`
			<div class="sn-spaces__popup-settings_title">
				<div
					data-id="spaces-settings-space-info-name"
					class="sn-spaces__popup-settings_name"
				>
					${Text.encode(this.#title)}
				</div>
				${this.#actions.canEdit ? this.#renderPencilIcon() : ''}
			</div>
		`;

		return this.#titleNode;
	}

	#renderEdit(): HTMLElement
	{
		const uiClasses = 'ui-ctl ui-ctl-textbox ui-ctl--w100 ui-ctl--transp '
			+ 'ui-ctl-no-border ui-ctl-xs ui-ctl-no-padding';

		this.#editNode = Tag.render`
			<div
				data-id="spaces-settings-space-info-edit"
				class="sn-spaces__popup-settings_title --hidden"
			>
				<div class="${uiClasses}">
					<input
						type="text"
						class="ui-ctl-element sn-spaces__popup-settings_name-input"
						value="${Text.encode(this.#title)}"
					>
				</div>
			</div>
		`;

		const input = this.#editNode.querySelector('input');

		Event.bind(input, 'keydown', (event: KeyboardEvent) => {
			if (event.key === 'Escape' || event.key === 'Enter')
			{
				input.blur();

				event.stopImmediatePropagation();
			}
		});

		Event.bind(input, 'blur', () => {
			if (this.#title !== input.value)
			{
				this.#setTitle(input.value);

				this.emit('changeTitle', this.#title);
			}

			this.#stopEditTitle();
		});

		return this.#editNode;
	}

	#renderPrivacy(): HTMLElement
	{
		const node = Tag.render`
			<div
				data-id="spaces-settings-space-info-privacy"
				class="sn-spaces__popup-settings_select-private"
			>
				<div class="sn-spaces__popup-settings_select-private-text">
					${this.#getPrivateLabel(this.#privacyCode)}
				</div>
				${this.#actions.canEdit ? this.#renderPrivacyIcon() : ''}
			</div>
		`;

		if (this.#actions.canEdit)
		{
			Event.bind(node, 'click', this.#showPrivacy.bind(this));
		}

		return node;
	}

	#renderPencilIcon(): HTMLElement
	{
		const node = Tag.render`
			<div
				data-id="spaces-settings-space-info-title-edit" 
				class="ui-icon-set --pencil-40"
				style="--ui-icon-set__icon-size: 18px;"
			></div>
		`;

		Event.bind(node, 'click', this.#startEditTitle.bind(this));

		return node;
	}

	#renderPrivacyIcon(): HTMLElement
	{
		return Tag.render`
			<div
				class="ui-icon-set --chevron-down"
				style="--ui-icon-set__icon-size: 14px;"
			></div>
		`;
	}

	#getPrivateLabel(privacyCode: 'open' | 'closed' | 'secret'): string
	{
		return Loc.getMessage(`SN_SPACES_MENU_INFO_VS_${privacyCode.toUpperCase()}`);
	}

	#changePrivacy(baseEvent: BaseEvent)
	{
		const privacyCode: 'open' | 'closed' | 'secret' = baseEvent.getData();

		this.#node
			.querySelector('.sn-spaces__popup-settings_select-private-text')
			.textContent = this.#getPrivateLabel(privacyCode)
		;

		this.emit('changePrivacy', privacyCode);
	}
}
