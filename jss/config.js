<? header("Content-type: text/javascript"); ?>
<?
	define('ROOT_DIR', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
	include ROOT_DIR.'engine'.DIRECTORY_SEPARATOR.'config.php';
?>

window.config = new Array();
window.config.site_dir = "<?=$def['site']['dir'];?>";
window.config.image_dir = window.config.site_dir + "/images";
