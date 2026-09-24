<?php
/**
 * Tabla comparativa planes × plazos. Sale 100 % de la fuente única:
 * si MotoCred cambia un valor en el admin, cambia acá, en la Home y en el simulador.
 */
$planes = $args['planes'] ?? MotoCred_Data::planes();
$plazos = MotoCred_Data::plazos();
if ( ! $planes ) {
	return;
}
?>
<div class="mc-table-wrap">
	<table class="mc-table mc-table--plans">
		<caption>Valor de cada cuota fija en pesos. Adjudicación según los Términos y Condiciones. <?php echo esc_html( MotoCred_Data::setting( 'aviso_legal' ) ); ?></caption>
		<thead>
			<tr>
				<th scope="col">Plan</th>
				<?php foreach ( $plazos as $pz ) : ?>
					<th scope="col"><?php echo esc_html( $pz['n'] ); ?> cuotas<small>Adjudica en cuota <?php echo esc_html( $pz['adj'] ); ?></small></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $planes as $p ) : ?>
				<tr>
					<th scope="row"><a href="<?php echo esc_url( $p['url'] ); ?>"><?php echo esc_html( $p['nombre'] ); ?></a> <?php echo motocred_badge( $p['validado'] ); // phpcs:ignore ?></th>
					<?php foreach ( $plazos as $pz ) : ?>
						<td><?php echo null !== $p['cuotas'][ $pz['n'] ] ? esc_html( motocred_money( $p['cuotas'][ $pz['n'] ] ) ) : '<span class="mc-muted">Consultar</span>'; ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php
$upd = array_filter( wp_list_pluck( $planes, 'actualizado' ) );
if ( $upd ) {
	printf( '<p class="mc-note">Valores actualizados al %s.</p>', esc_html( mysql2date( 'j/n/Y', max( $upd ) ) ) );
}
