<div class="booru_images">
	<?  
		if (is_array($data['main']['art']['thumbs'])) foreach ($data['main']['art']['thumbs'] as $key => $picture) {
			?>
			<div class="thumbnail <?=($sets['art']['largethumbs'] ? 'large_thumbnail' : 'small_thumbnail');?> show_nsfw_toggler" rel="<?=$picture['id'];?>">
				<?
					unset ($reason);
					if (array_key_exists('nsfw',$picture['meta']['category']) && is_array($picture['meta']['tag'])) {
						if (!$sets['show']['nsfw']) $reason['nsfw'] = true;
						if (!$sets['show']['yaoi'] && array_key_exists('yaoi',$picture['meta']['tag'])) $reason['yaoi'] = true;
						if (!$sets['show']['furry'] && array_key_exists('furry',$picture['meta']['tag'])) $reason['furry'] = true;
						if (!$sets['show']['guro'] && array_key_exists('guro',$picture['meta']['tag'])) $reason['guro'] = true;					
					}
					if (is_array($reason)) {
						?>
							<div class="art_not_showed">
								<?
									if ($reason['nsfw']) {
										?>
											18+ отключено. 
											<a href="#" class="toggle_show_art disabled" rel="show.nsfw">
												Включить.
											</a>
											<br />
										<?
									}
									if ($reason['yaoi']) {
										?>
											Яой отключен.
											<a href="#" class="toggle_show_art disabled" rel="show.yaoi">
												Включить.
											</a>
											<br />
										<?
									}
									if ($reason['furry']) {
										?>
											Фурри отключено.
											<a href="#" class="toggle_show_art disabled" rel="show.furry">
												Включить.
											</a>
											<br />
										<?
									}
									if ($reason['guro']) {
										?>
											Гуро отключено. 
											<a href="#" class="toggle_show_art disabled" rel="show.guro">
												Включить.
											</a>
											<br />
										<?
									}
									if (count($reason) > 1) {
										?>
											<a href="#" class="toggle_show_art disabled" rel="show.<?=implode(',show.',array_keys($reason));?>">
												Включить все.
											</a>
											<br />
										<?
									}								
								?>
								<br /><a href="#" class="show_art disabled">Показать эту картинку.</a>
							</div>
						<?
					}
						?>
							<a href="<?=$def['site']['dir']?>/art/<?=($sets['art']['download_mode'] ? 'download/'.$picture['md5'].'.'.$picture['extension'] : $picture['id']);?>" rel="<?=$picture['id'];?>" class="with_help3<?=(is_array($reason) ? " hidden hidden_art" : "");?>" title="
									<? if (!empty($picture['meta']['tag'])) {
										if (count($picture['meta']['tag']) > 1) {
											?>
												Теги: 
											<?
										}
										else {
											?>
												Тег: 
											<?
										}
										if (is_array($picture['meta']['tag'])) {
											foreach ($picture['meta']['tag'] as &$tag) $tag = $tag['name'];
											echo implode(', ',$picture['meta']['tag']);
										}
									?> | <? } ?>
									<? if (!empty($picture['meta']['author'])) {
										if (count($picture['meta']['author']) > 1) {
											?>
												Опубликовали: 
											<?
										}
										else {
											?>
												Опубликовал: 
											<?
										}
										echo implode(', ',$picture['meta']['author']);
									?> | <? } ?>
									<? if (!empty($picture['meta']['category'])) {
										if (count($picture['meta']['category']) > 1) {
											?>
												Категории: 
											<?
										}
										else {
											?>
												Категория: 
											<?
										}
										echo implode(', ',$picture['meta']['category']);
									?> | <? } ?>
									Рейтинг: <?=$picture['rating'];?>									
							"<?=($sets['art']['blank_mode'] ? ' target="_blank"' : '');?>>
								<img src="<?=$def['site']['dir']?>/images/booru/thumbs/<?=($sets['art']['largethumbs'] ? 'large_' : '');?><?=$picture['thumb'];?>.jpg">
							</a>
							<?=(!empty($picture['similar_count']) ? '<img class="art_sign" src="/images/plus_'.min(10,$picture['similar_count']).'.png">' : '');?>
							<?=(!empty($picture['animated']) ? '<img class="art_sign'.(!empty($picture['similar_count']) ? '2' : '').'" src="/images/animated.png">' : '');?>
						<?
				
				?>
			</div>
			<? 
		} 
	?>
</div>
