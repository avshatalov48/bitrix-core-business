import { Type, Uri, Event } from 'main.core';
import { Loader } from 'main.loader';
import { Actions } from '../actions';
import { ProviderRequestFactory } from '../provider-request-factory';
import { ProvidersType } from '../types/providers-type';
import { BaseProvider } from './base-provider';

export class SliderProvider extends BaseProvider
{
	constructor(params = {})
	{
		super();
		this.frameUrlTemplate = params.frameUrlTemplate || '';
		this.frameUrl = Type.isStringFilled(params.frameUrl) ? params.frameUrl : '';
		this.width = Type.isNumber(params.width) ? params.width : 700;
		this.constructorParams = params;

		if (params.dataSource && params.dataSource instanceof Promise)
		{
			this.dataSource = params.dataSource;
		}
		else
		{
			const providerRequestFactoryConfiguration = {
				type: ProvidersType.SLIDER,
				code: null,
				featureId: params.featureId,
			};
			this.dataSource = (new ProviderRequestFactory(providerRequestFactoryConfiguration)).getRequest();
		}
	}

	__showExternal(code, option): void
	{
		let width = 700;
		const sliderId = `${this.getId()}:${code}`;
		const frame = this.#createFrameNode();
		if (!!option && !!option.width && option.width > 0)
		{
			width = option.width;
		}

		const contentCallback = (slider) => {
			return new Promise((resolve, reject) => {
				const providerRequestFactoryConfiguration = {
					type: ProvidersType.SLIDER,
				};
				(new ProviderRequestFactory(providerRequestFactoryConfiguration)).getRequest()
					.then((response) => {
						frame.src = this.#buildUrl(code);

						return this.#createContainerNode(this.getLoader(), frame);
					})
					.then((content) => resolve(content));
			});
		};
		this.#openSlider({
			id: sliderId,
			contentCallback: contentCallback.bind(this),
			width: width,
			events: {
				onLoad: () => this.showFrame(frame),
			},
		});
	}

	show(code, params): void
	{
		if (this.isOpen())
		{
			return;
		}

		if (!Type.isPlainObject(params))
		{
			params = {};
		}

		if (!code && !params.featureId && !this.constructorParams.featureId && !this.constructorParams.dataSource)
		{
			return;
		}

		this.contentCallback = (slider) => {
			return new Promise((resolve, reject) => {
				if (this.hasOpen)
				{
					resolve(this.getContent());
				}
				else
				{
					this.dataSource
						.then((response) => {
							const { data } = response;
							if (data.code)
							{
								code = data.code;
							}

							this.frameUrlTemplate = data.frameUrlTemplate;
							this.frameUrl = this.#buildUrl(code, params, data);

							if (this.getFrame().src !== this.frameUrl)
							{
								this.getFrame().src = this.frameUrl;
							}
							this.bindEvent(data.availableDomainList);
							this.hasOpen = true;

							return resolve(this.getContent());
						})
				}
			});
		};

		this.#openSlider({
			id: this.getId(),
			contentCallback: this.contentCallback,
			width: this.width,
			events: {
				onLoad: () => this.showFrame(),
				onClose: () => {
					Event.unbindAll(window, 'message');
					if (this.frameNode)
					{
						this.frameNode.contentWindow?.postMessage({ action: 'onCloseWidget' }, '*');
					}
				},
			},
		});
	}

	bindEvent(availableDomainList): void
	{
		BX.bind(top.window, 'message', BX.proxy(function(event)
		{
			if (!event.origin || (!!event.origin && !availableDomainList.includes(event.origin)))
			{
				return;
			}

			if (!event.data || !Type.isObject(event.data))
			{
				return;
			}

			const action = Actions[event.data.action];

			if (Type.isFunction(action))
			{
				action(event.data);
			}
		}, this));
	}

	#openSlider(params): void
	{
		BX.SidePanel.Instance.open(
			params.id,
			{
				contentCallback: params.contentCallback,
				width: params.width,
				loader: 'default-loader',
				cacheable: false,
				customRightBoundary: 0,
				events: params.events,
			},
		);
	}

	#buildUrl(code, params = {}, data = null): string
	{
		let url = this.frameUrlTemplate.replace(/code/, code);

		if (params.featureId && Type.isArray(data.trialableFeatureList))
		{
			url = Uri.addParam(url, {
				featureId: params.featureId,
				trialableFeatureList: data.trialableFeatureList.join(','),
			});
		}

		return url;
	}

	close(): void
	{
		const slider = this.getSlider();
		if (slider && slider.isOpen())
		{
			slider.close();
		}
	}

	getContent(): HTMLElement
	{
		if (this.content)
		{
			return this.content;
		}
		this.content = this.#createContainerNode(this.getLoader(), this.getFrame());

		return this.content;
	}

	#createContainerNode(loader, frame): HTMLElement
	{
		return BX.create('div', {
			attrs: {
				className: 'info-helper-container',
				id: 'info-helper-container',
			},
			children: [
				loader,
				frame,
			],
		});
	}

	getId(): string
	{
		return 'ui:info_helper';
	}

	getFrame(): HTMLElement
	{
		if (this.frameNode)
		{
			return this.frameNode;
		}

		this.frameNode = this.#createFrameNode();

		return this.frameNode;
	}

	hasFrameNode(): boolean
	{
		return Type.isElementNode(this.frameNode);
	}

	#createFrameNode(): HTMLElement
	{
		return BX.create('iframe', {
			attrs: {
				className: 'info-helper-panel-iframe',
				src: 'about:blank',
			},
		});
	}

	showFrame(frame): void
	{
		if (!frame)
		{
			frame = this.getFrame();
		}

		setTimeout(() => {
			frame.classList.add('info-helper-panel-iframe-show');
		}, 600);
	}

	getSlider(): BX.SidePanel.Slider
	{
		return BX.SidePanel.Instance.getSlider(this.getId());
	}

	isOpen(): boolean
	{
		return this.getSlider() && this.getSlider().isOpen();
	}

	getLoader(): Loader
	{
		if (this.popupLoader)
		{
			return this.popupLoader;
		}

		const loader = new Loader({
			target: BX('info-helper-container'),
			size: 100,
		});

		loader.show();
		this.popupLoader = loader.data.container;

		return this.popupLoader;
	}
}
