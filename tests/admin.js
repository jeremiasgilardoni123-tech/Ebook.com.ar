/**
 * Flujo de administración: la fuente única de datos.
 * Carga una cuota en el Plan 125 desde el admin, la valida y verifica que el
 * mismo valor aparezca en Home, Planes, Financiación, el simulador y el shortcode
 * de la página de suscripción. Luego la des-valida y verifica que desaparezca.
 *
 *   BASE=http://127.0.0.1:8080 USER=admin PASS=admin node tests/admin.js
 */
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const BASE = process.env.BASE || 'http://127.0.0.1:8080';

(async () => {
	const b = await chromium.launch();
	const ctx = await b.newContext();
	const p = await ctx.newPage();
	await p.goto(BASE + '/wp-login.php');
	await p.fill('#user_login', process.env.USER_WP || 'admin');
	await p.fill('#user_pass', process.env.PASS_WP || 'admin');
	await p.click('#wp-submit');
	await p.waitForURL(/wp-admin/);

	// Panel "Datos a validar"
	await p.goto(BASE + '/wp-admin/admin.php?page=motocred-validar');
	const pend = await p.locator('table tbody tr').count();
	assert.ok(pend > 0, 'hay pendientes');
	console.log('ok   Panel "Datos a validar":', pend, 'pendientes');

	// Ajustes: WhatsApp central
	await p.goto(BASE + '/wp-admin/admin.php?page=motocred');
	await p.fill('input[name="s[whatsapp]"]', '261 111-2233');
	await p.click('#submit');
	await p.waitForSelector('.notice-success');
	console.log('ok   Ajustes guardados');

	// Editar Plan 125
	await p.goto(BASE + '/wp-admin/edit.php?post_type=mc_plan');
	await p.click('a.row-title:has-text("Plan 125")');
	await p.waitForSelector('#mc_plan_data');
	await p.fill('input[name="mc[cuotas][12]"]', '123.456');
	await p.check('input[name="mc[validado]"]');
	const publish = p.locator('#publish, .editor-post-publish-button');
	await publish.first().click();
	await p.waitForLoadState('networkidle');
	await p.waitForTimeout(1500);
	console.log('ok   Plan 125 guardado con cuota 12 = 123456 y validado');

	// Anónimo: el valor aparece en todas partes
	const anon = await b.newContext();
	const a = await anon.newPage();
	for (const u of ['/planes/', '/planes/plan-125/', '/financiacion/', '/suscribete-plan125/']) {
		const html = await (await a.request.get(BASE + u)).text();
		assert.match(html, /\$ 123\.456/, 'valor en ' + u);
		assert.doesNotMatch(html, /A validar/, 'sin badge en ' + u);
	}
	// Home: el dato viaja al simulador (JSON) y la tarjeta muestra el "desde" del plan.
	const home = await (await a.request.get(BASE + '/')).text();
	assert.match(home, /"nombre":"Plan 125"[^}]*"cuotas":\{"12":123456/);
	await a.goto(BASE + '/simulador/', { waitUntil: 'networkidle' });
	await a.locator('.mc-chip', { hasText: '125 cc' }).click();
	await a.locator('.mc-segment__opt', { hasText: '12' }).click();
	assert.equal(await a.locator('.mc-sim__amount strong').innerText(), '$ 123.456');
	const wa = decodeURIComponent(await a.locator('[data-mc-sim-wa]').getAttribute('href'));
	assert.match(wa, /^https:\/\/wa\.me\/5492611112233\?text=.*Plan 125 en 12 cuotas de \$ 123\.456/);
	console.log('ok   El mismo valor aparece en Home (simulador), Planes, detalle, Financiación, suscripción (shortcode) y simulador + WhatsApp');

	// Des-validar: desaparece del público
	await p.reload();
	await p.waitForSelector('#mc_plan_data');
	await p.uncheck('input[name="mc[validado]"]');
	await p.locator('#publish, .editor-post-publish-button').first().click();
	await p.waitForLoadState('networkidle');
	await p.waitForTimeout(1500);
	const html = await (await a.request.get(BASE + '/planes/')).text();
	assert.doesNotMatch(html, /123\.456/);
	console.log('ok   Al quitar la validación el valor deja de publicarse');

	await b.close();
})().catch((e) => { console.error('FAIL', e.message); process.exit(1); });
