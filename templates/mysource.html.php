<div class="infobox">
<table>
    <tr>
        <th>MySource</th>
        <td><?php echo $mysource->getTitle()->getSubpageText() ?></td>
	</tr>
	<tr>
		<th>Author</th>
		<td><?php echo $mysource->getAuthor() ?></td>
	</tr>
	<tr class="separator">
		<th>Coverage</th>
		<td></td>
	</tr>
	<tr>
		<th>Year range</th>
		<td></td>
	</tr>
	<tr>
		<th>Surnames</th>
		<td>
			<ul>
			<?php foreach ( $mysource->getSurnames() as $surname ): ?>
				<li><?php echo $surname ?></li>
			<?php endforeach ?>
			</ul>
		</td>
	</tr>
	<tr class="separator">
		<th>Publication information</th>
		<td></td>
	</tr>
	<tr>
		<th>Publication</th>
		<td><?php echo $mysource->getPublicationInfo() ?></td>
	</tr>
	<tr class="separator">
		<th>Citation</th>
		<td></td>
	</tr>
	<tr>
		<td colspan="2">
			<?php if ( $mysource->getAuthor() ) { echo $mysource->getAuthor() . '.';
   } ?>
			<?php echo '<em>' . $mysource->getTitle()->getSubpageText() . '</em>.' ?>
			<?php if ( $mysource->getPublicationInfo() ) {
				echo '(' . $mysource->getPublicationInfo() . ').';
   } ?>
		</td>
	</tr>
</table>
</div>
