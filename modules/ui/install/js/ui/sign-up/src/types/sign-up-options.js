import typeof {BaseEvent} from 'main.core.events';

export type SignUpOptions = {
	value?: {
		src?: string,
		initials?: {
			firstName: string,
			lastName: string,
		},
	},
	defaultState?: 'initials' | 'touch' | 'image',
	mode: 'mobile' | 'desktop',
	events?: {
		onSaveClick?: (event: BaseEvent) => void,
		onSaveClickAsync?: (event: BaseEvent) => Promise<any>,
		onCancelClick?: (event: BaseEvent) => void,
	},
};