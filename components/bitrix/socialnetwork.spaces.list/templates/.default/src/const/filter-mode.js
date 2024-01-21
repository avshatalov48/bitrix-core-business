export const FilterModeTypes = Object.freeze({
	my: 'my',
	other: 'other',
	all: 'all',
});

export const FilterModes = Object.freeze([
	{
		type: FilterModeTypes.my,
		nameMessageId: 'SOCIALNETWORK_SPACES_LIST_FILTER_MODE_MY_TITLE',
		descriptionMessageId: 'SOCIALNETWORK_SPACES_LIST_FILTER_MODE_MY_DESCRIPTION',
	},
	{
		type: FilterModeTypes.other,
		nameMessageId: 'SOCIALNETWORK_SPACES_LIST_FILTER_MODE_OTHER_TITLE',
		descriptionMessageId: 'SOCIALNETWORK_SPACES_LIST_FILTER_MODE_OTHER_DESCRIPTION',
	},
	{
		type: FilterModeTypes.all,
		nameMessageId: 'SOCIALNETWORK_SPACES_LIST_FILTER_MODE_ALL_TITLE',
		descriptionMessageId: 'SOCIALNETWORK_SPACES_LIST_FILTER_MODE_ALL_DESCRIPTION',
	},
]);
