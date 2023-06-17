declare module 'ui.vue3'
{
	import {RestClient} from 'rest.client';
	import {PullClient} from 'pull.client';
	import {EventEmitter} from 'main.core.events';

	namespace $Bitrix
	{
		const Application: $BitrixApplication;
		const Data: $BitrixData;
		const RestClient: $BitrixRestClient;
		const PullClient: $BitrixPullClient;
		const Loc: $BitrixLoc;
		const eventEmitter: EventEmitter;
	}

	class BitrixInstance
	{
		Application: $BitrixApplication;
		Data: $BitrixData;
		RestClient: $BitrixRestClient;
		PullClient: $BitrixPullClient;
		Loc: $BitrixLoc;
		eventEmitter: EventEmitter;
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
		getMessage(name: string, defaultValue?:any): any;
		getMessages(): object;
		setMessage(id: string | {[key: string]: string}, value?: string): void;
	}

	namespace BitrixVue
	{
		function createApp(rootComponent: BitrixVueComponentProps, rootProps?: {[key: string]: any}|null): VueCreateAppResult;
		function mutableComponent(name: string, definition: BitrixVueComponentProps): BitrixVueComponentProxy;
		function cloneComponent(source: string|object|BitrixVueComponentProxy, mutations: BitrixVueComponentProps): BitrixVueComponentProxy;
		function mutateComponent(source: string|BitrixVueComponentProxy, mutations: BitrixVueComponentProps): boolean;
		function defineAsyncComponent(extension: string|string[], componentExportName: string, options?: VueAsyncComponentOptions): Promise<BitrixVueComponentProps>;
		function getMutableComponent(name: string, silentMode?: boolean): BitrixVueComponentProps;
		function isComponent(name: string): boolean;
		function testNode(object: object, params: object): boolean;
		function getFilteredPhrases(vueInstance: VueCreateAppResult, phrasePrefix: string|Array<string>, phrases: object|null): ReadonlyArray<any>;
		const events: EventsList;
	}

	interface EventsList {
		restClientChange: string,
		pullClientChange: string,
	}

	function createApp(params: object): VueCreateAppResult;
	function nextTick(func: Function): void;

	function setup(data: { [key: string]: unknown }, context: VueSetupContext);
	function onBeforeMount(func: Function): void;
	function onMounted(func: Function): void;
	function onBeforeUpdate(func: Function): void;
	function onUpdated(func: Function): void;
	function onBeforeUnmount(func: Function): void;
	function onUnmounted(func: Function): void;
	function onErrorCaptured(func: Function): void;
	function onRenderTracked(func: Function): void;
	function onRenderTriggered(func: Function): void;
	function getCurrentInstance(): object;

	function inject(key: string, defaultValue: any): any
	function inject(
		key: string,
		defaultValue: () => any,
		treatDefaultAsFactory: true
	): any;
	function provide(key: string, value: any): void

	function effectScope(detached?: boolean): EffectScope

	interface EffectScope {
		run<T>(fn: () => T): T | undefined
		stop(): void
	}

	function ref(value): VueRefValue;
	function unref(value): any;
	function toRef(source: object, value): object;
	function toRefs(source: object): object;
	function isRef(object: object): boolean;
	function customRef(factory: Function): object;
	function shallowRef(source: object): object;
	function triggerRef(source: object): object;

	function computed(getter: () => object): object;
	function computed(options: { get: () => object; set: (value: object) => void }): object;
	function watchEffect(
		effect: (onInvalidate: (invalidate: () => void) => void) => void,
		options?: VueWatchEffectOptions
	): Function;
	function watch(func: Function, ...args: unknown[]): object;

	function reactive(target: object): object;
	function readonly(target: object): object;
	function isProxy(target: object): boolean;
	function isReactive(target: object): boolean;
	function isReadonly(target: object): boolean;
	function toRaw(target: object): object;
	function markRaw(target: object): object;
	function shallowReactive(target: object): object;
	function shallowReadonly(target: object): object;

	function h(type: String|Object|Function, props: Object, children: String|Array<object>|Object): object;
	function defineComponent();
	function defineAsyncComponent();
	function resolveComponent();
	function resolveDynamicComponent();
	function resolveDirective();
	function withDirectives();
	function createRenderer();
	function mergeProps();

	class VueCreateAppResult {
		component(name: string, definition: object): VueCreateAppResult;
		directive(name: string, definition: object): VueCreateAppResult;
		use(plugin: Object|Function): VueCreateAppResult;
		mount(rootContainer: string|Element): object;
		unmount(): void;
		config: VueAppConfig;
		provide(key: string|Symbol, value): VueCreateAppResult;
		mixin(mixin: object): VueCreateAppResult;
	}

	type BitrixVueComponentProps = {
		name?: string,
		compilerOptions?: {[key: string]: any},
		components?: {[key: string]: object},
		directives?: {[key: string]: object},
		extends?: {[key: string]: BitrixVueComponentProps},
		mixins?: {[key: string]: any},
		provide?: {[key: string]: any},
		inject?: Array<string>,
		inheritAttrs?: boolean,
		props?: {[key: string]: any}|Array<string>,
		emits?: {[key: string]: any}|Array<string>,
		setup?: Function,
		data?: Function,
		computed?: {[key: string]: any},
		watch?: {[key: string]: Function},
		beforeCreate?: Function,
		created?: Function,
		beforeMount?: Function,
		mounted?: Function,
		beforeUpdate?: Function,
		updated?: Function,
		activated?: Function,
		deactivated?: Function,
		beforeUnmount?: Function,
		unmounted?: Function,
		errorCaptured?: Function,
		renderTracked?: Function,
		renderTriggered?: Function,
		methods?: {[key: string]: Function},
		template?: string,
		render?: Function,
	}

	type BitrixVueComponentProxy = BitrixVueComponentProps;

	type VueAppConfig = {
		errorHandler?: Function,
		warnHandler?: Function,
		globalProperties?: {[key: string]: any},
		isCustomElement: (tag: string) => boolean,
		optionMergeStrategies: { [key: string]: Function },
		performance: boolean,
	};

	type VueAsyncComponentOptions = {
		loader?: Promise<object>,
		loadingComponent: Function,
		delay?: bigint,
		errorComponent: Function,
		timeout?: bigint,
		delayLoadExtension?: bigint,
	};

	type VueRefValue = {
		value: any
	}

	interface VueWatchEffectOptions {
	  flush?: 'pre' | 'post' | 'sync' // default: 'pre'
	  onTrack?: (event: VueDebuggerEvent) => void
	  onTrigger?: (event: VueDebuggerEvent) => void
	}

	interface VueDebuggerEvent {
		effect: any,
		target: any,
		type: any,
		key: string | symbol | undefined,
	}

	interface VueSetupContext {
		attrs: {[key: string]: unknown}
		slots: {[key: string]: unknown}
		emit: (event: string, ...args: unknown[]) => void
	}
}