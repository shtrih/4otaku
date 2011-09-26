<script type="text/javascript">
	$("input.disabled").click(function(event){  
		event.preventDefault();
	});

	$("input.hide_updates").click(function(){  	
		$("#updates_field").hide();
		$("#updates_field").html('&nbsp;');
	});	
</script>
<?
	foreach ($data as $update) {
		?>
			<div class="shell">
				<div class="right">
					<b>
						<? echo $update['pretty_date']; ?>
					</b>
				</div>					
				<div class="left ">
					Обновил: 
					<b>
						<?=$update['username'];?>
					</b>
				</div>
				&nbsp;
				<div class="entry">				
					<p>
						<?=$update['text'];?>
					</p>
					<p>
						<? 
							if (is_array($update['link'])) foreach ($update['link'] as $key => $link) { 
								if ($nonfirst) {
									?>
										<br />
									<?													
								} else $nonfirst = true;
								?>								
									<?=$link['name'];?>: 
									<?
										if (is_array($link['url'])) foreach ($link['url'] as $key2 => $linkurl) {
												?>
													<?
															if ($nonfirst2) {
																	?>
																				 | 
																	<?
															}	else $nonfirst2 = true;
													?>
													<a href="<?=($data['feed']['domain'] ? $linkurl : '/go?'.urlencode($linkurl));?>" target="_blank">
														<?=$link['alias'][$key2];?>
													</a>
												<?
										}	unset($nonfirst2);
									?>
									 (~<?=$link['size'];?> <?=$link['sizetype'];?>)
								<?
							}	unset($nonfirst);
						?>
					</p>
				</div>
			</div>		
		<?
	}