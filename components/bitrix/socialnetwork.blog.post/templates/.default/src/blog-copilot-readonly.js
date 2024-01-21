import { Dom, Event, Loc, Runtime, Tag } from 'main.core';

type Params = {
	container: HTMLElement,
	blogText: string,
	enabledBySettings: string,
	copilotParams: {
		moduleId: string,
		contextId: string,
		category: string,
	},
};

export class BlogCopilotReadonly
{
	#params: Params;

	#layout: {
		button: HTMLElement,
	};

	#copilotLoaded: boolean;
	#copilotReadonly: any;
	#copilotShown: boolean;

	constructor(params: Params)
	{
		this.#params = params;
		this.#layout = {};

		if (this.#enabledBySettings())
		{
			this.#createCopilot();
		}

		Dom.append(this.#render(), this.#params.container);
	}

	#render()
	{
		this.#layout.button = Tag.render`
			<span
				class="feed-inform-item feed-inform-comments feed-copilot-readonly"
				data-id="blog-post-button-copilot"
			>
				<a>${Loc.getMessage('BLOG_POST_BUTTON_COPILOT')}</a>
			</span>
		`;

		Event.bind(this.#layout.button, 'mousedown', this.#onButtonMouseDown.bind(this));
		Event.bind(this.#layout.button, 'click', this.#onButtonClick.bind(this));

		return this.#layout.button;
	}

	async #createCopilot(): void
	{
		const { Copilot } = await Runtime.loadExtension('ai.copilot');

		this.#copilotReadonly = new Copilot({
			moduleId: this.#params.copilotParams.moduleId,
			contextId: this.#params.copilotParams.contextId,
			category: this.#params.copilotParams.category,
			readonly: true,
			autoHide: true,
		});

		this.#copilotReadonly.subscribe('finish-init', () => {
			this.#copilotLoaded = true;
		});

		this.#copilotReadonly.init();
	}

	#onButtonMouseDown()
	{
		if (!this.#enabledBySettings())
		{
			return;
		}

		this.#copilotShown = this.#copilotReadonly?.isShown();
	}

	#onButtonClick()
	{
		if (!this.#enabledBySettings())
		{
			BX.UI.InfoHelper.show('limit_copilot_off');

			return;
		}

		if (this.#copilotShown)
		{
			this.#hide();
		}
		else
		{
			this.#show();
		}
	}

	#show()
	{
		if (this.#copilotLoaded)
		{
			this.#copilotReadonly.setContext(this.#params.blogText);

			const buttonRect = this.#layout.button.getBoundingClientRect();
			this.#copilotReadonly.show({
				bindElement: {
					left: buttonRect.left + window.scrollX,
					top: buttonRect.bottom + window.scrollY + 10,
				},
			});
		}
	}

	#hide()
	{
		this.#copilotReadonly.hide();
	}

	#enabledBySettings()
	{
		return this.#params.enabledBySettings === 'Y';
	}
}