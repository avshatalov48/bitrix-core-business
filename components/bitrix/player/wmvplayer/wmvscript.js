function showWMVPlayer(contID, JSConfig)
{
	var ply = new jeroenwijering.Player(document.getElementById(contID), '/bitrix/components/bitrix/player/wmvplayer/wmvplayer.xaml',  JSConfig);
}
