# Código para Agregar a index.php

## 1. HTML - Agregar después del input de foto

Buscar el input de foto de factura y agregar este HTML inmediatamente después:

```html
<!-- Resultados de IA (oculto inicialmente) -->
<div id="aiResults" style="display: none; margin-top: 20px; padding: 20px; border-radius: 12px; background: #f8f9fa; border: 2px solid #dee2e6;">
    <h4 style="margin: 0 0 15px 0; font-size: 1.1rem; color: var(--primary-teal); border-bottom: 2px solid var(--primary-teal); padding-bottom: 10px;">
        📊 Resultados de Lectura Automática
    </h4>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
        <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0;">
            <strong style="display: block; margin-bottom: 8px; color: #666; font-size: 0.9rem;">Código de Sorteo:</strong>
            <div id="aiCodigoValue" style="font-size: 1.3rem; font-weight: 700; color: var(--primary-teal);">-</div>
        </div>
        <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0;">
            <strong style="display: block; margin-bottom: 8px; color: #666; font-size: 0.9rem;">Puntos:</strong>
            <div id="aiPuntosValue" style="font-size: 1.3rem; font-weight: 700; color: var(--primary-teal);">-</div>
        </div>
    </div>
    
    <div id="aiMessage" style="padding: 15px; border-radius: 8px; font-weight: 500; line-height: 1.6;"></div>
    
    <div id="aiActions" style="margin-top: 20px; display: none; gap: 12px;">
        <button type="button" class="btn-retake" onclick="retakeFoto()" style="flex: 1; padding: 14px 24px; background: var(--primary-teal); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <span style="font-size: 1.2rem;">📷</span> Tomar Otra Foto
        </button>
        <button type="button" class="btn-manual" onclick="proceedManual()" style="flex: 1; padding: 14px 24px; background: #6c757d; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <span style="font-size: 1.2rem;">✋</span> Revisión Manual
        </button>
    </div>
</div>

<!-- Campos hidden para datos de IA -->
<input type="hidden" id="codigoSorteoIA" name="codigo_sorteo_ia" value="">
<input type="hidden" id="puntosIA" name="puntos_ia" value="">
<input type="hidden" id="validadoIA" name="validado_ia" value="0">
```

---

## 2. JavaScript - Agregar antes del cierre de </body>

```html
<script>
// ============================================
// Validación de IA
// ============================================

// Función para validar resultados de IA contra inputs del usuario
function validateAIResults(aiData, userInputs) {
    const aiResults = document.getElementById('aiResults');
    const aiCodigoValue = document.getElementById('aiCodigoValue');
    const aiPuntosValue = document.getElementById('aiPuntosValue');
    const aiMessage = document.getElementById('aiMessage');
    const aiActions = document.getElementById('aiActions');
    
    // Mostrar sección de resultados
    aiResults.style.display = 'block';
    
    // Scroll suave a resultados
    setTimeout(() => {
        aiResults.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 100);
    
    // Mostrar valores detectados (o "No detectado")
    aiCodigoValue.textContent = aiData.codigo_sorteo_ia || 'No detectado';
    aiCodigoValue.style.color = aiData.codigo_sorteo_ia ? 'var(--primary-teal)' : '#999';
    
    aiPuntosValue.textContent = aiData.puntos_ia || 'No detectado';
    aiPuntosValue.style.color = aiData.puntos_ia ? 'var(--primary-teal)' : '#999';
    
    // Caso 1: No se pudo leer ningún valor
    if (!aiData.codigo_sorteo_ia && !aiData.puntos_ia) {
        aiMessage.style.background = '#fff3cd';
        aiMessage.style.color = '#856404';
        aiMessage.style.border = '2px solid #ffc107';
        aiMessage.innerHTML = `
            <div style="font-size: 1.5rem; margin-bottom: 8px;">⚠️</div>
            <strong style="font-size: 1.1rem;">Factura no legible</strong><br>
            <span style="font-size: 0.95rem;">No se pudieron detectar los datos automáticamente. Puede tomar otra foto más clara o continuar a revisión manual.</span>
        `;
        aiActions.style.display = 'flex';
        return 'unreadable';
    }
    
    // Caso 2: Valores no coinciden
    const codigoMatch = aiData.codigo_sorteo_ia && (aiData.codigo_sorteo_ia == userInputs.codigo);
    const puntosMatch = aiData.puntos_ia && (aiData.puntos_ia == userInputs.puntos);
    
    if (!codigoMatch || !puntosMatch) {
        aiMessage.style.background = '#f8d7da';
        aiMessage.style.color = '#721c24';
        aiMessage.style.border = '2px solid #f5c6cb';
        
        let mismatchDetails = [];
        if (aiData.codigo_sorteo_ia && !codigoMatch) {
            mismatchDetails.push(`<strong>Código:</strong> Ingresado: ${userInputs.codigo}, Detectado: ${aiData.codigo_sorteo_ia}`);
        }
        if (aiData.puntos_ia && !puntosMatch) {
            mismatchDetails.push(`<strong>Puntos:</strong> Ingresado: ${userInputs.puntos}, Detectado: ${aiData.puntos_ia}`);
        }
        
        aiMessage.innerHTML = `
            <div style="font-size: 1.5rem; margin-bottom: 8px;">❌</div>
            <strong style="font-size: 1.1rem;">Datos no coinciden</strong><br>
            <div style="margin-top: 10px; font-size: 0.95rem;">
                ${mismatchDetails.join('<br>')}
            </div>
            <div style="margin-top: 10px; font-size: 0.9rem; opacity: 0.9;">
                Puede tomar otra foto o continuar a revisión manual.
            </div>
        `;
        aiActions.style.display = 'flex';
        return 'mismatch';
    }
    
    // Caso 3: Valores coinciden
    aiMessage.style.background = '#d4edda';
    aiMessage.style.color = '#155724';
    aiMessage.style.border = '2px solid #c3e6cb';
    aiMessage.innerHTML = `
        <div style="font-size: 1.5rem; margin-bottom: 8px;">✅</div>
        <strong style="font-size: 1.1rem;">¡Datos verificados correctamente!</strong><br>
        <span style="font-size: 0.95rem;">Los valores ingresados coinciden con la lectura automática.</span>
    `;
    aiActions.style.display = 'none';
    
    // Marcar como validado
    document.getElementById('validadoIA').value = '1';
    
    return 'match';
}

// Función para retomar foto
function retakeFoto() {
    // Limpiar foto actual
    const fotoInput = document.getElementById('fotoFactura');
    if (fotoInput) {
        fotoInput.value = '';
    }
    
    // Ocultar resultados
    document.getElementById('aiResults').style.display = 'none';
    
    // Limpiar campos hidden
    document.getElementById('codigoSorteoIA').value = '';
    document.getElementById('puntosIA').value = '';
    document.getElementById('validadoIA').value = '0';
    
    // Scroll a input de foto
    if (fotoInput) {
        fotoInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Opcional: Abrir cámara automáticamente después de scroll
        setTimeout(() => {
            fotoInput.click();
        }, 500);
    }
}

// Función para proceder con revisión manual
function proceedManual() {
    // Marcar como revisión manual
    document.getElementById('validadoIA').value = '0';
    
    // Ocultar botones de acción
    document.getElementById('aiActions').style.display = 'none';
    
    // Actualizar mensaje
    const aiMessage = document.getElementById('aiMessage');
    aiMessage.style.background = '#d1ecf1';
    aiMessage.style.color = '#0c5460';
    aiMessage.style.border = '2px solid #bee5eb';
    aiMessage.innerHTML = `
        <div style="font-size: 1.5rem; margin-bottom: 8px;">ℹ️</div>
        <strong style="font-size: 1.1rem;">Marcado para revisión manual</strong><br>
        <span style="font-size: 0.95rem;">Su registro será revisado por nuestro equipo. Puede continuar con el formulario.</span>
    `;
}

// Función para procesar foto con IA
function procesarFotoConIA() {
    const fotoInput = document.getElementById('fotoFactura');
    const codigoInput = document.getElementById('codigoSorteo'); // Ajustar ID según tu formulario
    const puntosInput = document.getElementById('puntos'); // Ajustar ID según tu formulario
    
    if (!fotoInput || !fotoInput.files || !fotoInput.files[0]) {
        return;
    }
    
    if (!codigoInput || !puntosInput) {
        console.error('No se encontraron los inputs de código o puntos');
        return;
    }
    
    const fotoFile = fotoInput.files[0];
    const formData = new FormData();
    formData.append('invoice_image', fotoFile);
    
    // Mostrar loading (opcional)
    const aiResults = document.getElementById('aiResults');
    aiResults.style.display = 'block';
    aiResults.innerHTML = '<div style="text-align: center; padding: 30px;"><div style="font-size: 2rem;">⏳</div><div style="margin-top: 10px;">Analizando factura...</div></div>';
    
    fetch('detect_invoice_areas.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(aiData => {
        // Restaurar estructura HTML
        aiResults.innerHTML = `
            <h4 style="margin: 0 0 15px 0; font-size: 1.1rem; color: var(--primary-teal); border-bottom: 2px solid var(--primary-teal); padding-bottom: 10px;">
                📊 Resultados de Lectura Automática
            </h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0;">
                    <strong style="display: block; margin-bottom: 8px; color: #666; font-size: 0.9rem;">Código de Sorteo:</strong>
                    <div id="aiCodigoValue" style="font-size: 1.3rem; font-weight: 700; color: var(--primary-teal);">-</div>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0;">
                    <strong style="display: block; margin-bottom: 8px; color: #666; font-size: 0.9rem;">Puntos:</strong>
                    <div id="aiPuntosValue" style="font-size: 1.3rem; font-weight: 700; color: var(--primary-teal);">-</div>
                </div>
            </div>
            <div id="aiMessage" style="padding: 15px; border-radius: 8px; font-weight: 500; line-height: 1.6;"></div>
            <div id="aiActions" style="margin-top: 20px; display: none; gap: 12px;">
                <button type="button" class="btn-retake" onclick="retakeFoto()" style="flex: 1; padding: 14px 24px; background: var(--primary-teal); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <span style="font-size: 1.2rem;">📷</span> Tomar Otra Foto
                </button>
                <button type="button" class="btn-manual" onclick="proceedManual()" style="flex: 1; padding: 14px 24px; background: #6c757d; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <span style="font-size: 1.2rem;">✋</span> Revisión Manual
                </button>
            </div>
        `;
        
        if (aiData.success) {
            // Obtener valores ingresados por usuario
            const userInputs = {
                codigo: codigoInput.value,
                puntos: puntosInput.value
            };
            
            // Validar y mostrar resultados
            const validationResult = validateAIResults(aiData, userInputs);
            
            // Guardar datos de IA en campos hidden
            document.getElementById('codigoSorteoIA').value = aiData.codigo_sorteo_ia || '';
            document.getElementById('puntosIA').value = aiData.puntos_ia || '';
        } else {
            // Error en IA
            const aiMessage = document.getElementById('aiMessage');
            aiMessage.style.background = '#f8d7da';
            aiMessage.style.color = '#721c24';
            aiMessage.style.border = '2px solid #f5c6cb';
            aiMessage.innerHTML = `
                <div style="font-size: 1.5rem; margin-bottom: 8px;">⚠️</div>
                <strong>Error al procesar imagen</strong><br>
                <span style="font-size: 0.9rem;">${aiData.message || 'No se pudo analizar la factura'}</span>
            `;
        }
    })
    .catch(error => {
        console.error('Error al validar con IA:', error);
        const aiMessage = document.getElementById('aiMessage');
        aiMessage.style.background = '#f8d7da';
        aiMessage.style.color = '#721c24';
        aiMessage.style.border = '2px solid #f5c6cb';
        aiMessage.innerHTML = `
            <div style="font-size: 1.5rem; margin-bottom: 8px;">❌</div>
            <strong>Error de conexión</strong><br>
            <span style="font-size: 0.9rem;">No se pudo conectar con el servicio de validación.</span>
        `;
    });
}

// Event listener para cuando se sube una foto
document.addEventListener('DOMContentLoaded', function() {
    const fotoInput = document.getElementById('fotoFactura');
    if (fotoInput) {
        fotoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // Esperar un momento para que el usuario vea la foto
                setTimeout(procesarFotoConIA, 500);
            }
        });
    }
});
</script>
```

---

## 3. CSS Adicional (Opcional - para hover effects)

Agregar en el `<style>` o en `css/styles.css`:

```css
.btn-retake:hover {
    background: #0d9488 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
}

.btn-manual:hover {
    background: #5a6268 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

#aiResults {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

---

## 4. Notas de Implementación

### IDs de Inputs a Verificar

**IMPORTANTE:** Debes verificar los IDs reales de los inputs en tu formulario y ajustar estas líneas:

```javascript
const codigoInput = document.getElementById('codigoSorteo'); // ← Verificar ID real
const puntosInput = document.getElementById('puntos'); // ← Verificar ID real
const fotoInput = document.getElementById('fotoFactura'); // ← Verificar ID real
```

### Ubicación del HTML

El HTML de resultados debe ir **inmediatamente después** del input de foto, dentro del mismo contenedor del formulario.

### Trigger de Validación

La validación se ejecuta automáticamente cuando el usuario selecciona una foto (evento `change`).

---

## 5. Flujo de Usuario

1. Usuario llena código y puntos
2. Usuario sube foto
3. **Automáticamente** se llama a IA
4. Se muestran resultados debajo de la foto
5. Según el caso:
   - ✅ **Coincide** → Puede continuar
   - ❌ **No coincide** → Botones: Retomar/Manual
   - ⚠️ **No legible** → Botones: Retomar/Manual
6. Si retoma foto → Vuelve al paso 2
7. Si elige manual → Marca `validado_ia=0` y continúa
