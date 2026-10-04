<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pitaya Love</title>
    <link rel="icon" type="image/png" href="img/icon12.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css?v=<?php echo mt_rand(1, 10000); ?>">
</head>

<body>

    <header class=" page-header">
        <img src="img/logo.png" alt="Pitaya Love" class="logo-top">
    </header>

    <nav class="tabs-nav">
        <button class="tab-btn active" onclick="showTab('registro', event)">Registro</button>
        <button class="tab-btn" onclick="showTab('reglas', event)">Reglas</button>
        <button class="tab-btn" onclick="showTab('premios', event)">Premios</button>
    </nav>

    <!-- Tab 1: Registro -->
    <section id="registro" class="tab-content active">
        <div class="hero" style="background-image: url('img/pasisaje1.png');">
            <h2 class="hero-title">¡PARTICIPÁ Y GANA!</h2>
        </div>

        <div class="content-container">
            <div class="info-card">

                <form id="registrationForm" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="nombre_completo">Nombre Completo:</label>
                            <input type="text" id="nombre_completo" name="nombre_completo" placeholder="Ej: Juan Pérez"
                                required onblur="capitalizeName(this)">
                        </div>
                        <div class="form-group">
                            <label for="numero_contacto">No. Contacto:</label>
                            <input type="tel" id="numero_contacto" name="numero_contacto" placeholder="Ej: 88888888"
                                required maxlength="8" pattern="[0-9]{8}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="form-group">
                            <label for="numero_cedula">No. Cédula:</label>
                            <input type="text" id="numero_cedula" name="numero_cedula" placeholder="000-000000-0000X">
                        </div>
                        <div class="form-group full-width">
                            <label for="correo_electronico">Correo electrónico:</label>
                            <input type="email" id="correo_electronico" name="correo_electronico"
                                placeholder="usuario@gmail.com" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.com$">
                        </div>
                        <div class="form-group">
                            <label for="numero_factura">Código de Sorteo:</label>
                            <input type="text" id="numero_factura" name="numero_factura" placeholder="Ej: 12345"
                                required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>

                        <div class="form-group">
                            <label for="monto_factura">Monto de Factura:</label>
                            <input type="number" step="0.01" id="monto_factura" name="monto_factura"
                                placeholder="C$ 0.00" required min="0">
                        </div>
                        <div class="form-group">
                            <label for="foto_factura">Foto de Factura:</label>
                            <div class="camera-container">
                                <button type="button" class="camera-btn" onclick="openCamera()">📷 Tomar Foto</button>
                                <div id="photoPreviewContainer"></div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">ENVIAR REGISTRO</button>
                    <div id="formMessage" style="margin-top: 15px; text-align: center; font-weight: 600;"></div>
                </form>
            </div>
        </div>
    </section>

    <!-- Tab 2: Reglas -->
    <section id="reglas" class="tab-content">
        <div class="hero" style="background-image: url('img/pasisaje2.png');">
            <h2 class="hero-title">¿CÓMO PARTICIPAR?</h2>
        </div>

        <div class="content-container">
            <div class="info-card">
                <div style="text-align: center;">
                    <p style="margin-bottom: 20px; font-weight: 600;">La participación se realizará mediante la
                        acumulación de puntos, los cuales se convertirán automáticamente en acciones para el sorteo.</p>
                </div>

                <div class="rules-steps">
                    <div class="step-item">
                        <img src="img/escanearcodigo.png" alt="QR">
                        <h4>Escanear el código QR</h4>
                        <p>impreso en su factura</p>
                    </div>
                    <div class="step-item">
                        <img src="img/completarformulario.png" alt="Form">
                        <h4>Completar el formulario Digital</h4>
                        <p>Datos personales, Fotografía de la factura* <br> Conservar la factura original.</p>
                    </div>
                </div>
                <p class="note-footer" style="margin-top: 15px; margin-bottom: 30px;">*Facturas ilegibles, incompletas,
                    alteradas,
                    duplicadas, fuera de fecha o no registradas via QR seran automaticamente invalidadas.<br></p>

                <div class="dark-teal-header">ACUMULACIÓN DE PUNTOS POR MONTO DE COMPRA</div>
                <div class="section-box">
                    <div style="font-weight: 700; color: var(--primary-teal); margin-bottom: 10px;">• CLIENTE REGULAR •
                    </div>
                    <div class="row-item">
                        <span>Por cada <strong>C$250.00</strong> en compras realizadas</span>
                        <span class="row-value">→ 1 punto</span>
                    </div>

                    <div style="font-weight: 700; color: var(--primary-teal); margin: 20px 0 10px;">• CLUB PITAYA •
                    </div>
                    <div class="row-item">
                        <span>Por cada <strong>C$250.00</strong> en compras realizadas</span>
                        <span class="row-value">→ 2 puntos</span>
                    </div>
                    <p class="note-footer" style="margin-top: 15px;">*El cálculo de los puntos se realizará únicamente
                        sobre compras efectivamente facturadas durante la vigencia de la rifa.</p>
                </div>

                <div class="dark-teal-header" style="background: var(--primary-teal);">PUNTOS ADICIONALES POR COMPRA CON
                    FRUTOS SECOS</div>
                <div class="section-box" style="border-color: var(--primary-teal); text-align: left;">
                    <p style="margin-bottom: 15px;">Cuando la factura incluya al menos un producto Pitaya que contenga
                        frutos secos, se otorgarán puntos adicionales de la siguiente forma:</p>
                    <div class="row-item">
                        <span>Cliente regular:</span>
                        <span class="row-value">1 punto adicional</span>
                    </div>
                    <div class="row-item">
                        <span>Club Pitaya:</span>
                        <span class="row-value">2 puntos adicionales</span>
                    </div>
                    <p style="margin-top: 15px; font-size: 0.85rem;">Estos puntos adicionales se sumarán automáticamente
                        a los puntos obtenidos por el monto de compra.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tab 3: Premios -->
    <section id="premios" class="tab-content">
        <div class="hero" style="background-image: url('img/pasisaje3.png');">
            <h2 class="hero-title">NUESTROS PREMIOS</h2>
        </div>

        <div class="content-container">
            <div class="info-card">
                <div style="text-align: center;">
                </div>

                <div class="prizes-container">
                    <!-- First Place -->
                    <div class="prize-block">
                        <h4>PRIMER LUGAR</h4>
                        <ul class="prize-list">
                            <li>Viaje de <strong>un día a TreeCasa</strong>, Nicaragua para <strong>dos
                                    personas</strong>. Incluye únicamente desayuno y cena.</li>
                            <li>Kit Pitaya para dos personas.</li>
                        </ul>

                        <h4>El premio NO incluye:</h4>
                        <ul class="prize-list">
                            <li>Transporte hacia o desde TreeCasa.</li>
                            <li>Gastos adicionales, consumos extras, bebidas, actividades, propinas y otros cargos
                                generados durante la estancia.</li>
                            <p class="prize-exclusions" style="font-style: italic;">*No incluye trasnporte ni otros
                                gastos adicionales que se generen producto de la visita a Trecasa.</p>
                        </ul>

                    </div>

                    <!-- Second Place -->
                    <div class="prize-block">
                        <h4>SEGUNDO LUGAR</h4>
                        <ul class="prize-list">
                            <li>Viaje para dos personas a un destino nacional (Isla de Ometepe / Day
                                pass / ocho de marzo), saliendo exclusivamente desde Managua, gestionado con Transporte
                                Davila.</li>
                            <li>Kit Pitaya para dos personas.</li>
                        </ul>
                        <p class="prize-exclusions" style="font-style: italic;">*No incluye gastos no especificados
                            expresamente.</p>
                    </div>

                    <!-- Third Place -->
                    <div class="prize-block">
                        <h4>TERCER LUGAR</h4>
                        <ul class="prize-list">
                            <li>Canasta de productos Pitaya.</li>
                            <li>Desayuno Pitaya para dos personas:</li>
                            <ul style="margin: 5px 0 0 15px; list-style: circle;">
                                <li>Dos waffles especiales.</li>
                                <li>Dos batidos a elección.</li>
                            </ul>

                        </ul>
                    </div>
                </div>

                <p class="note-footer">
                    *Cada participante podrá resultar ganador de 1 solo premio durante la rifa. <br>
                    Los premios no son transferibles ni canjeables por dinero en efectivo.
                </p>
            </div>
        </div>
    </section>

    <!-- Camera Modal -->
    <div id="cameraModal">
        <div class="camera-content">
            <div class="camera-container">
                <video id="video" autoplay playsinline></video>
                <canvas id="canvas"></canvas>

                <!-- Overlay with guides -->
                <div class="camera-overlay">
                    <div class="camera-overlay-corner corner-tl"></div>
                    <div class="camera-overlay-corner corner-tr"></div>
                    <div class="camera-overlay-corner corner-bl"></div>
                    <div class="camera-overlay-corner corner-br"></div>

                    <!-- Guides for specific areas -->
                    <div class="area-guide codigo-guide">
                        <span>Código de Sorteo</span>
                    </div>
                    <div class="area-guide puntos-guide">
                        <span>Puntos</span>
                    </div>
                </div>
            </div>

            <div class="camera-controls">
                <button id="captureBtn" onclick="capturePhoto()">📸 Capturar</button>
                <button id="closeCameraBtn" onclick="closeCamera()">✖ Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal">
        <div class="success-content">
            <div class="success-icon">🎉</div>
            <h3 class="success-title">¡Listo!</h3>
            <p class="success-message">
                Ya estás participando en Pitaya Love!<br>
                <strong id="pointsDisplay"></strong><br>
                Ahora deja que la suerte haga lo suyo💚
            </p>
            <button class="success-btn" onclick="closeSuccessModal()">Entendido</button>
        </div>
    </div>

    <script>
        // QR System Constants
        const QR_DATA_KEY = 'pitaya_qr_data';

        let stream = null;
        let capturedPhotoBlob = null;

        // Initialize QR validation on page load
        document.addEventListener('DOMContentLoaded', function () {
            initializeQRValidation();
        });

        function initializeQRValidation() {
            // Try to recover from sessionStorage first
            let qrData = sessionStorage.getItem(QR_DATA_KEY);

            if (!qrData) {
                // Read from URL
                const urlParams = new URLSearchParams(window.location.search);

                if (urlParams.has('k')) {
                    qrData = {
                        key: urlParams.get('k'),
                        nf: urlParams.get('nf'),
                        m: urlParams.get('m'),
                        p: urlParams.get('p'),
                        mb: urlParams.get('mb'),
                        cl: urlParams.get('cl') || '',
                        type: urlParams.get('t')
                    };

                    // Save to session
                    sessionStorage.setItem(QR_DATA_KEY, JSON.stringify(qrData));

                    // Clean URL
                    window.history.replaceState({}, document.title, window.location.pathname);
                } else {
                    // No QR - lock form
                    lockFormCompletely();
                    return;
                }
            } else {
                qrData = JSON.parse(qrData);
            }

            // Validate with backend
            validateQR(qrData);
        }

        async function validateQR(qrData) {
            try {
                const response = await fetch('validate_qr.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(qrData)
                });

                const result = await response.json();

                if (result.valid) {
                    if (qrData.type === 'online') {
                        unlockFormOnline(qrData);
                    } else {
                        unlockFormOffline(qrData);
                    }
                } else {
                    showError('QR inválido o expirado: ' + (result.message || ''));
                    lockFormCompletely();
                }
            } catch (error) {
                showError('Error al validar QR');
                lockFormCompletely();
            }
        }

        function unlockFormOnline(qrData) {
            // Show form
            document.getElementById('registrationForm').style.display = 'block';
            const qrMsg = document.getElementById('qrRequiredMessage');
            if (qrMsg) qrMsg.style.display = 'none';

            // Auto-fill and lock fields
            const numeroFactura = document.getElementById('numero_factura');
            numeroFactura.value = qrData.nf || '';
            numeroFactura.readOnly = true;
            numeroFactura.style.backgroundColor = '#f5f5f5';

            const montoFactura = document.getElementById('monto_factura');
            montoFactura.value = qrData.m || '';
            montoFactura.readOnly = true;
            montoFactura.style.backgroundColor = '#f5f5f5';

            // Add hidden inputs for puntos, tipo_qr, and codigo_local
            addHiddenInput('puntos_factura', qrData.p || '0');
            addHiddenInput('tipo_qr', 'online');
            addHiddenInput('codigo_local', qrData.cl || '');
        }

        function unlockFormOffline(qrData) {
            // Show form - all fields editable
            document.getElementById('registrationForm').style.display = 'block';
            const qrMsg = document.getElementById('qrRequiredMessage');
            if (qrMsg) qrMsg.style.display = 'none';

            // Add hidden inputs
            addHiddenInput('puntos_factura', qrData.p || '0');
            addHiddenInput('tipo_qr', 'offline');
            addHiddenInput('codigo_local', qrData.cl || '');
        }

        function lockFormCompletely() {
            document.getElementById('registrationForm').style.display = 'none';

            let message = document.getElementById('qrRequiredMessage');
            if (!message) {
                message = document.createElement('div');
                message.id = 'qrRequiredMessage';
                message.className = 'qr-required-message';
                message.innerHTML = `
                    <div class="lock-icon">🔒</div>
                    <h2>Acceso Restringido</h2>
                    <p>Para participar en Pitaya Love, escanea el código QR impreso en tu factura de compra.</p>
                `;
                const infoCard = document.querySelector('.info-card');
                if (infoCard) infoCard.appendChild(message);
            }
            message.style.display = 'block';
        }

        function addHiddenInput(name, value) {
            let input = document.getElementById(name + '_hidden');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.id = name + '_hidden';
                input.name = name;
                document.getElementById('registrationForm').appendChild(input);
            }
            input.value = value;
        }


        function showError(message) {
            const messageDiv = document.getElementById('formMessage');
            if (messageDiv) {
                messageDiv.textContent = message;
                messageDiv.style.color = 'red';
            }
        }

        // Capitalize each word in name
        function capitalizeName(input) {
            const words = input.value.trim().toLowerCase().split(/\s+/);
            if (words.length < 2) {
                input.setCustomValidity('Por favor ingrese nombre y apellido');
                return;
            }
            input.setCustomValidity('');
            input.value = words.map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        }

        async function openCamera() {
            const modal = document.getElementById('cameraModal');
            const video = document.getElementById('video');

            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' }
                });
                video.srcObject = stream;
                modal.classList.add('active');
            } catch (error) {
                alert('No se pudo acceder a la cámara. Por favor, verifica los permisos.');
            }
        }

        function closeCamera() {
            const modal = document.getElementById('cameraModal');
            const video = document.getElementById('video');

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }

            video.srcObject = null;
            modal.classList.remove('active');
        }

        async function capturePhoto() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0);

            // Show validation status
            showValidationStatus('Procesando factura...', 'info');

            canvas.toBlob(async (blob) => {
                capturedPhotoBlob = blob;

                // Show preview
                const previewContainer = document.getElementById('photoPreviewContainer');
                previewContainer.innerHTML = `<img src="${URL.createObjectURL(blob)}" class="photo-preview" alt="Vista previa">`;

                // Detect invoice areas with AI
                await detectInvoiceAreas(blob);

                closeCamera();
            }, 'image/jpeg', 0.95);
        }

        async function detectInvoiceAreas(blob) {
            showValidationStatus('Validando factura con IA...', 'info');

            const formData = new FormData();
            formData.append('invoice_image', blob, 'factura.jpg');

            try {
                const response = await fetch('detect_invoice_areas.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Validate detected data vs input data
                    validateDetectedData(result);

                    // Mark as AI validated if detection was successful
                    if (result.codigo_participar && result.puntos) {
                        addHiddenInput('validado_ia', '1');
                    } else {
                        addHiddenInput('validado_ia', '0');
                    }
                } else {
                    // No bloquear el registro, solo advertir
                    showValidationStatus('ℹ️ Validación automática no disponible. Verifica manualmente los datos.', 'info');
                    addHiddenInput('validado_ia', '0');
                }
            } catch (error) {
                // No bloquear el registro si falla la API
                showValidationStatus('ℹ️ Validación automática no disponible. Verifica manualmente los datos.', 'info');
                addHiddenInput('validado_ia', '0');
            }
        }

        function validateDetectedData(aiResult) {
            const inputInvoice = document.getElementById('numero_factura').value;
            const inputAmount = parseFloat(document.getElementById('monto_factura').value);
            const inputPoints = document.getElementById('puntos_factura_hidden') ? document.getElementById('puntos_factura_hidden').value : '0';

            // Agregar campos hidden para datos de IA
            addHiddenInput('codigo_sorteo_ia', aiResult.codigo_sorteo_ia !== null && aiResult.codigo_sorteo_ia !== undefined ? aiResult.codigo_sorteo_ia : '');
            addHiddenInput('puntos_ia', aiResult.puntos_ia !== null && aiResult.puntos_ia !== undefined ? aiResult.puntos_ia : '');

            // Mostrar resultados detectados
            showAIResults(aiResult);

            let warnings = [];
            let allGood = true;

            // Verificar si se detectó al menos un valor
            const codigoDetectado = aiResult.codigo_sorteo_ia !== null && aiResult.codigo_sorteo_ia !== undefined;
            const puntosDetectados = aiResult.puntos_ia !== null && aiResult.puntos_ia !== undefined;

            // Validar código de sorteo (codigo_sorteo_ia)
            if (codigoDetectado) {
                if (inputInvoice && aiResult.codigo_sorteo_ia != inputInvoice) {
                    warnings.push(`Código: ${aiResult.codigo_sorteo_ia} ≠ ${inputInvoice}`);
                    allGood = false;
                }
            }

            // Validar puntos (puntos_ia)
            if (puntosDetectados) {
                if (inputPoints && aiResult.puntos_ia != inputPoints) {
                    warnings.push(`Puntos: ${aiResult.puntos_ia} ≠ ${inputPoints}`);
                    allGood = false;
                }
            }

            // Determinar mensaje según resultados
            if (!codigoDetectado && !puntosDetectados) {
                // Caso 1: No se detectó nada
                showValidationStatus('⚠️ Factura no legible - No se detectaron datos. Puede retomar la foto o continuar a revisión manual.', 'warning');
            } else if (allGood && codigoDetectado && puntosDetectados) {
                // Caso 2: Todo coincide
                showValidationStatus('✅ Factura validada correctamente - Los datos coinciden', 'success');
            } else if (warnings.length > 0) {
                // Caso 3: No coinciden
                showValidationStatus('❌ Datos no coinciden: ' + warnings.join(', ') + '. Puede retomar la foto o continuar a revisión manual.', 'error');
            } else {
                // Caso 4: Validación parcial (solo se detectó uno de los dos valores)
                showValidationStatus('⚠️ Detección parcial - Solo se detectó uno de los valores. Puede retomar la foto o continuar a revisión manual.', 'warning');
            }
        }

        function showAIResults(aiResult) {
            // Crear o actualizar div de resultados de IA
            let resultsDiv = document.getElementById('aiResultsDisplay');
            if (!resultsDiv) {
                resultsDiv = document.createElement('div');
                resultsDiv.id = 'aiResultsDisplay';
                resultsDiv.className = 'ai-results-display';
                const previewContainer = document.getElementById('photoPreviewContainer');
                if (previewContainer) {
                    previewContainer.after(resultsDiv);
                }
            }

            // Verificar valores detectados (null/undefined = no detectado)
            const codigoDetectado = aiResult.codigo_sorteo_ia !== null && aiResult.codigo_sorteo_ia !== undefined;
            const puntosDetectados = aiResult.puntos_ia !== null && aiResult.puntos_ia !== undefined;

            resultsDiv.innerHTML = `
                <div style="font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; color: var(--primary-teal);">
                    📊 Lectura Automática:
                </div>
                <div style="display: flex; gap: 15px; font-size: 0.85rem;">
                    <div style="flex: 1;">
                        <span style="color: #666;">Código:</span>
                        <strong style="color: ${codigoDetectado ? 'var(--primary-teal)' : '#999'}; margin-left: 5px;">
                            ${codigoDetectado ? aiResult.codigo_sorteo_ia : 'No detectado'}
                        </strong>
                    </div>
                    <div style="flex: 1;">
                        <span style="color: #666;">Puntos:</span>
                        <strong style="color: ${puntosDetectados ? 'var(--primary-teal)' : '#999'}; margin-left: 5px;">
                            ${puntosDetectados ? aiResult.puntos_ia : 'No detectado'}
                        </strong>
                    </div>
                </div>
            `;
            resultsDiv.style.display = 'block';
        }

        function showValidationStatus(message, type = 'info') {
            let statusDiv = document.getElementById('validationStatus');
            if (!statusDiv) {
                statusDiv = document.createElement('div');
                statusDiv.id = 'validationStatus';
                statusDiv.className = 'validation-status';
                const previewContainer = document.getElementById('photoPreviewContainer');
                if (previewContainer) {
                    previewContainer.after(statusDiv);
                }
            }
            statusDiv.textContent = message;
            statusDiv.className = `validation-status ${type}`;
            statusDiv.style.display = 'block';
        }

        function showTab(tabId, event) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            event.currentTarget.classList.add('active');
            document.getElementById(tabId).classList.add('active');

            window.scrollTo({ top: 300, behavior: 'smooth' });
        }

        document.getElementById('registrationForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const messageDiv = document.getElementById('formMessage');

            // Validate photo is captured
            if (!capturedPhotoBlob) {
                messageDiv.textContent = 'Por favor tome una foto de la factura';
                messageDiv.style.color = 'red';
                return;
            }

            // Add captured photo if exists
            if (capturedPhotoBlob) {
                formData.append('foto_factura', capturedPhotoBlob, 'factura.jpg');
            }

            messageDiv.textContent = 'Enviando registro...';
            messageDiv.style.color = 'var(--primary-teal)';

            fetch('save_registration.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Get points from hidden input
                        const puntosInput = document.getElementById('puntos_factura_hidden');
                        const puntos = puntosInput ? puntosInput.value : '0';

                        // Update success message with points
                        const pointsDisplay = document.getElementById('pointsDisplay');
                        if (pointsDisplay) {
                            pointsDisplay.textContent = `Participas con ${puntos} punto${puntos !== '1' ? 's' : ''}`;
                        }

                        // Show success modal
                        document.getElementById('successModal').classList.add('active');

                        // Reset form
                        form.reset();

                        // Clear photo preview and blob
                        capturedPhotoBlob = null;
                        document.getElementById('photoPreviewContainer').innerHTML = '';

                        // Clear message
                        messageDiv.textContent = '';
                    } else {
                        messageDiv.textContent = 'Error: ' + data.message;
                        messageDiv.style.color = 'red';
                    }
                })
                .catch(error => {
                    messageDiv.textContent = 'Error al conectar con el servidor.';
                    messageDiv.style.color = 'red';
                });
        });

        function closeSuccessModal() {
            document.getElementById('successModal').classList.remove('active');

            // Clear sessionStorage to allow new QR scan
            sessionStorage.removeItem(QR_DATA_KEY);

            // Lock form completely for next registration
            lockFormCompletely();
        }
    </script>
</body>

</html>