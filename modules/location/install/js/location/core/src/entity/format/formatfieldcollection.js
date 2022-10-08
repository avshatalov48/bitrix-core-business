import FieldCollection from '../generic/fieldcollection';
import FormatField from './formatfield';

export default class FormatFieldCollection extends FieldCollection
{
	initFields(fieldsData)
	{
		if(Array.isArray(fieldsData))
		{
			fieldsData.forEach((data) => {

				const field = new FormatField(data);

				if(field)
				{
					this.setField(field);
				}
			});
		}
	}
}