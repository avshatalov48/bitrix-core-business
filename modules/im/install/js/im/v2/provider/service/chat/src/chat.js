import { DeleteService } from './classes/delete';
import { LoadService } from './classes/load';
import { CreateService } from './classes/create';
import { UpdateService } from './classes/update';
import { RenameService } from './classes/rename';
import { MuteService } from './classes/mute';
import { PinService } from './classes/pin';
import { ReadService } from './classes/read';
import { UserService } from './classes/user';
import { MessagesAutoDeleteService } from './classes/messages-auto-delete';

import type { UpdateChatConfig, GetMemberEntitiesConfig } from './types/chat';

export class ChatService
{
	#loadService: LoadService;
	#createService: CreateService;
	#updateService: UpdateService;
	#renameService: RenameService;
	#muteService: MuteService;
	#pinService: PinService;
	#readService: ReadService;
	#userService: UserService;
	#deleteService: DeleteService;
	#messagesAutoDeleteService: MessagesAutoDeleteService;

	constructor()
	{
		this.#initServices();
	}

	// region 'load'
	loadChat(dialogId: string): Promise
	{
		return this.#loadService.loadChat(dialogId);
	}

	loadChatByChatId(chatId: number): Promise
	{
		return this.#loadService.loadChatByChatId(chatId);
	}

	loadChatWithMessages(dialogId: string): Promise
	{
		return this.#loadService.loadChatWithMessages(dialogId);
	}

	loadChatWithContext(dialogId: string, messageId: number): Promise
	{
		return this.#loadService.loadChatWithContext(dialogId, messageId);
	}

	loadComments(postId: number): Promise
	{
		return this.#loadService.loadComments(postId);
	}

	loadCommentInfo(channelDialogId: string): Promise
	{
		return this.#loadService.loadCommentInfo(channelDialogId);
	}

	prepareDialogId(dialogId: string): Promise<string>
	{
		return this.#loadService.prepareDialogId(dialogId);
	}

	clearChat(dialogId: string): Promise
	{
		return this.#loadService.clearChat(dialogId);
	}
	// endregion 'load'

	// region 'create'
	createChat(chatConfig): Promise<{ newDialogId: string, newChatId: number }>
	{
		return this.#createService.createChat(chatConfig);
	}

	createCollab(collabConfig): Promise<{ newDialogId: string, newChatId: number }>
	{
		return this.#createService.createCollab(collabConfig);
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

	updateChat(chatId: number, chatConfig: UpdateChatConfig): Promise<boolean>
	{
		return this.#updateService.updateChat(chatId, chatConfig);
	}

	updateCollab(dialogId: string, collabConfig): Promise<boolean>
	{
		return this.#updateService.updateCollab(dialogId, collabConfig);
	}

	getMemberEntities(chatId: number): Promise<GetMemberEntitiesConfig>
	{
		return this.#updateService.getMemberEntities(chatId);
	}
	// endregion 'update'

	// region 'delete'
	deleteChat(dialogId: string): Promise
	{
		return this.#deleteService.deleteChat(dialogId);
	}

	deleteCollab(dialogId: string): Promise
	{
		return this.#deleteService.deleteCollab(dialogId);
	}
	// endregion 'delete'

	// region 'rename'
	renameChat(dialogId: string, newName: string): Promise
	{
		return this.#renameService.renameChat(dialogId, newName);
	}
	// endregion 'rename'

	// region 'mute'
	muteChat(dialogId: string): void
	{
		this.#muteService.muteChat(dialogId);
	}

	unmuteChat(dialogId: string): void
	{
		this.#muteService.unmuteChat(dialogId);
	}
	// endregion 'mute'

	// region 'pin'
	pinChat(dialogId: string): void
	{
		this.#pinService.pinChat(dialogId);
	}

	unpinChat(dialogId: string): void
	{
		this.#pinService.unpinChat(dialogId);
	}
	// endregion 'pin'

	// region 'read'
	readAll(): void
	{
		this.#readService.readAll();
	}

	readDialog(dialogId: string): void
	{
		this.#readService.readDialog(dialogId);
	}

	unreadDialog(dialogId: string): void
	{
		this.#readService.unreadDialog(dialogId);
	}

	readMessage(chatId: number, messageId: number): void
	{
		this.#readService.readMessage(chatId, messageId);
	}

	readChatQueuedMessages(chatId: number): void
	{
		this.#readService.readChatQueuedMessages(chatId);
	}

	clearDialogMark(dialogId: string): void
	{
		this.#readService.clearDialogMark(dialogId);
	}
	// endregion 'read'

	// region 'user'
	leaveChat(dialogId: string): void
	{
		this.#userService.leaveChat(dialogId);
	}

	leaveCollab(dialogId: string): void
	{
		this.#userService.leaveCollab(dialogId);
	}

	kickUserFromChat(dialogId: string, userId: number): void
	{
		this.#userService.kickUserFromChat(dialogId, userId);
	}

	kickUserFromCollab(dialogId: string, userId: number): void
	{
		this.#userService.kickUserFromCollab(dialogId, userId);
	}

	addToChat(addConfig: {chatId: number, members: string[], showHistory: boolean}): Promise
	{
		return this.#userService.addToChat(addConfig);
	}

	joinChat(dialogId: string): void
	{
		this.#userService.joinChat(dialogId);
	}

	addManager(dialogId: string, userId: number): void
	{
		this.#userService.addManager(dialogId, userId);
	}

	removeManager(dialogId: string, userId: number): void
	{
		this.#userService.removeManager(dialogId, userId);
	}
	// endregion 'user

	// region 'messages auto delete'
	setMessagesAutoDeleteDelay(dialogId: string, delay: number): void
	{
		this.#messagesAutoDeleteService.setDelay(dialogId, delay);
	}
	// endregion 'messages auto delete'

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
		this.#deleteService = new DeleteService();
		this.#messagesAutoDeleteService = new MessagesAutoDeleteService();
	}
}
