function ComponentPropsEditPlaylistDialog(arParams)
{
	var
		arData = arParams.data.split('||'),
		oBut = arParams.oCont.appendChild(BX.create("INPUT", {props: {'type': 'button', value: arData[0] || ''}}));

	oBut.onclick = function()
	{
		var arElements = arParams.getElements();
		if (!arElements || !arElements.PATH)
			return;

		var path = arElements.PATH.value;

		if (!path || path.lastIndexOf('.') == -1)
			return alert(arData[1] || 'Incorrect path to playlist');

		window.oPlaylistDialog = new BX.CDialog({
			content_url: '/bitrix/components/bitrix/player/player_playlist_edit.php?lang=ru&site=ru&path=' + BX.util.urlencode(path) + '&target=editor',
			width: 800,
			height: 350,
			min_height: 300,
			min_width: 800,
			resizable: true
		});
		BX.addClass(window.oPlaylistDialog.PARTS.CONTENT, "bx-playlist-edit");
		window.oPlaylistDialog.Show();
	}
}