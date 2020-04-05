import {Loc} from 'landing.loc';

export default function getFilterStub(): {key: string, name: string, value: any}
{
	return {
		key: 'filterStub',
		name: Loc.getMessage('LANDING_BLOCK__SOURCE_FILTER_STUB'),
		value: '',
	};
}