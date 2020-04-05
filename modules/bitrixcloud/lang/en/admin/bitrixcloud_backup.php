<?
$MESS["BCL_BACKUP_USAGE"] = "Space used: #USAGE# of #QUOTA#.";
$MESS["BCL_BACKUP_FILE_NAME"] = "File name";
$MESS["BCL_BACKUP_FILE_SIZE"] = "File size";
$MESS["BCL_BACKUP_TITLE"] = "Backups";
$MESS["BCL_BACKUP_DO_BACKUP"] = "Create backup";
$MESS["BCL_BACKUP_NOTE"] = "
<p>
Creating backups in cloud storage is available to all editions of Bitrix Site Manager; different <a target=\"_blank\" href=\"http://www.bitrixsoft.com/products/cms/features/cdn.php\">cloud space</a> is reserved for different editions. Saving backups to cloud storage is only available to websites running under an active and valid license.
</p>
<p>
Backups are saved to Bitrix Cloud Storage; up to <b>three most recent backup copies</b> are possible. One of the older copies is purged whenever a newer copy is sent to the cloud. However, the maximum number of copies may decrease if the total size of backup copies exceeds available cloud space. If the size of a single backup copy exceeds available cloud space, backup feature becomes unavailable.
</p>
<p>
A backup copy is encrypted using password you will have to provide when creating a backup. Bitrix Inc. does not store passwords and thus cannot access your information. Consequently, you won't be able to restore a website from a backup copy if you lose your password.
</p>
<p>
Cloud storage conditions and terms may be changed without prior notice.
</p>
";
?>