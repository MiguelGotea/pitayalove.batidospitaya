# Configuración de Google Vision API para Pitaya Love

## 📋 **Resumen**

Este documento explica cómo configurar la Google Cloud Vision API para habilitar la detección automática de datos en las facturas de Pitaya Love.

---

## 🎯 **¿Qué detecta la API?**

La cámara del sistema detecta automáticamente:

1. **Código de Sorteo**: `31406` (número único de factura)
2. **Puntos**: `10` (puntos asignados)
3. **Total a Pagar**: `305` (monto de la factura)

---

## 🚀 **Pasos para Configurar Google Vision API**

### **Paso 1: Crear Proyecto en Google Cloud**

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Click en **"Seleccionar proyecto"** → **"Nuevo proyecto"**
3. Nombre del proyecto: `Pitaya-Love-OCR` (o el que prefieras)
4. Click **"Crear"**

### **Paso 2: Habilitar Vision API**

1. En el menú lateral, ve a **"APIs y servicios"** → **"Biblioteca"**
2. Busca: `Vision API`
3. Click en **"Cloud Vision API"**
4. Click **"Habilitar"**

### **Paso 3: Crear Credenciales (API Key)**

1. Ve a **"APIs y servicios"** → **"Credenciales"**
2. Click **"Crear credenciales"** → **"Clave de API"**
3. Se generará una clave como: `AIzaSyD...` (cópiala)
4. **(Opcional pero recomendado)** Click en la clave creada y:
   - **Restricciones de aplicación**: Selecciona "Referentes HTTP"
   - Agrega: `https://erp.batidospitaya.com/*`
   - **Restricciones de API**: Selecciona "Cloud Vision API"
   - Click **"Guardar"**

### **Paso 4: Configurar la API Key en el Sistema**

Edita el archivo: `detect_invoice_areas.php`

```php
// Línea 11: Reemplaza con tu API key
$api_key = 'AIzaSyD...TU_API_KEY_AQUI';
```

**Ejemplo:**
```php
$api_key = 'AIzaSyDXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
```

---

## 💰 **Costos de Google Vision API**

### **Precios (2026)**

| Operación | Primeras 1,000/mes | Siguientes |
|-----------|-------------------|------------|
| Text Detection (OCR) | **GRATIS** | $1.50 por 1,000 |

### **Estimación para Pitaya Love**

- **Registros esperados**: ~500/mes
- **Costo mensual**: **$0** (dentro del tier gratuito)
- **Costo anual**: **$0** (si no excede 1,000/mes)

### **Si excedes el tier gratuito:**
- 2,000 registros/mes = $1.50/mes
- 5,000 registros/mes = $6.00/mes

---

## 🧪 **Probar la Configuración**

### **Opción 1: Desde el Navegador**

1. Abre: `https://erp.batidospitaya.com/modulos/PitayaLove/`
2. Escanea un QR de factura
3. Click en **"Abrir Cámara"**
4. Toma foto de la factura impresa
5. Verifica que aparezcan las guías verdes sobre:
   - Código de Sorteo
   - Puntos

### **Opción 2: Prueba Manual con cURL**

```bash
curl -X POST https://erp.batidospitaya.com/modulos/PitayaLove/detect_invoice_areas.php \
  -F "invoice_image=@factura_test.jpg"
```

**Respuesta esperada:**
```json
{
  "success": true,
  "codigo_participar": "31406",
  "puntos": 10,
  "monto_total": 305,
  "areas": [
    {
      "type": "codigo",
      "label": "Código de Sorteo",
      "x": 50,
      "y": 200,
      "width": 150,
      "height": 30
    },
    {
      "type": "puntos",
      "label": "Puntos",
      "x": 50,
      "y": 250,
      "width": 100,
      "height": 25
    }
  ]
}
```

---

## 🔒 **Seguridad**

### **Restricciones Recomendadas**

1. **Restricción de Referente HTTP:**
   - Solo permite llamadas desde `erp.batidospitaya.com`
   - Evita uso no autorizado de tu API key

2. **Restricción de API:**
   - Solo permite usar Vision API
   - Evita que la key se use para otros servicios de Google

3. **Monitoreo:**
   - Ve a **"APIs y servicios"** → **"Panel"**
   - Revisa el uso mensual
   - Configura alertas si excedes el tier gratuito

---

## 🛠️ **Solución de Problemas**

### **Error: "API key not valid"**
- Verifica que copiaste la key completa
- Asegúrate de que Vision API esté habilitada
- Espera 5 minutos después de crear la key

### **Error: "No se pudo detectar texto"**
- Verifica que la imagen sea clara
- Asegúrate de que la factura esté bien iluminada
- Prueba con una foto de mejor calidad

### **No detecta "Código de Sorteo"**
- Verifica que el texto en la factura sea exactamente: `Código de Sorteo: 31406`
- Revisa los regex en `detect_invoice_areas.php` línea 55

### **Excede el tier gratuito**
- Considera cachear resultados de facturas ya procesadas
- Implementa validación del lado del cliente antes de llamar la API

---

## 📊 **Monitoreo de Uso**

### **Ver Estadísticas**

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. **"APIs y servicios"** → **"Panel"**
3. Selecciona **"Cloud Vision API"**
4. Verás gráficas de:
   - Solicitudes por día
   - Errores
   - Latencia

### **Configurar Alertas**

1. **"Facturación"** → **"Presupuestos y alertas"**
2. Click **"Crear presupuesto"**
3. Configura:
   - Nombre: `Vision API Alert`
   - Monto: `$5.00/mes`
   - Alertas al: 50%, 90%, 100%

---

## 🎨 **Personalización**

### **Cambiar Patrones de Detección**

Edita `detect_invoice_areas.php`:

```php
// Detectar "Código de Sorteo: XXXXX"
if (preg_match('/Código\s+de\s+Sorteo\s*:\s*(\d+)/i', $full_text, $matches)) {
    $response['codigo_participar'] = $matches[1];
}

// Detectar "Puntos: X"
if (preg_match('/Puntos\s*:\s*(\d+)/i', $full_text, $matches)) {
    $response['puntos'] = intval($matches[1]);
}

// Detectar "Total a Pagar: XXX"
if (preg_match('/Total\s+a\s+Pagar\s*:\s*C?\$?\s*([\d,]+\.?\d*)/i', $full_text, $matches)) {
    $response['monto_total'] = floatval(str_replace(',', '', $matches[1]));
}
```

---

## ✅ **Checklist de Configuración**

- [ ] Proyecto creado en Google Cloud
- [ ] Vision API habilitada
- [ ] API Key generada
- [ ] Restricciones configuradas (opcional)
- [ ] API Key agregada a `detect_invoice_areas.php`
- [ ] Prueba realizada con factura real
- [ ] Detección de "Código de Sorteo" funciona
- [ ] Detección de "Puntos" funciona
- [ ] Alertas de presupuesto configuradas (opcional)

---

## 📞 **Soporte**

- **Documentación oficial**: [Cloud Vision API Docs](https://cloud.google.com/vision/docs)
- **Precios actualizados**: [Vision API Pricing](https://cloud.google.com/vision/pricing)
- **Consola de Google Cloud**: [console.cloud.google.com](https://console.cloud.google.com/)

---

## 🎯 **Resumen Rápido**

1. Crea proyecto en Google Cloud
2. Habilita Vision API
3. Crea API Key
4. Copia la key a `detect_invoice_areas.php` línea 11
5. Prueba con una factura real
6. ✅ ¡Listo!

**Tiempo estimado**: 10-15 minutos
