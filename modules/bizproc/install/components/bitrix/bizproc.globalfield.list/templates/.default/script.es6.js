import { Reflection, Type, Loc } from 'main.core';
import { MessageBox, MessageBoxButtons } from 'ui.dialogs.messagebox';
import { Globals } from 'bizproc.globals';

const namespace = Reflection.namespace('BX.Bizproc.Component');

class GlobalFieldListComponent
{
	componentName: string;
	signedParameters: string;

	gridId: string;
	signedDocumentType: string;
	mode: string;

	slider: BX.SidePanel.Slider | null;
	sliderDict: BX.SidePanel.Dictionary | null;

	constructor(options)
	{
		if (Type.isPlainObject(options)) {
			this.componentName = options.componentName;
			this.signedParameters = options.signedParameters;
			this.gridId = options.gridId;
			this.signedDocumentType = options.signedDocumentType;
			this.mode = options.mode;

			this.slider = options.slider;
		}
	}

	init()
	{
		this.sliderDict = this.slider ? this.slider.getData() : null;
	}

	getGrid(): BX.Main.grid | null
	{
		if (this.gridId) {
			return BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.gridId);
		}

		return null;
	}

	reloadGrid()
	{
		const grid = this.getGrid();
		if (grid) {
			grid.reload();
		}
	};

	onCreateButtonClick()
	{
		const me = this;
		Globals.Manager.Instance.createGlobals(this.mode, this.signedDocumentType)
			.then((slider) => me.onAfterUpsert(slider))
		;
	}

	editGlobalFieldAction(id, mode)
	{
		const me = this;
		Globals.Manager.Instance.editGlobals(id, mode, this.signedDocumentType)
			.then((slider) => me.onAfterUpsert(slider))
		;
	}

	onAfterUpsert(slider)
	{
		const info = slider.getData().entries();
		const keys = Object.keys(info);
		if (keys.length <= 0)
		{
			return;
		}

		if (this.sliderDict)
		{
			const items = this.sliderDict.get('upsert') ?? {};
			items[keys[0]] = info[keys[0]];
			this.sliderDict.set('upsert', items);
		}

		this.reloadGrid();
	}

	getDeletePhrase(mode): string
	{
		if (mode === Globals.Manager.Instance.mode.variable) {
			return Loc.getMessage('BIZPROC_GLOBALFIELDS_LIST_CONFIRM_VARIABLE_DELETE');
		}
		else if (mode === Globals.Manager.Instance.mode.constant) {
			return Loc.getMessage('BIZPROC_GLOBALFIELDS_LIST_CONFIRM_CONSTANT_DELETE');
		}
		else {
			return '';
		}
	}

	getPluralDeletePhrase(mode): string
	{
		if (mode === Globals.Manager.Instance.mode.variable) {
			return Loc.getMessage('BIZPROC_GLOBALFIELDS_LIST_CONFIRM_VARIABLES_DELETE');
		}
		else if (mode === Globals.Manager.Instance.mode.constant) {
			return Loc.getMessage('BIZPROC_GLOBALFIELDS_LIST_CONFIRM_CONSTANTS_DELETE');
		}
		else {
			return '';
		}
	}

	deleteGlobalFieldAction(id, mode)
	{
		const me = this;
		const message = this.getDeletePhrase(mode);

		new MessageBox({
			message: message,
			okCaption: Loc.getMessage('BIZPROC_GLOBALFIELDS_LIST_BTN_DELETE'),
			onOk: () => {
				Globals.Manager.Instance.deleteGlobalsAction(id, mode, me.signedDocumentType).then((response) => {
					if (response.data && response.data.error)
					{
						MessageBox.alert(response.data.error);
					}
					else
					{
						if (me.sliderDict)
						{
							const items = me.sliderDict.get('delete') ?? [];
							items.push(id);
							me.sliderDict.set('delete', items);
						}

						me.reloadGrid();
					}
				});

				return true;
			},
			buttons: MessageBoxButtons.OK_CANCEL,
			popupOptions: {
				events: {
					onAfterShow: (event) => {
						const okBtn = event.getTarget().getButton('ok');

						if (okBtn) {
							okBtn.getContainer().focus();
						}
					},
				},
			},
		}).show();
	}

	deleteFieldsAction(mode)
	{
		const me = this;
		const message = this.getPluralDeletePhrase(mode);

		new MessageBox({
			message,
			okCaption: Loc.getMessage('BIZPROC_GLOBALFIELDS_LIST_BTN_DELETE'),
			onOk: () => {
				BX.ajax.runComponentAction(me.componentName, 'processGridDelete', {
					mode: 'class',
					data: {
						signedParameters: this.signedParameters,
						documentType: this.signedDocumentType,
						mode: mode,
						ids: this.getGrid().getRows().getSelectedIds()
					}
				}).then((response) => {
					if (response.data && response.data.error)
					{
						MessageBox.alert(response.data.error);
					}
					else
					{
						if (me.sliderDict)
						{
							const items = me.sliderDict.get('delete') ?? [];
							this.getGrid().getRows().getSelectedIds()
								.forEach((id)=> {
									items.push(id);
								});
							me.sliderDict.set('delete', items);
						}

						me.reloadGrid();
					}
				});

				return true;
			},
			buttons: MessageBoxButtons.OK_CANCEL,
			popupOptions: {
				events: {
					onAfterShow: (event) => {
						const okBtn = event.getTarget().getButton('ok');

						if (okBtn) {
							okBtn.getContainer().focus();
						}
					},
				},
			},
		}).show();
	}
}

namespace.GlobalFieldListComponent = GlobalFieldListComponent;