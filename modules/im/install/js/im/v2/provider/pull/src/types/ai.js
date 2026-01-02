export type EngineUpdateParams = {
	chatId: number,
	engineCode: string,
	engineName: string,
};

export type FileTranscriptionParams = {
	fileId: number,
	status: 'Success' | 'Pending' | 'Error',
	transcriptText: string | null,
};

export type CopilotRoleParams = {
	chatId: number,
	dialogId: string,
	copilotRole: CopilotRoleCollection,
};

type CopilotRoleCollection = {
	chats: CopilotChatData[],
	roles: { [string]: CopilotRole },
}

type CopilotChatData = {
	dialogId: string,
	role: string,
};

type CopilotRole = {
	code: string,
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

type CopilotPrompt = {
	code: string,
	isNew: boolean,
	promptType: string,
	text: string,
	title: string
};
