import {Loc} from 'landing.loc';

export default function getFilterStub(text = ''): {key: string, name: string, value: any}
{
	let name = text;
	if (name === '')
	{
		name = Loc.getMessage('LANDING_BLOCK__SOURCE_FILTER_STUB');
	}

	return {
		key: 'filterStub',
		name,
		value: '',
	};
}