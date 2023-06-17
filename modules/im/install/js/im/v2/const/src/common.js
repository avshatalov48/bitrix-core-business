export const MutationType = Object.freeze({
	none: 'none',
	add: 'delete',
	update: 'update',
	delete: 'delete',
	set: 'set',
	setAfter: 'after',
	setBefore: 'before',
});

export const StorageLimit = Object.freeze({
	dialogues: 50,
	messages: 100,
});

export const OpenTarget = Object.freeze({
	current: 'current',
	auto: 'auto',
});

export const BotType = Object.freeze({
	bot: 'bot',
	network: 'network',
	support24: 'support24'
});