# 3. Propuesta visual y de contenidos

## 3.1 Concepto

**Fintech + motos + marketplace.** Claridad de app financiera (un número grande, una acción), energía de la moto (color de marca, tipografía con carácter) y lógica de catálogo (tarjetas comparables, filtros).

La marca **no se rediseña**: el logo actual se usa tal cual y el sistema se construye alrededor de él.

## 3.2 Logo

- Se toma del logo ya cargado en WordPress (*Apariencia → Personalizar → Identidad del sitio*). El theme no trae ningún logo propio.
- No se recolorea, no se deforma, no se le agregan efectos. Alto máximo: 40 px en mobile, 48 px en desktop.
- En el footer oscuro se apoya sobre una base blanca con esquinas redondeadas para no alterar sus colores. Si MotoCred tiene una versión oficial en negativo, conviene usarla ahí.

## 3.3 Color

| Token | Valor | Uso |
|---|---|---|
| `--mc-brand` | **Configurable** (Personalizar → MotoCred) · por defecto `#d7182a` | CTA principal, acentos, íconos. **[REQUIERE VALIDACIÓN]**: cargar el color exacto del logo. El texto sobre este color se calcula solo (blanco o negro) para mantener el contraste. |
| `--mc-ink` | `#0f1217` | Texto principal |
| `--mc-ink-2` / `--mc-muted` | `#3a404b` / `#5d6471` | Texto secundario (contraste AA sobre blanco) |
| `--mc-subtle` / `--mc-line` | `#f4f5f7` / `#e5e7eb` | Fondos de sección y bordes |
| `--mc-dark` | `#0c0f14` | Sección de respaldo y footer |
| `--mc-wa` | `#128c4a` | Sólo WhatsApp (verde oscuro: texto blanco con contraste AA) |

Regla: **el color de marca es para acciones**; los fondos son neutros. Así la página se ve limpia y el botón siempre se encuentra.

## 3.4 Tipografía

**Plus Jakarta Sans Variable** (licencia OFL), un solo archivo WOFF2 de 27 KB, servido desde el propio sitio (sin Google Fonts: mejor privacidad y un request menos) y precargado.

- Títulos: 800, interletrado negativo, balanceados (`text-wrap: balance`).
- Importes: 800 con números tabulares (no "bailan" al cambiar en el simulador).
- Texto: 450, 16–18 px, interlineado 1.6.

## 3.5 Componentes

- **Botones**: un solo sistema, cuatro variantes — *Primario* (marca), *Secundario* (borde), *WhatsApp* (verde), *WhatsApp secundario*. Altura mínima 48 px (42 en tarjetas). Microinteracción: -1 px al pasar, 0.98 al tocar.
- **Tarjeta de plan**: cilindrada gigante (`150cc`) + para qué sirve + modelos incluidos + "Cuotas desde" + [Simular] [Ver opciones].
- **Tarjeta de moto**: foto 16:11 + marca · cc + nombre + "Plan 150 · desde $X" + [Simular] [Consultar].
- **Simulador**: chips de cilindrada, control segmentado de cuotas, resultado grande en fondo de marca suave, acción principal "Enviar simulación por WhatsApp".
- **Sucursal**: mapa que se carga sólo al tocar "Ver mapa" (no pesa en la carga), "Cómo llegar", teléfono, WhatsApp de la sucursal si tiene.
- **Pasos**: 5 tarjetas con ícono y número.
- **FAQ**: acordeón nativo (`<details>`), accesible y sin JS.
- **Íconos**: SVG en línea de trazo simple (sin librerías de íconos).
- **Sombras**: una sola, sólo en el simulador y al pasar sobre tarjetas.
- **Animaciones**: entrada suave de tarjetas ligada al scroll, sólo con CSS (si el navegador no la soporta, el contenido se ve igual; respeta "reducir movimiento").

## 3.6 Fotografía

La estructura está lista, falta el material. Pautas:

1. **Hero**: una moto real del catálogo, 3/4 frontal, fondo limpio o calle de Mendoza, horizontal ≥ 1600 px. Se carga en *Personalizar → MotoCred → Foto principal*. Sin foto, el hero queda con el simulador como protagonista (no se usa una foto de banco genérica).
2. **Fichas**: fondo neutro uniforme para todas las motos (mismo ángulo y luz) → el catálogo se ve profesional. Mínimo 1200 px.
3. **Entregas reales**: cliente + moto + fachada/cartel de MotoCred. **Sólo con autorización escrita** (el admin exige tildar "consentimiento"; sin eso la foto no se publica).
4. Todas las imágenes nuevas se convierten a WebP automáticamente.

## 3.7 Copywriting

Tono: cercano, profesional, simple, con voseo. Sin superlativos ni promesas que no estén en los T&C.

| Antes (idea del texto indexado; no es cita textual) | Ahora | Por qué |
|---|---|---|
| "MotoCred hace realidad tu sueño de tener una moto 0KM de la forma más fácil y segura." | **"Tu moto 0KM, en cuotas fijas y en pesos."** | Dice qué es y cuál es el beneficio verificable. |
| "Retirá tu moto desde la primera cuota, sin esperas largas." | "La entrega se coordina según la adjudicación del plan que elegiste." + tabla de adjudicación | Coincide con los T&C (C1). |
| "Todas las cuotas son fijas en pesos y se mantienen congeladas durante todo el plan." | "Cuotas fijas en pesos. El valor no cambia durante el plan." | Sin redundancia. |
| "Accedés a tu financiación en el acto, solo con tu DNI." | "Iniciás con tu DNI. Un asesor te confirma si tu caso necesita algo más." | Evita "en el acto" (C12). |
| "Con el respaldo de una empresa líder de Mendoza." | (Se retira hasta nombrarla) | C5. |
| "Más información" / "Contactanos" | "Simulá tu cuota", "Enviar simulación por WhatsApp", "Consultar por la Honda Wave 110", "Cómo llegar" | Cada CTA dice qué pasa. |
| "COTIZADOR MOTOS USADAS" | "Cotizá tu usada" | Sin mayúsculas sostenidas. |

Los textos del hero se editan en *Personalizar → MotoCred* sin tocar código.
