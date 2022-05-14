export default {
	computed:
	{
		processing()
		{
			return this.status === 'P';
		},
		downloadable()
		{
			return this.status === 'Y' && this.link !== '';
		},
	}
};