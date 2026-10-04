' ============================================
' Módulo: PitayaLoveQRGenerator
' Descripción: Genera QR codes online para facturas de Pitaya Love
'              Con fallback a QRs offline si no hay conexión
' Autor: Sistema ERP Batidos Pitaya
' Fecha: 2026-02-07
' ============================================

Option Compare Database
Option Explicit

' Constante de seguridad - DEBE coincidir con el backend PHP
Private Const SECRET_SALT As String = "PITAYA_LOVE_2026_SECRET_KEY_XYZ"
Private Const BASE_URL As String = "https://pitayalove.batidospitaya.com/"

' Rutas configurables
Private Const RUTA_QR_DESTINO As String = "C:\Users\{USER}\Desktop\Sistema\"
Private Const RUTA_QR_OFFLINE As String = "C:\Users\{USER}\Google Drive BP\Base de Datos Pitaya\Sys Resources\offline_qrs\"

' Variable global para almacenar la última ruta generada
Private g_UltimaRutaQR As String

' ============================================
' DECLARACIONES WINDOWS API (BCRYPT) PARA SHA256
' ============================================
#If VBA7 Then
    Private Declare PtrSafe Function BCryptOpenAlgorithmProvider Lib "bcrypt.dll" (ByRef phAlgorithm As LongPtr, ByVal pszAlgId As LongPtr, ByVal pszImplementation As LongPtr, ByVal dwFlags As Long) As Long
    Private Declare PtrSafe Function BCryptCloseAlgorithmProvider Lib "bcrypt.dll" (ByVal hAlgorithm As LongPtr, ByVal dwFlags As Long) As Long
    Private Declare PtrSafe Function BCryptCreateHash Lib "bcrypt.dll" (ByVal hAlgorithm As LongPtr, ByRef phHash As LongPtr, ByVal pbHashObject As LongPtr, ByVal cbHashObject As Long, ByVal pbSecret As LongPtr, ByVal cbSecret As Long, ByVal dwFlags As Long) As Long
    Private Declare PtrSafe Function BCryptDestroyHash Lib "bcrypt.dll" (ByVal hHash As LongPtr) As Long
    Private Declare PtrSafe Function BCryptHashData Lib "bcrypt.dll" (ByVal hHash As LongPtr, ByVal pbInput As LongPtr, ByVal cbInput As Long, ByVal dwFlags As Long) As Long
    Private Declare PtrSafe Function BCryptFinishHash Lib "bcrypt.dll" (ByVal hHash As LongPtr, ByVal pbOutput As LongPtr, ByVal cbOutput As Long, ByVal dwFlags As Long) As Long
    Private Declare PtrSafe Function BCryptGetProperty Lib "bcrypt.dll" (ByVal hObject As LongPtr, ByVal pszProperty As LongPtr, ByVal pbOutput As LongPtr, ByVal cbOutput As Long, ByRef pcbResult As Long, ByVal dwFlags As Long) As Long
    Private Declare PtrSafe Function WideCharToMultiByte Lib "kernel32" (ByVal CodePage As Long, ByVal dwFlags As Long, ByVal lpWideCharStr As LongPtr, ByVal cchWideChar As Long, ByVal lpMultiByteStr As LongPtr, ByVal cbMultiByte As Long, ByVal lpDefaultChar As LongPtr, ByVal lpUsedDefaultChar As LongPtr) As Long
#Else
    Private Declare Function BCryptOpenAlgorithmProvider Lib "bcrypt.dll" (ByRef phAlgorithm As Long, ByVal pszAlgId As Long, ByVal pszImplementation As Long, ByVal dwFlags As Long) As Long
    Private Declare Function BCryptCloseAlgorithmProvider Lib "bcrypt.dll" (ByVal hAlgorithm As Long, ByVal dwFlags As Long) As Long
    Private Declare Function BCryptCreateHash Lib "bcrypt.dll" (ByVal hAlgorithm As Long, ByRef phHash As Long, ByVal pbHashObject As Long, ByVal cbHashObject As Long, ByVal pbSecret As Long, ByVal cbSecret As Long, ByVal dwFlags As Long) As Long
    Private Declare Function BCryptDestroyHash Lib "bcrypt.dll" (ByVal hHash As Long) As Long
    Private Declare Function BCryptHashData Lib "bcrypt.dll" (ByVal hHash As Long, ByVal pbInput As Long, ByVal cbInput As Long, ByVal dwFlags As Long) As Long
    Private Declare Function BCryptFinishHash Lib "bcrypt.dll" (ByVal hHash As Long, ByVal pbOutput As Long, ByVal cbOutput As Long, ByVal dwFlags As Long) As Long
    Private Declare Function BCryptGetProperty Lib "bcrypt.dll" (ByVal hObject As Long, ByVal pszProperty As Long, ByVal pbOutput As Long, ByVal cbOutput As Long, ByRef pcbResult As Long, ByVal dwFlags As Long) As Long
    Private Declare Function WideCharToMultiByte Lib "kernel32" (ByVal CodePage As Long, ByVal dwFlags As Long, ByVal lpWideCharStr As Long, ByVal cchWideChar As Long, ByVal lpMultiByteStr As Long, ByVal cbMultiByte As Long, ByVal lpDefaultChar As Long, ByVal lpUsedDefaultChar As Long) As Long
#End If

Private Const CP_UTF8 As Long = 65001
Private Const BCRYPT_SHA256_ALGORITHM As String = "SHA256"

' ============================================
' Función principal para generar QR de factura
' CON RUTA AUTOMÁTICA Y FALLBACK A OFFLINE
' ============================================
Public Function GenerarQRFactura(numeroFactura As Long, _
                                 montoFactura As Double, _
                                 puntos As Integer, _
                                 Optional codigoLocal As Variant = "") As Boolean
    
    On Error GoTo ErrorHandler
    
    ' Validar parámetros
    If numeroFactura <= 0 Then
        MsgBox "Número de factura inválido", vbExclamation
        GenerarQRFactura = False
        Exit Function
    End If
    
    ' Validar puntos (1-100 para offline)
    If puntos < 1 Then puntos = 1
    If puntos > 100 Then puntos = 100
    
    ' Construir ruta destino automáticamente
    Dim rutaDestino As String
    rutaDestino = Replace(RUTA_QR_DESTINO, "{USER}", NombreSistema())
    
    ' Crear carpeta si no existe
    If Dir(rutaDestino, vbDirectory) = "" Then
        MkDir rutaDestino
    End If
    
    rutaDestino = rutaDestino & "QR_" & numeroFactura & ".png"
    
    ' Guardar ruta para LimpiarQR
    g_UltimaRutaQR = rutaDestino
    
    ' Verificar conexión a internet y API
    Dim hayConexion As Boolean
    hayConexion = VerificarConexionAPI()
    
    Dim resultado As Boolean
    
    If hayConexion Then
        ' MODO ONLINE: Generar QR dinámico
        
        Dim securityKey As String
        securityKey = GenerarCodigoSeguridad(CStr(numeroFactura), montoFactura, puntos, codigoLocal)
        
        Dim qrURL As String
        qrURL = BASE_URL & "?k=" & securityKey & _
                "&nf=" & numeroFactura & _
                "&m=" & Format(montoFactura, "0.00") & _
                "&p=" & puntos & _
                "&cl=" & codigoLocal & _
                "&t=online"
        
        resultado = DescargarImagenQR(qrURL, rutaDestino)
        
        If resultado Then

        Else
            ' Si falla la descarga, intentar offline
            resultado = UsarQROffline(puntos, codigoLocal, rutaDestino)
        End If
    Else
        ' MODO OFFLINE: Usar QR pre-generado
        resultado = UsarQROffline(puntos, codigoLocal, rutaDestino)
    End If

    
    GenerarQRFactura = resultado
    Exit Function
    
ErrorHandler:
    MsgBox "Error generando QR: " & Err.Description, vbCritical
    GenerarQRFactura = False
End Function

' ============================================
' Obtiene la ruta del último QR generado
' ============================================
Public Function ObtenerRutaQR() As String
    ObtenerRutaQR = g_UltimaRutaQR
End Function

' ============================================
' Limpia (elimina) el archivo QR generado
' ============================================
Public Sub LimpiarQR()
    On Error Resume Next
    
    If g_UltimaRutaQR <> "" Then
        If Dir(g_UltimaRutaQR) <> "" Then
            Kill g_UltimaRutaQR
        End If
        g_UltimaRutaQR = ""
    End If
End Sub

' ============================================
' Limpia un QR específico por número de factura
' ============================================
Public Sub LimpiarQRFactura(numeroFactura As Long)
    On Error Resume Next
    
    Dim rutaQR As String
    rutaQR = Replace(RUTA_QR_DESTINO, "{USER}", NombreSistema())
    rutaQR = rutaQR & "QR_" & numeroFactura & ".png"
    
    If Dir(rutaQR) <> "" Then
        Kill rutaQR
    End If
End Sub

' ============================================
' Verifica si hay conexión con la API
' ============================================
Private Function VerificarConexionAPI() As Boolean
    On Error GoTo NoConexion
    
    Dim objHTTP As Object
    Set objHTTP = CreateObject("MSXML2.ServerXMLHTTP.6.0")
    
    ' Timeout corto para no esperar mucho
    objHTTP.setTimeouts 2000, 2000, 3000, 3000 ' resolve, connect, send, receive (ms)
    
    ' Intentar conectar con Google (más confiable que la API de QR)
    objHTTP.Open "GET", "https://www.google.com", False
    objHTTP.send
    
    ' Si llegamos aquí y el status es 200, hay conexión
    If objHTTP.Status = 200 Then
        VerificarConexionAPI = True
    Else
        VerificarConexionAPI = False
    End If
    
    Set objHTTP = Nothing
    Exit Function
    
NoConexion:
    VerificarConexionAPI = False
    Set objHTTP = Nothing
End Function

' ============================================
' Usa un QR offline pre-generado según puntos
' ============================================
Private Function UsarQROffline(puntos As Integer, codigoLocal As Variant, rutaDestino As String) As Boolean
    On Error GoTo ErrorHandler
    
    ' Validar puntos (1-20 para el nuevo banco)
    If puntos < 1 Or puntos > 20 Then
        ' Fallback a 20 si es mayor (el banco actual es hasta 20)
        If puntos > 20 Then puntos = 20
    End If
    
    ' Validar codigoLocal (1-20)
    Dim sucursalId As Integer
    sucursalId = Val(codigoLocal)
    If sucursalId < 1 Or sucursalId > 20 Then
        ' Si no hay sucursal válida, usar sucursal 1 por defecto
        sucursalId = 1
    End If
    
    ' Construir nombre del archivo offline
    ' Formato: qr_offline_s01_p10.png
    Dim nombreArchivoOffline As String
    nombreArchivoOffline = "qr_offline_s" & Format(sucursalId, "00") & "_p" & Format(puntos, "02") & ".png"
    
    ' Ruta completa del QR offline
    Dim rutaOffline As String
    rutaOffline = Replace(RUTA_QR_OFFLINE, "{USER}", NombreSistema())
    rutaOffline = rutaOffline & nombreArchivoOffline
    
    ' Verificar que existe el archivo
    If Dir(rutaOffline) = "" Then
        MsgBox "QR offline no encontrado: " & vbCrLf & rutaOffline, vbCritical
        UsarQROffline = False
        Exit Function
    End If
    
    ' Copiar archivo a destino
    FileCopy rutaOffline, rutaDestino
    
    UsarQROffline = True
    Exit Function
    
ErrorHandler:
    MsgBox "Error usando QR offline: " & Err.Description, vbCritical
    UsarQROffline = False
End Function

' ============================================
' Genera el código de seguridad SHA256
' ============================================
Private Function GenerarCodigoSeguridad(numeroFactura As String, _
                                        monto As Double, _
                                        puntos As Integer, _
                                        Optional codigoLocal As Variant = "") As String
    
    ' Construir string para hash (sin membresía)
    Dim dataString As String
    dataString = numeroFactura & Format(monto, "0.00") & puntos & codigoLocal & SECRET_SALT
    
    ' Generar hash SHA256
    Dim hash As String
    hash = SHA256Hash(dataString)
    
    ' Retornar primeros 16 caracteres
    GenerarCodigoSeguridad = Left(hash, 16)
End Function

' ============================================
' Función SHA256 usando NATIVE WINDOWS API (BCrypt)
' NO DEPENDE DE .NET NI COMPONENTES EXTERNOS
' ============================================
Private Function SHA256Hash(texto As String) As String
    On Error GoTo ErrorHandler
    
    #If VBA7 Then
        Dim hAlg As LongPtr, hHash As LongPtr
    #Else
        Dim hAlg As Long, hHash As Long
    #End If
    
    Dim status As Long
    Dim hashLen As Long
    Dim hashData() As Byte, inputData() As Byte
    Dim UTF8Len As Long
    Dim i As Long
    Dim res As String
    
    ' 1. Convertir texto UTF-16 a UTF-8 usando la API de Windows
    UTF8Len = WideCharToMultiByte(CP_UTF8, 0, StrPtr(texto), Len(texto), 0, 0, 0, 0)
    If UTF8Len <= 0 Then Exit Function
    
    ReDim inputData(UTF8Len - 1)
    status = WideCharToMultiByte(CP_UTF8, 0, StrPtr(texto), Len(texto), VarPtr(inputData(0)), UTF8Len, 0, 0)
    
    ' 2. Abrir proveedor SHA256
    status = BCryptOpenAlgorithmProvider(hAlg, StrPtr(BCRYPT_SHA256_ALGORITHM & vbNullChar), 0, 0)
    If status <> 0 Then GoTo CleanUp
    
    ' 3. Crear el objeto hash
    status = BCryptCreateHash(hAlg, hHash, 0, 0, 0, 0, 0)
    If status <> 0 Then GoTo CleanUp
    
    ' 4. Hashear los datos (en UTF-8)
    status = BCryptHashData(hHash, VarPtr(inputData(0)), UTF8Len, 0)
    If status <> 0 Then GoTo CleanUp
    
    ' 5. Preparar buffer para el resultado (32 bytes para SHA256)
    hashLen = 32
    ReDim hashData(hashLen - 1)
    
    ' 6. Finalizar hash
    status = BCryptFinishHash(hHash, VarPtr(hashData(0)), hashLen, 0)
    If status <> 0 Then GoTo CleanUp
    
    ' 7. Convertir a hexadecimal
    res = ""
    For i = 0 To hashLen - 1
        res = res & Right("0" & Hex(hashData(i)), 2)
    Next i
    
    SHA256Hash = LCase(res)

CleanUp:
    If hHash <> 0 Then BCryptDestroyHash hHash
    If hAlg <> 0 Then BCryptCloseAlgorithmProvider hAlg, 0
    Exit Function
    
ErrorHandler:
    SHA256Hash = ""
End Function

' ============================================
' Descarga imagen QR desde API externa
' ============================================
Private Function DescargarImagenQR(urlData As String, rutaDestino As String) As Boolean
    On Error GoTo ErrorHandler
    
    Dim qrApiURL As String
    Dim encodedURL As String
    
    ' Codificar URL para API
    encodedURL = URLEncode(urlData)
    
    ' Construir URL de la API
    qrApiURL = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" & encodedURL & "&margin=10"
    
    ' Descargar imagen
    Dim objHTTP As Object
    Set objHTTP = CreateObject("MSXML2.ServerXMLHTTP.6.0")
    
    ' Timeouts
    objHTTP.setTimeouts 5000, 5000, 10000, 10000
    
    objHTTP.Open "GET", qrApiURL, False
    objHTTP.send
    
    If objHTTP.Status = 200 Then
        ' Guardar imagen
        Dim objStream As Object
        Set objStream = CreateObject("ADODB.Stream")
        
        objStream.Type = 1 ' adTypeBinary
        objStream.Open
        objStream.Write objHTTP.responseBody
        objStream.SaveToFile rutaDestino, 2 ' adSaveCreateOverWrite
        objStream.Close
        
        Set objStream = Nothing
        DescargarImagenQR = True
    Else
        DescargarImagenQR = False
    End If
    
    Set objHTTP = Nothing
    Exit Function
    
ErrorHandler:
    DescargarImagenQR = False
End Function

' ============================================
' Codifica URL para uso en APIs
' ============================================
Private Function URLEncode(texto As String) As String
    Dim i As Long
    Dim char As String
    Dim asciiVal As Integer
    Dim resultado As String
    
    resultado = ""
    For i = 1 To Len(texto)
        char = Mid(texto, i, 1)
        asciiVal = Asc(char)
        
        ' Caracteres seguros (no codificar)
        If (asciiVal >= 48 And asciiVal <= 57) Or _
           (asciiVal >= 65 And asciiVal <= 90) Or _
           (asciiVal >= 97 And asciiVal <= 122) Or _
           char = "-" Or char = "_" Or char = "." Or char = "~" Then
            resultado = resultado & char
        Else
            ' Codificar caracteres especiales
            resultado = resultado & "%" & Right("0" & Hex(asciiVal), 2)
        End If
    Next i
    
    URLEncode = resultado
End Function


