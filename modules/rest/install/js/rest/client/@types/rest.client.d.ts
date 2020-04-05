type RestOptions = {
    endpoint?: string,
    queryParams?: {[key: string]: any},
    cors?: boolean
}

type RestResult = {
    answer: object,
    query: object,
    status: number,
    data: () => { [key: string]: any },
    error: () => {
        status: number,
        ex: {
            error: string,
            error_description: string
        }
    },
    more: () => boolean
};

declare module 'rest.client' {
    namespace rest {

        function callMethod(
            method: string,
            params?: {[key: string]: any},
            callback?: (result: RestResult) => void,
            sendCallback?: (xhr: XMLHttpRequest) => void,
            logTag?: string
        ): Promise<RestResult>;

        function callBatch(
            calls: Array<object>,
            callback: (result: RestResult) => {},
            bHaltOnError?: boolean,
            sendCallback?: Function,
            logTag?: string
        ): boolean;
    }

    class RestClient {
        constructor(options?: RestOptions);
        callMethod(
            method: string,
            params?: {[key: string]: any},
            callback?: (result: RestResult) => void,
            sendCallback?: (xhr: XMLHttpRequest) => void,
            logTag?: string
        ): Promise<RestResult>;
        callBatch(
            calls: Array<{[key: string]: any}>,
            callback:(result: RestResult) => void,
            bHaltOnError?: boolean,
            sendCallback?: (xhr: XMLHttpRequest) => void,
            logTag?: string
        ): boolean;
    }
}