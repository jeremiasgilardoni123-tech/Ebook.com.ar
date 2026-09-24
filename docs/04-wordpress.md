# 4. WordPress: análisis, decisión e implementación

## 4.1 Relevamiento técnico pendiente

No hubo acceso al admin ni al HTML en vivo (ver [01-auditoria.md](01-auditoria.md#10-cómo-se-hizo-y-qué-límites-tiene)). Antes de instalar, completar esto (≈30 min con acceso de administrador):

| Qué | Cómo verlo | Por qué importa |
|---|---|---|
| Versión de WP y PHP | *Herramientas → Salud del sitio → Información* | Requisito: WP ≥ 6.4, PHP ≥ 8.0 |
| Theme actual (¿hijo?, ¿Astra/Hello/Divi?) | *Apariencia → Temas* | Guardar su configuración antes de cambiar |
| Page builder | *Plugins* (Elementor, WPBakery, Divi) | `page.php` respeta páginas de Elementor a ancho completo |
| WooCommerce y pasarela (Mercado Pago, etc.) | *Plugins*, *WooCommerce → Ajustes → Pagos* | Las suscripciones siguen funcionando igual: el sitio nuevo sólo enlaza a esas páginas |
| Formularios (Contact Form 7, WPForms, Elementor Forms, Gravity) | *Plugins* | Sus envíos se miden solos como `form_submit` |
| SEO (Yoast, Rank Math…) | *Plugins* | Si hay uno, MotoCred no duplica títulos/metas/OG: sólo agrega schema propio |
| Analytics / GTM / Pixel | Ver código fuente: buscar `gtag(`, `GTM-`, `fbq(` | Para no cargarlos dos veces (ver [05-medicion.md](05-medicion.md)) |
| Caché / CDN | *Plugins* (WP Rocket, LiteSpeed, Cloudflare) | Purgar tras publicar; excluir `/wp-admin` |
| Páginas "Simulador de crédito", "Planes de Cuotas MC" y suscripciones 110/150/160/200 | *Páginas* | Conectarlas en *MotoCred → Ajustes → Páginas clave* y en cada Plan |
| Menús | *Apariencia → Menús* | Asignar al "Menú principal" o dejar el menú por defecto del theme |
| Core Web Vitals actuales | PageSpeed Insights (mobile) sobre `/`, `/financiacion/` y una ficha | Línea de base para comparar |

## 4.2 ¿Migrar o no?

**Recomendación: mantener WordPress.**

| | Mantener WordPress (elegido) | Migrar (Next.js/Astro + headless) |
|---|---|---|
| Riesgo sobre ventas actuales | Bajo: formularios, pagos, suscripciones y URLs siguen en su lugar | Alto: hay que rehacer pagos, formularios y suscripciones |
| Costo | Plugin + theme ya hechos | Reescritura completa + hosting nuevo + mantenimiento de dos sistemas |
| Autonomía del equipo | Cargan motos, cuotas y sucursales desde el admin que ya conocen | Requiere desarrollador o un CMS nuevo |
| Performance | Suficiente con este theme (6–7 requests, sin jQuery, WebP, caché) | Algo mejor, pero no cambia la conversión |

Migrar sólo tendría sentido si el negocio necesitara una app transaccional compleja (precalificación online con scoring, firma digital, portal de clientes). No es el caso hoy.

## 4.3 Qué se entrega

| Pieza | Ruta | Rol |
|---|---|---|
| **Plugin MotoCred Core** | `wp-content/plugins/motocred-core/` | Datos (planes, cuotas, motos, sucursales, entregas), simulador, WhatsApp, SEO, medición. Funciona con **cualquier** theme (shortcodes). |
| **Theme MotoCred 2026** | `wp-content/themes/motocred-2026/` | Presentación mobile-first. Requiere el plugin. |
| Script de entorno local | `tools/dev-setup.sh` | WordPress + SQLite + datos de ejemplo en un comando |
| Pruebas | `tests/` | E2E (mobile/desktop), modo público y flujo de admin |

### Shortcodes para páginas existentes (Elementor/Gutenberg)

| Shortcode | Resultado |
|---|---|
| `[motocred_simulador]` | Simulador completo (`mode="compact"` para la versión corta) |
| `[motocred_cuota plan="125" plazo="12"]` | Importe de la cuota desde la fuente única |
| `[motocred_cuota plan="125" campo="gastos"]` | Gastos de suscripción |
| `[motocred_dato campo="plazos"]` / `adjudicacion` / `whatsapp` / `sucursales` | "12, 18, 24, 30 o 36", texto de adjudicación, número, cantidad de sucursales |
| `[motocred_planes]`, `[motocred_motos destacadas="1"]`, `[motocred_sucursales]` | Grillas |
| `[motocred_whatsapp intent="plan" plan="125" texto="Consultar"]` | Botón con contexto |
| `[motocred_como_funciona]`, `[motocred_faq]`, `[motocred_plazos]`, `[motocred_entregas]` | Secciones |

**Paso clave:** en `/suscribete-plan125/` (y las demás), reemplazar los importes escritos a mano por `[motocred_cuota …]`. Así la fuente única cubre también las páginas de pago.

## 4.4 Puesta en producción (sin romper nada)

1. **Backup completo** (archivos + base) y **staging** (copia del sitio).
2. En staging: subir `motocred-core` y `motocred-2026`. Activar **primero el plugin** y después el theme.
3. *Personalizar → Identidad del sitio*: confirmar que está el **logo original**. *Personalizar → MotoCred*: color exacto del logo y foto de hero.
4. *MotoCred → Ajustes*: WhatsApp, razón social, CUIT, páginas clave, medición.
5. Cargar Planes (cuotas por plazo, gastos, URL de suscripción) y **tildar "validado" sólo después de revisar contra el tarifario vigente**.
6. Cargar Motos (plan, marca, tipo, fotos, ficha, **URL anterior** para el 301) y Sucursales.
7. *MotoCred → Datos a validar*: debe quedar vacío o con pendientes conscientes.
8. Reemplazar importes manuales en páginas existentes por shortcodes.
9. Cuando cada moto tenga su ficha nueva, **pasar a borrador** los productos/páginas viejos: recién ahí actúan los 301 (nunca pisan una página publicada). Lo mismo con `/gi/`.
10. Correr el checklist de [06-qa.md](06-qa.md) en staging. Publicar. Purgar caché. Enviar sitemap (`/wp-sitemap.xml` o el del plugin SEO) en Search Console.

**Volver atrás:** reactivar el theme anterior. El plugin puede quedar activo (sus URLs nuevas siguen funcionando) o desactivarse; no modifica contenido existente.

## 4.5 Entorno local

```bash
WP_DIR=$PWD/.wp ./tools/dev-setup.sh
php -S 127.0.0.1:8080 -t .wp        # http://127.0.0.1:8080 · admin / admin
cd tests && npm i && npm run e2e && npm run admin
```

El entorno local define `MOTOCRED_DEMO` (valores de EJEMPLO visibles con aviso). **Nunca** definir esa constante en producción.
