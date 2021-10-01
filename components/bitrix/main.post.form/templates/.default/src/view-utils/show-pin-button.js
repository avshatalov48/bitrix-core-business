import {Loc} from 'main.core';

export default function showPinButton(htmlEditor, editorParams)
{
	if (!document.querySelector('#lhe_button_editor_' + htmlEditor.formID))
	{
		return;
	}
	editorParams.pinEditorPanel = editorParams.pinEditorPanel === true;

	var pinId = 'toolbar_pin';
	const but = function (editor, wrap)
	{
		// Call parrent constructor
		but.superclass.constructor.apply(this, arguments);
		this.id = pinId;
		this.title = Loc.getMessage('MPF_PIN_EDITOR_PANNEL');
		this.className += ' ' + (editorParams.pinEditorPanel ? 'bxhtmled-button-toolbar-pined' : 'bxhtmled-button-toolbar-pin');
		this.Create();
		if (wrap)
			wrap.appendChild(this.GetCont());
	};

	BX.extend(but, window.BXHtmlEditor.Button);
	but.prototype.OnClick = function ()
	{
		BX.removeClass(this.pCont, 'bxhtmled-button-toolbar-pined');
		BX.removeClass(this.pCont, 'bxhtmled-button-toolbar-pin');
		if (editorParams.pinEditorPanel)
		{
			editorParams.pinEditorPanel = false;
			BX.addClass(this.pCont, 'bxhtmled-button-toolbar-pin');
		}
		else
		{
			editorParams.pinEditorPanel = true;
			BX.addClass(this.pCont, 'bxhtmled-button-toolbar-pined');
		}
		BX.userOptions.save('main.post.form', 'postEdit', 'pinEditorPanel', editorParams.pinEditorPanel ? "Y" : "N");
	};

	window.BXHtmlEditor.Controls[pinId] = but;
	BX.addCustomEvent(htmlEditor, "GetControlsMap", function (controlsMap)
	{
		controlsMap.push({
			id: pinId, compact: true, hidden: false, sort: 500, checkWidth: true, offsetWidth: 32, wrap: 'right'
		});
	});
}