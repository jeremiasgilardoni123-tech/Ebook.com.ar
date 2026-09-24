# 2. Nueva arquitectura

## 2.1 Principio rector

El usuario llega con una sola pregunta: **"¿Qué moto puedo tener y cuánto pago por mes?"**. Cada pantalla responde eso en menos de un scroll y ofrece el siguiente paso.

```
Llega → Entiende (hero) → Ve motos / planes → Simula → Ve la cuota → WhatsApp con la simulación → Suscripción
                         ↑______________ el simulador está en cada uno de estos puntos ______________↑
```

## 2.2 Menú

Se evaluaron tres opciones:

| Opción | Pros | Contras |
|---|---|---|
| A. Motos · Financiación · Planes · Cómo funciona · Sucursales · Nosotros + Simular (brief) | Completa | 6 ítems + CTA no entran en tablet; "Cómo funciona" y "Financiación" se pisan |
| **B. Motos · Planes · Financiación · Sucursales · Nosotros + [Simulá tu cuota] + WhatsApp** | 5 ítems; "Cómo funciona" vive dentro de Financiación (y en la Home) | — |
| C. Motos · Simulador · Contacto | Mínima | Esconde Planes y Sucursales, que generan confianza |

**Elegida: B.** "Contacto" sale del menú porque su función la cubren WhatsApp (header, barra inferior, cada tarjeta) y Sucursales; sigue en el footer.

**Mobile:** header con logo + "Simulá tu cuota" + menú; **barra inferior fija** Motos · Planes · **Simular** · WhatsApp (se probó: ningún CTA queda fuera del alcance del pulgar y no tapa contenido porque el body reserva su altura).

## 2.3 Mapa del sitio y URLs

| Página | URL | Tipo | Estado |
|---|---|---|---|
| Home | `/` | `front-page.php` | Nueva |
| Catálogo | `/motos/` | Archivo CPT `mc_moto` | Nueva |
| Motos por marca | `/motos/marca/{marca}/` | Taxonomía `mc_marca` | Nueva (reemplaza `/gi/`) |
| Motos por tipo | `/motos/tipo/{tipo}/` | Taxonomía `mc_tipo` | Nueva |
| Ficha de moto | `/motos/{modelo}/` | CPT `mc_moto` | Nueva (301 desde `/tienda-2/…`) |
| Planes | `/planes/` | Archivo CPT `mc_plan` | Nueva |
| Detalle de plan | `/planes/plan-125/` | CPT `mc_plan` | Nueva |
| Financiación | `/financiacion/` | `page-financiacion.php` | **Se conserva la URL** |
| Simulador | `/simulador/` (o la URL actual) | `page-simulador.php` / shortcode | Se conserva la URL existente vía *Ajustes → Páginas clave* |
| Sucursales | `/sucursales/` y `/sucursales/{sucursal}/` | CPT `mc_sucursal` | Nueva |
| Nosotros, Contacto, T&C, Suscripciones, Cotizador | **URLs actuales** | Páginas existentes | **Se conservan** (contenido y formularios intactos) |

Escalabilidad SEO: cada moto nueva genera sola su ficha, aparece en su marca, su tipo, su plan, el catálogo, el sitemap y el schema. No hay que tocar código.

## 2.4 Datos: una sola fuente

```
Admin WordPress
 ├─ Planes (Plan 110…200)  → cuota por plazo · gastos · URL de suscripción · ✔ validado
 ├─ Ajustes → Plazos        → 12/18/24/30/36 y cuota de adjudicación (de los T&C)
 ├─ Motos                   → marca · tipo · plan · fotos · ficha técnica
 ├─ Sucursales              → dirección · teléfono · WhatsApp · estado · ✔ validado
 └─ Ajustes                 → WhatsApp central · razón social · CUIT · cifras (✔) · FAQ · medición
        │
        ▼  MotoCred_Data (plugin)
        │
        ├─ Home (tarjetas "desde", simulador, FAQ, sucursales)
        ├─ Planes (tarjetas + tabla comparativa)
        ├─ Financiación (tabla de adjudicación, comparativa, FAQ)
        ├─ Fichas de moto ("cuotas desde", simulador precargado)
        ├─ Simulador (JSON)
        ├─ Páginas existentes → shortcodes [motocred_cuota plan="125" plazo="12"]
        └─ Schema, WhatsApp, eventos de medición
```

Reglas:

- **Ningún importe se escribe en una plantilla.** Ni en el theme, ni en Elementor: en páginas existentes se usa `[motocred_cuota]`.
- **Lo que no está validado no se publica.** El público ve "Consultá el valor"; el equipo logueado ve el valor con la etiqueta amarilla "A validar".
- Cambiar un plazo de adjudicación en *Ajustes* actualiza simulador, FAQ, tablas y textos a la vez.
- Cada plan guarda la **fecha de última modificación** de sus valores (se muestra "Valores actualizados al…").

## 2.5 Qué se conserva, qué cambia, qué se elimina

### Se conserva
- **El logo, sin ninguna modificación** (se toma del logo ya cargado en WordPress). En el footer oscuro se muestra sobre una base blanca para no alterar sus colores.
- El nombre MotoCred y su tono cercano.
- Las URLs de Financiación, Nosotros, Contacto, T&C, Suscripción y Cotizador (y sus formularios y pagos).
- Las condiciones de los T&C (plazos y adjudicación) como fuente de verdad.
- WordPress como plataforma.
- WooCommerce/pasarela, si existen, para el pago de suscripciones (el sitio sólo enlaza a esas páginas).

### Cambia
- Home: de página institucional a recorrido comercial con simulador en el hero.
- Planes: de "Plan 150" a "150 cc · Más potencia para todos los días · desde $X · Simular".
- Catálogo único con filtros y fichas por modelo.
- WhatsApp: de botón genérico a mensajes con contexto.
- Sucursales: cards con "Cómo llegar", llamada y mapa bajo demanda; "Próximamente" aparte.
- Títulos, descripciones, schema y URLs.
- Medición: eventos por intención (simular, WhatsApp, suscripción, formularios, sucursal).

### Se elimina (o sale del recorrido principal)
- Promesas no respaldadas por los T&C (retiro en la primera cuota, 24 hs) hasta validarlas.
- Cifras no verificables.
- Bloques de beneficios repetidos en tres páginas.
- Slider/carrusel automático (si lo hubiera): se reemplaza por un hero estático con acción.
- Newsletter del cuerpo de la Home.
- `/tienda-2/…` y `/gi/` como destinos (quedan con 301).
