import {LoadService} from './classes/message/load';
import {PinService} from './classes/message/pin';
import {EditService} from './classes/message/edit';
import {DeleteService} from './classes/message/delete';
import {MarkService} from './classes/message/mark';
import {FavoriteService} from './classes/message/favorite';

export class MessageService
{
	#loadService: LoadService;
	#pinService: PinService;
	#editService: EditService;
	#deleteService: DeleteService;
	#markService: MarkService;
	#favoriteService: FavoriteService;

	static getMessageRequestLimit(): number
	{
		return LoadService.MESSAGE_REQUEST_LIMIT;
	}

	constructor(params: {chatId: number})
	{
		const {chatId} = params;
		this.#initServices(chatId);
	}

	#initServices(chatId: number)
	{
		this.#loadService = new LoadService(chatId);
		this.#editService = new EditService(chatId);
		this.#deleteService = new DeleteService(chatId);
		this.#pinService = new PinService();
		this.#markService = new MarkService(chatId);
		this.#favoriteService = new FavoriteService(chatId);
	}

	// region 'pagination'
	loadUnread(): Promise
	{
		return this.#loadService.loadUnread();
	}

	loadHistory(): Promise
	{
		return this.#loadService.loadHistory();
	}

	hasPreparedHistoryMessages(): boolean
	{
		return this.#loadService.hasPreparedHistoryMessages();
	}

	drawPreparedHistoryMessages(): Promise
	{
		return this.#loadService.drawPreparedHistoryMessages();
	}

	hasPreparedUnreadMessages(): boolean
	{
		return this.#loadService.hasPreparedUnreadMessages();
	}

	drawPreparedUnreadMessages(): Promise
	{
		return this.#loadService.drawPreparedUnreadMessages();
	}

	isLoading(): boolean
	{
		return this.#loadService.isLoading();
	}
	// endregion 'pagination'

	// region 'context'
	loadContext(messageId: number): Promise
	{
		return this.#loadService.loadContext(messageId);
	}
	// endregion 'context

	// region 'reload messages'
	reloadMessageList(): Promise
	{
		return this.#loadService.reloadMessageList();
	}

	loadInitialMessages(): Promise
	{
		return this.#loadService.loadInitialMessages();
	}
	// endregion 'reload messages'

	// region 'pin'
	pinMessage(chatId: number, messageId: number)
	{
		this.#pinService.pinMessage(chatId, messageId);
	}

	unpinMessage(chatId: number, messageId: number)
	{
		this.#pinService.unpinMessage(chatId, messageId);
	}
	// endregion 'pin'

	// region 'mark'
	markMessage(messageId: number)
	{
		this.#markService.markMessage(messageId);
	}
	// endregion 'mark'

	// region 'favorite'
	addMessageToFavorite(messageId: number)
	{
		this.#favoriteService.addMessageToFavorite(messageId);
	}

	removeMessageFromFavorite(messageId: number)
	{
		this.#favoriteService.removeMessageFromFavorite(messageId);
	}
	// endregion 'favorite'

	// region 'edit'
	editMessageText(messageId: number, text: string)
	{
		this.#editService.editMessageText(messageId, text);
	}
	// endregion 'edit'

	// region 'delete'
	deleteMessage(messageId: number)
	{
		this.#deleteService.deleteMessage(messageId);
	}
	// endregion 'delete'
}