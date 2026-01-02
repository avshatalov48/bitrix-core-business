import {ajax, Type, Uri, Text} from 'main.core';
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
			if(Type.isStringFilled(url))
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

	createGlobals(
		mode: string,
		documentType: string,
		name: string,
		additionContext: {
			visibility?: string,
			availableTypes?: Array<string>,
		},
	): Promise<?BX.SidePanel.Slider>
	{
		const customName = Type.isStringFilled(name) ? name : '';

		let url = Uri.addParam(this.editUrl, { documentType, mode: this.mode[mode], name: customName });
		if (Type.isPlainObject(additionContext))
		{
			if (Type.isStringFilled(additionContext.visibility))
			{
				url = Uri.addParam(url, { visibility: additionContext.visibility });
			}

			if (Type.isArrayFilled(additionContext.availableTypes))
			{
				url = Uri.addParam(url, { availableTypes: additionContext.availableTypes });
			}
		}

		return this.constructor.openSlider(url, this.editSliderOptions);
	}

	editGlobals(id: string, mode: string, documentType: string): Promise<?BX.SidePanel.Slider>
	{
		id = Type.isStringFilled(id) ? Text.decode(id) : '';

		return this.constructor.openSlider(
			Uri.addParam(this.editUrl, {fieldId: id, mode: this.mode[mode], documentType}),
			this.editSliderOptions
		);
	}

	showGlobals(mode: string, documentType: string): Promise<?BX.SidePanel.Slider>
	{
		return this.constructor.openSlider(
			Uri.addParam(this.listUrl, {documentType, mode: this.mode[mode]}),
			this.listSliderOptions
		);
	}

	deleteGlobalsAction(id: string, mode: string, documentType: string): Promise
	{
		return ajax.runAction(
			'bizproc.globalfield.delete',
			{
				analyticsLabel: 'bizprocGlobalFieldDelete',
				data: {
					fieldId: id,
					mode,
					documentType,
				}
			}
		);
	}

	upsertGlobalsAction(id: string, property: {}, documentType: string, mode: string): Promise
	{
		return ajax.runAction(
			'bizproc.globalfield.upsert',
			{
				analyticsLabel: 'bizprocGlobalFieldUpsert',
				data: {
					fieldId: id,
					property,
					documentType,
					mode
				}
			}
		);
	}
}
