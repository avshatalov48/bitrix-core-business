<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Contact Us");
?>
<p>Have a question you can't find the answer to?</p>

<p>Our furniture experts are here to help.</p>

<h2>Contact us by phone</h2>
<p>Please don't hesitate to contact us on the telephone number below.</p>
<p>X.XXX.XXX.XXXX</p>

<h2>Contact us by email</h2>

<ul> 
  <li><a href="mailto:info@example.com">info@example.com</a> &mdash; For order status on an unshipped order</li>
  <li><a href="mailto:sales@example.com">sales@example.com</a> &mdash; For product questions or to place an order</li>
</ul>

<h2>Visit our showroom at Castlemilk, Glasgow</h2>

<p><?$APPLICATION->IncludeComponent("bitrix:map.google.view", ".default", array(
	"KEY" => "ABQIAAAAOSNukcWVjXaGbDo6npRDcxS1yLxjXbTnpHav15fICwCqFS-qhhSby0EyD6rK_qL4vuBSKpeCz5cOjw",
	"INIT_MAP_TYPE" => "NORMAL",
	"MAP_DATA" => "a:4:{s:10:\"google_lat\";d:55.88119494391713;s:10:\"google_lon\";d:-4.256536178588872;s:12:\"google_scale\";i:15;s:10:\"PLACEMARKS\";a:1:{i:0;a:3:{s:4:\"TEXT\";s:0:\"\";s:3:\"LON\";d:-4.256536178588872;s:3:\"LAT\";d:55.88119494391713;}}}",
	"MAP_WIDTH" => "600",
	"MAP_HEIGHT" => "500",
	"CONTROLS" => array(
		0 => "LARGE_MAP_CONTROL",
		1 => "MINIMAP",
		2 => "HTYPECONTROL",
		3 => "SCALELINE",
	),
	"OPTIONS" => array(
		0 => "ENABLE_SCROLL_ZOOM",
		1 => "ENABLE_DBLCLICK_ZOOM",
		2 => "ENABLE_DRAGGING",
	),
	"MAP_ID" => ""
	),
	false
);?></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>