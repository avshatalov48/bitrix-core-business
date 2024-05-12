import { LoadService } from './classes/chat/load';
import { CreateService } from './classes/chat/create';
import { UpdateService } from './classes/chat/update';
import { RenameService } from './classes/chat/rename';
import { MuteService } from './classes/chat/mute';
import { PinService } from './classes/chat/pin';
import { ReadService } from './classes/chat/read';
import { UserService } from './classes/chat/user';

export class ChatService
{
	static DEBOUNCE_TIME = 500;

	#loadService: LoadService;
	#createService: CreateService;
	#updateService: UpdateService;
	#renameService: RenameService;
	#muteService: MuteService;
	#pinService: PinService;
	#readService: ReadService;
	#userService: UserService;

	constructor()
	{
		this.#initServices();
	}

	// region 'load'
	loadChat(dialogId: string): Promise
	{
		return this.#loadService.loadChat(dialogId);
	}

	loadChatWithMessages(dialogId: string): Promise
	{
		return this.#loadService.loadChatWithMessages(dialogId);
	}

	loadChatWithContext(dialogId: string, messageId: number): Promise
	{
		return this.#loadService.loadChatWithContext(dialogId, messageId);
	}

	prepareDialogId(dialogId: string): Promise<string>
	{
		return this.#loadService.prepareDialogId(dialogId);
	}
	// endregion 'load'

	// region 'create'
	createChat(chatConfig): Promise<{ newDialogId: string, newChatId: number }>
	{
		return this.#createService.createChat(chatConfig);
	}
	// endregion 'create'

	// region 'update'
	prepareAvatar(avatarFile: File): Promise<File>
	{
		return this.#updateService.prepareAvatar(avatarFile);
	}

	changeAvatar(chatId: number, avatarFile: File): Promise
	{
		return this.#updateService.changeAvatar(chatId, avatarFile);
	}
	// endregion 'update'

	// region 'rename'
	renameChat(dialogId: string, newName: string): Promise
	{
		return this.#renameService.renameChat(dialogId, newName);
	}
	// endregion 'rename'

	// region 'mute'
	muteChat(dialogId: string)
	{
		this.#muteService.muteChat(dialogId);
	}

	unmuteChat(dialogId: string)
	{
		this.#muteService.unmuteChat(dialogId);
	}
	// endregion 'mute'

	// region 'pin'
	pinChat(dialogId: string)
	{
		this.#pinService.pinChat(dialogId);
	}

	unpinChat(dialogId: string)
	{
		this.#pinService.unpinChat(dialogId);
	}
	// endregion 'pin'

	// region 'read'
	readAll(): Promise
	{
		return this.#readService.readAll();
	}

	readDialog(dialogId: string)
	{
		this.#readService.readDialog(dialogId);
	}

	unreadDialog(dialogId: string)
	{
		this.#readService.unreadDialog(dialogId);
	}

	readMessage(chatId: number, messageId: number)
	{
		this.#readService.readMessage(chatId, messageId);
	}

	clearDialogMark(dialogId: string)
	{
		this.#readService.clearDialogMark(dialogId);
	}
	// endregion 'read'

	// region 'user'
	leaveChat(dialogId: string)
	{
		this.#userService.leaveChat(dialogId);
	}

	kickUserFromChat(dialogId: string, userId: number)
	{
		this.#userService.kickUserFromChat(dialogId, userId);
	}

	addToChat(addConfig: {chatId: number, members: string[], showHistory: boolean}): Promise
	{
		return this.#userService.addToChat(addConfig);
	}

	joinChat(dialogId: string)
	{
		this.#userService.joinChat(dialogId);
	}
	// endregion 'user

	#initServices()
	{
		this.#loadService = new LoadService();
		this.#createService = new CreateService();
		this.#updateService = new UpdateService();
		this.#renameService = new RenameService();
		this.#muteService = new MuteService();
		this.#pinService = new PinService();
		this.#readService = new ReadService();
		this.#userService = new UserService();
	}
}
