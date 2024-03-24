import { Dom, Event, Loc, Tag, Text } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import type { GroupData } from '../group-settings';
import { Perms } from '../group-settings';
import { Logo, LogoData } from 'socialnetwork.logo';
import { GroupPrivacy } from 'socialnetwork.group-privacy';
import { Controller } from 'socialnetwork.controller';

type Params = {
	title: number,
	logo: LogoData,
	privacyCode: string,
	actions: Perms,
}

export class Info extends EventEmitter
{
	#groupId: number;
	#title: string;
	#logo: LogoData;
	#privacyCode: string;
	#privacyPopup: GroupPrivacy;
	#actions: Perms;

	#layout: {
		wrapNode: HTMLElement,
		avatar: HTMLElement,
		titleNode: HTMLElement,
		titleTextNode: HTMLElement,
		editTitleNode: HTMLElement,
		editTitleInput: HTMLInputElement,
		privacyTextNode: HTMLElement,
		moreButtonNode: HTMLElement,
	};

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Info');

		this.#layout = {};
		this.#groupId = params.groupId;
		this.#title = params.title;
		this.#logo = params.logo;
		this.#privacyCode = params.privacyCode;
		this.#actions = params.actions;
	}

	update(groupData: GroupData): void
	{
		const { name, privacyCode, avatar } = groupData;

		if (name)
		{
			this.#setTitle(name);
		}
		if (privacyCode)
		{
			this.#setPrivacy(privacyCode);
		}
		if (avatar)
		{
			this.#setAvatar(avatar);
		}
	}

	render(): HTMLElement
	{
		this.#layout.wrapNode = Tag.render`
			<div class="sn-spaces__popup-item --main">
				<div class="sn-spaces__popup-settings_logo">
					${this.#renderSpaceAvatar()}
				</div>
				<div class="sn-spaces__popup-settings_info">
					${this.#renderTitle()}
					${this.#renderEditTitle()}
					${this.#renderPrivacy()}
				</div>
				${this.#renderMoreButton()}
			</div>
		`;

		Event.bind(this.#layout.wrapNode, 'click', (event) => {
			if (event.target === this.#layout.wrapNode || event.target === this.#layout.moreButtonNode)
			{
				this.emit('more');
			}
		});

		return this.#layout.wrapNode;
	}

	#renderSpaceAvatar(): HTMLElement
	{
		const logo = new Logo(this.#logo);

		const avatarNode = Tag.render`
			<div class="sn-spaces__space-logo ${logo.getClass() ?? ''}">
				${logo.render()}
			</div>
		`;

		this.#layout.avatar?.replaceWith(avatarNode);
		this.#layout.avatar = avatarNode;

		return this.#layout.avatar;
	}

	#renderMoreButton(): HTMLElement
	{
		this.#layout.moreButtonNode = Tag.render`
			<div 
				data-id="spaces-settings-space-info-btn"
				class="ui-icon-set --chevron-right"
				title="${Loc.getMessage('SN_SPACES_MENU_INFO_MORE_BTN')}"
			></div>
		`;

		return this.#layout.moreButtonNode;
	}

	#startEditTitle()
	{
		Dom.addClass(this.#layout.titleNode, '--hidden');
		Dom.removeClass(this.#layout.editTitleNode, '--hidden');

		const input = this.#layout.editTitleInput;

		input.focus();
		input.setSelectionRange(input.value.length, input.value.length);
	}

	#stopEditTitle()
	{
		Dom.addClass(this.#layout.editTitleNode, '--hidden');
		Dom.removeClass(this.#layout.titleNode, '--hidden');
	}

	#setTitle(value: string)
	{
		this.#title = value;
		this.#layout.editTitleInput.value = this.#title;

		this.#layout.titleTextNode.textContent = Text.encode(this.#title);
	}

	#renderTitle(): HTMLElement
	{
		this.#layout.titleNode = Tag.render`
			<div class="sn-spaces__popup-settings_title">
				${this.#renderTitleText()}
				${this.#actions.canEdit ? this.#renderPencilIcon() : ''}
			</div>
		`;

		return this.#layout.titleNode;
	}

	#renderTitleText(): HTMLElement
	{
		this.#layout.titleTextNode = Tag.render`
			<div
				data-id="spaces-settings-space-info-name"
				class="sn-spaces__popup-settings_name"
			>
				${Text.encode(this.#title)}
			</div>
		`;

		return this.#layout.titleTextNode;
	}

	#renderEditTitle(): HTMLElement
	{
		const uiClasses = 'ui-ctl ui-ctl-textbox ui-ctl--w100 ui-ctl--transp '
			+ 'ui-ctl-no-border ui-ctl-xs ui-ctl-no-padding';

		this.#layout.editTitleNode = Tag.render`
			<div
				data-id="spaces-settings-space-info-edit"
				class="sn-spaces__popup-settings_title --hidden"
			>
				<div class="${uiClasses}">
					${this.#renderEditTitleInput()}
				</div>
			</div>
		`;

		const input = this.#layout.editTitleInput;

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

				Controller.changeTitle(this.#groupId, this.#title);
				this.emit('changeTitle', this.#title);
			}

			this.#stopEditTitle();
		});

		return this.#layout.editTitleNode;
	}

	#renderEditTitleInput()
	{
		this.#layout.editTitleInput = Tag.render`
			<input
				type="text"
				class="ui-ctl-element sn-spaces__popup-settings_name-input"
				value="${Text.encode(this.#title)}"
			>
		`;

		return this.#layout.editTitleInput;
	}

	#renderPrivacy(): HTMLElement
	{
		this.#privacyPopup = this.#createPrivacyPopup();

		const node = Tag.render`
			<div
				data-id="spaces-settings-space-info-privacy"
				class="sn-spaces__popup-settings_select-private"
			>
				${this.#renderPrivacyText()}
				${this.#actions.canEdit ? this.#renderPrivacyIcon() : ''}
			</div>
		`;

		if (this.#actions.canEdit)
		{
			Event.bind(node, 'click', this.#showPrivacy.bind(this));
		}

		return node;
	}

	#createPrivacyPopup(): GroupPrivacy
	{
		const privacyPopup = new GroupPrivacy({
			privacyCode: this.#privacyCode,
		});

		privacyPopup.subscribe('onShow', () => this.emit('setAutoHide', false));
		privacyPopup.subscribe('onAfterClose', () => this.emit('setAutoHide', true));
		privacyPopup.subscribe('changePrivacy', this.#changePrivacy.bind(this));

		return privacyPopup;
	}

	#showPrivacy(event)
	{
		this.#privacyPopup.show(event.target);
	}

	#renderPrivacyText(): HTMLElement
	{
		this.#layout.privacyTextNode = Tag.render`
			<div class="sn-spaces__popup-settings_select-private-text">
				${this.#privacyPopup.getLabel()}
			</div>
		`;

		return this.#layout.privacyTextNode;
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

	#changePrivacy(baseEvent: BaseEvent)
	{
		const privacyCode: 'open' | 'closed' | 'secret' = baseEvent.getData();

		this.#setPrivacy(privacyCode);

		Controller.changePrivacy(this.#groupId, privacyCode);

		this.emit('changePrivacy', privacyCode);
	}

	#setPrivacy(privacyCode: 'open' | 'closed' | 'secret')
	{
		this.#privacyCode = privacyCode;
		this.#privacyPopup.setPrivacy(this.#privacyCode);
		this.#layout.privacyTextNode.textContent = this.#privacyPopup.getLabel();
	}

	#setAvatar(avatar: string)
	{
		this.#logo = {
			id: avatar,
			type: 'image',
		};
		this.#renderSpaceAvatar();
	}
}
