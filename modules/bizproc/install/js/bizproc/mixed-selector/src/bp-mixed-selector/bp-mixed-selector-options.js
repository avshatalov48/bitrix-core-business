export type BpMixedSelectorOptions = {
	targetNode: HTMLElement,
	targetTitle?: string,
	objectTabs?: {
		Parameter?: {
			[string]: {
				Name: string
			}
		},
		Variable?: {
			[string]: {
				Name: string
			}
		},
		Constant?: {
			[string]: {
				Name: string
			}
		},
		GlobalConst?: {
			[string]: {
				Name: string
			}
		},
		GlobalVar?: {
			[string]: {
				Name: string
			}
		},
		Document?: {
			[string]: {
				Name: string
			}
		},
		Activity?: {
			[string]: {
				NAME: string,
				RETURN?: object,
				ADDITIONAL_RESULT?: object
			}
		},
	},
	template?: Array,
	activityName?: string,
	checkActivityChildren?: boolean,
	size?: {
		maxWidth?: number,
		maxHeight?: number,
		minWidth?: number,
		minHeight?: number
	},
	inputNames?: {
		object: string,
		field: string
	}
};