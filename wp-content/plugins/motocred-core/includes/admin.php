<?php
/**
 * Administración: campos de Planes, Motos, Sucursales y Entregas,
 * pantalla de Ajustes y panel "Datos a validar".
 */
defined( 'ABSPATH' ) || exit;

/* =========================================================================
 * Meta boxes
 * ========================================================================= */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'mc_plan_data', 'Datos comerciales del plan (fuente única)', 'motocred_mb_plan', 'mc_plan', 'normal', 'high' );
	add_meta_box( 'mc_moto_data', 'Ficha de la moto', 'motocred_mb_moto', 'mc_moto', 'normal', 'high' );
	add_meta_box( 'mc_suc_data', 'Datos de la sucursal', 'motocred_mb_sucursal', 'mc_sucursal', 'normal', 'high' );
	add_meta_box( 'mc_ent_data', 'Entrega real', 'motocred_mb_entrega', 'mc_entrega', 'normal', 'high' );
	foreach ( array( 'page', 'post' ) as $pt ) {
		add_meta_box( 'mc_seo', 'MotoCred · Descripción SEO', 'motocred_mb_seo', $pt, 'side', 'low' );
	}
} );

function motocred_field( $name, $label, $value, $args = array() ) {
	$type = $args['type'] ?? 'text';
	$id   = 'mc_' . sanitize_key( $name );
	echo '<p class="mc-admin-field"><label for="' . esc_attr( $id ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
	if ( 'textarea' === $type ) {
		printf( '<textarea id="%s" name="%s" rows="%d" class="large-text">%s</textarea>', esc_attr( $id ), esc_attr( $name ), (int) ( $args['rows'] ?? 4 ), esc_textarea( $value ) );
	} elseif ( 'checkbox' === $type ) {
		printf( '<label><input type="checkbox" id="%s" name="%s" value="1" %s> %s</label>', esc_attr( $id ), esc_attr( $name ), checked( $value, '1', false ), esc_html( $args['cb_label'] ?? '' ) );
	} elseif ( 'select' === $type ) {
		printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
		foreach ( $args['options'] as $k => $v ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( (string) $value, (string) $k, false ), esc_html( $v ) );
		}
		echo '</select>';
	} else {
		printf( '<input type="%s" id="%s" name="%s" value="%s" class="%s" placeholder="%s">', esc_attr( $type ), esc_attr( $id ), esc_attr( $name ), esc_attr( $value ), esc_attr( $args['class'] ?? 'regular-text' ), esc_attr( $args['placeholder'] ?? '' ) );
	}
	if ( ! empty( $args['help'] ) ) {
		echo '<br><span class="description">' . wp_kses_post( $args['help'] ) . '</span>';
	}
	echo '</p>';
}

function motocred_mb_plan( $post ) {
	wp_nonce_field( 'mc_save_meta', 'mc_nonce' );
	$m      = fn( $k ) => get_post_meta( $post->ID, '_mc_' . $k, true );
	$cuotas = (array) $m( 'cuotas' );
	motocred_field( 'mc[cc]', 'Cilindrada (cc)', $m( 'cc' ), array( 'class' => 'small-text', 'placeholder' => '125' ) );
	motocred_field( 'mc[uso]', 'Para qué sirve (título comercial)', $m( 'uso' ), array( 'placeholder' => 'Ideal para ciudad', 'help' => 'Se muestra como título de la tarjeta. Ej.: «Ideal para ciudad», «Trabajo y ruta».' ) );

	echo '<h4>Valor de la cuota por plazo (en pesos, sin puntos)</h4><table class="widefat striped" style="max-width:520px"><thead><tr><th>Plazo</th><th>Adjudicación</th><th>Valor de cuota</th></tr></thead><tbody>';
	foreach ( MotoCred_Data::plazos() as $p ) {
		printf(
			'<tr><td>%1$d cuotas</td><td>Cuota %2$d</td><td><input type="text" inputmode="numeric" name="mc[cuotas][%1$d]" value="%3$s" class="regular-text" placeholder="Vacío = «Consultá el valor»"></td></tr>',
			(int) $p['n'],
			(int) $p['adj'],
			esc_attr( $cuotas[ $p['n'] ] ?? '' )
		);
	}
	echo '</tbody></table>';
	motocred_field( 'mc[gastos]', 'Gastos de suscripción ($)', $m( 'gastos' ), array( 'class' => 'regular-text', 'help' => 'Opcional. Se muestra en el simulador.' ) );
	motocred_field( 'mc[suscripcion_url]', 'URL de suscripción / pago', $m( 'suscripcion_url' ), array( 'type' => 'url', 'class' => 'large-text', 'placeholder' => '/suscribete-plan125/', 'help' => 'Página existente donde se paga la primera cuota. No se modifica esa página.' ) );
	motocred_field( 'mc[validado]', 'Validación', $m( 'validado' ), array( 'type' => 'checkbox', 'cb_label' => 'Revisé estos valores contra el tarifario vigente y los T&C. Mostrarlos al público.' ) );
	if ( $m( 'actualizado' ) ) {
		echo '<p class="description">Última modificación de valores: <strong>' . esc_html( $m( 'actualizado' ) ) . '</strong></p>';
	}
}

function motocred_mb_moto( $post ) {
	wp_nonce_field( 'mc_save_meta', 'mc_nonce' );
	$m     = fn( $k ) => get_post_meta( $post->ID, '_mc_' . $k, true );
	$plans = array( '' => '— Sin plan —' );
	foreach ( MotoCred_Data::planes() as $p ) {
		$plans[ $p['id'] ] = $p['nombre'];
	}
	motocred_field( 'mc[plan]', 'Plan de financiación', $m( 'plan' ), array( 'type' => 'select', 'options' => $plans, 'help' => 'La cuota de la moto sale del plan: no se carga acá.' ) );
	motocred_field( 'mc[cc]', 'Cilindrada (cc)', $m( 'cc' ), array( 'class' => 'small-text', 'help' => 'Si se deja vacío se usa la del plan.' ) );
	motocred_field( 'mc[specs]', 'Características principales (una por línea, «Dato: valor»)', $m( 'specs' ), array( 'type' => 'textarea', 'rows' => 6, 'help' => 'Ej.: «Motor: 4 tiempos, monocilíndrico». Tomar los datos de la ficha oficial de la marca.' ) );
	motocred_field( 'mc[specs_validado]', '', $m( 'specs_validado' ), array( 'type' => 'checkbox', 'cb_label' => 'Características verificadas con la ficha oficial.' ) );
	motocred_field( 'mc[precio_ref]', 'Precio de referencia contado ($, opcional)', $m( 'precio_ref' ), array( 'class' => 'regular-text' ) );
	motocred_field( 'mc[precio_validado]', '', $m( 'precio_validado' ), array( 'type' => 'checkbox', 'cb_label' => 'Precio validado: mostrarlo al público y en Google.' ) );
	motocred_field( 'mc[destacada]', 'Destacada', $m( 'destacada' ), array( 'type' => 'checkbox', 'cb_label' => 'Mostrar en la Home.' ) );
	motocred_field( 'mc[url_anterior]', 'URL anterior (redirección 301)', $m( 'url_anterior' ), array( 'class' => 'large-text', 'placeholder' => '/tienda-2/honda/honda-wave-110-cc/', 'help' => 'Si la moto tenía otra URL, se redirige automáticamente a esta ficha (sólo si la URL vieja deja de existir).' ) );
}

function motocred_mb_sucursal( $post ) {
	wp_nonce_field( 'mc_save_meta', 'mc_nonce' );
	$m = fn( $k ) => get_post_meta( $post->ID, '_mc_' . $k, true );
	motocred_field( 'mc[estado]', 'Estado', $m( 'estado' ) ?: 'operativa', array( 'type' => 'select', 'options' => array( 'operativa' => 'Operativa', 'proximamente' => 'Próximamente (se muestra aparte, con menos peso)' ) ) );
	motocred_field( 'mc[direccion]', 'Dirección', $m( 'direccion' ), array( 'placeholder' => 'Calle 123' ) );
	motocred_field( 'mc[localidad]', 'Localidad / departamento', $m( 'localidad' ), array( 'placeholder' => 'Maipú' ) );
	$tel = $m( 'telefono' );
	motocred_field( 'mc[telefono]', 'Teléfono', $tel, array( 'help' => ( $tel && ! motocred_phone( $tel ) ) ? '<strong style="color:#b32d2e">Número inválido: debe tener 10 dígitos con código de área. No se mostrará.</strong>' : 'Con código de área, ej.: 261 631-4304' ) );
	motocred_field( 'mc[whatsapp]', 'WhatsApp de la sucursal (opcional)', $m( 'whatsapp' ), array( 'help' => 'Si se completa, los botones de esta sucursal van a este número en lugar del central.' ) );
	motocred_field( 'mc[horario]', 'Horario', $m( 'horario' ), array( 'class' => 'large-text', 'placeholder' => 'Lun a vie 9 a 18 · Sáb 9 a 13' ) );
	motocred_field( 'mc[lat]', 'Latitud (opcional)', $m( 'lat' ), array( 'class' => 'regular-text' ) );
	motocred_field( 'mc[lng]', 'Longitud (opcional)', $m( 'lng' ), array( 'class' => 'regular-text' ) );
	motocred_field( 'mc[validado]', 'Validación', $m( 'validado' ), array( 'type' => 'checkbox', 'cb_label' => 'Dirección, teléfono y estado verificados.' ) );
}

function motocred_mb_entrega( $post ) {
	wp_nonce_field( 'mc_save_meta', 'mc_nonce' );
	$m    = fn( $k ) => get_post_meta( $post->ID, '_mc_' . $k, true );
	$sucs = array( '' => '—' );
	foreach ( MotoCred_Data::sucursales() as $s ) {
		$sucs[ $s['id'] ] = $s['nombre'];
	}
	echo '<p>Usá como <strong>título</strong> el nombre de pila del cliente y el modelo (ej.: «Lucía y su Wave 110»). La foto va como <strong>imagen destacada</strong>.</p>';
	motocred_field( 'mc[sucursal]', 'Sucursal', $m( 'sucursal' ), array( 'type' => 'select', 'options' => $sucs ) );
	motocred_field( 'mc[consentimiento]', 'Consentimiento', $m( 'consentimiento' ), array( 'type' => 'checkbox', 'cb_label' => 'El cliente autorizó por escrito publicar su foto. Sin esta marca, la entrega no se muestra.' ) );
}

function motocred_mb_seo( $post ) {
	wp_nonce_field( 'mc_save_meta', 'mc_nonce' );
	if ( motocred_seo_plugin_active() ) {
		echo '<p class="description">Hay un plugin SEO activo: usá sus campos.</p>';
		return;
	}
	motocred_field( 'mc[seo_desc]', 'Meta description (máx. 155 caracteres)', get_post_meta( $post->ID, '_mc_seo_desc', true ), array( 'type' => 'textarea', 'rows' => 3 ) );
}

add_action( 'save_post', function ( $post_id, $post ) {
	if ( ! isset( $_POST['mc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mc_nonce'] ) ), 'mc_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$in = isset( $_POST['mc'] ) ? wp_unslash( (array) $_POST['mc'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	$fields = array(
		'mc_plan'     => array( 'cc' => 'int', 'uso' => 'text', 'gastos' => 'money', 'suscripcion_url' => 'url', 'validado' => 'bool' ),
		'mc_moto'     => array( 'plan' => 'int', 'cc' => 'int', 'specs' => 'textarea', 'specs_validado' => 'bool', 'precio_ref' => 'money', 'precio_validado' => 'bool', 'destacada' => 'bool', 'url_anterior' => 'path' ),
		'mc_sucursal' => array( 'estado' => 'key', 'direccion' => 'text', 'localidad' => 'text', 'telefono' => 'text', 'whatsapp' => 'text', 'horario' => 'text', 'lat' => 'float', 'lng' => 'float', 'validado' => 'bool' ),
		'mc_entrega'  => array( 'sucursal' => 'int', 'consentimiento' => 'bool' ),
		'page'        => array( 'seo_desc' => 'textarea' ),
		'post'        => array( 'seo_desc' => 'textarea' ),
	);
	if ( ! isset( $fields[ $post->post_type ] ) ) {
		return;
	}

	foreach ( $fields[ $post->post_type ] as $key => $type ) {
		$v = $in[ $key ] ?? '';
		switch ( $type ) {
			case 'int':
				$v = '' === $v ? '' : (string) absint( $v );
				break;
			case 'money':
				$v = motocred_int_or_null( $v );
				$v = null === $v ? '' : (string) $v;
				break;
			case 'url':
				$v = esc_url_raw( trim( $v ) );
				break;
			case 'bool':
				$v = empty( $v ) ? '' : '1';
				break;
			case 'textarea':
				$v = sanitize_textarea_field( $v );
				break;
			case 'key':
				$v = sanitize_key( $v );
				break;
			case 'float':
				$v = is_numeric( $v ) ? (string) (float) $v : '';
				break;
			case 'path':
				$v = trim( $v ) ? untrailingslashit( strtolower( (string) wp_parse_url( trim( $v ), PHP_URL_PATH ) ) ) : '';
				break;
			default:
				$v = sanitize_text_field( $v );
		}
		update_post_meta( $post_id, '_mc_' . $key, $v );
	}

	if ( 'mc_plan' === $post->post_type ) {
		$old    = (array) get_post_meta( $post_id, '_mc_cuotas', true );
		$cuotas = array();
		foreach ( (array) ( $in['cuotas'] ?? array() ) as $n => $val ) {
			$cuotas[ absint( $n ) ] = motocred_int_or_null( $val );
		}
		if ( $cuotas != $old ) { // phpcs:ignore Universal.Operators.StrictComparisons
			update_post_meta( $post_id, '_mc_actualizado', current_time( 'Y-m-d H:i' ) );
		}
		update_post_meta( $post_id, '_mc_cuotas', $cuotas );
	}
	MotoCred_Data::flush();
}, 10, 2 );

/* =========================================================================
 * Ajustes
 * ========================================================================= */

add_action( 'admin_menu', function () {
	add_menu_page( 'MotoCred', 'MotoCred', 'manage_options', 'motocred', 'motocred_settings_page', 'dashicons-performance', 24 );
	add_submenu_page( 'motocred', 'Ajustes MotoCred', 'Ajustes', 'manage_options', 'motocred', 'motocred_settings_page' );
	add_submenu_page( 'motocred', 'Datos a validar', 'Datos a validar', 'edit_posts', 'motocred-validar', 'motocred_validation_page' );
} );

add_action( 'admin_enqueue_scripts', function () {
	wp_add_inline_style( 'common', '.mc-admin-field{margin:0 0 14px}.mc-settings h2{margin-top:32px}.mc-settings .mc-row input[type=text]{width:100%}.mc-flag{display:inline-block;padding:2px 8px;border-radius:99px;background:#fcf0c3;color:#6b4e00;font-weight:600;font-size:12px}.mc-ok{background:#d7f5dd;color:#135b21}' );
} );

function motocred_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['mc_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mc_settings_nonce'] ) ), 'mc_settings' ) ) {
		motocred_save_settings( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		echo '<div class="notice notice-success"><p>Ajustes guardados.</p></div>';
	}
	$s      = MotoCred_Data::settings();
	$pages  = array( 0 => '— Automático (por slug) —' );
	foreach ( get_pages() as $p ) {
		$pages[ $p->ID ] = $p->post_title . ' (/' . $p->post_name . '/)';
	}
	?>
	<div class="wrap mc-settings">
		<h1>Ajustes MotoCred</h1>
		<p>Todo lo que se carga acá se refleja automáticamente en Home, Planes, Financiación, fichas de motos y Simulador.</p>
		<form method="post">
			<?php wp_nonce_field( 'mc_settings', 'mc_settings_nonce' ); ?>

			<h2>Contacto</h2>
			<?php
			$wa_ok = motocred_phone( $s['whatsapp'] );
			motocred_field( 's[whatsapp]', 'WhatsApp comercial central', $s['whatsapp'], array( 'help' => $s['whatsapp'] && ! $wa_ok ? '<strong style="color:#b32d2e">Número inválido.</strong>' : 'Con código de área, sin 0 ni 15. Ej.: 261 123-4567. Todos los botones de WhatsApp usan este número.' ) );
			motocred_field( 's[whatsapp_saludo]', 'Saludo inicial del mensaje', $s['whatsapp_saludo'] );
			motocred_field( 's[email]', 'Email de contacto', $s['email'], array( 'type' => 'email' ) );
			motocred_field( 's[facebook]', 'Facebook (URL)', $s['facebook'], array( 'type' => 'url', 'class' => 'large-text' ) );
			motocred_field( 's[instagram]', 'Instagram (URL)', $s['instagram'], array( 'type' => 'url', 'class' => 'large-text' ) );
			?>

			<h2>Datos legales (footer y schema)</h2>
			<?php
			motocred_field( 's[razon_social]', 'Razón social', $s['razon_social'] );
			motocred_field( 's[cuit]', 'CUIT', $s['cuit'] );
			motocred_field( 's[aviso_legal]', 'Aviso legal del simulador', $s['aviso_legal'], array( 'type' => 'textarea', 'rows' => 2 ) );
			?>

			<h2>Plazos y adjudicación</h2>
			<p class="description">Fuente: Términos y Condiciones. Si cambian los T&amp;C, actualizá esta tabla y se actualiza toda la web (simulador, FAQ, tabla de Financiación).</p>
			<table class="widefat striped" style="max-width:420px"><thead><tr><th>Cantidad de cuotas</th><th>Cuota de adjudicación</th></tr></thead><tbody>
			<?php
			$plazos = MotoCred_Data::plazos();
			for ( $i = 0; $i < 8; $i++ ) {
				printf(
					'<tr class="mc-row"><td><input type="text" name="plazos[%1$d][n]" value="%2$s"></td><td><input type="text" name="plazos[%1$d][adj]" value="%3$s"></td></tr>',
					$i,
					esc_attr( $plazos[ $i ]['n'] ?? '' ),
					esc_attr( $plazos[ $i ]['adj'] ?? '' )
				);
			}
			?>
			</tbody></table>

			<h2>Cifras de confianza</h2>
			<p class="description">Sólo se muestran al público las cifras marcadas como verificadas. No publicar cifras que no se puedan respaldar.</p>
			<table class="widefat striped" style="max-width:760px"><thead><tr><th>Cifra</th><th>Descripción</th><th>Verificada</th></tr></thead><tbody>
			<?php
			for ( $i = 0; $i < 4; $i++ ) {
				$st = $s['stats'][ $i ] ?? array();
				printf(
					'<tr class="mc-row"><td><input type="text" name="s[stats][%1$d][value]" value="%2$s" placeholder="+1.000"></td><td><input type="text" name="s[stats][%1$d][label]" value="%3$s" placeholder="motos entregadas en 2025"></td><td><input type="checkbox" name="s[stats][%1$d][validado]" value="1" %4$s></td></tr>',
					$i,
					esc_attr( $st['value'] ?? '' ),
					esc_attr( $st['label'] ?? '' ),
					checked( ! empty( $st['validado'] ), true, false )
				);
			}
			?>
			</tbody></table>

			<h2>Preguntas frecuentes</h2>
			<p class="description">Podés usar <code>{plazos}</code> y <code>{adjudicacion}</code>: se reemplazan por los valores de la tabla de plazos.</p>
			<table class="widefat striped"><tbody>
			<?php
			for ( $i = 0; $i < 10; $i++ ) {
				$f = $s['faqs'][ $i ] ?? array();
				printf(
					'<tr class="mc-row"><td style="width:35%%"><input type="text" name="s[faqs][%1$d][q]" value="%2$s" placeholder="Pregunta"></td><td><textarea name="s[faqs][%1$d][a]" rows="2" class="large-text" placeholder="Respuesta">%3$s</textarea></td></tr>',
					$i,
					esc_attr( $f['q'] ?? '' ),
					esc_textarea( $f['a'] ?? '' )
				);
			}
			?>
			</tbody></table>

			<h2>Páginas clave</h2>
			<?php
			foreach ( array( 'simulador' => 'Simulador', 'financiacion' => 'Financiación', 'contacto' => 'Contacto', 'terminos' => 'Términos y condiciones', 'nosotros' => 'Nosotros', 'cotizador' => 'Cotizador de usadas' ) as $k => $label ) {
				motocred_field( 's[page_' . $k . ']', $label, $s[ 'page_' . $k ] ?? 0, array( 'type' => 'select', 'options' => $pages ) );
			}
			?>

			<h2>Medición</h2>
			<p class="description">Si GA4 o el Pixel ya los carga otro plugin, dejá desactivada la carga acá: los eventos de MotoCred igual llegan vía <code>dataLayer</code>, <code>gtag()</code> y <code>fbq()</code>.</p>
			<?php
			motocred_field( 's[gtm_id]', 'Google Tag Manager (GTM-XXXX)', $s['gtm_id'] );
			motocred_field( 's[ga4_id]', 'GA4 Measurement ID (G-XXXX)', $s['ga4_id'] );
			motocred_field( 's[load_gtag]', '', $s['load_gtag'], array( 'type' => 'checkbox', 'cb_label' => 'Cargar gtag.js desde MotoCred (sólo si no hay GTM ni otro plugin de GA)' ) );
			motocred_field( 's[meta_pixel_id]', 'Meta Pixel ID', $s['meta_pixel_id'] );
			motocred_field( 's[load_pixel]', '', $s['load_pixel'], array( 'type' => 'checkbox', 'cb_label' => 'Cargar el Pixel desde MotoCred (sólo si no lo carga otro plugin)' ) );
			?>

			<h2>Redirecciones 301</h2>
			<?php motocred_field( 's[redirects]', 'Una por línea: /url-vieja/ -> /url-nueva/', $s['redirects'], array( 'type' => 'textarea', 'rows' => 6, 'help' => 'Sólo se aplican si la URL vieja da 404: nunca pisan una página existente.' ) ); ?>

			<h2>Imágenes</h2>
			<?php motocred_field( 's[webp_uploads]', '', $s['webp_uploads'], array( 'type' => 'checkbox', 'cb_label' => 'Generar versiones WebP de las imágenes nuevas' ) ); ?>

			<?php submit_button( 'Guardar ajustes' ); ?>
		</form>
	</div>
	<?php
}

function motocred_save_settings( array $post ) {
	$in  = (array) ( $post['s'] ?? array() );
	$out = MotoCred_Data::settings();

	foreach ( array( 'whatsapp', 'whatsapp_saludo', 'razon_social', 'cuit', 'gtm_id', 'ga4_id', 'meta_pixel_id' ) as $k ) {
		$out[ $k ] = sanitize_text_field( $in[ $k ] ?? '' );
	}
	foreach ( array( 'facebook', 'instagram' ) as $k ) {
		$out[ $k ] = esc_url_raw( $in[ $k ] ?? '' );
	}
	$out['email']        = sanitize_email( $in['email'] ?? '' );
	$out['aviso_legal']  = sanitize_textarea_field( $in['aviso_legal'] ?? '' );
	$out['redirects']    = sanitize_textarea_field( $in['redirects'] ?? '' );
	foreach ( array( 'load_gtag', 'load_pixel', 'webp_uploads' ) as $k ) {
		$out[ $k ] = empty( $in[ $k ] ) ? 0 : 1;
	}
	foreach ( array( 'simulador', 'financiacion', 'contacto', 'terminos', 'nosotros', 'cotizador' ) as $k ) {
		$out[ 'page_' . $k ] = absint( $in[ 'page_' . $k ] ?? 0 );
	}

	$out['stats'] = array();
	foreach ( (array) ( $in['stats'] ?? array() ) as $st ) {
		if ( ! empty( $st['value'] ) && ! empty( $st['label'] ) ) {
			$out['stats'][] = array(
				'value'    => sanitize_text_field( $st['value'] ),
				'label'    => sanitize_text_field( $st['label'] ),
				'validado' => ! empty( $st['validado'] ),
			);
		}
	}

	$out['faqs'] = array();
	foreach ( (array) ( $in['faqs'] ?? array() ) as $f ) {
		if ( ! empty( $f['q'] ) && ! empty( $f['a'] ) ) {
			$out['faqs'][] = array( 'q' => sanitize_text_field( $f['q'] ), 'a' => sanitize_textarea_field( $f['a'] ) );
		}
	}

	$plazos = array();
	foreach ( (array) ( $post['plazos'] ?? array() ) as $p ) {
		$n   = absint( $p['n'] ?? 0 );
		$adj = absint( $p['adj'] ?? 0 );
		if ( $n && $adj && $adj <= $n ) {
			$plazos[ $n ] = array( 'n' => $n, 'adj' => $adj );
		}
	}
	if ( $plazos ) {
		update_option( MotoCred_Data::OPT_PLAZOS, array_values( $plazos ) );
	}

	update_option( MotoCred_Data::OPT_SETTINGS, $out );
	MotoCred_Data::flush();
}

/* =========================================================================
 * Panel "Datos a validar" + avisos
 * ========================================================================= */

function motocred_validation_issues() {
	$issues = array();
	$s      = MotoCred_Data::settings();
	if ( ! motocred_phone( $s['whatsapp'] ) ) {
		$issues[] = array( 'Ajustes', 'Falta el WhatsApp comercial central (o es inválido). Los botones de WhatsApp llevan a Contacto.', admin_url( 'admin.php?page=motocred' ) );
	}
	if ( ! $s['razon_social'] || ! $s['cuit'] ) {
		$issues[] = array( 'Ajustes', 'Faltan razón social y/o CUIT para el footer (dato de confianza y obligación de información al consumidor).', admin_url( 'admin.php?page=motocred' ) );
	}
	if ( ! get_page_by_path( 'boton-de-arrepentimiento' ) ) {
		$issues[] = array( 'Legal', 'No existe la página /boton-de-arrepentimiento/. Si se venden o suscriben planes online, verificar con asesoría legal la obligación del «Botón de arrepentimiento» (Res. SCI 424/2020). El footer la enlaza automáticamente al crearla.', admin_url( 'post-new.php?post_type=page' ) );
	}
	foreach ( get_posts( array( 'post_type' => 'mc_plan', 'posts_per_page' => -1, 'post_status' => 'any' ) ) as $p ) {
		if ( ! get_post_meta( $p->ID, '_mc_validado', true ) ) {
			$issues[] = array( 'Plan', $p->post_title . ': valores de cuota sin validar (el público ve «Consultá el valor»).', get_edit_post_link( $p->ID, 'raw' ) );
		}
		if ( ! get_post_meta( $p->ID, '_mc_suscripcion_url', true ) ) {
			$issues[] = array( 'Plan', $p->post_title . ': sin URL de suscripción.', get_edit_post_link( $p->ID, 'raw' ) );
		}
	}
	foreach ( get_posts( array( 'post_type' => 'mc_moto', 'posts_per_page' => -1, 'post_status' => 'any' ) ) as $p ) {
		if ( ! get_post_meta( $p->ID, '_mc_plan', true ) ) {
			$issues[] = array( 'Moto', $p->post_title . ': no tiene plan asignado.', get_edit_post_link( $p->ID, 'raw' ) );
		}
		if ( ! has_post_thumbnail( $p ) ) {
			$issues[] = array( 'Moto', $p->post_title . ': falta la foto (imagen destacada).', get_edit_post_link( $p->ID, 'raw' ) );
		}
		if ( get_post_meta( $p->ID, '_mc_specs', true ) && ! get_post_meta( $p->ID, '_mc_specs_validado', true ) ) {
			$issues[] = array( 'Moto', $p->post_title . ': características sin verificar con la ficha oficial.', get_edit_post_link( $p->ID, 'raw' ) );
		}
	}
	foreach ( get_posts( array( 'post_type' => 'mc_sucursal', 'posts_per_page' => -1, 'post_status' => 'any' ) ) as $p ) {
		$tel = get_post_meta( $p->ID, '_mc_telefono', true );
		if ( $tel && ! motocred_phone( $tel ) ) {
			$issues[] = array( 'Sucursal', $p->post_title . ': teléfono inválido «' . $tel . '».', get_edit_post_link( $p->ID, 'raw' ) );
		}
		if ( ! get_post_meta( $p->ID, '_mc_validado', true ) ) {
			$issues[] = array( 'Sucursal', $p->post_title . ': dirección/estado sin validar.', get_edit_post_link( $p->ID, 'raw' ) );
		}
		if ( ! get_post_meta( $p->ID, '_mc_localidad', true ) ) {
			$issues[] = array( 'Sucursal', $p->post_title . ': falta la localidad.', get_edit_post_link( $p->ID, 'raw' ) );
		}
	}
	return $issues;
}

function motocred_validation_page() {
	$issues = motocred_validation_issues();
	echo '<div class="wrap"><h1>Datos a validar</h1>';
	echo '<p>Antes de publicar, cada dato comercial debe revisarse contra el tarifario vigente y los Términos y Condiciones. Los datos sin validar <strong>no se muestran al público</strong> (se ven sólo logueado, con la etiqueta «A validar»).</p>';
	if ( ! $issues ) {
		echo '<p><span class="mc-flag mc-ok">Todo validado</span></p></div>';
		return;
	}
	echo '<table class="widefat striped"><thead><tr><th>Tipo</th><th>Pendiente</th><th></th></tr></thead><tbody>';
	foreach ( $issues as $i ) {
		printf( '<tr><td><span class="mc-flag">%s</span></td><td>%s</td><td><a href="%s">Editar</a></td></tr>', esc_html( $i[0] ), esc_html( $i[1] ), esc_url( $i[2] ) );
	}
	echo '</tbody></table></div>';
}

add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( ! $screen || ! current_user_can( 'edit_posts' ) || 'dashboard' !== $screen->id ) {
		return;
	}
	$n = count( motocred_validation_issues() );
	if ( $n ) {
		printf(
			'<div class="notice notice-warning"><p><strong>MotoCred:</strong> hay %d datos comerciales pendientes de validación. <a href="%s">Revisarlos</a></p></div>',
			(int) $n,
			esc_url( admin_url( 'admin.php?page=motocred-validar' ) )
		);
	}
} );

/* Columnas útiles en los listados */
add_filter( 'manage_mc_plan_posts_columns', fn( $c ) => array_slice( $c, 0, 2 ) + array( 'mc_desde' => 'Cuota desde', 'mc_ok' => 'Validado', 'mc_upd' => 'Actualizado' ) + $c );
add_action( 'manage_mc_plan_posts_custom_column', function ( $col, $id ) {
	$p = MotoCred_Data::plan( $id );
	if ( 'mc_desde' === $col ) {
		echo esc_html( $p['desde'] ? motocred_money( $p['desde'] ) : '—' );
	} elseif ( 'mc_ok' === $col ) {
		echo $p['validado'] ? '<span class="mc-flag mc-ok">Sí</span>' : '<span class="mc-flag">A validar</span>';
	} elseif ( 'mc_upd' === $col ) {
		echo esc_html( $p['actualizado'] ?: '—' );
	}
}, 10, 2 );
