BX.namespace('BX.UI');

if (BX.Type.isUndefined(BX.UI.EntityPull))
{
	BX.UI.EntityPull = function(params = {})
	{
		const DELAY = 200;

		this.editor = params.editor ?? {};

		this.reloadEditor = BX.Runtime.debounce(this.reloadEditor, DELAY, this);
	};

	BX.UI.EntityPull.prototype = {
		onItemUpdated()
		{
			if (document.hidden && window.sessionStorage)
			{
				window.sessionStorage.setItem(this.editor.needReloadStorageKey, 'Y');

				return;
			}

			this.reloadEditor();
		},
		reloadEditor()
		{
			if (this.editor)
			{
				this.editor.reload();
			}
		},
	};
}
