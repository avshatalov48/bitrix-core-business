import { Loc, Tag, Dom } from 'main.core';
import { Dialog, BaseHeader } from 'ui.entity-selector';
import { EventEmitter } from 'main.core.events';
import CreateChatFooter from './create-chat-footer';

export class UserSelector extends EventEmitter
{
	#bindElement: HTMLElement;
	#title: string;
	#preselectedItems: any[];
	#onClose: function;
	#onLoad: function;
	#groupId: number;
	#createChat: boolean;

	constructor(options)
	{
		super(options);

		this.setEventNamespace('SocialNetwork.Spaces.UserSelector');

		this.#bindElement = options.bindElement;

		this.#title = options.title;

		this.#preselectedItems = options.preselectedItems ?? [];

		this.#onClose = options.onClose ?? (() => {});
		this.#onLoad = options.onLoad ?? (() => {});

		this.#groupId = options.groupId ?? 0;
		this.#createChat = options.createChat ?? false;
	}

	getDialog(): Dialog
	{
		if (!this.dialog)
		{
			const title = this.#title;

			class UserSelectorDialogHeader extends BaseHeader
			{
				render(): HTMLElement
				{
					return Tag.render`
						<div class="sn-spaces__entity-dialog-header">${title}</div>
					`;
				}
			}

			this.dialog = new Dialog({
				targetNode: this.#bindElement,
				width: 400,
				dropdownMode: true,
				header: UserSelectorDialogHeader,
				enableSearch: true,
				context: 'socialnetwork.spaces',
				preselectedItems: this.#preselectedItems,
				entities: [
					{
						id: 'user',
						substituteEntityId: this.#createChat ? 'project-user' : null,
						options: {
							footerInviteIntranetOnly: true,
							showInvitationFooter: !this.#createChat,
							projectId: this.#groupId,
						},
					},
				],
				searchTabOptions: this.#createChat && {
					stubOptions: {
						title: Loc.getMessage('SN_SPACES_USER_SELECTOR_SEARCH_TAB_EMPTY_TITLE'),
						subtitle: Loc.getMessage('SN_SPACES_USER_SELECTOR_SEARCH_TAB_EMPTY_TEXT'),
					},
				},
				events: {
					onHide: this.#onClose,
					onLoad: this.#onLoad,
					'SearchTab:onLoad': this.#onLoad,
				},
			});

			if (this.#createChat)
			{
				this.dialog.setFooter(CreateChatFooter);
			}

			this.#bindSliderEvents();
		}

		return this.dialog;
	}

	reload()
	{
		this.dialog.loadState = 'UNSENT';
		this.dialog.load();
	}

	#bindSliderEvents()
	{
		EventEmitter.subscribe('SidePanel.Slider:onOpenStart', (event) => {
			const slider = event.target;

			if (this.#getImBar())
			{
				Dom.style(this.#getImBar(), 'zIndex', slider.getZindex());
			}
		});

		EventEmitter.subscribe('SidePanel.Slider:onBeforeCloseComplete', (event) => {
			const slider = event.target;

			if (this.#isInviteSlider(slider) && this.#getImBar())
			{
				Dom.style(this.#getImBar(), 'zIndex', '');
			}
		});
	}

	#isInviteSlider(slider)
	{
		return slider.options.data?.entitySelectorId === this.dialog.getId();
	}

	#getImBar()
	{
		return document.getElementById('bx-im-bar');
	}
}
