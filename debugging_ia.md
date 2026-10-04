# Debugging: Detección de IA

## Problema Actual

**Síntoma:**
- Puntos se detectan correctamente (ej: 2)
- Código de sorteo NO se detecta (muestra "No detectado")
- En BD: `puntos_ia = 2`, `codigo_sorteo_ia = NULL`

**Causa Probable:**
El patrón regex no coincide con el formato real del código en la factura.

---

## Solución: Ver Texto Detectado

### Paso 1: Habilitar Debug en detect_invoice_areas.php

La respuesta ya incluye `raw_text` (línea 153). Para verlo en el navegador:

**Opción A: Ver en Consola del Navegador**

Agregar en `index.php` dentro de la función `detectInvoiceAreas`:

```javascript
async function detectInvoiceAreas(blob) {
    // ... código existente ...
    
    const result = await response.json();
    
    // AGREGAR ESTO PARA DEBUG
    console.log('=== TEXTO DETECTADO POR IA ===');
    console.log(result.raw_text);
    console.log('=== VALORES EXTRAÍDOS ===');
    console.log('Código:', result.codigo_sorteo_ia);
    console.log('Puntos:', result.puntos_ia);
    
    if (result.success) {
        // ... resto del código ...
    }
}
```

Luego:
1. Abrir navegador
2. Presionar F12 (Herramientas de Desarrollador)
3. Ir a pestaña "Console"
4. Tomar foto
5. Ver el texto completo detectado

---

### Paso 2: Analizar el Texto

Buscar en el texto detectado:
- ¿Cómo aparece el código de sorteo?
- ¿Qué palabras lo rodean?
- ¿Qué formato tiene?

**Ejemplos comunes:**

```
Código de Sorteo: 12345
CODIGO PARTICIPAR 12345
No. 12345
Factura #12345
Sorteo: 12345
```

---

### Paso 3: Ajustar Regex

Según el formato encontrado, actualizar en `detect_invoice_areas.php`:

#### Ejemplo 1: "Código de Sorteo: 12345"

```php
// Patrón ya incluido (línea 120)
if (preg_match('/(?:CODIGO|SORTEO)(?:\s+DE\s+SORTEO)?[\s:]*(\d{4,15})/i', $full_text, $matches)) {
    $codigo_sorteo_ia = (int) $matches[1];
}
```

#### Ejemplo 2: "Factura No. 12345"

```php
// Agregar nuevo patrón
if ($codigo_sorteo_ia === null) {
    if (preg_match('/FACTURA\s+(?:No\.?|#)[\s]*(\d{4,15})/i', $full_text, $matches)) {
        $codigo_sorteo_ia = (int) $matches[1];
    }
}
```

#### Ejemplo 3: Código al inicio de línea

```php
// Agregar nuevo patrón
if ($codigo_sorteo_ia === null) {
    if (preg_match('/^(\d{4,8})\s/m', $full_text, $matches)) {
        $codigo_sorteo_ia = (int) $matches[1];
    }
}
```

---

## Patrones Regex Actuales

### Código de Sorteo (detect_invoice_areas.php líneas 119-135)

```php
// Patrón 1: "CODIGO DE SORTEO: 12345" o "CODIGO: 12345"
if (preg_match('/(?:CODIGO|SORTEO|PARTICIPAR)(?:\s+DE\s+SORTEO)?[\s:]*(\d{4,15})/i', $full_text, $matches)) {
    $codigo_sorteo_ia = (int) $matches[1];
}

// Patrón 2: "No. XXXXX" o "#XXXXX"
if ($codigo_sorteo_ia === null) {
    if (preg_match('/(?:No\.?|#)[\s]*(\d{4,15})/i', $full_text, $matches)) {
        $codigo_sorteo_ia = (int) $matches[1];
    }
}

// Patrón 3: Número aislado de 5-15 dígitos
if ($codigo_sorteo_ia === null) {
    if (preg_match('/\b(\d{5,15})\b/', $full_text, $matches)) {
        $codigo_sorteo_ia = (int) $matches[1];
    }
}
```

**Cambios Recientes:**
- ✅ Acepta códigos de **4 dígitos** (antes era 6)
- ✅ Busca "CODIGO DE SORTEO" completo
- ✅ Busca "No." o "#"

---

### Puntos (detect_invoice_areas.php líneas 137-153)

```php
// Patrón 1: "PUNTOS: 10"
if (preg_match('/PUNTOS[\s:]*(\d+)/i', $full_text, $matches)) {
    $puntos_ia = (int) $matches[1];
}

// Patrón 2: "10 PUNTOS"
if ($puntos_ia === null) {
    if (preg_match('/(\d+)[\s]*PUNTOS/i', $full_text, $matches)) {
        $puntos_ia = (int) $matches[1];
    }
}

// Patrón 3: "PUNTOS GANADOS: 10"
if ($puntos_ia === null) {
    if (preg_match('/PUNTOS\s+GANADOS[\s:]*(\d+)/i', $full_text, $matches)) {
        $puntos_ia = (int) $matches[1];
    }
}
```

**Estado:** ✅ Funcionando (detectó 2 puntos correctamente)

---

## Casos Comunes y Soluciones

### Caso 1: Código muy corto (1-3 dígitos)

**Problema:** Patrón requiere mínimo 4 dígitos

**Solución:** Cambiar `{4,15}` a `{1,15}` en línea 120

```php
if (preg_match('/(?:CODIGO|SORTEO|PARTICIPAR)(?:\s+DE\s+SORTEO)?[\s:]*(\d{1,15})/i', $full_text, $matches)) {
```

⚠️ **Advertencia:** Puede detectar números incorrectos

---

### Caso 2: Código con guiones o espacios

**Ejemplo:** "12-345" o "12 345"

**Solución:** Agregar patrón que acepta separadores

```php
if ($codigo_sorteo_ia === null) {
    if (preg_match('/(?:CODIGO|SORTEO)[\s:]*(\d+[-\s]?\d+)/i', $full_text, $matches)) {
        // Remover guiones y espacios
        $codigo_sorteo_ia = (int) str_replace(['-', ' '], '', $matches[1]);
    }
}
```

---

### Caso 3: Múltiples números en factura

**Problema:** Detecta número incorrecto (ej: monto en lugar de código)

**Solución:** Ser más específico con el contexto

```php
// Buscar solo después de palabras clave
if (preg_match('/(?:CODIGO|SORTEO|PARTICIPAR).*?(\d{4,15})/i', $full_text, $matches)) {
    $codigo_sorteo_ia = (int) $matches[1];
}
```

---

## Verificación Rápida

### Checklist de Debug

1. [ ] Abrir consola del navegador (F12)
2. [ ] Tomar foto de factura
3. [ ] Ver `raw_text` en consola
4. [ ] Identificar formato del código
5. [ ] Ajustar regex según formato
6. [ ] Probar nuevamente
7. [ ] Verificar que se detecta correctamente
8. [ ] Verificar que se guarda en BD

---

## Ejemplo de Formato Real

Si el texto detectado es:

```
BATIDOS PITAYA
Factura No. 12345
Fecha: 08/02/2026
Total: C$100.00
Puntos: 2
```

**Patrón necesario:**
```php
if (preg_match('/Factura\s+No\.\s*(\d+)/i', $full_text, $matches)) {
    $codigo_sorteo_ia = (int) $matches[1];
}
```

---

## Próximos Pasos

1. **Habilitar debug** en `index.php`
2. **Tomar foto** de factura real
3. **Ver texto** en consola
4. **Compartir formato** del código en la factura
5. **Ajustar regex** específicamente para ese formato

---

## Nota Importante

⚠️ **Los patrones actuales son genéricos**. Para mejor precisión, necesitamos ver el formato exacto de las facturas reales y ajustar los regex específicamente para ese formato.

El patrón de **puntos funciona** porque probablemente la palabra "PUNTOS" aparece claramente en la factura. El **código no funciona** porque el formato real no coincide con los patrones genéricos.
