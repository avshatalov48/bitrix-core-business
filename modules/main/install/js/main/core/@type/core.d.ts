declare type result = string | number;
declare type ajaxConfig = {
    url: string,
    method?: 'POST' | 'GET',
    data?: string | Object,
    dataType?: 'html' | 'json' | 'script',
    timeout?: number,
    async?: boolean,
    processData?: boolean,
    scriptsRunFirst?: boolean,
    emulateOnload?: boolean,
    start?: boolean,
    cache?: boolean,
    onsuccess?: (result: any) => {},
    onfailure?: (result: any) => {},
};

declare type scriptEntry = {
    isInternal: boolean,
    JS: string,
}


declare namespace ajax
{
    function xhr(): XMLHttpRequest | null;
    function isCrossDomain(url: string, location?: Location): boolean;
    function getHostPort(protocol: string, host: string): string;
    function processRequestData(data: any, config: ajaxConfig): void;
    function processScripts(scripts: scriptEntry[], runFirst?: boolean, callback?: () => {}): void;
    function prepareData(data: string | string[], prefix?: string): string;
    function xhrSuccess(xhr: XMLHttpRequest): boolean;
    function replaceLocalStorageValue(id: string, data: any, ttl?): void;
    function get(url: string, data: Object, callback?): XMLHttpRequest | null;
    function get(url: string, callback): XMLHttpRequest | null;
    function post(url: string, data: Object, callback): XMLHttpRequest;
    function getCaptcha(callback?): XMLHttpRequest | null;
    function insertToNode(url: string, node: Node): XMLHttpRequest | void;
    function promise(config: ajaxConfig): Promise<any>;
    function loadScriptAjax(src: string, callback: () => {}, preload?: boolean): XMLHttpRequest | void;
    function loadJSON(url: string, data: any, success: (result) => {}, failure?: (any) => {}): XMLHttpRequest | void;
    function runAction(action: string, config: ajaxConfig): Promise<any>;
    function runComponentAction(component: string, action: string, config: ajaxConfig): Promise<any>;
    function load(items: any, callback?: (result: any) => {}): Promise<any>;
    function submit(form: HTMLFormElement, callback?: (result: any) => {}): false;
    function submitComponentForm(form: HTMLFormElement, container: Node, wait?: boolean): true;
    function prepareForm(form: HTMLFormElement, data?: any): {data: Object, filesCount: number, roughSize: number};
    function submitAjax(form: HTMLFormElement, config: ajaxConfig): void;
}