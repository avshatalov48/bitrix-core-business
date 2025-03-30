export const REVISION = 19; // api revision - check module/pull/include.php

export const ConnectionType = {
	WebSocket: 'webSocket',
	LongPolling: 'longPolling',
};

export const PullStatus = {
	Online: 'online',
	Offline: 'offline',
	Connecting: 'connect',
};

export const SenderType = {
	Unknown: 0,
	Client: 1,
	Backend: 2,
};

export const SubscriptionType = {
	Server: 'server',
	Client: 'client',
	Online: 'online',
	Status: 'status',
	Revision: 'revision',
};

export const CloseReasons = {
	NORMAL_CLOSURE: 1000,
	SERVER_DIE: 1001,
	CONFIG_REPLACED: 3000,
	CHANNEL_EXPIRED: 3001,
	SERVER_RESTARTED: 3002,
	CONFIG_EXPIRED: 3003,
	MANUAL: 3004,
	STUCK: 3005,
	BACKEND_ERROR: 3006,
	WRONG_CHANNEL_ID: 4010,
};

export const SystemCommands = {
	CHANNEL_EXPIRE: 'CHANNEL_EXPIRE',
	CONFIG_EXPIRE: 'CONFIG_EXPIRE',
	SERVER_RESTART: 'SERVER_RESTART',
};

export const ServerMode = {
	Shared: 'shared',
	Personal: 'personal',
};

export const RpcMethod = {
	Publish: 'publish',
	GetUsersLastSeen: 'getUsersLastSeen',
	Ping: 'ping',
	ListChannels: 'listChannels',
	SubscribeStatusChange: 'subscribeStatusChange',
	UnsubscribeStatusChange: 'unsubscribeStatusChange',
};
