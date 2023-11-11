import { EventEmitter } from 'main.core.events';

import { UserRole, DialogType } from 'im.v2.const';

const EVENT_NAMESPACE = 'BX.Messenger.v2.CreateChatManager';

type UserRoleItem = $Values<typeof UserRole>;
type ChatFields = {
	chatTitle: string,
	avatarFile: ?File,
	chatMembers: number[],
	settings: {
		isAvailableInSearch: boolean,
		description: string,
	},
	rights: {
		ownerId: number,
		managerIds: number[],
		manageUsers: UserRoleItem,
		manageSettings: UserRoleItem,
		manageUi: UserRoleItem,
	},
};
type ChatTypeItem = $Values<typeof DialogType>;

export class CreateChatManager extends EventEmitter
{
	static events = {
		creationStatusChange: 'creationStatusChange',
		titleChange: 'titleChange',
		avatarChange: 'avatarChange',
		chatTypeChange: 'chatTypeChange',
	};

	static #instance: CreateChatManager;

	#isCreating: boolean = false;
	#chatType: ChatTypeItem = DialogType.chat;
	#chatTitle: string = '';
	#chatAvatarFile: File = null;
	#chatFields: ChatFields;

	static getInstance(): CreateChatManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	constructor(props)
	{
		super(props);
		this.setEventNamespace(EVENT_NAMESPACE);
	}

	isCreating(): boolean
	{
		return this.#isCreating;
	}

	getChatType(): ChatTypeItem
	{
		return this.#chatType;
	}

	getChatTitle(): string
	{
		return this.#chatTitle;
	}

	getChatAvatar(): ?File
	{
		return this.#chatAvatarFile;
	}

	setChatType(type: ChatTypeItem)
	{
		this.#chatType = type;
		this.emit(CreateChatManager.events.chatTypeChange, type);
	}

	setCreationStatus(flag: boolean)
	{
		this.#isCreating = flag;
		this.clearFields();
		this.emit(CreateChatManager.events.creationStatusChange, flag);
	}

	setChatTitle(chatTitle: string)
	{
		this.#chatTitle = chatTitle;
		this.emit(CreateChatManager.events.titleChange, chatTitle);
	}

	setChatAvatar(chatAvatarFile: ?File)
	{
		this.#chatAvatarFile = chatAvatarFile;
		this.emit(CreateChatManager.events.avatarChange, chatAvatarFile);
	}

	saveFields(chatFields: ChatFields)
	{
		this.#chatFields = chatFields;
	}

	getFields(): ?ChatFields
	{
		return this.#chatFields;
	}

	clearFields()
	{
		this.#chatFields = null;
		this.setChatTitle('');
		this.setChatAvatar(null);
	}
}
