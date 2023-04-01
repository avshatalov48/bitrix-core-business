import {ajax, Text, Type, Tag, Uri, Dom, Loc} from 'main.core';
import {MessageBox} from 'ui.dialogs.messagebox';
import {UI} from 'ui.notification';
import {Popup} from 'main.popup';
import {Button} from 'ui.buttons';
import 'sidepanel';
import 'bp_field_type';

let instance = null;

export default class Manager
{
	scriptEditUrl = '/bitrix/components/bitrix/bizproc.script.edit/';
	scriptListUrl = '/bitrix/components/bitrix/bizproc.script.list/';
	scriptQueueListUrl = '/bitrix/components/bitrix/bizproc.script.queue.list/';
	scriptQueueDocumentListUrl = '/bitrix/components/bitrix/bizproc.script.queue.document.list/';

	static get Instance(): Manager
	{
		if(instance === null)
		{
			instance = new Manager();
		}

		return instance;
	}

	startScript(scriptId, placement)
	{
		const documentIds = this.getDocumentIds(...placement.split(':'));

		if (!documentIds.length)
		{
			MessageBox.alert(Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_NOTHING_SELECTED'));
			return;
		}

		const startCallback = () =>
		{
			this.#startScriptInternal(scriptId, documentIds);
			return true;
		};

		if (documentIds.length > 1)
		{
			MessageBox.confirm(
				Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_TEXT_START')
					.replace('#CNT#', documentIds.length),
				startCallback,
				Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_BUTTON_START')
			);
		}
		else
		{
			startCallback();
		}
	}

	#startScriptInternal(scriptId, documentIds, parameters = {}, popup)
	{
		let data = {scriptId, documentIds, parameters};

		if (parameters instanceof FormData)
		{
			data = parameters;
			data.set('scriptId', scriptId);
			documentIds.forEach(id => data.append('documentIds[]', id));
		}

		ajax.runAction('bizproc.script.start', {
			analyticsLabel: 'bizprocScriptStart',
			data
		})
			.then((response) =>
			{
				if (response.data.error)
				{
					MessageBox.alert(response.data.error);
				}

				if (response.data.status === 'FILL_PARAMETERS')
				{
					this.#showFillParametersPopup(scriptId, documentIds, response.data);
				}
				else if (response.data.status === 'INVALID_PARAMETERS')
				{
					//error has already shown by MessageBox.alert
					//no actions to do
				}
				else if (response.data.status === 'QUEUED')
				{
					if (popup)
					{
						popup.close();
					}

					UI.Notification.Center.notify({
						content: Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_QUEUED')
					});

					this.#keepAliveQueue(response.data.queueId);
				}
			})
			.catch(response => MessageBox.alert(response.errors.pop().message))
		;
	}

	#keepAliveQueue(queueId, delay = 500)
	{
		setTimeout(() => {
			ajax.runAction('bizproc.script.execQueue', {
				data: {queueId}
			}).then((response) =>
			{
				if (!response.data.finished)
				{
					this.#keepAliveQueue(queueId, delay);
				}
				else
				{
					UI.Notification.Center.notify({
						content: Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_FINISHED')
					});
				}
			});
		}, delay);
	}

	#showFillParametersPopup(scriptId, documentIds, {parameters, documentType, scriptName})
	{
		const form = this.renderParametersPopupContent(parameters, documentType);
		const popup = new Popup(null, null, {
			events: {
				onPopupClose: () => {
					popup.destroy();
				}
			},
			titleBar: scriptName || Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_PARAMS_POPUP_TITLE'),
			content: form,
			width: 595,
			contentNoPaddings: true,
			buttons: [
				new Button({
					text : Loc.getMessage('BIZPROC_SCRIPT_MANAGER_START_BUTTON_SEND_PARAMS'),
					color: Button.Color.SUCCESS,
					onclick: () => {
						this.#startScriptInternal(scriptId, documentIds, new FormData(form), popup);
					}
				}),
				new BX.UI.Button({
					text : Loc.getMessage('UI_MESSAGE_BOX_CANCEL_CAPTION'),
					color: BX.UI.Button.Color.LINK,
					onclick: () => {
						popup.close();
					}
				})
			]
		});
		popup.show();
	}

	renderParametersPopupContent(parameters: [], documentType)
	{
		const form = Dom.create('form', {attrs: {className: 'bp-script-start-form'}});

		parameters.forEach((param) => {
			const field = BX.Bizproc.FieldType.renderControl(documentType, param, param.Id, param.Default || '');
			const description = param.Description
				? Dom.create('span', {
					text: param.Description,
					attrs: {className: 'bp-script-start-form-row-desc'}
				})
				: ''
			;

			Dom.append(
				Tag.render`
					<div class="bp-script-start-form-row">
						<span class="bp-script-start-form-row-title">${Text.encode(param.Name)}</span>
						${description}
						<div class="bp-script-start-form-row-field">${field}</div>
					</div>
				`,
				form
			);
		});

		return form;
	}

	getDocumentIds(section, entity): []
	{
		let ids = [];
		if (section === 'crm_switcher')
		{
			const grid = this.#findGridInstance(entity);
			if (grid)
			{
				ids = grid.getRows().getSelectedIds();
			}
			else if (BX.CRM && BX.CRM.Kanban && BX.CRM.Kanban.Grid && BX.CRM.Kanban.Grid.Instance)
			{
				ids = BX.CRM.Kanban.Grid.Instance.getCheckedId();
			}
		}
		else if (section === 'crm_detail')
		{
			ids = [BX.Crm.EntityEditor.getDefault().getEntityId()];
		}

		//Prepare crm document ids
		if (Type.isArrayFilled(ids))
		{
			ids = ids.map((id) => `${entity.toUpperCase()}_${id}`);
		}

		return ids;
	}

	#findGridInstance(entity: string)
	{
		if (!BX.Main.gridManager)
		{
			return null;
		}

		const gridId = `CRM_${entity.toUpperCase()}_LIST`;
		const grid = BX.Main.gridManager.data.find((current) => {
			return current.id.indexOf(gridId) === 0 || current.id.indexOf('crm-type-item-list') === 0;
		})

		return grid ? grid.instance : null;
	}

	createScript(documentType: string, placement: string): Promise
	{
		return Manager.openSlider(
			Uri.addParam(this.scriptEditUrl, {documentType, placement}),
			{
				width: 930,
				cacheable: false,
				allowChangeHistory: false
			}
		);
	}

	showScriptList(documentType: string, placement: string)
	{

		Manager.openSlider(
			Uri.addParam(this.scriptListUrl, {documentType, placement}),
			{cacheable: false, allowChangeHistory: false}
		).then((slider) =>
		{
			if(slider.isLoaded())
			{
				//do smth
			}
		});
	}

	showScriptQueueList(scriptId: number)
	{
		Manager.openSlider(
			Uri.addParam(this.scriptQueueListUrl, {scriptId}),
			{cacheable: false, allowChangeHistory: false}
		);
	}

	showScriptQueueDocumentList(queueId: number)
	{
		Manager.openSlider(
			Uri.addParam(this.scriptQueueDocumentListUrl, {queueId}),
			{cacheable: false, allowChangeHistory: false}
		);
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

	editScript(scriptId: number, placement: string): Promise
	{
		return Manager.openSlider(
			Uri.addParam(this.scriptEditUrl, {scriptId, placement}),
			{width: 930, cacheable: false, allowChangeHistory: false}
		);
	}

	deleteScript(scriptId: number): Promise
	{
		return ajax.runAction('bizproc.script.delete', {
			analyticsLabel: 'bizprocScriptDelete',
			data: {scriptId}
		});
	}

	activateScript(scriptId: number): Promise
	{
		return ajax.runAction('bizproc.script.activate', {
			analyticsLabel: 'bizprocScriptActivate',
			data: {scriptId}
		});
	}

	deactivateScript(scriptId: number): Promise
	{
		return ajax.runAction('bizproc.script.deactivate', {
			analyticsLabel: 'bizprocScriptDeactivate',
			data: {scriptId}
		});
	}

	terminateScriptQueue(queueId: number)
	{
		ajax.runAction('bizproc.script.terminateQueue', {
			analyticsLabel: 'bizprocScriptTerminateQueue',
			data: {queueId}
		}).then((response) =>
		{
			if (response.data.error)
			{
				MessageBox.alert(response.data.error);
			}
		});
	}

	deleteScriptQueue(queueId: number)
	{
		ajax.runAction('bizproc.script.deleteQueue', {
			analyticsLabel: 'bizprocScriptDeleteQueue',
			data: {queueId}
		}).then((response) =>
		{
			if (response.data.error)
			{
				MessageBox.alert(response.data.error);
			}
		});
	}
}