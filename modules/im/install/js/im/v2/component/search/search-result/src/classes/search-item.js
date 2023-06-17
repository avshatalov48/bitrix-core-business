import {Type} from 'main.core';
import {SearchEntityIdTypes, DialogType} from 'im.v2.const';
import {SearchUtils} from './search-utils';

export class SearchItem
{
	id: number | string;
	entityId: $Values<typeof SearchEntityIdTypes>;
	entityType: ?string = null;
	dialogId: string = null;
	title: ?string = null;
	subtitle: ?string = null;
	name: ?string = null;
	lastName: ?string = null;
	secondName: ?string = null;
	position: ?string = null;
	avatar: ?string = null;
	avatarOptions: ?Object = null;
	customSort: number = 0;
	contextSort: number = 0;
	fromStore: boolean = false;
	rawData: ?Object = null;

	constructor(itemOptions)
	{
		this.#setRawData(itemOptions);
		this.#setId(itemOptions);
		this.#setDialogId(itemOptions);
		this.#setEntityId(itemOptions);
		this.#setEntityType(itemOptions);
		this.#setTitle(itemOptions);
		this.#setSubtitle(itemOptions);
		this.#setName(itemOptions);
		this.#setLastName(itemOptions);
		this.#setSecondName(itemOptions);
		this.#setPosition(itemOptions);
		this.#setAvatar(itemOptions);
		this.#setAvatarOptions(itemOptions);
		this.#setContextSort(itemOptions);
	}

	#isFromProviderResponse(itemOptions: Object): boolean
	{
		return Type.isString(itemOptions.entityId) && !Type.isNil(itemOptions.id);
	}

	isFromModel(itemOptions: Object): boolean
	{
		return Type.isString(itemOptions.dialogId) && Type.isObject(itemOptions.dialog);
	}

	#setId(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			this.id = itemOptions.id;
		}
		else if (this.isFromModel(itemOptions))
		{
			const id = itemOptions.dialogId.startsWith('chat') ? itemOptions.dialogId.slice(4) : itemOptions.dialogId;
			this.id = Number.parseInt(id, 10);
			this.fromStore = true;
		}
	}

	#setDialogId(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			if (itemOptions.customData?.imChat?.ID > 0)
			{
				this.dialogId = `chat${itemOptions.customData.imChat.ID}`;
			}
			else if (itemOptions.customData?.imUser?.ID > 0)
			{
				this.dialogId = itemOptions.customData.imUser.ID.toString();
			}
		}
		else if (this.isFromModel(itemOptions))
		{
			this.dialogId = itemOptions.dialogId;
		}
	}

	#setEntityId(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			this.entityId = itemOptions.entityId;
		}
		else if (this.isFromModel(itemOptions))
		{
			if (!itemOptions.user)
			{
				this.entityId = SearchEntityIdTypes.chat;
			}
			else if (itemOptions.user.bot)
			{
				this.entityId = SearchEntityIdTypes.bot;
			}
			else
			{
				this.entityId = SearchEntityIdTypes.user;
			}
		}
	}

	#setEntityType(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			this.entityType = itemOptions.entityType ? itemOptions.entityType.toLowerCase() : '';
		}
		else if (this.isFromModel(itemOptions))
		{
			const {type} = itemOptions.dialog;

			if (type === DialogType.user)
			{
				this.entityType = itemOptions.user.extranet ? 'extranet' : 'employee';
			}
			else
			{
				this.entityType = type;
			}
		}
	}

	#setTitle(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			this.title = itemOptions.title;
		}
		else if (this.isFromModel(itemOptions))
		{
			this.title = itemOptions.dialog.name;
		}
	}

	#setSubtitle(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			this.subtitle = itemOptions.subtitle;
		}
	}

	#setName(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			this.name = itemOptions.customData?.name;
		}
		else if (this.isFromModel(itemOptions))
		{
			this.name = itemOptions.user?.firstName;
		}
	}

	#setLastName(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			this.lastName = itemOptions.customData?.lastName;
		}
		else if (this.isFromModel(itemOptions))
		{
			this.lastName = itemOptions.user?.lastName;
		}
	}

	#setSecondName(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			this.secondName = itemOptions.customData?.secondName;
		}
	}

	#setPosition(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			this.position = itemOptions.customData?.position;
		}
		else if (this.isFromModel(itemOptions))
		{
			this.position = itemOptions.user?.workPosition;
		}
	}

	#setAvatar(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			this.avatar = itemOptions.avatar !== '' ? itemOptions.avatar : null;
		}
		else if (this.isFromModel(itemOptions))
		{
			const avatar = itemOptions.user ? itemOptions.user.avatar : itemOptions.dialog.avatar;
			this.avatar = avatar !== '' ? decodeURI(avatar) : null;
		}
	}

	#setAvatarOptions(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			this.avatarOptions = itemOptions.avatarOptions;
		}
	}

	#setContextSort(itemOptions: Object)
	{
		if (this.#isFromProviderResponse(itemOptions))
		{
			this.contextSort = itemOptions.contextSort;
		}
	}

	#setRawData(itemOptions: Object)
	{
		this.rawData = itemOptions;
	}

	getId(): number | string
	{
		return this.id;
	}

	getEntityId(): string
	{
		return this.entityId;
	}

	getEntityType(): ?string
	{
		return this.entityType.toLowerCase();
	}

	getEntityFullId(): string
	{
		const type = SearchUtils.getTypeByEntityId(this.entityId);

		return `${type}|${this.id}`;
	}

	getTitle(): ?string
	{
		return this.title;
	}

	getSubtitle(): ?string
	{
		return this.subtitle;
	}

	getName(): ?string
	{
		return this.name;
	}

	getLastName(): ?string
	{
		return this.lastName;
	}

	getSecondName(): ?string
	{
		return this.secondName;
	}

	getPosition(): ?string
	{
		return this.position;
	}

	getCustomData(): ?Object
	{
		return this.rawData.customData;
	}

	getDialogId(): string
	{
		return this.dialogId;
	}

	getAvatar(): ?string
	{
		return this.avatar;
	}

	getAvatarOptions()
	{
		return this.avatarOptions;
	}

	getContextSort(): number
	{
		return this.contextSort ? this.contextSort : 0;
	}

	addCustomSort(value: number)
	{
		this.customSort += value;
	}

	getCustomSort(): number
	{
		return this.customSort;
	}

	isUser(): boolean
	{
		if (this.#isFromProviderResponse(this.rawData))
		{
			return !!this.rawData.customData?.imUser && this.rawData.customData.imUser.ID > 0;
		}

		return !!this.rawData.user;
	}

	isChat(): boolean
	{
		return [SearchEntityIdTypes.chat, SearchEntityIdTypes.chatUser].includes(this.getEntityId());
	}

	isExtranet(): boolean
	{
		if (this.#isFromProviderResponse(this.rawData))
		{
			return !!this.rawData.customData?.imUser?.EXTRANET || !!this.rawData.customData?.imChat?.EXTRANET;
		}
		else if (this.isFromModel(this.rawData))
		{
			return !!this.rawData.user?.extranet || !!this.rawData.dialog.extranet;
		}
	}

	getUserCustomData()
	{
		return this.rawData.customData?.imUser ? this.rawData.customData.imUser : null;
	}

	getChatCustomData()
	{
		return this.rawData.customData?.imChat ? this.rawData.customData.imChat : null;
	}

	isOpeLinesType(): boolean
	{
		return this.getEntityType() === 'lines';
	}

	isDepartmentType(): boolean
	{
		return this.getEntityId() === SearchEntityIdTypes.department;
	}

	isNetworkType(): boolean
	{
		return this.getEntityId() === SearchEntityIdTypes.network;
	}

	getOpenlineEntityId(): string
	{
		if (!this.isOpeLinesType())
		{
			return '';
		}
		const entityId = this.rawData.customData?.imChat?.ENTITY_ID;

		return entityId.toString().split('|')[0];
	}

	getAvatarColor(): string
	{
		let color = '';
		if (this.#isFromProviderResponse(this.rawData))
		{
			if (this.isUser())
			{
				color = this.rawData.customData?.imUser?.COLOR?.toString();
			}
			else if (this.isChat())
			{
				color = this.rawData.customData?.imChat?.COLOR?.toString();
			}
		}
		else if (this.isFromModel(this.rawData))
		{
			color = this.rawData.dialog.color.toString();
		}

		return color;
	}

	isCrmSession()
	{
		if (this.#isFromProviderResponse(this.rawData) && this.isOpeLinesType())
		{
			const sessionData = this.rawData.customData?.imChat?.ENTITY_DATA_1.toString().split('|');

			return sessionData[0] === 'Y';
		}

		return false;
	}
}