import { Reflection } from 'main.core';
import { ProductSetFieldFactory } from 'catalog.entity-editor.field.productset';
import { SectionSetFieldFactory } from 'catalog.entity-editor.field.sectionset';
import { ContractorFieldFactory } from 'catalog.entity-editor.field.contractor';
import { ModelFactory } from 'catalog.agent-contract';
import { ControllersFactory } from 'catalog.agent-contract';

const namespace = Reflection.namespace('BX.Catalog.Agent.ContractorComponent');

class Detail
{
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
}

namespace.Detail = Detail;
