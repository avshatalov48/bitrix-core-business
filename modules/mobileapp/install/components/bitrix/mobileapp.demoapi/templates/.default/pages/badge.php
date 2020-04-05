<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
} ?>
<div class="range-badge">
	<span id="num" class="badge_num">0</span>
	<input onmousemove="" orient=vertical id="badge_range" type="range" name="points" min="0" max="60">
</div>
<script>
	var timeout = null;
	BXMobileApp.UI.Page.TopBar.updateButtons(
		{
			button: {
				type: "basket",
				badgeCode: "button"
			}
		}
	);
	BX.bind(BX("badge_range"), "change", function (e)
	{
		if (timeout != null)
		{
			clearTimeout(timeout)
		}
		var value = e.target.value;
		BX("num").innerHTML = value;
		timeout = setTimeout(function ()
		{
			timeout = null;
			BXMobileApp.UI.Badge.setButtonBadge("button", value);
		}, 200)
	})
</script>
