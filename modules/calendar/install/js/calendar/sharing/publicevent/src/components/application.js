import { SharedEvent } from './sharedevent';

export const Application = {
	components: {
		SharedEvent
	},
	props: {
		link: Object,
		event: Object,
		owner: Object,
		ownerMeetingStatus: String,
		action: String,
	},
	created()
	{
	},
	template: `
		<SharedEvent
			:link="link"
			:event="event"
			:owner="owner"
			:ownerMeetingStatus="ownerMeetingStatus"
			:action="action"
		/>
	`
};