/**
 * MotoCred: simulador en vivo + medición de conversiones.
 * Sin dependencias (no requiere jQuery). ~5 KB.
 */
(function () {
	'use strict';

	var D = window.MOTOCRED || { planes: [], plazos: [], motos: [] };

	/* ------------------------------------------------------------------
	 * Atribución: primer origen del lead (UTM / referrer), por sesión.
	 * ---------------------------------------------------------------- */
	function store(key, value) {
		try {
			if (value === undefined) { return JSON.parse(localStorage.getItem(key) || 'null'); }
			localStorage.setItem(key, JSON.stringify(value));
		} catch (e) { return null; }
	}

	var origin = store('mc_origin');
	if (!origin) {
		var q = new URLSearchParams(location.search);
		var ref = document.referrer && document.referrer.indexOf(location.host) === -1 ? document.referrer : '';
		origin = {
			source: q.get('utm_source') || (q.get('gclid') ? 'google' : q.get('fbclid') ? 'facebook' : (ref ? new URL(ref).hostname : 'directo')),
			medium: q.get('utm_medium') || (q.get('gclid') ? 'cpc' : ref ? 'referral' : '(none)'),
			campaign: q.get('utm_campaign') || '',
			landing: location.pathname
		};
		store('mc_origin', origin);
	}

	/* ------------------------------------------------------------------
	 * Envío de eventos: dataLayer (GTM/GA4) + gtag + Meta Pixel.
	 * ---------------------------------------------------------------- */
	var FB_MAP = {
		whatsapp_click: ['track', 'Contact'],
		contact_click: ['track', 'Contact'],
		sucursal_llamada: ['track', 'Contact'],
		suscripcion_click: ['track', 'InitiateCheckout'],
		form_submit: ['track', 'Lead'],
		simulador_completo: ['trackCustom', 'SimulacionCompleta']
	};

	function track(event, params) {
		var p = Object.assign({
			page_path: location.pathname,
			lead_source: origin.source,
			lead_medium: origin.medium,
			lead_campaign: origin.campaign,
			lead_landing: origin.landing
		}, params || {});
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push(Object.assign({ event: event }, p));
		if (typeof window.gtag === 'function') { window.gtag('event', event, p); }
		if (typeof window.fbq === 'function' && FB_MAP[event]) {
			window.fbq(FB_MAP[event][0], FB_MAP[event][1], { content_name: p.plan || p.modelo || p.intent || event });
		}
	}
	window.motocredTrack = track;

	document.addEventListener('click', function (e) {
		var el = e.target.closest('[data-mc-event]');
		if (!el) { return; }
		var params = {};
		try { params = JSON.parse(el.getAttribute('data-mc-params') || '{}'); } catch (err) {}
		track(el.getAttribute('data-mc-event'), params);
	});

	// Formularios existentes (Contact Form 7, Elementor, WPForms, Gravity Forms).
	document.addEventListener('wpcf7mailsent', function (e) {
		track('form_submit', { form_plugin: 'cf7', form_id: String(e.detail && e.detail.contactFormId || '') });
	});
	window.addEventListener('load', function () {
		var $ = window.jQuery;
		if (!$) { return; }
		$(document).on('submit_success', function (e) { track('form_submit', { form_plugin: 'elementor', form_id: e.target && e.target.id || '' }); });
		$(document).on('wpformsAjaxSubmitSuccess', function (e) { track('form_submit', { form_plugin: 'wpforms', form_id: e.target && e.target.id || '' }); });
		$(document).on('gform_confirmation_loaded', function (e, id) { track('form_submit', { form_plugin: 'gravity', form_id: String(id) }); });
	});

	/* ------------------------------------------------------------------
	 * Mapas: se cargan sólo al tocar (no penalizan la carga inicial).
	 * ---------------------------------------------------------------- */
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-mc-map-load]');
		if (!btn) { return; }
		var box = btn.closest('[data-mc-map]');
		var f = document.createElement('iframe');
		f.src = box.getAttribute('data-mc-map');
		f.loading = 'lazy';
		f.title = btn.getAttribute('aria-label') || 'Mapa';
		f.referrerPolicy = 'no-referrer-when-downgrade';
		box.replaceChildren(f);
	});

	/* ------------------------------------------------------------------
	 * Simulador
	 * ---------------------------------------------------------------- */
	var nf = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 0 });
	function money(v) { return '$ ' + nf.format(v); }
	function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
	function byId(list, id) { id = parseInt(id, 10); for (var i = 0; i < list.length; i++) { if (list[i].id === id) { return list[i]; } } return null; }
	function plazo(n) { n = parseInt(n, 10); for (var i = 0; i < D.plazos.length; i++) { if (+D.plazos[i].n === n) { return D.plazos[i]; } } return null; }

	var ICON = {
		pesos: '<svg class="mc-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M14.5 9.2c-.5-.8-1.4-1.2-2.5-1.2-1.4 0-2.5.8-2.5 2s1.1 1.6 2.5 2 2.5.8 2.5 2-1.1 2-2.5 2c-1.1 0-2-.4-2.5-1.2M12 6.5V8M12 16v1.5"/></svg>',
		key: '<svg class="mc-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="15" r="4"/><path d="M11 12l8-8M16 7l2 2M14 9l2 2"/></svg>',
		plans: '<svg class="mc-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2.5"/><path d="M3 9h18M8 14h3M8 17h6"/></svg>'
	};

	function resultHTML(plan, n, moto) {
		var cuota = plan.cuotas[n];
		var pz = plazo(n);
		var label = moto ? moto.nombre + ' · ' + plan.nombre : plan.nombre;
		var badge = D.preview && !plan.validado ? ' <span class="mc-validate">A validar</span>' : '';
		var h = '<p class="mc-sim__plan">' + esc(label) + badge + '</p>';
		if (cuota !== null && cuota !== undefined) {
			h += '<p class="mc-sim__amount"><span class="mc-sim__n">' + n + ' cuotas fijas de</span><strong>' + money(cuota) + '</strong></p>';
		} else {
			h += '<p class="mc-sim__amount mc-sim__amount--empty"><strong>Valor a confirmar</strong><span class="mc-sim__n">Un asesor te pasa la cuota actualizada por WhatsApp.</span></p>';
		}
		h += '<ul class="mc-sim__facts"><li>' + ICON.pesos + 'Cuotas fijas en pesos</li>';
		if (pz) { h += '<li>' + ICON.key + 'Adjudicación en la cuota ' + pz.adj + '</li>'; }
		if (plan.gastos !== null && plan.gastos !== undefined) { h += '<li>' + ICON.plans + 'Gastos de suscripción: ' + money(plan.gastos) + '</li>'; }
		return h + '</ul>';
	}

	function waMessage(plan, n, moto) {
		var cuota = plan.cuotas[n];
		var que = moto ? 'la ' + moto.nombre + ' (' + plan.nombre + ')' : 'el ' + plan.nombre;
		var m = (D.saludo || 'Hola MotoCred') + ', simulé ' + que + ' en ' + n + ' cuotas';
		if (cuota !== null && cuota !== undefined) { m += ' de ' + money(cuota); }
		return m + '. Quiero avanzar con la compra.\n\n(Simulación desde la web)';
	}

	function initSim(form) {
		var result = form.querySelector('[data-mc-sim-result]');
		var wa = form.querySelector('[data-mc-sim-wa]');
		var next = form.querySelector('[data-mc-sim-next]');
		var motoSel = form.querySelector('select[data-mc-sim-moto]');
		var motoHidden = form.querySelector('input[data-mc-sim-moto]');
		var ubicacion = form.getAttribute('data-ubicacion') || 'simulador';
		var started = false;
		var done = {};

		function val(name) { var el = form.querySelector('input[name="' + name + '"]:checked'); return el ? el.value : null; }

		function update(fromUser) {
			var plan = byId(D.planes, val('plan'));
			var n = val('plazo');
			if (!plan || !n) { return; }
			var motoId = motoSel ? motoSel.value : (motoHidden ? motoHidden.value : '');
			var moto = motoId ? byId(D.motos, motoId) : null;
			if (moto && moto.plan !== plan.id) { moto = null; if (motoSel) { motoSel.value = ''; } }

			result.innerHTML = resultHTML(plan, n, moto);

			if (wa && D.wa) {
				wa.href = 'https://wa.me/' + D.wa + '?text=' + encodeURIComponent(waMessage(plan, n, moto));
				wa.setAttribute('data-mc-params', JSON.stringify({ intent: 'simulacion', plan: plan.nombre, plazo: +n, modelo: moto ? moto.nombre : undefined, ubicacion: ubicacion }));
			}
			if (next) {
				next.href = plan.suscripcion || plan.url;
				next.querySelector('span').textContent = plan.suscripcion ? 'Suscribirme a este plan' : 'Ver detalle del plan';
				next.setAttribute('data-mc-event', plan.suscripcion ? 'suscripcion_click' : 'consulta_plan');
				next.setAttribute('data-mc-params', JSON.stringify({ plan: plan.nombre, ubicacion: ubicacion }));
			}

			if (fromUser) {
				if (!started) { started = true; track('simulador_inicio', { ubicacion: ubicacion }); }
				var key = plan.id + '-' + n + '-' + (moto ? moto.id : '');
				if (!done[key]) {
					done[key] = true;
					track('simulador_completo', { ubicacion: ubicacion, plan: plan.nombre, plazo: +n, modelo: moto ? moto.nombre : undefined, cuota_informada: plan.cuotas[n] != null });
				}
				if (ubicacion === 'simulador' && history.replaceState) {
					var u = new URL(location.href);
					u.searchParams.set('plan', plan.id);
					u.searchParams.set('plazo', n);
					if (moto) { u.searchParams.set('moto', moto.id); } else { u.searchParams.delete('moto'); }
					history.replaceState(null, '', u);
				}
			}
		}

		form.addEventListener('change', function (e) {
			if (e.target === motoSel && motoSel.value) {
				var opt = motoSel.options[motoSel.selectedIndex];
				var r = form.querySelector('input[name="plan"][value="' + opt.getAttribute('data-plan') + '"]');
				if (r) { r.checked = true; }
			}
			update(true);
		});
		form.addEventListener('submit', function (e) { e.preventDefault(); update(true); });

		// Preselección desde la URL también en el navegador: funciona aunque un
		// plugin de caché sirva la página sin procesar ?plan= / ?plazo= / ?moto=.
		var qs = new URLSearchParams(location.search);
		var qMoto = qs.get('moto') ? byId(D.motos, qs.get('moto')) : null;
		var qPlan = qs.get('plan') || (qMoto ? qMoto.plan : null);
		var rPlan = qPlan && form.querySelector('input[name="plan"][value="' + parseInt(qPlan, 10) + '"]');
		var rPlazo = qs.get('plazo') && form.querySelector('input[name="plazo"][value="' + parseInt(qs.get('plazo'), 10) + '"]');
		if (rPlan) { rPlan.checked = true; }
		if (rPlazo) { rPlazo.checked = true; }
		if (qMoto && motoSel) { motoSel.value = String(qMoto.id); }
		update(false);
	}

	function boot() {
		document.querySelectorAll('[data-mc-sim]').forEach(initSim);
	}
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
