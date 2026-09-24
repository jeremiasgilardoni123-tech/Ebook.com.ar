# 1. Auditoría de motocred.com.ar

> Fecha: 24/09/2026 · Alcance: todo el sitio público, no sólo la Home.

## 1.0 Cómo se hizo y qué límites tiene

**El sitio no se pudo abrir directamente.** El entorno donde se hizo este trabajo bloquea por política de red el dominio `motocred.com.ar` (y también los servicios de caché/archivo). Por eso la auditoría se armó con:

| Fuente | Qué aporta |
|---|---|
| Índice de buscadores (títulos, URLs y fragmentos de texto de cada página de motocred.com.ar) | Estructura de URLs, títulos, textos comerciales, sucursales, teléfonos, planes de pago de los T&C |
| Directorios de terceros (Full Time Motos, vLex, CuitOnline, Facebook) | Localidad, teléfonos alternativos, datos societarios, reseñas |

Consecuencias:

- **Todo lo que figura acá como "texto del sitio" es lo que indexaron los buscadores.** Puede estar desactualizado respecto de lo que hoy está publicado.
- **No se pudieron medir:** performance real (Core Web Vitals), theme/plugins/page builder instalados, formularios, píxeles, pasarela de pago ni la experiencia mobile real. En [04-wordpress.md](04-wordpress.md#41-relevamiento-técnico-pendiente) está el checklist para relevarlo en 30 minutos con acceso al admin.
- **No se inventó ningún dato.** Donde dos fuentes no coinciden se marca **[REQUIERE VALIDACIÓN]**; donde falta la cifra, **[DATO A VALIDAR]**.

## 1.1 Mapa de URLs detectadas

| URL | Título indexado | Observación |
|---|---|---|
| `/` | MotoCred – Mendoza, Argentina | El título no dice qué vende (motos, 0KM, cuotas). |
| `/financiacion/` | Financiación – MotoCred | |
| `/nosotros/` | Nosotros – MotoCred | |
| `/contacto/` | Contacto – MotoCred | |
| `/terminos-y-condiciones/` | Términos y Condiciones – MotoCred | Única fuente de plazos y adjudicación. |
| `/suscribete-plan125/` | Suscripción Plan 125 – MotoCred | Única página de suscripción indexada. Slug con verbo ("suscribete") y sin tilde. |
| `/cotizador-motos-usadas/` | COTIZADOR MOTOS USADAS – MotoCred | Título en mayúsculas. No se explica en otra página qué pasa con la usada. |
| `/gi/` | GILERA - MotoCred | Slug críptico (`gi`). Página de marca aislada. |
| `/tienda-2/honda/honda-wave-110-cc/` | HONDA WAVE 110 CC - MotoCred | Estructura tipo WooCommerce. `tienda-2` indica una página "tienda" duplicada. |
| `/tienda-2/zanella/zanella-zt-150cc/` | ZANELLA ZT 150CC - MotoCred | Ídem. Separador de título distinto ("-" vs "–"): dos plantillas o dos plugins SEO. |

**No aparecen indexadas** las páginas "Planes", "Planes de Cuotas MC" ni "Simulador de crédito" mencionadas en el brief, ni páginas de suscripción de los planes 110, 150, 160 y 200. **[REQUIERE VALIDACIÓN]**: confirmar sus URLs y si tienen `noindex` (si lo tienen por error, están perdiendo tráfico).

## 1.2 Inconsistencias comerciales (lo más importante)

| # | Qué se contradice | Dónde | Estado |
|---|---|---|---|
| **C1** | "Retirás tu moto **desde la primera cuota**" y "entregas exprés en **24 hs** en muchos modelos" **vs.** T&C: "Plan 12 cuotas: adjudicación en cuota 2 · 18: cuota 4 · 24: cuota 6 · 30: cuota 7 · 36: cuota 8". | Home, Financiación, Suscripción Plan 125 **vs.** Términos y Condiciones | **[REQUIERE VALIDACIÓN]** — Crítica. Hay que definir cuál es la condición real y si "desde la primera cuota" aplica a algún caso concreto. Mientras tanto, el nuevo sitio **usa sólo lo que dicen los T&C** y no promete retiro en la primera cuota ni en 24 hs. Recomendado revisarlo con asesoría legal (publicidad y defensa del consumidor). |
| **C2** | Se habla de "financiación", "crédito" y "simulador de crédito", pero los T&C describen **adjudicación**, **Nota de Pedido** y **suscripción** (mecánica más cercana a un plan con adjudicación que a un crédito). | Home / Financiación **vs.** T&C | **[REQUIERE VALIDACIÓN]** legal: definir la denominación correcta del producto. El nuevo sitio usa "plan", "cuotas" y "financiación" y evita "crédito" hasta que se confirme. |
| **C3** | "Celebramos la entrega de nuestras **primeras 100 unidades**" **vs.** "**más de 1.000 motos** entregadas este año (2025)" **vs.** "**miles de clientes**". | Nosotros / Home | **[DATO A VALIDAR]** Cifra real de entregas y período. El nuevo sitio no muestra ninguna cifra hasta que se cargue y marque como verificada. |
| **C4** | "Hace años que ayudamos…" **vs.** registro societario de **Motocred S.A.S. constituida el 08/04/2025** (vLex). También existe **"MOTOCRED FIDEICOMISO"** (CUIT 30-71758475-5, Godoy Cruz) en CuitOnline. | Nosotros **vs.** registros públicos | **[REQUIERE VALIDACIÓN]** ¿Qué entidad vende y firma? ¿Desde cuándo opera la marca? Esa razón social y CUIT deben ir en el footer. |
| **C5** | "Con el respaldo de **una empresa líder de Mendoza**" sin decir cuál. | Home / Nosotros | **[REQUIERE VALIDACIÓN]** Nombrarla (si se puede) o quitar la frase: un respaldo anónimo resta confianza. |
| **C6** | Localidad: directorio de terceros dice **Maipú**; la dirección Espejo 216 corresponde (según el nombre de la calle) a Ciudad de Mendoza; el fideicomiso figura en **Godoy Cruz**. | Terceros **vs.** sitio | **[REQUIERE VALIDACIÓN]** Localidad de cada sucursal. En el nuevo sitio el campo "Localidad" quedó vacío a propósito. |
| **C7** | Sucursales: Espejo 216, Cabildo Abierto 411, Maza 2567, Ejército de los Andes y 25 de Mayo. Según el índice, **Maza 2567 y otra figuran como "Próximamente"** pero se muestran junto a las operativas y con teléfono. | Home / Contacto | **[REQUIERE VALIDACIÓN]** Estado de cada una. En el seed quedaron Espejo 216 y Cabildo Abierto 411 como operativas y las otras dos como "Próximamente", todas marcadas "sin validar". |
| **C8** | Teléfono de Cabildo Abierto 411: **"261 589-51544"** → tiene 11 dígitos (sobra uno). | Home / Contacto | **[REQUIERE VALIDACIÓN]** Enlace `tel:` roto. El nuevo sitio **no muestra** teléfonos inválidos y lo avisa en el admin. |
| **C9** | El directorio de terceros publica como teléfono principal **+54 261 570-9828**, que en el sitio es el de **Maza 2567** (¿"Próximamente"?). | Terceros **vs.** sitio | **[REQUIERE VALIDACIÓN]** Unificar el número comercial en Google Business Profile y directorios. |
| **C10** | No se encontró el **número de WhatsApp comercial** en el contenido indexado. | Todo el sitio | **[DATO A VALIDAR]** Cargarlo en *MotoCred → Ajustes*. Sin él, los botones llevan a Contacto (nunca a un enlace roto). |
| **C11** | **Importes de cuotas** de los planes 110/125/150/160/200 y **"gastos"** de suscripción: no están en el índice, así que **no se pudieron auditar** contra Home/Planes/Suscripción. Además "gastos" no se cuantifica en los textos indexados. | Planes / Suscripción | **[DATO A VALIDAR]** Todos los importes. Se cargan una sola vez por plan y plazo en el admin; los "gastos" también. |
| **C12** | "Accedés **en el acto, solo con tu DNI**" — la suscripción además requiere pagar primera cuota y gastos, y la operación queda sujeta a Nota de Pedido. | Home / Financiación | **[REQUIERE VALIDACIÓN]** ¿Hay alguna verificación (ingresos, domicilio, scoring)? El nuevo sitio dice "Iniciás con tu DNI" y "un asesor te confirma si tu caso necesita algo más". |
| **C13** | Plan N ⇔ motos de N cc (Plan 125 = motos 125 cc). Es lo que sugieren los nombres, pero no está dicho en ninguna parte. | Planes | **[REQUIERE VALIDACIÓN]** El nuevo sitio lo explica así ("el número del plan es la cilindrada"). Confirmarlo. |
| **C14** | Especificaciones de Honda Wave 110 y Zanella ZT 150 cargadas desde fuentes generales. | Fichas | **[DATO A VALIDAR]** contra la ficha oficial de cada marca (el admin tiene un check "Características verificadas"). |

## 1.3 Contenido duplicado, CTA repetidos y textos innecesarios

- **El mismo bloque de beneficios se repite** en Home, Financiación y Suscripción ("cuotas fijas en pesos y congeladas", "retirala desde la primera cuota", "solo con DNI", "elegí → te asesoramos → primera cuota → retirás"). Tres versiones del mismo texto = tres lugares donde puede quedar desactualizado (y hoy uno de ellos contradice los T&C).
- **"Cuotas fijas en pesos" + "congeladas durante todo el plan"** dicen lo mismo dos veces en la misma frase.
- **Proceso duplicado** con distinta redacción en Home y Suscripción.
- **Newsletter** ("lanzamientos, promociones, tips de seguridad") compite con el objetivo comercial y no hay evidencia de que se envíe. Recomendado: sacarla del recorrido principal.
- **CTA genéricos**: "Más información", "Ver más", "Contactanos". No dicen qué pasa al tocar.

## 1.4 UX y mobile (a partir de la estructura; confirmar en dispositivo)

- No hay un recorrido claro: moto → cuota → contacto. El simulador vive en una página aparte y no se ofrece desde cada moto o plan.
- "Plan 110/125/150…" exige entender la nomenclatura interna antes de ver motos.
- Catálogo partido en tres estructuras (`/tienda-2/marca/modelo/`, `/gi/`, páginas de plan) sin filtros comunes.
- WhatsApp sin contexto: el asesor recibe "Hola" y tiene que preguntar qué moto, qué plan y qué cuota.
- Sucursales "Próximamente" con el mismo peso visual que las operativas → visitas a un local cerrado.
- Página "Cotizador de motos usadas" desconectada: no se explica si la usada se toma como parte de pago. **[REQUIERE VALIDACIÓN]**

## 1.5 SEO

| Problema | Impacto | Solución implementada |
|---|---|---|
| Títulos genéricos ("MotoCred – Mendoza, Argentina", "Financiación – MotoCred") | No posiciona "motos 0km Mendoza", "motos en cuotas Mendoza" | Títulos por tipo de página (`seo.php`) — p. ej. "Motos 0KM en cuotas fijas en Mendoza \| MotoCred" |
| Títulos en MAYÚSCULAS y con dos separadores distintos | Descuido visible en Google | Plantilla única de títulos |
| URLs `/tienda-2/…`, `/gi/`, `/suscribete-plan125/` | Poco descriptivas, duplicado "tienda-2" | `/motos/{modelo}/`, `/motos/marca/{marca}/`, `/planes/{plan}/` + **301 automáticos** desde las viejas |
| Sin datos estructurados detectados | Sin rich results | `MotorcycleDealer` por sucursal, `Product`, `FAQPage`, `BreadcrumbList` |
| Una sola "página de marca" (Gilera) | No hay arquitectura para "motos Honda Mendoza", etc. | Archivo por marca y por tipo, escalable |
| Datos NAP (nombre, dirección, teléfono) inconsistentes entre sitio y directorios (C6–C9) | Penaliza SEO local | Una sola fuente de sucursales; **unificar además Google Business Profile** |

## 1.6 Performance

**[REQUIERE VALIDACIÓN]** No se pudo medir. Checklist en [04-wordpress.md](04-wordpress.md#41-relevamiento-técnico-pendiente). La versión nueva, medida en local: 6–7 requests y ~120–150 KB por página sin comprimir (fuente incluida), sin jQuery, CLS 0 (ver [06-qa.md](06-qa.md)).

## 1.7 Elementos que restan confianza

1. Promesa de retiro inmediato que el contrato no respalda (C1).
2. Cifras que no coinciden entre sí (C3) y "años" frente a una sociedad de 2025 (C4).
3. "Empresa líder" sin nombre (C5).
4. Teléfono mal escrito (C8) y sucursales cerradas mostradas como abiertas (C7).
5. Falta de razón social y CUIT visibles (dato básico para quien va a firmar un plan).
6. **[REQUIERE VALIDACIÓN] legal:** si la suscripción/pago se hace online, verificar la obligación del **"Botón de arrepentimiento"** (Res. SCI 424/2020). El panel "Datos a validar" lo recuerda y el footer lo enlaza en cuanto exista la página `/boton-de-arrepentimiento/`.

## 1.8 Oportunidades comerciales

- **Simulador en la Home y en cada ficha** (hecho): la cuota es la pregunta n.º 1.
- **WhatsApp con contexto** (hecho): cada mensaje dice plan, modelo, plazo y cuota simulada, y desde qué página vino.
- **Páginas por marca y modelo** para SEO local (hecho, escalable).
- **Fotos de entregas reales con consentimiento** (estructura hecha; falta el material).
- **Reseñas de Google**: hoy la única calificación pública encontrada es un 8/10 con 53 opiniones en un directorio de terceros. **[DATO A VALIDAR]** Pedir reseñas en Google Business Profile tras cada entrega.
- **Usadas como parte de pago**: si aplica, es un argumento de venta fuerte que hoy está escondido. **[REQUIERE VALIDACIÓN]**

## 1.9 Lista consolidada de validaciones

Todo esto también aparece **dentro del WordPress** en *MotoCred → Datos a validar* (se actualiza solo a medida que se completa).

- [ ] C1 Momento real de retiro / adjudicación y entregas "24 hs".
- [ ] C2 Denominación legal del producto (plan con adjudicación vs. crédito).
- [ ] C3 Cifra de motos entregadas (y período).
- [ ] C4 Razón social, CUIT y fecha de inicio de actividad.
- [ ] C5 Nombre de la "empresa líder" que respalda.
- [ ] C6 Localidad de cada sucursal.
- [ ] C7 Estado (operativa / próximamente) de cada sucursal.
- [ ] C8 Teléfono de Cabildo Abierto 411.
- [ ] C9 Número comercial principal.
- [ ] C10 WhatsApp comercial.
- [ ] C11 Cuotas por plan y plazo + gastos de suscripción.
- [ ] C12 Requisitos reales además del DNI.
- [ ] C13 Plan N = motos de N cc.
- [ ] C14 Fichas técnicas de cada moto.
- [ ] URLs de "Planes", "Planes de Cuotas MC", "Simulador de crédito" y suscripciones de los demás planes.
- [ ] Botón de arrepentimiento.
- [ ] Usadas como parte de pago.
