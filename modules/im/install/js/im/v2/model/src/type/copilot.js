export type CopilotPrompt = {
	code: string,
	promptType: string,
	text: string,
	title: string
};

export type CopilotRole = {
	code: CopilotRoleCode,
	name: string,
	desc: string,
	default: boolean,
	avatar: {
		small: string,
		medium: string,
		large: string,
	},
	prompts: CopilotPrompt[],
};

export type CopilotRoleCode = string;

export type AvatarSize = 'S' | 'M' | 'L';
