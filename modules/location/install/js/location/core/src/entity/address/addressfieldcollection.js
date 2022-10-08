import FieldCollection from '../generic/fieldcollection';
import AddressField from './addressfield';

export default class AddressFieldCollection extends FieldCollection
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
			new AddressField({type, value})
		);

		return this;
	}
}