import { Type, Dom } from 'main.core';

type Params = {
	toolbarId?: string
};

/**
 * @memberOf BX.Report.Integration
 */
export class Toolbar
{
	#toolbar: ?BX.UI.Toolbar;

	constructor(params: ?Params)
	{
		const objectParams = Type.isPlainObject(params) ? params : {};

		if (Type.isStringFilled(objectParams.toolbarId))
		{
			this.#toolbar = BX?.UI?.ToolbarManager?.get(objectParams.toolbarId);
		}
		else
		{
			this.#toolbar = BX?.UI?.ToolbarManager?.getDefaultToolbar();
		}
	}

	setTitle(title: string): void
	{
		if (!this.#toolbar)
		{
			return;
		}

		if (Type.isFunction(this.#toolbar.setTitle))
		{
			this.#toolbar.setTitle(title);
		}
		else
		{
			const pagetitle = this.#toolbar.titleContainer.querySelector('#pagetitle');
			if (pagetitle)
			{
				pagetitle.textContent = title;
			}
		}
	}

	getContainer(): ?HTMLElement
	{
		if (!this.#toolbar)
		{
			return null;
		}

		if (Type.isFunction(this.#toolbar.getContainer))
		{
			return this.#toolbar.getContainer();
		}

		return this.#toolbar.toolbarContainer;
	}

	getRightButtonsContainer(): ?HTMLElement
	{
		if (!this.#toolbar)
		{
			return null;
		}

		if (Type.isFunction(this.#toolbar.getRightButtonsContainer))
		{
			this.#toolbar.getRightButtonsContainer();
		}

		return this.#toolbar.rightButtons;
	}

	cleanContent(): void
	{
		if (!this.#toolbar)
		{
			return;
		}

		this.setTitle('');
		Dom.clean(this.getRightButtonsContainer());
	}

	createSkeleton(): HTMLElement
	{
		return Dom.create('img', {
			attrs: {
				src: '/bitrix/images/report/visualconstructor/preview/view-without-menu.svg',
				height: '1200px',
				width: '100%',
			},
			style: {
				'margin-top': `-${this.getContainer()?.offsetHeight}px`,
			},
		});
	}
}
