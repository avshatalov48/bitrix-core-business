import FieldCollection from '../generic/fieldcollection';
import LocationField from './locationfield';

export default class LocationFieldCollection extends FieldCollection
{
	getFieldValue(type)
	{
		let result = null;

		if(this.isFieldExists(type))
		{
			const field = this.getField(type);

			if(field)
			{
				result = field.value;
			}
		}

		return result;
	}

	setFieldValue(type, value)
	{
		this.setField(
			new LocationField({type, value})
		);

		return this;
	}
}