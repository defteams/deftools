<table class="" cellspacing="0" id="status" style="width:100%;">
	<tbody>
		<?php foreach ( $info as $key => $env ) : ?>
		<tr>
			<td><?php echo $env['label']; ?></td>
			<td>:</td>
			<td><?php echo $env['value']; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
