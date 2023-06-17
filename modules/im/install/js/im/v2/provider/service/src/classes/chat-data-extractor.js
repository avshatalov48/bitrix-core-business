import {RestMethod, DialogType} from 'im.v2.const';
import {UserManager} from 'im.v2.lib.user';

export class ChatDataExtractor
{
	response: Object = {};
	chatId: number = 0;
	dialogId: string = '';

	rawUsers: Array = [];

	users: Object = {};
	dialogues: Object = {};
	files: Object = {};
	messages: Object = {};
	reactions: Object[] = [];
	additionalUsers: Object[] = [];
	messagesToStore: Object = {};
	pinnedMessageIds: number[] = [];

	constructor(response: Object)
	{
		this.response = response;
	}

	extractData()
	{
		this.extractChatResult();
		this.extractUserResult();
		this.extractMessageListResult();
		this.extractContextResult();
		this.extractPinnedMessagesResult();

		this.fillChatsForUsers();
	}

	isOpenlinesChat(): boolean
	{
		const chat = this.dialogues[this.dialogId];
		if (!chat)
		{
			return false;
		}

		return chat.type === DialogType.lines;
	}

	getChatId(): number
	{
		return this.chatId;
	}

	getUsers(): Array
	{
		return this.rawUsers;
	}

	getDialogues(): Array
	{
		return Object.values(this.dialogues);
	}

	getMessages(): Array
	{
		return Object.values(this.messages);
	}

	getMessagesToStore(): Array
	{
		return Object.values(this.messagesToStore);
	}

	getFiles(): Array
	{
		return Object.values(this.files);
	}

	getPinnedMessages(): number[]
	{
		return this.pinnedMessageIds;
	}

	getReactions(): Object[]
	{
		return this.reactions;
	}

	getAdditionalUsers(): Object[]
	{
		return this.additionalUsers;
	}

	extractChatResult()
	{
		const chat = this.response[RestMethod.imChatGet];
		this.chatId = chat.id;
		this.dialogId = chat.dialog_id;
		if (!this.dialogues[chat.dialog_id])
		{
			this.dialogues[chat.dialog_id] = chat;
		}
	}

	extractUserResult()
	{
		// solo user for group chats
		const soloUser = this.response[RestMethod.imUserGet];
		if (soloUser)
		{
			this.rawUsers = [soloUser];
			return;
		}

		// two users for 1v1
		const userList = this.response[RestMethod.imUserListGet];
		if (userList)
		{
			this.rawUsers = Object.values(userList);
		}
	}

	extractMessageListResult()
	{
		const messageList = this.response[RestMethod.imV2ChatMessageList];
		if (!messageList)
		{
			return;
		}

		this.extractPaginationFlags(messageList);
		this.extractMessages(messageList);
		this.extractReactions(messageList);
		this.extractAdditionalUsers(messageList);
	}

	extractPaginationFlags(data: {hasNextPage: boolean, hasPrevPage: boolean})
	{
		const {hasPrevPage, hasNextPage} = data;
		this.dialogues[this.dialogId] = {...this.dialogues[this.dialogId], hasPrevPage, hasNextPage};
	}

	extractContextResult()
	{
		const contextMessageList = this.response[RestMethod.imV2ChatMessageGetContext];
		if (!contextMessageList)
		{
			return;
		}

		this.extractPaginationFlags(contextMessageList);
		this.extractMessages(contextMessageList);
		this.extractReactions(contextMessageList);
		this.extractAdditionalUsers(contextMessageList);
	}

	extractReactions(data: {reactions: Object[]})
	{
		const {reactions} = data;
		this.reactions = reactions;
	}

	extractAdditionalUsers(data: {usersShort: Object[]})
	{
		const {usersShort} = data;
		this.additionalUsers = usersShort;
	}

	extractPinnedMessagesResult()
	{
		const pinMessageList = this.response[RestMethod.imV2ChatPinTail];
		if (!pinMessageList)
		{
			return;
		}

		const {list = [], users: pinnedUsers = [], files: pinnedFiles = []} = pinMessageList;
		this.rawUsers = [...this.rawUsers, ...pinnedUsers];
		pinnedFiles.forEach(file => {
			this.files[file.id] = file;
		});
		list.forEach(pinnedItem => {
			this.pinnedMessageIds.push(pinnedItem.messageId);
			this.messagesToStore[pinnedItem.message.id] = pinnedItem.message;
		});
	}

	extractMessages(data: {messages: Object[], users: Object[], files: Object[]})
	{
		const {messages, users, files} = data;
		files.forEach(file => {
			this.files[file.id] = file;
		});
		messages.forEach(message => {
			this.messages[message.id] = message;
		});

		this.rawUsers = [...this.rawUsers, ...users];
	}

	fillChatsForUsers()
	{
		this.rawUsers.forEach(user => {
			if (!this.dialogues[user.id])
			{
				this.dialogues[user.id] = UserManager.getDialogForUser(user);
			}
			else
			{
				this.dialogues[user.id] = {...this.dialogues[user.id], ...UserManager.getDialogForUser(user)};
			}
		});
	}
}