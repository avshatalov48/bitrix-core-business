import { Event, ajax, Dom } from 'main.core';

export type FeedbackFormOptions = {
	id: string;
	forms: FeedbackFormOptionsForm[];
	presets?: Object;
	title?: string,
	defaultForm?: FeedbackFormOptionsForm;
	portalUri?: string;
}

type FeedbackFormOptionsForm = {
	zones: string[];
	id: number;
	lang: string;
	sec: string;
}

export class Form
{
	static #list = [];
	static #loadedList: {[string]: Form} = {};
	static #opened = false;

	static getList(): Array
	{
		return Form.#list;
	}

	static getById(id: string | number): Form | null
	{
		return Form.#list.find((form) => {
			return form.id === id;
		}) || null;
	}

	static open(formOptions: FeedbackFormOptions): void
	{
		if (Form.#opened)
		{
			return;
		}

		const formId = formOptions.id;
		const loadedForm = Form.#loadedList[formId];

		if (loadedForm)
		{
			loadedForm.openPanel();

			return;
		}

		const form = new Form({ map: formOptions });

		Form.#loadedList[formOptions.id] = form;

		form.openPanel();
	}

	map: FeedbackFormOptions;
	id: string;
	portal: string;
	presets: Object;
	form: Object;
	forms: FeedbackFormOptionsForm[];
	defaultForm: Object;
	title: ?string;
	button: ?HTMLElement;
	portalUri: ?string;

	cached: boolean;

	/**
	 * @deprecated use static method open
	 * @param formOptions
	 */
	constructor(formOptions)
	{
		this.init(formOptions);
		Form.#list.push(this);
	}

	init(formOptions): void
	{
		this.cached = false;

		if (formOptions.map !== undefined)
		{
			this.map = formOptions.map;

			return;
		}

		this.id = formOptions.id;
		this.portal = formOptions.portal;
		this.presets = formOptions.presets || {};
		this.form = formOptions.form || {};
		this.title = formOptions.title || '';

		if (formOptions.button)
		{
			this.button = BX(formOptions.button);
			Event.bind(this.button, 'click', this.openPanel.bind(this));
		}
	}

	appendPresets(presets): void
	{
		Object.entries(presets).forEach(([key, value]) => {
			this.presets[key] = value;
		});
	}

	openPanel(): void
	{
		Form.#opened = true;

		BX.SidePanel.Instance.open(`ui:feedback-form-${this.id}`, {
			cacheable: false,
			contentCallback: () => {
				return Promise.resolve();
			},
			animationDuration: 200,
			events: {
				onLoad: this.checkSidePanelLoad.bind(this),
				onBeforeCloseComplete: this.checkSidePanelClosed.bind(this),
			},
			width: 600,
		});
	}

	checkSidePanelClosed()
	{
		Form.#opened = false;
	}

	checkSidePanelLoad(event): void
	{
		if (this.map && this.cached === false)
		{
			ajax.runAction('ui.feedback.loadData', {
				json: {
					title: this.map.title || null,
					id: this.map.id || null,
					presets: this.map.presets || null,
					portalUri: this.map.portalUri || null,
					forms: this.map.forms || null,
					defaultForm: this.map.defaultForm || null,
				},
			}).then((response) => {
				const params = response.data.params;
				this.id = params.id;
				this.title = params.title;
				this.form = params.form;
				this.presets = params.presets;
				this.portal = params.portal;
				this.cached = true;

				this.onSidePanelLoad(event);
			}).catch((response) => {
				console.error(response);
			});

			return;
		}

		this.onSidePanelLoad(event);
	}

	onSidePanelLoad(event)
	{
		const slider = event.getSlider();

		if (!slider)
		{
			return;
		}

		this.#appendFormToSlider(slider);

		setTimeout(() => {
			slider.showLoader();
		}, 0);

		this.loadForm(this.checkLoader.bind(this, slider));
	}

	#appendFormToSlider(slider): void
	{
		if (!slider)
		{
			return;
		}

		this.formNode = Dom.create('div');
		const titleNode = Dom.create('div', {
			style: {
				marginBottom: '25px',
				font: '26px/26px var(--ui-font-family-primary, var(--ui-font-family-helvetica))',
				color: 'var(--ui-color-text-primary)',
			},
			text: this.title,
		});

		const containerNode = Dom.create('div', {
			style: {
				padding: '20px',
				overflowY: 'auto',
			},
			children: [
				titleNode,
				this.formNode,
			],
		});

		Dom.append(containerNode, slider.layout.content);
	}

	checkLoader(slider): void
	{
		setTimeout(() => {
			slider.closeLoader();
		}, 100);
	}

	loadForm(callback)
	{
		const form = this.form;
		if (!form || !form.id || !form.lang || !form.sec)
		{
			return;
		}

		if (form.presets)
		{
			this.appendPresets(form.presets);
		}

		const objectId = `b24form${this.id}`;

		this.#appendFormScript(`${this.portal}/bitrix/js/crm/form_loader.js`, objectId);

		Event.bind(top, 'b24:form:init', this.#handleB24FormInit);

		top[objectId]({
			id: form.id,
			lang: form.lang,
			sec: form.sec,
			type: 'inline',
			node: this.formNode,
			presets: this.presets,
			handlers: {
				load: callback,
			},
		});
	}

	#appendFormScript(u: string, b: string)
	{
		top.Bitrix24FormObject = b;

		top[b] = top[b] || function() {
			// eslint-disable-next-line prefer-rest-params
			arguments[0].ref = u;
			// eslint-disable-next-line prefer-rest-params
			(top[b].forms = top[b].forms || []).push(arguments[0]);
		};

		if (top[b].forms)
		{
			return;
		}
		const scriptElement = top.document.createElement('script');
		const r = Date.now();
		scriptElement.async = 1;
		scriptElement.src = `${u}?${r}`;
		const h = top.document.getElementsByTagName('script')[0];
		Dom.insertBefore(scriptElement, h);
	}

	#handleB24FormInit(event)
	{
		const eventForm = event.detail.object;
		eventForm.design.setFont('var(--ui-font-family-primary),var(--ui-font-family-helvetica)');
	}
}
