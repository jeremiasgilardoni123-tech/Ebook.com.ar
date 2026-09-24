<?php
/**
 * Componentes de interfaz reutilizables (theme + shortcodes).
 * Todos leen de MotoCred_Data: nunca reciben importes escritos a mano.
 */
defined( 'ABSPATH' ) || exit;

/**
 * URL de una página clave. Se puede fijar en Ajustes (ID de página);
 * si no, se busca por slug; si no existe, se usa la ruta esperada.
 */
function motocred_page_url( $key ) {
	$map = array(
		'simulador'    => 'simulador',
		'financiacion' => 'financiacion',
		'contacto'     => 'contacto',
		'terminos'     => 'terminos-y-condiciones',
		'nosotros'     => 'nosotros',
		'cotizador'    => 'cotizador-motos-usadas',
	);
	$id = (int) MotoCred_Data::setting( 'page_' . $key );
	if ( $id && 'publish' === get_post_status( $id ) ) {
		return get_permalink( $id );
	}
	$slug = $map[ $key ] ?? $key;
	$page = get_page_by_path( $slug );
	if ( $page && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}
	return home_url( '/' . $slug . '/' );
}

/** Enlace/botón "Simular" con medición. */
function motocred_sim_link( $label = 'Simulá tu cuota', $class = 'mc-btn mc-btn--primary', array $args = array() ) {
	$url = motocred_page_url( 'simulador' );
	$q   = array_filter( array(
		'plan' => $args['plan'] ?? null,
		'moto' => $args['moto'] ?? null,
	) );
	if ( $q ) {
		$url = add_query_arg( $q, $url );
	}
	$url .= '#simulador';
	return sprintf(
		'<a class="%s" href="%s"%s>%s<span>%s</span></a>',
		esc_attr( $class ),
		esc_url( $url ),
		motocred_track_attrs( 'cta_simular_click', array( 'ubicacion' => $args['ubicacion'] ?? 'general' ) ),
		motocred_icon( 'calc' ),
		esc_html( $label )
	);
}

/* =========================================================================
 * SIMULADOR
 * ========================================================================= */

function motocred_enqueue_front() {
	wp_enqueue_style( 'motocred' );
	wp_enqueue_script( 'motocred' );
}

/**
 * @param array $args mode: compact|full · plan: ID · moto: ID · ubicacion: string
 */
function motocred_simulador( array $args = array() ) {
	motocred_enqueue_front();
	$planes = MotoCred_Data::planes();
	if ( ! $planes ) {
		return current_user_can( 'edit_posts' ) ? '<p class="mc-admin-note">Simulador: cargá al menos un Plan en <em>Planes</em>.</p>' : '';
	}

	$mode   = $args['mode'] ?? 'compact';
	$moto   = null;
	$moto_id = absint( $args['moto'] ?? 0 ) ?: absint( $_GET['moto'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( $moto_id ) {
		$moto = MotoCred_Data::moto( $moto_id );
	}
	$plan_id = absint( $args['plan'] ?? 0 ) ?: absint( $_GET['plan'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! $plan_id && $moto && $moto['plan'] ) {
		$plan_id = $moto['plan']['id'];
	}
	$plan = null;
	foreach ( $planes as $p ) {
		if ( $p['id'] === $plan_id ) {
			$plan = $p;
		}
	}
	$plan   = $plan ?: $planes[0];
	$plazos = MotoCred_Data::plazos();
	$nums   = wp_list_pluck( $plazos, 'n' );
	$plazo  = absint( $_GET['plazo'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! in_array( $plazo, $nums, true ) ) {
		$plazo = $plan['desde_plazo'] ?: end( $nums );
	}

	$uid        = 'mc-sim-' . wp_unique_id();
	$ubicacion  = $args['ubicacion'] ?? ( is_front_page() ? 'home' : 'simulador' );
	$motos_all  = 'full' === $mode ? MotoCred_Data::motos() : array();

	ob_start();
	?>
	<form class="mc-sim mc-sim--<?php echo esc_attr( $mode ); ?>" id="<?php echo esc_attr( $uid ); ?>" data-mc-sim data-ubicacion="<?php echo esc_attr( $ubicacion ); ?>" action="<?php echo esc_url( motocred_page_url( 'simulador' ) ); ?>#simulador" method="get">
		<div class="mc-sim__head">
			<p class="mc-sim__title"><?php echo motocred_icon( 'calc', 22 ); ?> <?php echo 'full' === $mode ? 'Simulá tu financiación' : 'Simulá tu cuota'; ?></p>
			<p class="mc-sim__sub">Elegí cilindrada y cantidad de cuotas.</p>
		</div>

		<?php if ( 'full' === $mode && $motos_all ) : ?>
			<label class="mc-field">
				<span class="mc-field__label">¿Ya elegiste un modelo? <small>(opcional)</small></span>
				<select name="moto" data-mc-sim-moto>
					<option value="">Todavía no</option>
					<?php foreach ( $motos_all as $m ) : ?>
						<option value="<?php echo esc_attr( $m['id'] ); ?>" data-plan="<?php echo esc_attr( $m['plan']['id'] ?? 0 ); ?>" <?php selected( $moto['id'] ?? 0, $m['id'] ); ?>><?php echo esc_html( $m['nombre'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		<?php elseif ( $moto ) : ?>
			<input type="hidden" name="moto" value="<?php echo esc_attr( $moto['id'] ); ?>" data-mc-sim-moto>
		<?php endif; ?>

		<fieldset class="mc-sim__group">
			<legend class="mc-field__label">Cilindrada</legend>
			<div class="mc-chips" role="radiogroup">
				<?php foreach ( $planes as $p ) : ?>
					<label class="mc-chip">
						<input type="radio" name="plan" value="<?php echo esc_attr( $p['id'] ); ?>" <?php checked( $p['id'], $plan['id'] ); ?>>
						<span><?php echo esc_html( $p['cc'] ? $p['cc'] . ' cc' : $p['nombre'] ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<fieldset class="mc-sim__group">
			<legend class="mc-field__label">Cantidad de cuotas</legend>
			<div class="mc-segment" role="radiogroup">
				<?php foreach ( $plazos as $pz ) : ?>
					<label class="mc-segment__opt">
						<input type="radio" name="plazo" value="<?php echo esc_attr( $pz['n'] ); ?>" <?php checked( $pz['n'], $plazo ); ?>>
						<span><?php echo esc_html( $pz['n'] ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<div class="mc-sim__result" data-mc-sim-result aria-live="polite">
			<?php echo motocred_sim_result_html( $plan, $plazo, $moto ); // phpcs:ignore ?>
		</div>

		<div class="mc-sim__actions">
			<?php
			echo motocred_sim_wa_link( $plan, $plazo, $moto, $ubicacion ); // phpcs:ignore
			?>
			<a class="mc-btn mc-btn--ghost" data-mc-sim-next href="<?php echo esc_url( $plan['suscripcion_url'] ?: $plan['url'] ); ?>"<?php echo motocred_track_attrs( $plan['suscripcion_url'] ? 'suscripcion_click' : 'consulta_plan', array( 'plan' => $plan['nombre'], 'ubicacion' => $ubicacion ) ); ?>>
				<span><?php echo $plan['suscripcion_url'] ? 'Suscribirme a este plan' : 'Ver detalle del plan'; ?></span><?php echo motocred_icon( 'arrow', 18 ); ?>
			</a>
			<noscript><button class="mc-btn mc-btn--primary" type="submit">Calcular</button></noscript>
		</div>
		<?php if ( MotoCred_Data::setting( 'aviso_legal' ) ) : ?>
			<p class="mc-sim__legal"><?php echo esc_html( MotoCred_Data::setting( 'aviso_legal' ) ); ?> <a href="<?php echo esc_url( motocred_page_url( 'terminos' ) ); ?>">Ver condiciones</a>.</p>
		<?php endif; ?>
	</form>
	<?php
	return ob_get_clean();
}

function motocred_find_plazo( $n ) {
	foreach ( MotoCred_Data::plazos() as $p ) {
		if ( (int) $p['n'] === (int) $n ) {
			return $p;
		}
	}
	return null;
}

/** Resultado del simulador (la versión JS en motocred.js replica este markup). */
function motocred_sim_result_html( array $plan, $plazo, $moto = null ) {
	$cuota = $plan['cuotas'][ $plazo ] ?? null;
	$pz    = motocred_find_plazo( $plazo );
	$label = $moto ? $moto['nombre'] . ' · ' . $plan['nombre'] : $plan['nombre'];

	ob_start();
	echo '<p class="mc-sim__plan">' . esc_html( $label ) . ' ' . motocred_badge( $plan['validado'] ) . '</p>';
	if ( null !== $cuota ) {
		printf(
			'<p class="mc-sim__amount"><span class="mc-sim__n">%1$d cuotas fijas de</span><strong>%2$s</strong></p>',
			(int) $plazo,
			esc_html( motocred_money( $cuota ) )
		);
	} else {
		echo '<p class="mc-sim__amount mc-sim__amount--empty"><strong>Valor a confirmar</strong><span class="mc-sim__n">Un asesor te pasa la cuota actualizada por WhatsApp.</span></p>';
	}
	echo '<ul class="mc-sim__facts">';
	echo '<li>' . motocred_icon( 'pesos', 16 ) . 'Cuotas fijas en pesos</li>';
	if ( $pz ) {
		printf( '<li>%sAdjudicación en la cuota %d</li>', motocred_icon( 'key', 16 ), (int) $pz['adj'] );
	}
	if ( null !== $plan['gastos'] ) {
		printf( '<li>%sGastos de suscripción: %s</li>', motocred_icon( 'plans', 16 ), esc_html( motocred_money( $plan['gastos'] ) ) );
	}
	echo '</ul>';
	return ob_get_clean();
}

function motocred_sim_wa_message( array $plan, $plazo, $moto = null ) {
	$saludo = trim( (string) MotoCred_Data::setting( 'whatsapp_saludo' ) ) ?: 'Hola MotoCred';
	$cuota  = $plan['cuotas'][ $plazo ] ?? null;
	$que    = $moto ? sprintf( 'la %s (%s)', $moto['nombre'], $plan['nombre'] ) : sprintf( 'el %s', $plan['nombre'] );
	$msg    = sprintf( '%s, simulé %s en %d cuotas', $saludo, $que, (int) $plazo );
	$msg   .= null !== $cuota ? sprintf( ' de %s', motocred_money( $cuota ) ) : '';
	$msg   .= '. Quiero avanzar con la compra.';
	return $msg . "\n\n(Simulación desde la web)";
}

function motocred_sim_wa_link( array $plan, $plazo, $moto, $ubicacion ) {
	$wa = motocred_phone( MotoCred_Data::setting( 'whatsapp' ) );
	$params = array( 'intent' => 'simulacion', 'plan' => $plan['nombre'], 'plazo' => (int) $plazo, 'modelo' => $moto['nombre'] ?? null, 'ubicacion' => $ubicacion );
	if ( ! $wa ) {
		return sprintf( '<a class="mc-btn mc-btn--wa" href="%s"%s>%s<span>Hablar con un asesor</span></a>', esc_url( motocred_page_url( 'contacto' ) ), motocred_track_attrs( 'contact_click', array_filter( $params ) ), motocred_icon( 'phone' ) );
	}
	$url = 'https://wa.me/' . $wa['wa'] . '?text=' . rawurlencode( motocred_sim_wa_message( $plan, $plazo, $moto ) );
	return sprintf(
		'<a class="mc-btn mc-btn--wa" data-mc-sim-wa href="%s" target="_blank" rel="noopener"%s>%s<span>Enviar simulación por WhatsApp</span></a>',
		esc_url( $url ),
		motocred_track_attrs( 'whatsapp_click', array_filter( $params ) ),
		motocred_icon( 'whatsapp' )
	);
}

/* =========================================================================
 * CARDS
 * ========================================================================= */

function motocred_plan_card( array $plan, array $args = array() ) {
	$motos = MotoCred_Data::motos( array( 'plan' => $plan['id'], 'limit' => 4 ) );
	$img   = $plan['imagen'];
	if ( ! $img && $motos && $motos[0]['imagen_id'] ) {
		$img = wp_get_attachment_image_url( $motos[0]['imagen_id'], 'mc-card' );
	}
	ob_start();
	?>
	<article class="mc-plan-card">
		<a class="mc-plan-card__media" href="<?php echo esc_url( $plan['url'] ); ?>" tabindex="-1" aria-hidden="true">
			<?php if ( $img ) : ?>
				<img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy" decoding="async" width="640" height="440">
			<?php else : ?>
				<span class="mc-placeholder"><?php echo motocred_icon( 'moto', 56 ); ?></span>
			<?php endif; ?>
			<span class="mc-plan-card__cc"><?php echo esc_html( $plan['cc'] ); ?><small>cc</small></span>
		</a>
		<div class="mc-plan-card__body">
			<p class="mc-eyebrow"><?php echo esc_html( $plan['nombre'] ); ?> <?php echo motocred_badge( $plan['validado'] ); ?></p>
			<h3 class="mc-plan-card__title"><a href="<?php echo esc_url( $plan['url'] ); ?>"><?php echo esc_html( $plan['uso'] ?: $plan['nombre'] ); ?></a></h3>
			<?php if ( $motos ) : ?>
				<p class="mc-plan-card__models"><?php echo esc_html( implode( ' · ', wp_list_pluck( $motos, 'nombre' ) ) ); ?></p>
			<?php endif; ?>
			<div class="mc-price">
				<?php if ( null !== $plan['desde'] ) : ?>
					<span class="mc-price__label">Cuotas desde</span>
					<strong class="mc-price__value"><?php echo esc_html( motocred_money( $plan['desde'] ) ); ?></strong>
					<span class="mc-price__note"><?php echo esc_html( $plan['desde_plazo'] ); ?> cuotas fijas en pesos</span>
				<?php else : ?>
					<span class="mc-price__label">Cuota</span>
					<strong class="mc-price__value mc-price__value--ask">Consultá el valor</strong>
					<span class="mc-price__note"><?php echo esc_html( MotoCred_Data::plazos_text() ); ?> cuotas fijas</span>
				<?php endif; ?>
			</div>
			<div class="mc-card-actions">
				<?php echo motocred_sim_link( 'Simular', 'mc-btn mc-btn--primary mc-btn--sm', array( 'plan' => $plan['id'], 'ubicacion' => $args['ubicacion'] ?? 'plan_card' ) ); ?>
				<a class="mc-btn mc-btn--ghost mc-btn--sm" href="<?php echo esc_url( $plan['url'] ); ?>"<?php echo motocred_track_attrs( 'consulta_plan', array( 'plan' => $plan['nombre'], 'ubicacion' => $args['ubicacion'] ?? 'plan_card' ) ); ?>><span>Ver opciones</span></a>
			</div>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

function motocred_moto_card( array $moto, array $args = array() ) {
	$plan  = $moto['plan'];
	$attrs = sprintf(
		' data-cc="%s" data-marca="%s" data-tipo="%s" data-desde="%s"',
		esc_attr( $moto['cc'] ),
		esc_attr( $moto['marca_slug'] ),
		esc_attr( $moto['tipo_slug'] ),
		esc_attr( $plan['desde'] ?? '' )
	);
	ob_start();
	?>
	<article class="mc-moto-card"<?php echo $attrs; // phpcs:ignore ?>>
		<a class="mc-moto-card__media" href="<?php echo esc_url( $moto['url'] ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			if ( $moto['imagen_id'] ) {
				echo wp_get_attachment_image( $moto['imagen_id'], 'mc-card', false, array( 'alt' => '', 'loading' => 'lazy', 'sizes' => '(min-width: 1100px) 340px, (min-width: 700px) 45vw, 90vw' ) );
			} else {
				echo '<span class="mc-placeholder">' . motocred_icon( 'moto', 56 ) . '</span>'; // phpcs:ignore
			}
			?>
		</a>
		<div class="mc-moto-card__body">
			<p class="mc-eyebrow"><?php echo esc_html( trim( $moto['marca'] . ( $moto['cc'] ? ' · ' . $moto['cc'] . ' cc' : '' ), ' ·' ) ); ?></p>
			<h3 class="mc-moto-card__title"><a href="<?php echo esc_url( $moto['url'] ); ?>"><?php echo esc_html( $moto['nombre'] ); ?></a></h3>
			<div class="mc-price mc-price--sm">
				<?php if ( $plan && null !== $plan['desde'] ) : ?>
					<span class="mc-price__label"><?php echo esc_html( $plan['nombre'] ); ?> · desde</span>
					<strong class="mc-price__value"><?php echo esc_html( motocred_money( $plan['desde'] ) ); ?></strong>
				<?php elseif ( $plan ) : ?>
					<span class="mc-price__label"><?php echo esc_html( $plan['nombre'] ); ?></span>
					<strong class="mc-price__value mc-price__value--ask">Consultá la cuota</strong>
				<?php endif; ?>
			</div>
			<div class="mc-card-actions">
				<?php echo motocred_sim_link( 'Simular', 'mc-btn mc-btn--primary mc-btn--sm', array( 'moto' => $moto['id'], 'ubicacion' => $args['ubicacion'] ?? 'moto_card' ) ); ?>
				<?php echo motocred_wa_button( array( 'intent' => 'moto', 'moto' => $moto, 'ubicacion' => $args['ubicacion'] ?? 'moto_card' ), 'Consultar', 'mc-btn mc-btn--wa-ghost mc-btn--sm' ); ?>
			</div>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

function motocred_sucursal_card( array $s, array $args = array() ) {
	$soon = 'proximamente' === $s['estado'];
	ob_start();
	?>
	<article class="mc-branch<?php echo $soon ? ' mc-branch--soon' : ''; ?>">
		<?php if ( ! $soon && ! empty( $args['map'] ) ) : ?>
			<div class="mc-branch__map" data-mc-map="<?php echo esc_url( $s['embed'] ); ?>">
				<button type="button" class="mc-branch__map-btn" data-mc-map-load aria-label="Mostrar mapa de <?php echo esc_attr( $s['nombre'] ); ?>"><?php echo motocred_icon( 'pin', 22 ); ?><span>Ver mapa</span></button>
			</div>
		<?php endif; ?>
		<div class="mc-branch__body">
			<p class="mc-eyebrow"><?php echo $soon ? 'Próximamente' : esc_html( $s['localidad'] ?: 'Sucursal' ); ?> <?php echo motocred_badge( $s['validado'] ); ?></p>
			<h3 class="mc-branch__title"><?php echo esc_html( $s['nombre'] ); ?></h3>
			<?php $addr = trim( $s['direccion'] . ( $s['localidad'] ? ', ' . $s['localidad'] : '' ), ', ' ); ?>
			<?php if ( $addr && $addr !== $s['nombre'] ) : ?>
				<p class="mc-branch__addr"><?php echo motocred_icon( 'pin', 18 ); ?><span><?php echo esc_html( $addr ); ?></span></p>
			<?php endif; ?>
			<?php if ( $s['horario'] ) : ?>
				<p class="mc-branch__addr"><?php echo motocred_icon( 'clock', 18 ); ?><span><?php echo esc_html( $s['horario'] ); ?></span></p>
			<?php endif; ?>
			<?php if ( ! $soon ) : ?>
				<div class="mc-card-actions">
					<a class="mc-btn mc-btn--primary mc-btn--sm" href="<?php echo esc_url( $s['llegar'] ); ?>" target="_blank" rel="noopener"<?php echo motocred_track_attrs( 'sucursal_como_llegar', array( 'sucursal' => $s['nombre'] ) ); ?>><?php echo motocred_icon( 'arrow', 18 ); ?><span>Cómo llegar</span></a>
					<?php if ( $s['telefono'] ) : ?>
						<a class="mc-btn mc-btn--ghost mc-btn--sm" href="tel:<?php echo esc_attr( $s['telefono']['tel'] ); ?>"<?php echo motocred_track_attrs( 'sucursal_llamada', array( 'sucursal' => $s['nombre'] ) ); ?>><?php echo motocred_icon( 'phone', 18 ); ?><span><?php echo esc_html( $s['telefono']['display'] ); ?></span></a>
					<?php elseif ( $s['tel_raw'] && current_user_can( 'edit_posts' ) ) : ?>
						<span class="mc-validate">Teléfono inválido: «<?php echo esc_html( $s['tel_raw'] ); ?>»</span>
					<?php endif; ?>
					<?php
					if ( $s['whatsapp'] ) {
						echo motocred_wa_button( array( 'intent' => 'sucursal', 'sucursal' => $s ), 'WhatsApp', 'mc-btn mc-btn--wa-ghost mc-btn--sm' ); // phpcs:ignore
					}
					?>
				</div>
			<?php endif; ?>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * SECCIONES
 * ========================================================================= */

function motocred_steps() {
	return array(
		array( 'icon' => 'moto', 't' => 'Elegí tu moto', 'd' => 'Mirá los modelos por cilindrada y encontrá la que va con tu día a día.' ),
		array( 'icon' => 'calc', 't' => 'Simulá tu cuota', 'd' => 'Elegí en cuántas cuotas querés pagar y mirá el valor al instante.' ),
		array( 'icon' => 'whatsapp', 't' => 'Hablá con un asesor', 'd' => 'Te confirma el valor vigente, resuelve tus dudas y te guía con los datos.' ),
		array( 'icon' => 'id', 't' => 'Suscribí tu plan', 'd' => 'Iniciás con tu DNI, pagando la primera cuota y los gastos.' ),
		array( 'icon' => 'key', 't' => 'Retirá tu moto', 'd' => 'La entrega se coordina según la adjudicación del plan que elegiste.' ),
	);
}

function motocred_como_funciona() {
	ob_start();
	echo '<ol class="mc-steps">';
	foreach ( motocred_steps() as $i => $s ) {
		printf(
			'<li class="mc-step"><span class="mc-step__n">%1$d</span><span class="mc-step__icon">%2$s</span><h3 class="mc-step__t">%3$s</h3><p class="mc-step__d">%4$s</p></li>',
			$i + 1,
			motocred_icon( $s['icon'], 24 ),
			esc_html( $s['t'] ),
			esc_html( $s['d'] )
		);
	}
	echo '</ol>';
	return ob_get_clean();
}

function motocred_plazos_table() {
	ob_start();
	?>
	<div class="mc-table-wrap">
		<table class="mc-table">
			<caption>Plazos y adjudicación según los Términos y Condiciones</caption>
			<thead><tr><th scope="col">Cantidad de cuotas</th><th scope="col">Adjudicación</th></tr></thead>
			<tbody>
			<?php foreach ( MotoCred_Data::plazos() as $p ) : ?>
				<tr><td><?php echo esc_html( $p['n'] ); ?> cuotas</td><td>Cuota <?php echo esc_html( $p['adj'] ); ?></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
	return ob_get_clean();
}

function motocred_faq( ?array $faqs = null ) {
	$faqs = $faqs ?? MotoCred_Data::faqs();
	if ( ! $faqs ) {
		return '';
	}
	motocred_seo_collect_faq( $faqs );
	ob_start();
	echo '<div class="mc-faq">';
	foreach ( $faqs as $f ) {
		printf(
			'<details class="mc-faq__item"><summary class="mc-faq__q">%s<span class="mc-faq__icon" aria-hidden="true"></span></summary><div class="mc-faq__a"><p>%s</p></div></details>',
			esc_html( $f['q'] ),
			esc_html( $f['a'] )
		);
	}
	echo '</div>';
	return ob_get_clean();
}

function motocred_entregas_grid( $limit = 8 ) {
	$items = MotoCred_Data::entregas( $limit );
	if ( ! $items ) {
		return '';
	}
	ob_start();
	echo '<div class="mc-deliveries" role="list">';
	foreach ( $items as $e ) {
		echo '<figure class="mc-delivery" role="listitem">';
		echo wp_get_attachment_image( $e['imagen'], 'mc-square', false, array( 'loading' => 'lazy', 'alt' => $e['titulo'], 'sizes' => '(min-width: 900px) 280px, 45vw' ) );
		printf(
			'<figcaption><strong>%s</strong>%s</figcaption>',
			esc_html( $e['titulo'] ),
			$e['sucursal'] ? '<span>' . esc_html( $e['sucursal'] ) . '</span>' : ''
		);
		echo '</figure>';
	}
	echo '</div>';
	return ob_get_clean();
}
