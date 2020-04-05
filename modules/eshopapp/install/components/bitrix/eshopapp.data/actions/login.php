<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if ($USER->IsAuthorized()):
$userName = ($userName= $USER->GetFullName()) ? $userName : $USER->GetLogin();
?>
	<script>
		BX.ready(function(){
			app.onCustomEvent('onAuthSuccess', {"user_name":"<?=$userName?>", "id":"<?=$USER->GetID()?>"});
		});
	</script>
<?endif?>