export const ChatEmbeddedApplication = {
	task: 'task',
	aiAssistantWidget: 'aiAssistantWidget',
};

export type ChatEmbeddedApplicationType = $Values<typeof ChatEmbeddedApplication>;
export type ChatEmbeddedApplicationInstance = {
	render: (element: HTMLElement | string) => Promise,
};
