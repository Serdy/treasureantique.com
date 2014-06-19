<ul class="subsubsub">
	<li><a href="<?php echo add_query_arg( 'filter', null ); ?>"<?php wpsc_ce_archives_quicklink_current( 'all' ); ?>><?php _e( 'All', 'wpsc_ce' ); ?> <span class="count">(<?php wpsc_ce_archives_quicklink_count(); ?>)</span></a> |</li>
	<li><a href="<?php echo add_query_arg( 'filter', 'products' ); ?>"<?php wpsc_ce_archives_quicklink_current( 'products' ); ?>><?php _e( 'Products', 'wpsc_ce' ); ?> <span class="count">(<?php wpsc_ce_archives_quicklink_count( 'products' ); ?>)</span></a> |</li>
	<li><a href="<?php echo add_query_arg( 'filter', 'categories' ); ?>"<?php wpsc_ce_archives_quicklink_current( 'categories' ); ?>><?php _e( 'Categories', 'wpsc_ce' ); ?> <span class="count">(<?php wpsc_ce_archives_quicklink_count( 'categories' ); ?>)</span></a> |</li>
	<li><a href="<?php echo add_query_arg( 'filter', 'tags' ); ?>"<?php wpsc_ce_archives_quicklink_current( 'tags' ); ?>><?php _e( 'Tags', 'wpsc_ce' ); ?> <span class="count">(<?php wpsc_ce_archives_quicklink_count( 'tags' ); ?>)</span></a> |</li>
	<li><a href="<?php echo add_query_arg( 'filter', 'orders' ); ?>"<?php wpsc_ce_archives_quicklink_current( 'orders' ); ?>><?php _e( 'Orders', 'wpsc_ce' ); ?> <span class="count">(<?php wpsc_ce_archives_quicklink_count( 'orders' ); ?>)</span></a> |</li>
	<li><a href="<?php echo add_query_arg( 'filter', 'customers' ); ?>"<?php wpsc_ce_archives_quicklink_current( 'customers' ); ?>><?php _e( 'Customers', 'wpsc_ce' ); ?> <span class="count">(<?php wpsc_ce_archives_quicklink_count( 'customers' ); ?>)</span></a> |</li>
	<li><a href="<?php echo add_query_arg( 'filter', 'coupons' ); ?>"<?php wpsc_ce_archives_quicklink_current( 'coupons' ); ?>><?php _e( 'Coupons', 'wpsc_ce' ); ?> <span class="count">(<?php wpsc_ce_archives_quicklink_count( 'coupons' ); ?>)</span></a></li>
</ul>
<br class="clear" />
<form action="" method="GET">
	<table class="widefat fixed media" cellspacing="0">
		<thead>

			<tr>
				<th scope="col" id="icon" class="manage-column column-icon"></th>
				<th scope="col" id="title" class="manage-column column-title"><?php _e( 'Filename', 'wpsc_ce' ); ?></th>
				<th scope="col" class="manage-column column-type"><?php _e( 'Type', 'wpsc_ce' ); ?></th>
				<th scope="col" class="manage-column column-author"><?php _e( 'Author', 'wpsc_ce' ); ?></th>
				<th scope="col" id="title" class="manage-column column-title"><?php _e( 'Date', 'wpsc_ce' ); ?></th>
			</tr>

		</thead>
		<tfoot>

			<tr>
				<th scope="col" class="manage-column column-icon"></th>
				<th scope="col" class="manage-column column-title"><?php _e( 'Filename', 'wpsc_ce' ); ?></th>
				<th scope="col" class="manage-column column-type"><?php _e( 'Type', 'wpsc_ce' ); ?></th>
				<th scope="col" class="manage-column column-author"><?php _e( 'Author', 'wpsc_ce' ); ?></th>
				<th scope="col" class="manage-column column-title"><?php _e( 'Date', 'wpsc_ce' ); ?></th>
			</tr>

		</tfoot>
		<tbody id="the-list">

<?php if( $files ) { ?>
	<?php foreach( $files as $file ) { ?>
			<tr id="post-<?php echo $file->ID; ?>" class="author-self status-<?php echo $file->post_status; ?>" valign="top">
				<td class="column-icon media-icon">
					<?php echo $file->media_icon; ?>
				</td>
				<td class="post-title page-title column-title">
					<strong><a href="<?php echo $file->guid; ?>" class="row-title"><?php echo $file->post_title; ?></a></strong>
					<div class="row-actions">
						<span class="view"><a href="<?php echo get_edit_post_link( $file->ID ); ?>" title="<?php _e( 'Edit', 'wpsc_ce' ); ?>"><?php _e( 'Edit', 'wpsc_ce' ); ?></a></span> | 
						<span class="delete"><a href="<?php echo get_delete_post_link( $file->ID, '', true ); ?>" title="<?php _e( 'Delete Permanently', 'wpsc_ce' ); ?>" class="delete-tag"><?php _e( 'Delete', 'wpsc_ce' ); ?></a></span>
					</div>
				</td>
				<td class="title">
					<a href="<?php echo add_query_arg( 'filter', $file->export_type ); ?>"><?php echo $file->export_type_label; ?></a>
				</td>
				<td class="author column-author"><?php echo $file->post_author_name; ?></td>
				<td class="date column-date"><?php echo $file->post_date; ?></td>
			</tr>
	<?php } ?>
<?php } else { ?>
			<tr id="post-<?php echo $file->ID; ?>" class="author-self" valign="top">
				<td colspan="3" class="colspanchange"><?php _e( 'No past exports found.', 'wpsc_ce' ); ?></td>
			</tr>
<?php } ?>

		</tbody>
	</table>
</form>