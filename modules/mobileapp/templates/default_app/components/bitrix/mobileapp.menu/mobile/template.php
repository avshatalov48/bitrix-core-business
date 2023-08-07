<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$APPLICATION->SetPageProperty("BodyClass", "menu-page");
CJSCore::Init(array("fx"))
?>
<div class="menu-wrap">
<div  class="menu-items" id="menu-items">

	<?

	$htmlMenu = "";

	foreach ($arResult["MENU"] as $arMenuSection)
	{
		if (!isset($arMenuSection['type']) && $arMenuSection['type'] != "section")
		{
			continue;
		}

		$htmlMenu .= '<div class="menu-separator">' . (isset($arMenuSection['text']) ? $arMenuSection['text'] : '') . '</div>';

		if (!isset($arMenuSection['items']) || !is_array($arMenuSection['items']))
		{
			continue;
		}

		$htmlMenu .= '<div class="menu-section menu-section-groups">';

		foreach ($arMenuSection['items'] as $arMenuItem)
		{
			$htmlMenu .= '<div class="menu-item';

			if (isset($arMenuItem["class"]))
			{
				$htmlMenu .= ' ' . $arMenuItem["class"];
			}

			$htmlMenu .= '"';

			foreach ($arMenuItem as $attrName => $attrVal)
			{
				if ($attrName == 'text' || $attrName == 'type' || $attrName == 'class')
				{
					continue;
				}

				$htmlMenu .= ' ' . $attrName . '="' . $attrVal . '"';
			}

			$htmlMenu .= '>';

			if (isset($arMenuItem['text']))
			{
				$htmlMenu .= $arMenuItem['text'];
			}

			$htmlMenu .= '</div>';
		}

		$htmlMenu .= '</div>';
	}

	echo $htmlMenu;
	?>
</div>

	<div id="mobile_menu_preview_wrap">
		<div class="navigation-panel"></div>
		<iframe id="mobile_menu_preview"></iframe>
		<div id="preview_loading">
			<div class="loading-label">Loading...</div>
		</div>
	</div>

</div>

<script type="text/javascript">

	BXMSlider.setStateEnabled(BXMSlider.state.LEFT, true);
	document.addEventListener("DOMContentLoaded", function ()
	{
		Menu.init(null);
	}, false);

	Menu = {
		currentItem: null,
		init: function (currentItem)
		{
			this.isDesktop = false;

			var userAgent  = navigator.userAgent;
			if(userAgent.indexOf("Android") < 0 && userAgent.indexOf("iPhone") < 0 && userAgent.indexOf("iPad") < 0)
			{
				this.isDesktop = true;
			}
			else
			{
				BX("menu-items").style.float = "none";
			}
			this.currentItem = currentItem;
			var items = document.getElementById("menu-items");
			var that = this;


			items.addEventListener("click", function (event)
			{
				that.onItemClick(event);

			}, false);
		},

		onItemClick: function (event)
		{
			var target = event.target;
			if (target && target.nodeType && target.nodeType == 1 && BX.hasClass(target, "menu-item"))
			{
				if (this.currentItem != null)
					this.unselectItem(this.currentItem);
				this.selectItem(target);

				var url = target.getAttribute("data-url");
				var pageId = target.getAttribute("data-pageid");

				if(!this.isDesktop)
				{
					if(BX.type.isNotEmptyString(url) && BX.type.isNotEmptyString(pageId))
						app.loadPage(url, pageId);
					else if(BX.type.isNotEmptyString(url))
						app.loadPage(url);
				}
				else
				{
					var previewFrame = BX("mobile_menu_preview");
					var wrapPreview = BX("mobile_menu_preview_wrap");
					BX("preview_loading").style.display = "table";
					BX("preview_loading").style.opacity = 1.0;

					wrapPreview.style.display = "inline";

					previewFrame.src = url;
					previewFrame.onload = function(){

						(new BX.fx({
							start: 100,
							finish: 0,
							type: "linear",
							time: 0.2,
							step: 0.05,
							callback: BX.proxy(function (value)
							{
								BX("preview_loading").style.opacity = value/100;

							}, this),
							callback_complete: function ()
							{
								BX("preview_loading").style.display = "none"
							}

						})).start();
					}
				}

				this.currentItem = target;
			}

		},

		selectItem: function (item)
		{
			if (!BX.hasClass(item, "menu-item-selected"))
				BX.addClass(item, "menu-item-selected");
		},

		unselectItem: function (item)
		{
			BX.removeClass(item, "menu-item-selected");
		}
	}
</script>
