<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Contacts");
?>
<p>Get answers to your questions, quickly and easily.</p>

<p>You can contact us by phone, email or visit the Bank branch. We will be glad to answer all your questions.</p>

<h2>Contact us by phone</h2>

    <table width="100%">
		<tr>
			<td width="30%">Bank cards</td> <td width="35%">New Customers</td><td width="35%">Existing Customers</td>
		</tr>
		<tr>
			<td width="30%"> </td> <td width="35%">X.XXX.XXX.XXXX</td><td width="35%">X.XXX.XXX.XXXX</td>
		</tr>
		<tr>
			<td width="30%">Deposits</td> <td width="35%">New Customers</td><td width="35%">Existing Customers</td>
		</tr>
		<tr>
			<td width="30%"> </td> <td width="35%">X.XXX.XXX.XXXX</td><td width="35%">X.XXX.XXX.XXXX</td>
		</tr>
		<tr>
			<td width="30%">Mortgage and Auto loans</td> <td width="35%">New Customers</td><td width="35%">Existing Customers</td>
		</tr>
		<tr>
			<td width="30%"> </td> <td width="35%">X.XXX.XXX.XXXX</td><td width="35%">X.XXX.XXX.XXXX</td>
		</tr>
	</table>
	
    <p>Customer service representatives are available 24 hours a day/ 7 days a week.</p>

	<h2>Contact us by email</h2>
	bank@bank.com
<h2>Bank Branch in Bristol</h2> 

<p><?$APPLICATION->IncludeComponent("bitrix:map.google.view", ".default", array(
	"KEY" => "ABQIAAAAOSNukcWVjXaGbDo6npRDcxS1yLxjXbTnpHav15fICwCqFS-qhhSby0EyD6rK_qL4vuBSKpeCz5cOjw",
	"INIT_MAP_TYPE" => "NORMAL",
	"MAP_DATA" => "a:4:{s:10:\"google_lat\";d:51.45665432908274;s:10:\"google_lon\";d:-2.592473030090332;s:12:\"google_scale\";i:15;s:10:\"PLACEMARKS\";a:1:{i:0;a:3:{s:4:\"TEXT\";s:0:\"\";s:3:\"LON\";d:-2.592473030090332;s:3:\"LAT\";d:51.455878838401;}}}",
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