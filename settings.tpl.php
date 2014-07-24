<!-- -*-html-*- -->
<?php if ($updated): ?>
<div class="updated"><p><strong><?php _e('settings saved.', 'feed-post-writer'); ?></strong></p></div>
<?php endif; ?>

<div class="wrap">
<h2><?php _e('Feed Post Writer Settings', 'feed-post-writer'); ?></h2>

<form method="POST" name="feed-post-writer">

<?php settings_fields('feed-post-writer'); ?>
<table class="form-table">
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
</table>
<?php submit_button();?>
</form>
</div>
