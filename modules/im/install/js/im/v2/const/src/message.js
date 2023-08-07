export const MessageType = Object.freeze({
	self: 'self',
	opponent: 'opponent',
	system: 'system',
});

export const MessageComponent = Object.freeze({
	base: 'BaseMessage',
	chatCreation: 'ChatCreationMessage',
});

export const MessageExtension = Object.freeze({
	text: 'TextExtension',
	deleted: 'DeletedExtension',
	unsupported: 'UnsupportedExtension',
	callInvite: 'CallInviteExtension',
});

export const MessageMentionType = Object.freeze({
	user: 'USER',
	chat: 'CHAT',
	context: 'CONTEXT',
});

export const OwnMessageStatus = Object.freeze({
	sending: 'sending',
	sent: 'sent',
	viewed: 'viewed',
});
