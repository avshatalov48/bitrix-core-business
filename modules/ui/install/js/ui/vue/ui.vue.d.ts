declare module 'ui.vue'
{
	import {RestClient} from "rest.client";
	import {PullClient} from "pull.client";
	import {EventEmitter} from "main.core.events";

	namespace $Bitrix
	{
		const Application: $BitrixApplication;
		const Loc: $BitrixLoc;
		const Data: $BitrixData;
		const eventEmitter: EventEmitter;
		const RestClient: $BitrixRestClient;
		const PullClient: $BitrixPullClient;
	}

	class $BitrixApplication {
		get(): Object;
		set(instance: Object): void
	}
	class $BitrixData {
		get(name: string, defaultValue?:any): any;
		set(name: string, value: any): void;
	}
	class $BitrixRestClient {
		get(): RestClient;
		set(instance: RestClient): void;
		isCustom(): boolean;
	}
	class $BitrixPullClient {
		get(): PullClient;
		set(instance: PullClient): void;
		isCustom(): boolean;
	}
	class $BitrixLoc {
		getMessage(name: string, replacements?: {[key: string]: string}): string;
		hasMessage(name: string): boolean;
		getMessages(): object;
		setMessage(id: string | {[key: string]: string}, value?: string): void;
	}

	namespace BitrixVue
	{
		function createApp(props?: {[key: string]: unknown}|null): VueCreateAppResult;
		function component(name: string, definition: object, options?: BitrixVueComponentOptions): boolean;
		function localComponent(name: string, definition: object, options?: BitrixVueComponentOptions): object;
		function getLocalComponent(name: string): object;
		function mutateComponent(name: string, mutations: object): BitrixVueRevertHandle;
		function cloneComponent(name: string, source: string, mutations: object): boolean;
		function cloneLocalComponent(source: object|string, mutations: object): object;
		function isComponent(name: string): boolean;
		function isLocal(name: string): boolean;
		function isMutable(name: string): boolean;
		function directive(name: string, definition: object): boolean;
		function getFilteredPhrases(phrasePrefix: string|Array<string>, phrases?: object|null): ReadonlyArray<any>;
		function testNode(object: object, params: object): boolean;
		const events: EventsList;
	}

	interface EventsList {
		restClientChange: string,
		pullClientChange: string,
	}

	class VueCreateAppResult {
		component(name: string, definition: object): VueCreateAppResult;
		directive(name: string, definition: object): VueCreateAppResult;
		use(plugin: Object|Function): VueCreateAppResult;
		mixin(mixin: object): VueCreateAppResult;
		mount(element: Element|string): object;
	}

	interface BitrixVueComponentOptions {
		immutable?: boolean,
		local?: boolean,
	}

	type BitrixVueRevertHandle = () => void
}