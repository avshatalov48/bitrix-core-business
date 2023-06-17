
type OpenDialogOptions = {
	onSelect: () => {},
};

export class Disk
{
	static openDialog({onSelect}: OpenDialogOptions)
	{
		const urlSelect = '/bitrix/tools/disk/uf.php?action=selectFile&dialog2=Y&SITE_ID=' + BX.message('SITE_ID');
		const dialogName = 'LandingDiskFile';

		BX.ajax.get(urlSelect, 'multiselect=N&dialogName=' + dialogName,
			BX.delegate(function() {
				setTimeout(BX.delegate(function() {
					BX.DiskFileDialog.obElementBindPopup[dialogName].overlay = {
						backgroundColor: '#cdcdcd',
						opacity: '.1'
					};
					BX.DiskFileDialog.obCallback[dialogName] = {
						saveButton: function(tab, path, selected)
						{
							const selectedItem = selected[Object.keys(selected)[0]];
							if (!selectedItem)
							{
								return;
							}

							let fileId = selectedItem.id;
							if (fileId[0] === 'n')
							{
								fileId = fileId.substring(1);
							}

							if (onSelect)
							{
								onSelect(fileId);
							}

						}.bind(this)
					};
					BX.DiskFileDialog.openDialog(dialogName);
				}, this), 10);
			}, this)
		);
	}
}
