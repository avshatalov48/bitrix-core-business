</div>
<script>
	var pullParams = {
			enable:true,
			pulltext:"<?=GetMessage("PULL_TEXT")?>",
			downtext:"<?=GetMessage("DOWN_TEXT")?>",
			loadtext:"<?=GetMessage("LOAD_TEXT")?>"
		};
	if(app.enableInVersion(2))
		pullParams.action = "RELOAD";
	else
		pullParams.callback = function(){document.location.reload();};
	app.pullDown(pullParams);
</script>
