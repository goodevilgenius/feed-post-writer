<!-- -*-html-*- -->
<?php if ($updated): ?>
<div class="updated"><p><strong><?php _e('settings saved.', 'feed-post-writer'); ?></strong></p></div>
<?php endif; ?>

<div class="wrap">
<h2><?php _e('Feed Post Writer Settings', 'feed-post-writer'); ?></h2>

<form method="POST" name="feed-post-writer">

<?php settings_fields('feed-post-writer'); ?>
<table class="form-table">

<?php foreach($feeds as $k => $f):?>
<tbody class="table-group fpw-feed">
  <tr>
	<th scope="row"><label for="fpw-feed-<?=$k?>-url">Feed URL</label></th>
	<td><input type="text" id="fpw-feed-<?=$k?>-url" name="feeds[<?=$k?>][url]" value="<?=$f['url']?>" class="regular-text" /></td>
  </tr>
  <tr>
	<th scope="row"><label for="fpw-feed-<?=$k?>-pid">Post ID</label></th>
	<td><input type="text" id="fpw-feed-<?=$k?>-pid" name="feeds[<?=$k?>][pid]" value="<?=$f['pid']?>" class="small-text" /></td>
  </tr>
  <tr>
	<th scope="row"><label for="fpw-feed-<?=$k?>-schedule">Update Schedule</label></th>
	<td>
	  <select name="feeds[<?=$k?>][schedule]" id="fpw-feed-<?=$k?>-schedule">
		<option value=""> --------- </option>
		<?php foreach($schedules as $schn => $sch):?>
		<option value="<?=$schn?>"
				<?php if ($f['schedule'] == $schn):?>selected="selected"<?php endif;?>
				><?=$sch['display']?></option>
		<?php endforeach;?>
	  </select>
  </tr>
  <tr>
	<th scope="row">Options</th>
	<td>
	  <fieldset>
		<input id="fpw-feed-<?=$k?>-update-featured-image" name="feeds[<?=$k?>][update_featured_image]" 
			   type="checkbox" value="1" 
			   <?php if (!empty($f['update_featured_image'])):?>checked="checked"<?php endif;?>
			   />
		<label for="fpw-feed-<?=$k?>-update-featured-image">Update featured image</label>
		<br />
		<input id="fpw-feed-<?=$k?>-update-title" name="feeds[<?=$k?>][update_title]" 
			   type="checkbox" value="1" 
			   <?php if (!empty($f['update_title'])):?>checked="checked"<?php endif;?>
			   />
		<label for="fpw-feed-<?=$k?>-update-title">Update title</label>
		<br />
		<input name="delete_feed[]" id="fpw-delete-feed-<?=$k?>" value="<?=$k?>" type="checkbox" />
		<label for="fpw-delete-feed-<?=$k?>">Delete this feed</label>
	  </fieldset>
	</td>
  </tr>
</tbody>
<?php endforeach;?>
<tbody>
  <tr><td colspan="2"><?php submit_button('Add feed',array('secondary','large'),'add-feed',false,array('id'=>'fpw-feed-add'));?></td></tr>
</tbody>

<?php /* ?>
<thead>
  <tr><th scope="col">Feed URL</th><th scope="col">Post ID</th><th scop="col">Delete</th></tr>
</thead>
<tbody>
  <?php foreach($feeds as $k => $f):?>
  <tr>
	<td><input type="text" id="fpw-feed-<?=$k?>-url" name="feeds[<?=$k?>][url]" value="<?=$f['url']?>" size="60" /></td>
	<td><input type="text" id="fpw-feed-<?=$k?>-pid" name="feeds[<?=$k?>][pid]" value="<?=$f['pid']?>" size="3" /></td>
	<td><input type="submit" id="fpw-feed-<?=$k?>-delete" name="delete-feed-<?=$k?>" value="Delete" class="button" /></td>
  </tr>
  <?php endforeach;?>
  <tr><td colspan="2"><input type="submit" id="fpw-feed-add" name="add-feed" value="Add feed" class="button" /></td></tr>
</tbody>
<?php */ ?>


</table>
<?php submit_button();?>
</form>
</div>
