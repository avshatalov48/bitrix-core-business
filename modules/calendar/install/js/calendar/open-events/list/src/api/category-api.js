export type ListParams = {
	isBanned: boolean,
	query: string,
	page: number,
	categoryId: number,
};

export class CategoryApi
{
	static async list(params: ListParams): Promise<CategoryDto>
	{
		const response = await BX.ajax.runAction('calendar.open-events.Category.list', {
			data: params,
		});

		return response.data;
	}

	static async add(fields: CreateCategoryDto): Promise<CategoryDto>
	{
		const response = await BX.ajax.runAction('calendar.open-events.Category.add', {
			data: {
				name: fields.name,
				description: fields.description,
				closed: fields.closed,
				attendees: fields.attendees,
				departmentIds: fields.departmentIds,
				channelId: fields.channelId,
			},
		});

		return response.data;
	}

	static update(fields: UpdateCategoryDto): Promise<void>
	{
		return BX.ajax.runAction('calendar.open-events.Category.update', {
			data: {
				id: fields.id,
				name: fields.name,
				description: fields.description,
			},
		});
	}

	static setMute(id: number, muteState: boolean): Promise<void>
	{
		return BX.ajax.runAction('calendar.open-events.Category.setMute', {
			data: { id, muteState },
		});
	}

	static setBan(id: number, banState: boolean): Promise<void>
	{
		return BX.ajax.runAction('calendar.open-events.Category.setBan', {
			data: { id, banState },
		});
	}

	static async getChannelInfo(id: number): Promise<ChannelInfo>
	{
		const response = await BX.ajax.runAction('calendar.open-events.Category.getChannelInfo', {
			data: { id },
		});

		return response.data;
	}
}
