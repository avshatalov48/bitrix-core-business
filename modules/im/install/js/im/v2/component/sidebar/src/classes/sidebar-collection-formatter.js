import {DateFormatter, DateTemplate} from 'im.v2.lib.date-formatter';

type FormattableCollection = {
	date: Date,
};

type DateGroup = {
	id: number,
	title: string
};

export class SidebarCollectionFormatter
{
	cachedDateGroups: Object = {};

	format(collection: FormattableCollection[]): Object[]
	{
		const dateGroups = {};

		collection.forEach(item => {
			const dateGroup = this.getDateGroup(item.date);
			if (!dateGroups[dateGroup.title])
			{
				dateGroups[dateGroup.title] = {
					dateGroupTitle: dateGroup.title,
					items: []
				};
			}

			dateGroups[dateGroup.title].items.push(item);
		});

		return Object.values(dateGroups);
	}

	getDateGroup(date: Date): DateGroup
	{
		const INDEX_BETWEEN_DATE_AND_TIME = 10;
		// 2022-10-25T14:58:44.000Z => 2022-10-25
		const shortDate = date.toJSON().slice(0, INDEX_BETWEEN_DATE_AND_TIME);
		if (this.cachedDateGroups[shortDate])
		{
			return this.cachedDateGroups[shortDate];
		}

		this.cachedDateGroups[shortDate] = {
			id: shortDate,
			title: DateFormatter.formatByTemplate(date, DateTemplate.dateGroup)
		};

		return this.cachedDateGroups[shortDate];
	}

	destroy()
	{
		this.cachedDateGroups = {};
	}
}