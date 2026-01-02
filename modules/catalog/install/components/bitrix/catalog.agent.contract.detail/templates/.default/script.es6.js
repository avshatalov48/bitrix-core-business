import { Dom, Reflection, Tag } from 'main.core';
import { ProductSetFieldFactory } from 'catalog.entity-editor.field.productset';
import { SectionSetFieldFactory } from 'catalog.entity-editor.field.sectionset';
import { ContractorFieldFactory } from 'catalog.entity-editor.field.contractor';
import { ModelFactory } from 'catalog.agent-contract';
import { ControllersFactory } from 'catalog.agent-contract';

const namespace = Reflection.namespace('BX.Catalog.Agent.ContractorComponent');

class Detail
{
	constructor()
	{
		this.#appendEditButton();
	}

	static registerFieldFactory(entityEditorControlFactory)
	{
		new ProductSetFieldFactory(entityEditorControlFactory);
		new SectionSetFieldFactory(entityEditorControlFactory);
		new ContractorFieldFactory(entityEditorControlFactory);
	}

	static registerControllerFactory(entityEditorControllerFactory)
	{
		new ControllersFactory(entityEditorControllerFactory);
	}

	static registerModelFactory()
	{
		new ModelFactory();
	}

	#appendEditButton(): void
	{
		const toolbar = BX.UI?.ToolbarManager?.getDefaultToolbar();
		const titleContainer = toolbar?.titleContainer.querySelector('.ui-toolbar-title-item-box');

		if (!titleContainer)
		{
			return;
		}

		const editButton = Tag.render`
			<span id="pagetitle_btn_wrapper" class="pagetitile-button-container">
				<span id="pagetitle_edit" class="pagetitle-edit-button"></span>
			</span>
		`;

		Dom.append(editButton, titleContainer);
	}
}

namespace.Detail = Detail;
