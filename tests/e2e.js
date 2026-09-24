/**
 * Pruebas E2E de MotoCred contra un WordPress local (ver tools/dev-setup.sh).
 *
 *   BASE=http://127.0.0.1:8080 node tests/e2e.js           # modo demo (MOTOCRED_DEMO = true)
 *   BASE=... MODE=public node tests/e2e.js                   # producción (MOTOCRED_DEMO = false)
 *
 * Sin frameworks: Playwright + assert. Sale con código 1 si algo falla.
 */
const { chromium } = require('playwright');
const assert = require('node:assert/strict');

const BASE = process.env.BASE || 'http://127.0.0.1:8080';
const MODE = process.env.MODE || 'demo';
const results = [];

async function test(name, fn) {
	try {
		await fn();
		results.push(['ok', name]);
	} catch (e) {
		results.push(['FAIL', name, e.message.split('\n')[0]]);
	}
}

function jsonLd(html) {
	const out = [];
	const re = /<script type="application\/ld\+json">([\s\S]*?)<\/script>/g;
	let m;
	while ((m = re.exec(html))) {
		const data = JSON.parse(m[1]);
		out.push(...(data['@graph'] || [data]));
	}
	return out;
}

(async () => {
	const browser = await chromium.launch();
	const mobile = await browser.newContext({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
	const desktop = await browser.newContext({ viewport: { width: 1440, height: 900 } });
	const page = await mobile.newPage();
	const errors = [];
	page.on('pageerror', (e) => errors.push(e.message));

	await test('Home carga con H1 único y CTA de simular', async () => {
		await page.goto(BASE + '/', { waitUntil: 'networkidle' });
		assert.equal(await page.locator('h1').count(), 1);
		assert.match(await page.locator('h1').innerText(), /0KM/);
		assert.ok(await page.locator('.mc-hero [data-mc-event="cta_simular_click"]').count() >= 1);
		assert.ok(await page.locator('.mc-tabbar').isVisible(), 'tab bar visible en mobile');
	});

	if (MODE === 'demo') {
		await test('Simulador: cambia la cuota, el WhatsApp y mide inicio/completo', async () => {
			const sim = page.locator('.mc-hero [data-mc-sim]');
			const before = await sim.locator('.mc-sim__amount strong').innerText();
			await sim.locator('.mc-chip', { hasText: '150 cc' }).click();
			await sim.locator('.mc-segment__opt', { hasText: '12' }).click();
			const after = await sim.locator('.mc-sim__amount strong').innerText();
			assert.notEqual(before, after);
			assert.match(after, /^\$ [\d.]+$/);
			const href = decodeURIComponent(await sim.locator('[data-mc-sim-wa]').getAttribute('href'));
			assert.match(href, /^https:\/\/wa\.me\/549\d{10}\?text=/);
			assert.match(href, /Plan 150 en 12 cuotas de \$ [\d.]+/);
			const events = await page.evaluate(() => window.dataLayer.map((e) => e.event).filter(Boolean));
			assert.ok(events.includes('simulador_inicio'), 'simulador_inicio');
			assert.ok(events.includes('simulador_completo'), 'simulador_completo');
			const last = await page.evaluate(() => window.dataLayer.filter((e) => e.event === 'simulador_completo').pop());
			assert.equal(last.plan, 'Plan 150');
			assert.equal(last.plazo, 12);
			assert.ok(last.lead_source, 'origen del lead');
		});

		await test('Click en WhatsApp registra whatsapp_click con contexto', async () => {
			// Evita salir de la página: el tracking corre antes (listener en document).
			await page.evaluate(() => window.addEventListener('click', (e) => { if (e.target.closest('a[href*="wa.me"]')) { e.preventDefault(); } }));
			await page.locator('.mc-hero [data-mc-sim-wa]').click();
			const ev = await page.evaluate(() => window.dataLayer.filter((e) => e.event === 'whatsapp_click').pop());
			assert.ok(ev, 'evento whatsapp_click');
			assert.equal(ev.intent, 'simulacion');
			assert.equal(ev.plan, 'Plan 150');
			assert.equal(ev.ubicacion, 'home_hero');
		});
	}

	await test('Menú mobile abre y cierra', async () => {
		await page.goto(BASE + '/', { waitUntil: 'networkidle' });
		const btn = page.locator('[data-mc-menu]');
		await btn.click();
		assert.ok(await page.locator('#mc-drawer').isVisible());
		assert.equal(await btn.getAttribute('aria-expanded'), 'true');
		await page.keyboard.press('Escape');
		assert.ok(await page.locator('#mc-drawer').isHidden());
	});

	await test('Catálogo: filtro por cilindrada', async () => {
		await page.goto(BASE + '/motos/', { waitUntil: 'networkidle' });
		const total = await page.locator('[data-mc-list] > article').count();
		assert.ok(total >= 2);
		await page.locator('.mc-filter-chip', { hasText: '150 cc' }).click();
		assert.equal(await page.locator('[data-mc-list] > article:visible').count(), 1);
		assert.match(await page.locator('[data-mc-count]').innerText(), /^1 moto$/);
		assert.ok(await page.locator('[data-mc-empty]').isHidden());
	});

	await test('Ficha de moto: WhatsApp identifica el modelo y schema Product/Breadcrumb', async () => {
		const res = await page.goto(BASE + '/motos/honda-wave-110/', { waitUntil: 'networkidle' });
		const html = await res.text();
		const wa = page.locator('.mc-product__cta a.mc-btn--wa');
		if (MODE === 'demo') {
			const href = decodeURIComponent(await wa.getAttribute('href'));
			assert.match(href, /consultar por la Honda Wave 110 \(Plan 110\)/);
		}
		const types = jsonLd(html).map((n) => n['@type']);
		assert.ok(types.includes('Product'), 'Product');
		assert.ok(types.includes('BreadcrumbList'), 'BreadcrumbList');
		const product = jsonLd(html).find((n) => n['@type'] === 'Product');
		assert.equal(product.offers, undefined, 'sin precio validado no hay Offer');
	});

	await test('Simulador sin JavaScript (GET) calcula en el servidor', async () => {
		const ctx = await browser.newContext({ javaScriptEnabled: false });
		const p = await ctx.newPage();
		await p.goto(BASE + '/simulador/', { waitUntil: 'domcontentloaded' });
		const planId = await p.locator('input[name=plan]').nth(1).getAttribute('value');
		await p.goto(`${BASE}/simulador/?plan=${planId}&plazo=18`, { waitUntil: 'domcontentloaded' });
		assert.equal(await p.locator('input[name=plazo]:checked').getAttribute('value'), '18');
		assert.equal(await p.locator('input[name=plan]:checked').getAttribute('value'), planId);
		const txt = await p.locator('[data-mc-sim-result]').innerText();
		assert.match(txt, MODE === 'demo' ? /18 cuotas fijas de/ : /Valor a confirmar/);
		await ctx.close();
	});

	await test('Redirección 301 desde URL vieja de WooCommerce', async () => {
		const r = await page.request.get(BASE + '/tienda-2/honda/honda-wave-110-cc/', { maxRedirects: 0 });
		assert.equal(r.status(), 301);
		assert.match(r.headers().location, /\/motos\/honda-wave-110\/$/);
	});

	await test('Home: FAQPage y metadatos SEO', async () => {
		const r = await page.request.get(BASE + '/');
		const html = await r.text();
		const types = jsonLd(html).map((n) => n['@type']);
		assert.ok(types.includes('FAQPage'), 'FAQPage');
		assert.ok(types.includes('Organization'), 'Organization');
		assert.match(html, /<meta name="description" content="[^"]{50,}/);
		assert.match(html, /<meta property="og:title"/);
		assert.match(html, /<title>Motos 0KM en cuotas fijas en Mendoza/);
		assert.match(html, /<html lang="es/);
	});

	if (MODE === 'public') {
		await test('Público: ningún importe sin validar se publica', async () => {
			for (const u of ['/', '/planes/', '/financiacion/', '/motos/', '/planes/plan-125/', '/suscribete-plan125/']) {
				const html = await (await page.request.get(BASE + u)).text();
				assert.doesNotMatch(html, /\$ ?\d{2,3}\.\d{3}/, 'importe visible en ' + u);
				assert.doesNotMatch(html, /A validar/, 'badge interno visible en ' + u);
				assert.doesNotMatch(html, /"cuotas":\{"12":\d/, 'importe en JSON del simulador en ' + u);
			}
		});
		await test('Público: sin WhatsApp configurado, los botones van a Contacto', async () => {
			const html = await (await page.request.get(BASE + '/')).text();
			assert.doesNotMatch(html, /wa\.me\/5492610000000/);
		});
	}

	await test('Sin enlaces internos rotos (crawl)', async () => {
		const seen = new Set();
		const queue = ['/'];
		const broken = [];
		while (queue.length && seen.size < 120) {
			const u = queue.shift();
			if (seen.has(u)) { continue; }
			seen.add(u);
			const r = await page.request.get(BASE + u, { maxRedirects: 5 });
			if (r.status() >= 400) { broken.push(u + ' → ' + r.status()); continue; }
			const ct = r.headers()['content-type'] || '';
			if (!ct.includes('text/html')) { continue; }
			const html = await r.text();
			for (const m of html.matchAll(/href="([^"#]+)/g)) {
				let href = m[1].replace(/&amp;/g, '&');
				if (href.startsWith(BASE)) { href = href.slice(BASE.length) || '/'; }
				if (!href.startsWith('/') || href.startsWith('//')) { continue; }
				if (/wp-(admin|login|json)|xmlrpc|\/feed\/|\?/.test(href)) { continue; }
				if (/\.(css|js|woff2|png|jpe?g|webp|svg|ico)$/.test(href.split('?')[0])) { continue; }
				if (!seen.has(href)) { queue.push(href); }
			}
		}
		assert.deepEqual(broken, [], 'rotos: ' + broken.join(', '));
		results.push(['info', `páginas recorridas: ${seen.size}`]);
	});

	await test('Desktop: sin overflow horizontal y tab bar oculta', async () => {
		const d = await desktop.newPage();
		for (const u of ['/', '/motos/', '/planes/', '/financiacion/', '/simulador/', '/sucursales/']) {
			await d.goto(BASE + u, { waitUntil: 'networkidle' });
			assert.ok(!(await d.evaluate(() => document.documentElement.scrollWidth > innerWidth)), 'overflow ' + u);
			assert.ok(await d.locator('.mc-tabbar').isHidden());
		}
	});

	await test('Mobile: sin overflow horizontal y tap targets >= 40px', async () => {
		for (const u of ['/', '/motos/', '/motos/honda-wave-110/', '/planes/', '/financiacion/', '/simulador/', '/sucursales/']) {
			await page.goto(BASE + u, { waitUntil: 'networkidle' });
			assert.ok(!(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)), 'overflow ' + u);
			const small = await page.evaluate(() => [...document.querySelectorAll('.mc-btn, .mc-tabbar__item, .mc-chip span, .mc-segment__opt span')]
				.filter((el) => el.offsetParent && el.getBoundingClientRect().height < 40).map((el) => el.textContent.trim()));
			assert.deepEqual(small, [], u);
		}
	});

	await test('Sin errores de JavaScript', async () => {
		assert.deepEqual(errors, []);
	});

	await browser.close();
	let fail = 0;
	for (const r of results) {
		if (r[0] === 'FAIL') { fail++; }
		console.log(r[0].padEnd(4), r[1], r[2] ? '— ' + r[2] : '');
	}
	console.log(fail ? `\n${fail} prueba(s) fallaron` : '\nTodas las pruebas pasaron');
	process.exit(fail ? 1 : 0);
})();
