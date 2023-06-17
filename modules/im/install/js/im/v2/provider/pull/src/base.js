import {MessagePullHandler} from './base/message';
import {ChatPullHandler} from './base/chat';
import {UserPullHandler} from './base/user';
import {DesktopPullHandler} from './base/desktop';

export class BasePullHandler
{
	#messageHandler: MessagePullHandler;
	#chatHandler: ChatPullHandler;
	#userHandler: UserPullHandler;
	#desktopHandler: DesktopPullHandler;

	constructor()
	{
		this.#messageHandler = new MessagePullHandler();
		this.#chatHandler = new ChatPullHandler();
		this.#userHandler = new UserPullHandler();
		this.#desktopHandler = new DesktopPullHandler();
	}

	getModuleId()
	{
		return 'im';
	}

	// region 'message'
	handleMessage(params)
	{
		this.#messageHandler.handleMessageAdd(params);
	}

	handleMessageChat(params)
	{
		this.#messageHandler.handleMessageAdd(params);
	}

	handleMessageUpdate(params)
	{
		this.#messageHandler.handleMessageUpdate(params);
	}

	handleMessageDelete(params)
	{
		this.#messageHandler.handleMessageDelete(params);
	}

	handleMessageDeleteComplete(params)
	{
		this.#messageHandler.handleMessageDeleteComplete(params);
	}

	handleAddReaction(params)
	{
		this.#messageHandler.handleAddReaction(params);
	}

	handleDeleteReaction(params)
	{
		this.#messageHandler.handleDeleteReaction(params);
	}

	handleMessageParamsUpdate(params)
	{
		this.#messageHandler.handleMessageParamsUpdate(params);
	}

	handleReadMessage(params, extra)
	{
		this.#messageHandler.handleReadMessage(params, extra);
	}

	handleReadMessageChat(params, extra)
	{
		this.#messageHandler.handleReadMessage(params, extra);
	}

	handleReadMessageOpponent(params)
	{
		this.#messageHandler.handleReadMessageOpponent(params);
	}

	handleReadMessageChatOpponent(params)
	{
		this.#messageHandler.handleReadMessageOpponent(params);
	}

	handlePinAdd(params)
	{
		this.#messageHandler.handlePinAdd(params);
	}

	handlePinDelete(params)
	{
		this.#messageHandler.handlePinDelete(params);
	}
	// endregion 'message'

	// region 'chat'
	handleChatOwner(params)
	{
		this.#chatHandler.handleChatOwner(params);
	}

	handleChatManagers(params)
	{
		this.#chatHandler.handleChatManagers(params);
	}

	handleChatUserAdd(params)
	{
		this.#chatHandler.handleChatUserAdd(params);
	}

	handleChatUserLeave(params)
	{
		this.#chatHandler.handleChatUserLeave(params);
	}

	handleStartWriting(params)
	{
		this.#chatHandler.handleStartWriting(params);
	}

	handleChatUnread(params)
	{
		this.#chatHandler.handleChatUnread(params);
	}

	handleReadAllChats()
	{
		this.#chatHandler.handleReadAllChats();
	}

	handleChatMuteNotify(params)
	{
		this.#chatHandler.handleChatMuteNotify(params);
	}

	handleChatRename(params)
	{
		this.#chatHandler.handleChatRename(params);
	}

	handleChatAvatar(params)
	{
		this.#chatHandler.handleChatAvatar(params);
	}
	// endregion 'chat'

	// region 'user'
	handleUserInvite(params)
	{
		this.#userHandler.handleUserInvite(params);
	}
	// endregion 'user'

	// region 'desktop'
	handleDesktopOnline(params)
	{
		this.#desktopHandler.handleDesktopOnline(params);
	}

	handleDesktopOffline()
	{
		this.#desktopHandler.handleDesktopOffline();
	}
	// endregion 'desktop'
}