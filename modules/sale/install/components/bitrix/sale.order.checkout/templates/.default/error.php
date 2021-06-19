<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arResult
 */
?>
<div class="checkout-container">
<?php
foreach ($arResult['ERRORS'] as $error):
?>
<div class="alert alert-danger" role="alert">
	<?= $error ?>
</div>
<?php
endforeach;
?>
</div>