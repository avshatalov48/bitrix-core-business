import { Event, Loc, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';

type Params = {
	canUse: boolean,
}

export class ChatAction extends EventEmitter
{
	#canUse: boolean;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.ChatAction');

		this.#canUse = params.canUse === true;
	}

	render(): HTMLElement
	{
		const videoCallId = 'spaces-settings-video-call';
		const openChatId = 'spaces-settings-open-chat';
		const createChatId = 'spaces-settings-create-chat';

		const disabled = this.#canUse ? '' : '--disabled';

		const node = Tag.render`
			<div class="sn-spaces__popup-communication">
				<div
					data-id="${videoCallId}"
					class="sn-spaces__popup-communication-item ${disabled}"
				>
					<div
						class="ui-icon-set --video-1"
						style="--ui-icon-set__icon-size: 26px;"
					></div>
					<div class="sn-spaces__popup-communication-item_text">
						${Loc.getMessage('SN_SPACES_MENU_CHAT_VIDEO_CALL_HD')}
					</div>
				</div>
				<div
					data-id="${openChatId}"
					class="sn-spaces__popup-communication-item ${disabled}"
				>
					<div
						class="ui-icon-set --chat-1"
						style="--ui-icon-set__icon-size: 26px;"
					></div>
					<div class="sn-spaces__popup-communication-item_text">
						${Loc.getMessage('SN_SPACES_MENU_CHAT_OPEN')}
					</div>
				</div>
				<div
					data-id="${createChatId}"
					class="sn-spaces__popup-communication-item ${disabled}" 
				>
					<div
						class="ui-icon-set --add-chat"
						style="--ui-icon-set__icon-size: 26px;"
					></div>
					<div class="sn-spaces__popup-communication-item_text">
						${Loc.getMessage('SN_SPACES_MENU_CHAT_CREATE')}
					</div>
				</div>
			</div>
		`;

		Event.bind(
			node.querySelector(`[data-id='${videoCallId}']`),
			'click',
			() => this.emit('videoCall'),
		);

		Event.bind(
			node.querySelector(`[data-id='${openChatId}']`),
			'click',
			() => this.emit('openChat'),
		);

		Event.bind(
			node.querySelector(`[data-id='${createChatId}']`),
			'click',
			() => this.emit('createChat'),
		);

		return node;
	}
}
