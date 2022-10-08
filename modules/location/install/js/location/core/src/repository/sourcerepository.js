import BaseRepository from './baserepository';

export default class SourceRepository extends BaseRepository
{
	constructor(props = {}) {
		props.path = 'location.api.source';
		super(props);
	}

	getProps(): Promise
	{
		return this.actionRunner.run('getProps', {})
			.then(this.processResponse);
	}
}
