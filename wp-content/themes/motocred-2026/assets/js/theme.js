/**
 * MotoCred 2026 · interacciones del theme (sin dependencias, ~2 KB).
 */
(function () {
	'use strict';

	var header = document.querySelector('[data-mc-header]');

	/* Header con borde al hacer scroll */
	if (header) {
		var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	/* Menú mobile */
	var toggle = document.querySelector('[data-mc-menu]');
	var drawer = document.getElementById('mc-drawer');
	function setMenu(open) {
		toggle.setAttribute('aria-expanded', String(open));
		drawer.hidden = !open;
		header.classList.toggle('is-open', open);
		document.body.classList.toggle('mc-lock', open);
	}
	if (toggle && drawer) {
		toggle.addEventListener('click', function () { setMenu(toggle.getAttribute('aria-expanded') !== 'true'); });
		drawer.addEventListener('click', function (e) { if (e.target.closest('a')) { setMenu(false); } });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !drawer.hidden) { setMenu(false); toggle.focus(); } });
		window.matchMedia('(min-width: 1024px)').addEventListener('change', function (m) { if (m.matches) { setMenu(false); } });
	}

	/* Catálogo: filtros en el cliente */
	var filters = document.querySelector('[data-mc-filters]');
	var list = document.querySelector('[data-mc-list]');
	if (filters && list) {
		var state = { cc: '', marca: '', tipo: '' };
		var cards = Array.prototype.slice.call(list.children);
		var original = cards.slice();
		var count = filters.querySelector('[data-mc-count]');
		var empty = document.querySelector('[data-mc-empty]');

		var apply = function () {
			var n = 0;
			cards.forEach(function (c) {
				var ok = (!state.cc || c.dataset.cc === state.cc) &&
					(!state.marca || c.dataset.marca === state.marca) &&
					(!state.tipo || c.dataset.tipo === state.tipo);
				c.hidden = !ok;
				if (ok) { n++; }
			});
			if (count) { count.textContent = n + (n === 1 ? ' moto' : ' motos'); }
			if (empty) { empty.hidden = n > 0; }
			if (window.motocredTrack && (state.cc || state.marca || state.tipo)) {
				window.motocredTrack('catalogo_filtro', { cc: state.cc || undefined, marca: state.marca || undefined, tipo: state.tipo || undefined, resultados: n });
			}
		};

		filters.addEventListener('click', function (e) {
			var chip = e.target.closest('.mc-filter-chip');
			if (!chip) { return; }
			filters.querySelectorAll('.mc-filter-chip[data-filter="' + chip.dataset.filter + '"]').forEach(function (b) {
				b.classList.toggle('is-active', b === chip);
				b.setAttribute('aria-pressed', String(b === chip));
			});
			state[chip.dataset.filter] = chip.dataset.value;
			apply();
		});
		filters.addEventListener('change', function (e) {
			var el = e.target;
			if (el.matches('select[data-filter]')) { state[el.dataset.filter] = el.value; apply(); }
			if (el.matches('select[data-sort]')) {
				var v = el.value;
				var sorted = original.slice();
				var num = function (c, k) { var x = parseFloat(c.dataset[k]); return isNaN(x) ? Infinity : x; };
				if (v === 'desde-asc') { sorted.sort(function (a, b) { return num(a, 'desde') - num(b, 'desde'); }); }
				if (v === 'cc-asc') { sorted.sort(function (a, b) { return num(a, 'cc') - num(b, 'cc'); }); }
				if (v === 'cc-desc') { sorted.sort(function (a, b) { return num(b, 'cc') - num(a, 'cc'); }); }
				sorted.forEach(function (c) { list.appendChild(c); });
				cards = sorted;
			}
		});
		var reset = document.querySelector('[data-mc-reset]');
		if (reset) {
			reset.addEventListener('click', function () {
				state = { cc: '', marca: '', tipo: '' };
				filters.querySelectorAll('select[data-filter]').forEach(function (s) { s.value = ''; });
				filters.querySelectorAll('.mc-filter-chip').forEach(function (b) { var on = b.dataset.value === ''; b.classList.toggle('is-active', on); b.setAttribute('aria-pressed', String(on)); });
				apply();
			});
		}
	}

	/* Galería de ficha: indicador de foto actual */
	var gallery = document.querySelector('[data-mc-gallery]');
	var dots = document.querySelectorAll('.mc-gallery__dots span');
	if (gallery && dots.length) {
		var mark = function () {
			var i = Math.round(gallery.scrollLeft / gallery.clientWidth);
			dots.forEach(function (d, j) { d.classList.toggle('is-active', i === j); });
		};
		mark();
		gallery.addEventListener('scroll', mark, { passive: true });
	}

	/* Sub-navegación: marca la sección visible */
	var subnav = document.querySelectorAll('.mc-subnav a');
	if (subnav.length && 'IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting) {
					subnav.forEach(function (a) { a.classList.toggle('is-active', a.getAttribute('href') === '#' + en.target.id); });
				}
			});
		}, { rootMargin: '-40% 0px -55% 0px' });
		subnav.forEach(function (a) { var t = document.querySelector(a.getAttribute('href')); if (t) { io.observe(t); } });
	}

})();
