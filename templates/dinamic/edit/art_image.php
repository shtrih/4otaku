<? 
include_once('templates'.SL.'dinamic'.SL.'edit'.SL.'top.php');
?>
<script type="text/javascript" src="<?=$def['site']['dir']?>/jss/m/?b=jss&f=edit_form.js,fileupload.js,add/art.js"></script>
<table width="100%">		
	<tr>
		<td width="20%">
			Загрузить картинку
		</td>
		<td>
			<table>
				<tr>
					<td>
						<div id="art-image" rel="single"></div>
					</td>
					<td>
						<img class="processing hidden" src="<?=$def['site']['dir']?>/images/ajax-processing.gif" />
					</td>
					<td>
						<span class="processing hidden">Изображение загружается.</span>
					</td>
				</tr>
			</table>
		</td>
	</tr>			
	<tr>
		<td colspan="2" id="error">
		
		</td>
	</tr>			
	<tr id="transparent" class="art_images">
		<td colspan="2">

		</td>
	</tr>
</table>
<? 
include_once('templates'.SL.'dinamic'.SL.'edit'.SL.'bottom.php');
?>
