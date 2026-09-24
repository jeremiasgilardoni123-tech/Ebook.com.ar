# 5. Analytics y conversión

## 5.1 Cómo funciona

`motocred.js` envía cada evento a:

1. `window.dataLayer` (GTM y GA4) — siempre.
2. `gtag('event', …)` — si GA4 está cargado (por MotoCred o por otro plugin).
3. `fbq(…)` — si el Meta Pixel está cargado (por MotoCred o por otro plugin). **Si ya existe un Pixel, se conserva**: dejar desactivada la carga en *MotoCred → Ajustes → Medición* y los eventos igual llegan.

Cada evento lleva el **origen del lead** (primer contacto, guardado en el navegador): `lead_source`, `lead_medium`, `lead_campaign`, `lead_landing`, más `page_path`.

## 5.2 Eventos

| Evento | Cuándo | Parámetros | Meta Pixel |
|---|---|---|---|
| `cta_simular_click` | Toca cualquier "Simulá tu cuota" | `ubicacion` (header, hero, tabbar, plan_card, moto_card, footer…) | — |
| `simulador_inicio` | Primera interacción con un simulador | `ubicacion` | — |
| `simulador_completo` | Cada combinación plan + plazo (+ modelo) distinta | `plan`, `plazo`, `modelo`, `cuota_informada`, `ubicacion` | `SimulacionCompleta` (custom) |
| `whatsapp_click` | Cualquier botón de WhatsApp | `intent` (general, plan, moto, financiacion, sucursal, simulacion), `plan`, `modelo`, `marca`, `plazo`, `sucursal`, `ubicacion` | `Contact` |
| `contact_click` | Botón de contacto cuando no hay WhatsApp configurado | ídem | `Contact` |
| `consulta_plan` | "Ver opciones" / "Ver detalle del plan" | `plan`, `ubicacion` | — |
| `suscripcion_click` | "Suscribirme a este plan" | `plan`, `ubicacion` | `InitiateCheckout` |
| `form_submit` | Envío exitoso de CF7, Elementor Forms, WPForms o Gravity Forms | `form_plugin`, `form_id` | `Lead` |
| `sucursal_como_llegar` | "Cómo llegar" | `sucursal` | — |
| `sucursal_llamada` | Tocar el teléfono de una sucursal | `sucursal` | `Contact` |
| `catalogo_filtro` | Filtrar el catálogo | `cc`, `marca`, `tipo`, `resultados` | — |

## 5.3 Configuración en GA4

1. **Eventos clave** (conversiones): `whatsapp_click`, `form_submit`, `suscripcion_click`, `sucursal_llamada`. Secundarios: `simulador_completo`.
2. **Dimensiones personalizadas** (ámbito evento): `plan`, `modelo`, `plazo`, `intent`, `ubicacion`, `sucursal`, `lead_source`.
3. Informes sugeridos (Exploraciones):
   - Embudo: `page_view` → `simulador_inicio` → `simulador_completo` → `whatsapp_click` (intent = simulacion) → `suscripcion_click`.
   - Consultas por modelo y por plan: `whatsapp_click` desglosado por `modelo` / `plan`.
   - Conversiones por sucursal: `sucursal_llamada` + `sucursal_como_llegar` + `whatsapp_click` (intent = sucursal) por `sucursal`.
   - Origen del lead: eventos clave por `lead_source` / `lead_medium`.
4. Con GTM: un disparador "Evento personalizado" por nombre y una etiqueta GA4 que pase los parámetros de la capa de datos.

## 5.4 Suscripciones

La conversión final (pago de la primera cuota) ocurre en la página de suscripción/pasarela actual. **[REQUIERE VALIDACIÓN]** Si es WooCommerce, GA4 puede medir `purchase` con la integración de Site Kit o GTM4WP; si es un link externo de pago, pedir a la pasarela la URL de retorno para registrar la conversión.

## 5.5 WhatsApp: qué recibe el asesor

```
Hola MotoCred, simulé el Plan 150 en 12 cuotas de $ XX.XXX. Quiero avanzar con la compra.

(Simulación desde la web)
```
```
Hola MotoCred, quiero consultar por la Honda Wave 110 (Plan 110). ¿Qué opciones de financiación tengo?

(Consulta desde la web: /motos/honda-wave-110/)
```
El saludo es editable en *Ajustes*. Cada sucursal puede tener su propio WhatsApp.
