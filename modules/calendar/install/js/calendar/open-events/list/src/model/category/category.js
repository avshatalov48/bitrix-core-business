export class CategoryModel
{
	#id: number;
	#closed: number;
	#name: string;
	#description: string;
	#eventsCount: number;
	#permissions: EventCategoryPermissions;
	#channelId: number;
	#isMuted: boolean;
	#isBanned: boolean;
	#newCount: number;
	#updatedAt: number = 0;
	#channel: ChannelInfo;

	#isSelected: boolean;

	//TODO: can see fields value on vue debug tools
	fields: {};

	constructor(fields: CategoryDto = {})
	{
		this.#id = fields.id;
		this.#closed = fields.closed;
		this.#name = fields.name;
		this.#description = fields.description;
		this.#eventsCount = fields.eventsCount;
		this.#permissions = fields.permissions;
		this.#channelId = fields.channelId;
		this.#isMuted = fields.isMuted;
		this.#isBanned = fields.isBanned;
		this.#newCount = fields.newCount;
		this.#updatedAt = fields.updatedAt || 0;
		this.#channel = fields.channel;

		this.#isSelected = false;

		this.fields = fields;
	}

	get id(): number
	{
		return this.#id;
	}

	get closed(): number
	{
		return this.#closed;
	}

	get name(): string
	{
		return this.#name;
	}

	get description(): string
	{
		return this.#description;
	}

	get eventsCount(): number
	{
		return this.#eventsCount;
	}

	get permissions(): EventCategoryPermissions
	{
		return this.#permissions;
	}

	get channelId(): number
	{
		return this.#channelId;
	}

	get isMuted(): boolean
	{
		return this.#isMuted;
	}

	set isMuted(isMuted: boolean): void
	{
		this.#isMuted = isMuted;
	}

	get isBanned(): boolean
	{
		return this.#isBanned;
	}

	set isBanned(isBanned: boolean): void
	{
		this.#isBanned = isBanned;
	}

	get newCount(): number
	{
		return this.#newCount;
	}

	get isSelected(): boolean
	{
		return this.#isSelected;
	}

	set isSelected(isSelected: number): void
	{
		this.#isSelected = isSelected;
	}

	get updatedAt(): number
	{
		return this.#updatedAt;
	}

	get channel(): ChannelInfo
	{
		return this.#channel;
	}

	set channel(channel: ChannelInfo): void
	{
		this.#channel = channel;
	}
}
