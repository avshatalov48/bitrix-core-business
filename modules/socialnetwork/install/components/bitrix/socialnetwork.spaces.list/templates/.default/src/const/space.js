export const SpaceViewModeTypes = Object.freeze({
	open: 'open',
	closed: 'closed',
	secret: 'secret',
});

export const SpaceViewModes = Object.freeze([
	{
		type: SpaceViewModeTypes.open,
		nameMessageId: 'SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_OPEN_TITLE',
		descriptionMessageId: 'SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_OPEN_DESCRIPTION',
	},
	{
		type: SpaceViewModeTypes.closed,
		nameMessageId: 'SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_CLOSED_TITLE',
		descriptionMessageId: 'SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_CLOSED_DESCRIPTION',
	},
	{
		type: SpaceViewModeTypes.secret,
		nameMessageId: 'SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_SECRET_TITLE',
		descriptionMessageId: 'SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_SECRET_DESCRIPTION',
	},
]);

export const SpaceUserRoles = Object.freeze({
	nonMember: 'nonMember',
	applicant: 'applicant',
	invited: 'invited',
	member: 'member',
});

export const SpaceCommonToCommentActivityTypes = Object.freeze({
	calendar: 'calendar_comment',
	task: 'task_comment',
	livefeed: 'livefeed_comment',
});
