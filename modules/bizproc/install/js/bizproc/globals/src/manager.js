import {ajax, Type, Uri} from 'main.core';
import 'sidepanel';

let instance = null;

export default class Manager
{
	mode = {
		variable: 'variable',
		constant: 'constant',
	}

	listUrl = '/bitrix/components/bitrix/bizproc.globalfield.list/';
	editUrl = '/bitrix/components/bitrix/bizproc.globalfield.edit/';

	listSliderOptions = {
		width: 1150,
		cacheable: false,
		allowChangeHistory: false,
	};

	editSliderOptions = {
		width: 500,
		cacheable: false,
		allowChangeHistory: false
	};

	static get Instance(): Manager
	{
		if(instance === null)
		{
			instance = new Manager();
		}

		return instance;
	}

	static openSlider(url, options): Promise<?BX.SidePanel.Slider>
	{
		if(!Type.isPlainObject(options))
		{
			options = {};
		}
		options = {...{cacheable: false, allowChangeHistory: true, events: {}}, ...options};
		return new Promise((resolve) =>
		{
			if(Type.isString(url) && url.length > 1)
			{
				options.events.onClose = function(event)
				{
					resolve(event.getSlider());
				};
				BX.SidePanel.Instance.open(url, options);
			}
			else
			{
				resolve();
			}
		});
	}

	createGlobals(mode: string, documentType: string, name: string, additionContext: object)
	{
		let customName = name ?? '';
		let visibility = null;
		let availableTypes = [];
		if (additionContext !== undefined)
		{
			visibility = additionContext['visibility'] ?? null;
			availableTypes = additionContext['availableTypes'] ?? [];
		}
		return Manager.openSlider(
			Uri.addParam(this.editUrl, {documentType, mode: this.mode[mode], name: customName, visibility, availableTypes}),
			this.editSliderOptions
		);
	}

	editGlobals(id: string, mode: string, documentType: string)
	{
		id = BX.util.htmlspecialcharsback(id);
		return Manager.openSlider(
			Uri.addParam(this.editUrl, {fieldId: id, mode, documentType,}),
			this.editSliderOptions
		);
	}

	showGlobals(mode: string, documentType: string)
	{
		return Manager.openSlider(
			Uri.addParam(this.listUrl, {documentType, mode}),
			this.listSliderOptions
		);
	}

	deleteGlobalsAction(id: string, mode: string, documentType: string)
	{
		return ajax.runAction('bizproc.globalfield.delete', {
			analyticsLabel: 'bizprocGlobalFieldDelete',
			data: {
				fieldId: id,
				mode,
				documentType,
			}
		});
	}

	upsertGlobalsAction(id: string, property: object, documentType: string, mode: string)
	{
		return ajax.runAction('bizproc.globalfield.upsert', {
			analyticsLabel: 'bizprocGlobalFieldUpsert',
			data: {
				fieldId: id,
				property,
				documentType,
				mode
			}
		});
	}
}