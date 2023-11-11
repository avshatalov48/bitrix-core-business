export const MessageType = Object.freeze({
	self: 'self',
	opponent: 'opponent',
	system: 'system',
});

export const MessageComponent = Object.freeze({
	default: 'DefaultMessage',
	file: 'FileMessage',
	smile: 'SmileMessage',
	unsupported: 'UnsupportedMessage',
	deleted: 'DeletedMessage',
	callInvite: 'CallInviteMessage',
	chatCreation: 'ChatCreationMessage',
	conferenceCreation: 'ConferenceCreationMessage',
	system: 'SystemMessage',
});

export const MessageMentionType = Object.freeze({
	user: 'USER',
	chat: 'CHAT',
	lines: 'LINES',
	context: 'CONTEXT',
	call: 'CALL',
});

export const MessageStatus = {
	received: 'received',
	delivered: 'delivered',
	error: 'error',
};

export const OwnMessageStatus = Object.freeze({
	sending: 'sending',
	sent: 'sent',
	viewed: 'viewed',
});
