type SubscriptionType = {
    Server: 'server',
	Client: 'client',
	Online: 'online',
	Status: 'status',
	Revision: 'revision'
};
type PullStatus = {
    Online: 'online',
    Offline: 'offline',
    Connecting: 'connect'
};

type SubscriptionOptions = {
    type: SubscriptionType,
    moduleId?: string,
    command?: string,
    callback: Function,
};

type CloseReasons = {
    NORMAL_CLOSURE : 1000,
    SERVER_DIE : 1001,
    CONFIG_REPLACED : 3000,
    CHANNEL_EXPIRED : 3001,
    SERVER_RESTARTED : 3002,
    CONFIG_EXPIRED : 3003,
    MANUAL : 3004,
};

type PullOptions = {
    serverEnabled?: boolean,
    userId?: number,
    siteId?: string,
    restClient?: object,
    configTimestamp?: number,
    skipCheckRevision?: boolean,
};

type JsonRpcRequest = {
    jsonrpc?: string,
    method: string,
    params: string,
    id: string
}

type PullExtraData = {
	revision_im_mobile: number,
	revision_im_rest: number,
	revision_im_web: number,
	revision_mobile: number,
	revision_web: number,
	sender: object,
	server_name: string,
	server_time: string,
	server_time_ago: number,
	server_time_unix: number,
}

// user id to time in seconds, when user was connected last time
type UsersLastSeen = {
    [userId: number]: number
}


type UserStatusCallbackParameters = {
    userId: number,
    isOnline: boolean,
}

type UserStatusCallback = (event: UserStatusCallbackParameters) => void


declare class PullCommandHandler {
    getModuleId(): string;
    getSubscriptionType(): SubscriptionType;
    getMap(): object;
}

declare module 'pull.client' {
    namespace PULL {
        function subscribe(params: SubscriptionOptions|PullCommandHandler): Function;
        function extendWatch(tagId: string, force?: boolean): boolean;
        function clearWatch(tagId: string): boolean;
        function capturePullEvent(enable?: boolean): void;
        function getDebugInfo(): void;
        function start(options?: PullOptions): Promise<Function>;
        function disconnect(code?: string, reason?: string): void;
        function isJsonRpc(): boolean;
        function getUsersLastSeen(userList: number[]): Promise<UsersLastSeen>;
        function subscribeUserStatusChange(userId: number, callback: UserStatusCallback): Promise<null>;
        function unsubscribeUserStatusChange(userId: number, callback: UserStatusCallback): Promise<null>;
    }

    class PullClient {
        constructor(options?: PullOptions);
        subscribe(params: SubscriptionOptions|PullCommandHandler): Function;
        extendWatch(tagId: string, force?: boolean): boolean;
        clearWatch(tagId: string): boolean;
        capturePullEvent(enable?: boolean): void;
        getDebugInfo(): void;
        start(options?: PullOptions): Promise<Function>;
        disconnect(code?: string, reason?: string): void;
        static PullStatus: PullStatus;
        static SubscriptionType: SubscriptionType;
        static CloseReasons: CloseReasons;
    }
}