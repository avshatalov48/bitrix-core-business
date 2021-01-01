import {BaseCollection} from 'landing.collection.basecollection';

/**
 * @memberOf BX.Landing.UI.Collection
 */
export class FormCollection extends BaseCollection
{
	fetchFields(): BaseCollection
	{
		const collection = new BaseCollection();

		this.forEach((form) => {
			collection.push(...form.fields);
		});

		return collection;
	}

	fetchChanges(): FormCollection
	{
		return this.filter((form) => form.isChanged());
	}
}