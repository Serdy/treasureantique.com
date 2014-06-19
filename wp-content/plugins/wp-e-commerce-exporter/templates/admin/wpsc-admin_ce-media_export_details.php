<table class="widefat" style="font-family:monospace;">
	<thead>

		<tr>
			<th colspan="2"><?php _e( 'Export Details', 'wpsc_ce' ); ?></th>
		</tr>

	</thead>
	<tbody>

		<tr>
			<th style="width:20%;"><?php _e( 'Dataset', 'wpsc_ce' ); ?></th>
			<td><?php echo wpsc_ce_export_type_label( $dataset ); ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Filepath', 'wpsc_ce' ); ?></th>
			<td><?php echo $filepath; ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Total columns', 'wpsc_ce' ); ?></th>
			<td><?php echo $columns; ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Total rows', 'wpsc_ce' ); ?></th>
			<td><?php echo $rows; ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Process time', 'wpsc_ce' ); ?></th>
			<td><?php echo wpsc_ce_display_time_elapsed( $start_time, $end_time ); ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Idle memory usage (start)', 'wpsc_ce' ); ?></th>
			<td><?php wpsc_ce_display_memory( $idle_memory_start ); ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Memory usage prior to loading dataset', 'wpsc_ce' ); ?></th>
			<td><?php wpsc_ce_display_memory( $data_memory_start ); ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Memory usage after loading dataset', 'wpsc_ce' ); ?></th>
			<td><?php wpsc_ce_display_memory( $data_memory_end ); ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Memory usage at render time', 'wpsc_ce' ); ?></th>
			<td>-</td>
		</tr>
		<tr>
			<th><?php _e( 'Idle memory usage (end)', 'wpsc_ce' ); ?></th>
			<td><?php wpsc_ce_display_memory( $idle_memory_end ); ?></td>
		</tr>

	</tbody>
</table>
<br />