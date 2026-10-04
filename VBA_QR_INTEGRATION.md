# Integración VBA para Generación de QR Codes Online

## Descripción
Módulo VBA para generar códigos QR online de facturas Pitaya Love desde Microsoft Access.

## Características
- ✅ Genera código de seguridad SHA256 (coincide con backend PHP)
- ✅ Descarga imagen QR desde API externa (300x300 PNG)
- ✅ Integración directa con formularios de Access
- ✅ Sin dependencias externas (usa MSXML2 y ADODB)

## Instalación en Access

### 1. Importar Módulo
1. Abre tu base de datos de Access
2. Presiona `Alt + F11` para abrir el editor VBA
3. Ve a `Archivo > Importar archivo...`
4. Selecciona `PitayaLoveQRGenerator.bas`

### 2. Habilitar Referencias (si es necesario)
En el editor VBA:
1. `Herramientas > Referencias`
2. Marca:
   - ✅ Microsoft XML, v6.0
   - ✅ Microsoft ActiveX Data Objects 6.1 Library

## Uso Básico

### Ejemplo 1: Generar QR desde Formulario

```vba
Private Sub btnGenerarQR_Click()
    Dim rutaQR As String
    Dim numeroFactura As String
    Dim monto As Double
    Dim puntos As Integer
    Dim membresia As Integer
    
    ' Obtener datos del formulario
    numeroFactura = Me.txtNumeroFactura.Value
    monto = Me.txtMontoTotal.Value
    puntos = CalcularPuntos(monto)
    membresia = Nz(Me.txtNumeroMembresia.Value, 0)
    
    ' Ruta destino
    rutaQR = CurrentProject.Path & "\QR_Temp\QR_" & numeroFactura & ".png"
    
    ' Crear carpeta si no existe
    If Dir(CurrentProject.Path & "\QR_Temp", vbDirectory) = "" Then
        MkDir CurrentProject.Path & "\QR_Temp"
    End If
    
    ' Generar QR
    If GenerarQRFactura(numeroFactura, monto, puntos, membresia, rutaQR) Then
        Me.imgQRCode.Picture = rutaQR
        MsgBox "QR generado exitosamente", vbInformation
    Else
        MsgBox "Error generando QR", vbCritical
    End If
End Sub
```

### Ejemplo 2: Generar QR en Reporte (Al Imprimir)

```vba
Private Sub Report_Open(Cancel As Integer)
    Dim rutaQR As String
    
    ' Generar QR para la factura actual
    rutaQR = CurrentProject.Path & "\QR_Temp\QR_" & Me.NumeroFactura & ".png"
    
    If GenerarQRFactura(Me.NumeroFactura, Me.MontoTotal, _
                        CalcularPuntos(Me.MontoTotal), _
                        Nz(Me.NumeroMembresia, 0), rutaQR) Then
        Me.imgQRCode.Picture = rutaQR
    End If
End Sub
```

### Ejemplo 3: Generar QR para Múltiples Facturas

```vba
Sub GenerarQRsMasivos()
    Dim rs As DAO.Recordset
    Dim rutaQR As String
    Dim contador As Integer
    
    Set rs = CurrentDb.OpenRecordset("SELECT * FROM Facturas WHERE QRGenerado = False")
    
    contador = 0
    Do While Not rs.EOF
        rutaQR = CurrentProject.Path & "\QR_Facturas\QR_" & rs!NumeroFactura & ".png"
        
        If GenerarQRFactura(rs!NumeroFactura, rs!MontoTotal, _
                           CalcularPuntos(rs!MontoTotal), _
                           Nz(rs!NumeroMembresia, 0), rutaQR) Then
            
            ' Marcar como generado
            rs.Edit
            rs!QRGenerado = True
            rs!RutaQR = rutaQR
            rs.Update
            
            contador = contador + 1
        End If
        
        rs.MoveNext
    Loop
    
    rs.Close
    Set rs = Nothing
    
    MsgBox "QRs generados: " & contador, vbInformation
End Sub
```

## API Utilizada

**QR Server API** (Gratuita, sin límites)
- URL: `https://api.qrserver.com/v1/create-qr-code/`
- Tamaño: 300x300 px
- Formato: PNG
- Margen: 10px

### APIs Alternativas

Si necesitas cambiar la API, modifica la función `DescargarImagenQR`:

```vba
' Opción 1: Google Charts (deprecated pero funciona)
qrApiURL = "https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=" & encodedURL

' Opción 2: QRCode Monkey (gratis hasta 100k/mes)
qrApiURL = "https://api.qrcode-monkey.com/qr/custom?data=" & encodedURL & "&size=300"

' Opción 3: GoQR.me
qrApiURL = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" & encodedURL
```

## Estructura de Datos

### Parámetros de Entrada

| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| numeroFactura | String | Número único de factura | "12345" |
| montoFactura | Double | Monto total de la factura | 250.50 |
| puntos | Integer | Puntos asignados | 2 |
| numeroMembresia | Integer | Número de membresía (0 si no tiene) | 100 |
| rutaDestino | String | Ruta donde guardar el PNG | "C:\QR\qr_12345.png" |

### URL Generada

```
https://erp.batidospitaya.com/modulos/PitayaLove/?k=7848b961ddd37fd9&nf=12345&m=250.50&p=2&mb=100&t=online
```

## Seguridad

**⚠️ IMPORTANTE:** El `SECRET_SALT` en el código VBA **DEBE** coincidir exactamente con el del backend PHP:

**VBA:**
```vba
Private Const SECRET_SALT As String = "PITAYA_LOVE_2026_SECRET_KEY_XYZ"
```

**PHP (validate_qr.php):**
```php
$secret_salt_online = "PITAYA_LOVE_2026_SECRET_KEY_XYZ";
```

Si cambias uno, debes cambiar el otro.

## Solución de Problemas

### Error: "No se puede crear el objeto"
**Causa:** Falta referencia a MSXML2  
**Solución:** Habilitar "Microsoft XML, v6.0" en Referencias

### Error: "Acceso denegado" al guardar imagen
**Causa:** Permisos de carpeta  
**Solución:** Crear carpeta con permisos de escritura o usar carpeta temporal:
```vba
rutaQR = Environ("TEMP") & "\QR_" & numeroFactura & ".png"
```

### QR no valida en el sitio web
**Causa:** SECRET_SALT diferente entre VBA y PHP  
**Solución:** Verificar que ambos salts sean idénticos

### Imagen QR no se descarga
**Causa:** Sin conexión a internet o API caída  
**Solución:** Verificar conexión, probar API alternativa

## Personalización

### Cambiar Tamaño del QR

```vba
' En la función DescargarImagenQR, cambiar:
qrApiURL = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" & encodedURL
```

### Agregar Logo al QR

Usar API que soporte logos (requiere cuenta):
```vba
' QRCode Monkey con logo
qrApiURL = "https://api.qrcode-monkey.com/qr/custom?data=" & encodedURL & _
           "&size=300&logo=https://tudominio.com/logo.png"
```

## Integración con Reportes

### Agregar QR a Reporte de Factura

1. **Diseño del Reporte:**
   - Agregar control de imagen: `imgQRCode`
   - Tamaño recomendado: 3cm x 3cm

2. **Código en Evento Open:**
```vba
Private Sub Report_Open(Cancel As Integer)
    Dim rutaQR As String
    rutaQR = Environ("TEMP") & "\QR_" & Me.NumeroFactura & ".png"
    
    If GenerarQRFactura(Me.NumeroFactura, Me.MontoTotal, _
                        CalcularPuntos(Me.MontoTotal), _
                        Nz(Me.NumeroMembresia, 0), rutaQR) Then
        Me.imgQRCode.Picture = rutaQR
    Else
        Me.imgQRCode.Visible = False
    End If
End Sub
```

3. **Limpiar al Cerrar:**
```vba
Private Sub Report_Close()
    ' Eliminar archivo temporal
    On Error Resume Next
    Kill Environ("TEMP") & "\QR_" & Me.NumeroFactura & ".png"
End Sub
```

## Notas Adicionales

- Los QR se generan en tiempo real al imprimir/visualizar
- Requiere conexión a internet
- Tiempo de generación: ~1-2 segundos por QR
- Formato de salida: PNG, 300x300px, ~15KB por imagen
- Compatible con Access 2010, 2013, 2016, 2019, 365

## Soporte

Para problemas o consultas, verificar:
1. Conexión a internet activa
2. Referencias VBA habilitadas
3. SECRET_SALT coincide con backend
4. Permisos de escritura en carpeta destino
