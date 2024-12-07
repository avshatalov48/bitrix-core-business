import { Runtime, Type, Uri } from 'main.core';
import 'sidepanel';

type OpenSliderParams = {
	iBlockTypeId: string,
	iBlockId: number,
	fillConstantsUrl?: string,
	onClose?: Function,
	analyticsSection?: string,
	analyticsP1?: string,
};

export class CreationGuide
{
	static open(params: OpenSliderParams)
	{
		if (!Type.isPlainObject(params) || !Type.isStringFilled(params.iBlockTypeId) || !Type.isInteger(params.iBlockId))
		{
			throw new TypeError('invalid params');
		}

		const url = Uri.addParam(
			'/bitrix/components/bitrix/lists.element.creation_guide/',
			{
				iBlockTypeId: encodeURIComponent(params.iBlockTypeId),
				iBlockId: encodeURIComponent(params.iBlockId),
				fillConstantsUrl: encodeURIComponent(this.#getFillConstantsUrl(params)),
				analyticsSection: params.analyticsSection || '',
			},
		);

		BX.SidePanel.Instance.open(
			url,
			{
				width: 900,
				cacheable: false,
				allowChangeHistory: false,
				loader: '/bitrix/js/lists/element/creation-guide/images/skeleton.svg',
				events: {
					onCloseComplete: () => {
						if (Type.isFunction(params.onClose))
						{
							params.onClose();
						}
					},
				},
			},
		);

		this.#sendAnalytics(params);
	}

	static #getFillConstantsUrl(params: OpenSliderParams): string
	{
		if (Type.isStringFilled(params.fillConstantsUrl))
		{
			return params.fillConstantsUrl;
		}

		return Uri.addParam('/bizproc/userprocesses/', {
			iBlockId: params.iBlockId,
		});
	}

	static #sendAnalytics(params: OpenSliderParams): void
	{
		Runtime.loadExtension('ui.analytics')
			.then(({ sendData }) => {
				sendData({
					tool: 'automation',
					category: 'bizproc_operations',
					event: 'process_start_attempt',
					c_section: params.analyticsSection || 'bizproc',
					p1: params.analyticsP1,
				});
			})
			.catch(() => {})
		;
	}
}
