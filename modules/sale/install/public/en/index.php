<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Personal section");
?>
<p>In the personal section, users can view and edit their personal information.</p>
<ul>
	<li>The <b>User profile </b>page is used to edit personal and job information etc. The <b>User
		profile </b>component fully implements this functionality.
	<li>Users can open the <b>Subscription </b>page to edit the e-mail subscription preferences:
		subscribe to newsletters, modify subscription format, unsubscribe etc.
	<li>The <b>Shopping cart</b> page displays products that a user has chosen to purchase. A user
		can continue shopping, or check out.
	<li>The Orders page shows all orders made by a user. A user can view their status and details on
		each order. This page was created using the <b>User's Orders</b> component (<i>e-Store
		–&gt; Personal section</i>).</li>
</ul>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>