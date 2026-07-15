<?php
//BLOQUE INICIO DE SESION
session_start();

// CONTROLADOR MVC: index.php es la vista de acceso y AuthController recibe
// las acciones del formulario antes de delegarlas al Facade/Services.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    require_once __DIR__ . '/conexion.php';
    require_once __DIR__ . '/app/Controllers/AuthController.php';
    header('Content-Type: application/json');

    $controller = new AuthController(new SistemaDachiFacade($conn));
    $respuesta = $controller->handle($_POST);
    if (in_array($_POST['accion'], ['login', 'registro'], true)) {
        $respuesta = $controller->startSession($respuesta, $_SESSION);
    }

    echo json_encode($respuesta);
    $conn->close();
    exit();
}

//BLOQUE VERIFICAR SESION ACTIVA
if (isset($_SESSION['usuario'])) {
    $rolSesion = strtolower(trim($_SESSION['usuario']['nom_rol'] ?? ''));
    $redirect = match ($rolSesion) {
        'administrador' => 'admin.php',
        'logistico' => 'logistica.php',
        default => 'panel.php'
    };
    header("Location: $redirect");
    exit();
}
?>
<!DOCTYPE html>
<html class="light" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>DACHI | Iniciar Sesión</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=Source+Serif+4:wght@600;700&display=swap"
        rel="stylesheet" />
    <link href="css/dachi-brand.css" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        try {
            tailwind.config = {
                darkMode: "class",
                theme: {
                    extend: {
                        "colors": {
                            "on-tertiary-fixed": "#370c14", "surface-container-highest": "#e0e3df", "surface-container-high": "#e6e9e4",
                            "on-primary-fixed": "#00210e", "inverse-on-surface": "#eff2ed", "surface-container": "#ecefea",
                            "on-tertiary": "#ffffff", "primary-container": "#16482b", "tertiary-fixed-dim": "#ffb2b9",
                            "secondary-fixed-dim": "#f6be39", "on-surface": "#191c1a", "on-error": "#ffffff",
                            "on-primary-fixed-variant": "#1f5032", "outline": "#717971", "on-secondary-container": "#715300",
                            "surface-variant": "#e0e3df", "tertiary-fixed": "#ffdadc", "outline-variant": "#c1c9bf",
                            "on-tertiary-fixed-variant": "#6d363d", "tertiary-container": "#642f36", "on-secondary": "#ffffff",
                            "secondary-container": "#ffc641", "surface-container-lowest": "#ffffff", "primary-fixed": "#b9efc7",
                            "on-surface-variant": "#414942", "surface-variant2": "#e0e3df", "surface-tint": "#386848",
                            "background": "#f7faf5", "on-primary-container": "#83b691", "on-secondary-fixed-variant": "#5c4300",
                            "secondary": "#795900", "on-secondary-fixed": "#261a00", "surface-dim": "#d8dbd6",
                            "tertiary": "#491a21", "primary": "#003118", "surface-container-low": "#f1f4f0",
                            "inverse-surface": "#2d312e", "on-tertiary-container": "#e0979f", "error": "#ba1a1a",
                            "primary-fixed-dim": "#9ed3ac", "secondary-fixed": "#ffdfa0", "surface-bright": "#f7faf5",
                            "inverse-primary": "#9ed3ac", "surface": "#f7faf5", "error-container": "#ffdad6",
                            "on-error-container": "#93000a", "on-primary": "#ffffff", "on-background": "#191c1a"
                        },
                        "borderRadius": { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "12px", "2xl": "24px", "full": "9999px" },
                        "spacing": { "stack-xl": "64px", "gutter": "24px", "margin-desktop": "48px", "stack-lg": "32px", "base": "8px", "stack-md": "16px", "margin-mobile": "16px", "container-max": "1280px", "stack-sm": "8px" },
                        "fontFamily": { "label-md": ["Hanken Grotesk"], "label-sm": ["Hanken Grotesk"], "headline-md": ["'Source Serif 4'"], "display-lg": ["'Source Serif 4'"], "body-lg": ["Hanken Grotesk"], "body-md": ["Hanken Grotesk"], "headline-sm": ["'Source Serif 4'"] },
                        "fontSize": { "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.05em", "fontWeight": "600" }], "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "500" }], "headline-md": ["32px", { "lineHeight": "40px", "fontWeight": "600" }], "display-lg": ["56px", { "lineHeight": "64px", "letterSpacing": "-0.02em", "fontWeight": "700" }], "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }], "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }], "headline-sm": ["24px", { "lineHeight": "32px", "fontWeight": "600" }] }
                    }
                }
            }
        } catch (_e) { }
    </script>
    <link href="css/dachi-botanical.css" rel="stylesheet" />
</head>

<body class="dachi-auth bg-background text-on-background font-body-md h-screen overflow-hidden">
    <main class="flex flex-col md:flex-row h-full">

        <!-- BLOQUE VISUAL -->
        <section class="hidden md:flex relative md:w-1/2 lg:w-3/5 overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('img/Fond.png?v=2')"></div>
            <div
                class="absolute inset-0 bg-black/40 backdrop-blur-[1px] flex flex-col items-center justify-center p-margin-desktop text-white text-center">
                <div class="flex-1 flex flex-col items-center justify-center min-h-0">
                    <img alt="DACHI Logo"
                        class="h-48 lg:h-64 xl:h-80 w-auto object-contain brightness-0 invert mb-stack-lg"
                        src="img/LG.png?v=2" />
                    <div class="max-w-md">
                        <h1
                            class="font-headline-sm text-[20px] md:text-headline-sm mb-stack-sm leading-tight opacity-90">
                            Del campo panameño directo a tu mesa, sin intermediarios</h1>
                        <p class="font-body-md text-body-md opacity-85 max-w-xs mx-auto">Conectando productores locales
                            con tecnología de vanguardia para garantizar frescura y calidad.</p>
                    </div>
                </div>
                <div class="flex gap-stack-md justify-center mt-auto flex-none">
                    <div
                        class="flex items-center gap-2 bg-white/10 backdrop-blur-md px-4 py-2 rounded-full border border-white/20">
                        <span class="material-symbols-outlined text-secondary-fixed">verified</span>
                        <span class="font-label-sm text-label-sm">100% Orgánico</span>
                    </div>
                    <div
                        class="flex items-center gap-2 bg-white/10 backdrop-blur-md px-4 py-2 rounded-full border border-white/20">
                        <span class="material-symbols-outlined text-secondary-fixed">local_shipping</span>
                        <span class="font-label-sm text-label-sm">Logística de Precisión</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- BLOQUE AUTH -->
        <section class="flex-1 flex p-margin-mobile md:p-margin-desktop bg-surface overflow-hidden h-full min-h-0">
            <div class="w-full max-w-md py-stack-sm md:py-stack-lg mx-auto flex flex-col h-full min-h-0">

                <div class="md:hidden flex flex-col items-center mb-stack-sm flex-none">
                    <img alt="DACHI Logo" class="h-14 w-auto mb-1" src="img/LG.png?v=2" />
                    <h2 class="font-headline-sm text-headline-sm text-primary">Cultivando Confianza</h2>
                </div>

                <div
                    class="bg-surface-container-lowest rounded-2xl p-stack-lg border border-outline-variant/30 shadow-sm flex flex-col flex-1 min-h-0">

                    <!-- BLOQUE TABS -->
                    <div class="flex border-b border-outline-variant mb-stack-lg flex-none" id="authTabs">
                        <button
                            class="flex-1 pb-stack-sm font-label-md text-label-md transition-all border-b-2 border-primary text-primary"
                            id="loginTab" onclick="switchTab('login')">INGRESAR</button>
                        <button
                            class="flex-1 pb-stack-sm font-label-md text-label-md transition-all text-on-surface-variant hover:text-primary border-b-2 border-transparent"
                            id="signupTab" onclick="switchTab('signup')">REGISTRARSE</button>
                    </div>

                    <!-- BLOQUE FORMULARIOS -->
                    <div class="relative flex-1" id="formContainer">

                        <!-- BLOQUE LOGIN -->
                        <form class="absolute inset-0 space-y-stack-md overflow-y-auto" id="loginForm"
                            onsubmit="handleLogin(event)">
                            <div id="loginError"
                                class="hidden p-3 bg-error-container rounded-xl text-error font-label-sm text-label-sm text-center">
                            </div>
                            <div id="loginSuccess"
                                class="hidden p-3 bg-primary-container/10 border border-primary/20 rounded-xl text-primary font-label-sm text-label-sm text-center">
                            </div>
                            <div>
                                <label
                                    class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Correo
                                    Electrónico</label>
                                <input
                                    class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-white font-body-md"
                                    id="emailInput" placeholder="ejemplo@dachi.pa" required type="email" />
                            </div>
                            <div>
                                <label
                                    class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Contraseña</label>
                                <div class="relative">
                                    <input
                                        class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-white font-body-md"
                                        id="passwordInput" placeholder="••••••••" required type="password" />
                                    <button class="absolute right-4 top-3 text-on-surface-variant"
                                        onclick="togglePassword()" type="button">
                                        <span class="material-symbols-outlined text-[20px]"
                                            id="visibilityIcon">visibility</span>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary"
                                        id="rememberInput" type="checkbox" />
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">Recordarme</span>
                                </label>
                                <button class="font-label-sm text-label-sm text-secondary hover:underline"
                                    onclick="showRecovery()" type="button">¿Olvidó su contraseña?</button>
                            </div>
                            <button
                                class="w-full bg-primary text-on-primary py-4 rounded-xl font-label-md text-label-md hover:bg-primary-container transition-all active:scale-[0.98] mt-stack-sm"
                                type="submit">ACCEDER AL PANEL</button>
                        </form>

                        <!-- BLOQUE SIGNUP -->
                        <form class="absolute inset-0 space-y-stack-md overflow-y-auto hidden" id="signupForm"
                            onsubmit="handleSignup(event)">
                            <div id="signupError"
                                class="hidden p-3 bg-error-container rounded-xl text-error font-label-sm text-label-sm text-center">
                            </div>
                            <div class="grid grid-cols-2 gap-stack-md">
                                <div>
                                    <label
                                        class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Nombre</label>
                                    <input
                                        class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-white font-body-md"
                                        id="signupNombre" maxlength="60" minlength="2" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]+"
                                        placeholder="Juan" required type="text" />
                                </div>
                                <div>
                                    <label
                                        class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Apellido</label>
                                    <input
                                        class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-white font-body-md"
                                        id="signupApellido" maxlength="60" minlength="2"
                                        pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]+" placeholder="Pérez" required type="text" />
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block font-label-sm text-label-sm text-on-surface-variant mb-3 ml-1">Seleccione
                                    su Rol</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label
                                        class="flex flex-col items-center justify-center gap-2 p-3 border border-outline-variant rounded-xl cursor-pointer hover:border-primary has-[:checked]:border-primary has-[:checked]:bg-primary-container/5 transition-all text-center">
                                        <input class="hidden peer" name="role" required type="radio"
                                            value="consumidor" />
                                        <span
                                            class="material-symbols-outlined text-on-surface-variant peer-checked:text-primary">shopping_basket</span>
                                        <span
                                            class="font-label-sm text-label-sm text-on-surface-variant peer-checked:text-primary">Consumidor</span>
                                    </label>
                                    <label
                                        class="flex flex-col items-center justify-center gap-2 p-3 border border-outline-variant rounded-xl cursor-pointer hover:border-primary has-[:checked]:border-primary has-[:checked]:bg-primary-container/5 transition-all text-center">
                                        <input class="hidden peer" name="role" type="radio" value="productor" />
                                        <span
                                            class="material-symbols-outlined text-on-surface-variant peer-checked:text-primary">agriculture</span>
                                        <span
                                            class="font-label-sm text-label-sm text-on-surface-variant peer-checked:text-primary">Productor</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Correo
                                    Electrónico</label>
                                <input
                                    class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-white font-body-md"
                                    id="signupEmail" maxlength="150" placeholder="ejemplo@dachi.pa" required
                                    type="email" />
                            </div>
                            <div>
                                <label
                                    class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Contraseña</label>
                                <input
                                    class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-white font-body-md"
                                    id="signupPassword" minlength="8" placeholder="Mínimo 8 caracteres" required
                                    type="password" />
                                <p class="font-label-sm text-label-sm text-on-surface-variant mt-2 ml-1">
                                    Debe incluir mayúscula, minúscula y número.
                                </p>
                            </div>
                            <p
                                class="font-label-sm text-label-sm text-on-surface-variant text-center px-4 leading-relaxed">
                                Al registrarse, usted acepta nuestros <a class="text-primary underline"
                                    href="#">Términos de Servicio</a> y <a class="text-primary underline"
                                    href="#">Política de Privacidad</a>.</p>
                            <button
                                class="w-full bg-primary text-on-primary py-4 rounded-xl font-label-md text-label-md hover:bg-primary-container transition-all active:scale-[0.98]"
                                type="submit">CREAR CUENTA</button>
                        </form>

                        <!-- BLOQUE RECOVERY -->
                        <div class="absolute inset-0 space-y-stack-md overflow-y-auto hidden" id="recoverySection">
                            <div class="flex items-center gap-2 mb-stack-sm">
                                <button class="material-symbols-outlined text-on-surface-variant hover:text-primary"
                                    onclick="hideRecovery()">arrow_back</button>
                                <h3 class="font-headline-sm text-headline-sm text-primary">Recuperar Contraseña</h3>
                            </div>
                            <div id="recoveryError"
                                class="hidden p-3 bg-error-container rounded-xl text-error font-label-sm text-label-sm text-center">
                            </div>

                            <!-- PASO 1: SOLICITAR CODIGO -->
                            <div id="recoveryStepRequest">
                                <p class="font-body-md text-body-md text-on-surface-variant mb-stack-md">Ingrese su
                                    correo para enviarle un código de verificación.</p>
                                <form class="space-y-stack-md" id="recoveryForm" onsubmit="handleRecovery(event)">
                                    <div>
                                        <label
                                            class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Correo
                                            Electrónico</label>
                                        <input id="recoveryEmail"
                                            class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-white font-body-md"
                                            placeholder="ejemplo@dachi.pa" required type="email" />
                                    </div>
                                    <button
                                        class="w-full bg-primary text-on-primary py-4 rounded-xl font-label-md text-label-md hover:bg-primary-container transition-all active:scale-[0.98]"
                                        type="submit">ENVIAR CÓDIGO</button>
                                </form>
                            </div>

                            <!-- PASO 2: CONFIRMAR CODIGO Y NUEVA CONTRASENA -->
                            <div class="hidden" id="recoveryStepConfirm">
                                <div class="p-4 bg-primary-container/10 border border-primary/20 rounded-xl mb-stack-md"
                                    id="recoveryFeedback">
                                    <p class="font-label-sm text-label-sm text-primary text-center">Si el correo está
                                        registrado, se envió un código de verificación de 6 dígitos válido por 15
                                        minutos.</p>
                                </div>
                                <form class="space-y-stack-md" id="recoveryConfirmForm"
                                    onsubmit="handleRecoveryConfirm(event)">
                                    <div>
                                        <label
                                            class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Código
                                            de verificación</label>
                                        <input id="recoveryCode"
                                            class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-white font-body-md tracking-[0.3em] text-center"
                                            inputmode="numeric" maxlength="6" pattern="[0-9]{6}" placeholder="123456"
                                            required type="text" />
                                    </div>
                                    <div>
                                        <label
                                            class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Nueva
                                            Contraseña</label>
                                        <input id="recoveryNewPassword"
                                            class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-white font-body-md"
                                            minlength="8" placeholder="Mínimo 8 caracteres" required type="password" />
                                        <p class="font-label-sm text-label-sm text-on-surface-variant mt-2 ml-1">Debe
                                            incluir mayúscula, minúscula y número.</p>
                                    </div>
                                    <button
                                        class="w-full bg-primary text-on-primary py-4 rounded-xl font-label-md text-label-md hover:bg-primary-container transition-all active:scale-[0.98]"
                                        type="submit">CAMBIAR CONTRASEÑA</button>
                                    <button
                                        class="w-full text-center font-label-sm text-label-sm text-secondary hover:underline"
                                        onclick="volverASolicitudRecovery()" type="button">¿No recibió el código?
                                        Solicitar de nuevo</button>
                                </form>
                            </div>
                        </div>

                    </div>

                </div>
                <footer class="mt-stack-sm text-center flex-none">
                    <p class="font-label-sm text-label-sm text-on-surface-variant">© 2024 DACHI. Cultivando confianza en
                        Panamá.</p>
                </footer>
            </div>
        </section>
    </main>

    <script>
        //BLOQUE TABS
        function switchTab(type) {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const recoverySection = document.getElementById('recoverySection');
            const loginTab = document.getElementById('loginTab');
            const signupTab = document.getElementById('signupTab');
            const authTabs = document.getElementById('authTabs');

            recoverySection.classList.add('hidden');
            authTabs.classList.remove('hidden');
            document.getElementById('loginSuccess').classList.add('hidden');

            if (type === 'login') {
                loginForm.classList.remove('hidden');
                signupForm.classList.add('hidden');
                loginTab.classList.add('border-primary', 'text-primary');
                loginTab.classList.remove('text-on-surface-variant', 'border-transparent');
                signupTab.classList.remove('border-primary', 'text-primary');
                signupTab.classList.add('text-on-surface-variant', 'border-transparent');
            } else {
                signupForm.classList.remove('hidden');
                loginForm.classList.add('hidden');
                signupTab.classList.add('border-primary', 'text-primary');
                signupTab.classList.remove('text-on-surface-variant', 'border-transparent');
                loginTab.classList.remove('border-primary', 'text-primary');
                loginTab.classList.add('text-on-surface-variant', 'border-transparent');
            }
        }

        //BLOQUE RECOVERY
        function showRecovery() {
            document.getElementById('loginForm').classList.add('hidden');
            document.getElementById('signupForm').classList.add('hidden');
            document.getElementById('authTabs').classList.add('hidden');
            document.getElementById('recoverySection').classList.remove('hidden');
            volverASolicitudRecovery();
        }
        function hideRecovery() {
            document.getElementById('recoverySection').classList.add('hidden');
            switchTab('login');
        }
        function volverASolicitudRecovery() {
            document.getElementById('recoveryError').classList.add('hidden');
            document.getElementById('recoveryStepConfirm').classList.add('hidden');
            document.getElementById('recoveryStepRequest').classList.remove('hidden');
            document.getElementById('recoveryCode').value = '';
            document.getElementById('recoveryNewPassword').value = '';
        }

        function isValidRecoveryCode(value) {
            return /^\d{6}$/.test(value);
        }

        function handleRecovery(event) {
            event.preventDefault();
            const errorBox = document.getElementById('recoveryError');
            errorBox.classList.add('hidden');

            const correo = document.getElementById('recoveryEmail').value.trim();
            if (!correo || !isValidEmail(correo)) {
                showFormError(errorBox, 'Ingrese un correo electrónico válido');
                return;
            }

            const datos = new FormData();
            datos.append('accion', 'recuperar_solicitud');
            datos.append('correo', correo);

            fetch('index.php', { method: 'POST', body: datos })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('recoveryFeedback').querySelector('p').textContent = data.message;
                        document.getElementById('recoveryStepRequest').classList.add('hidden');
                        document.getElementById('recoveryStepConfirm').classList.remove('hidden');
                    } else {
                        showFormError(errorBox, data.message);
                    }
                })
                .catch(() => {
                    showFormError(errorBox, 'Error de conexión con el servidor.');
                });
        }

        function handleRecoveryConfirm(event) {
            event.preventDefault();
            const errorBox = document.getElementById('recoveryError');
            errorBox.classList.add('hidden');

            const correo = document.getElementById('recoveryEmail').value.trim();
            const codigo = document.getElementById('recoveryCode').value.trim();
            const nuevaContrasena = document.getElementById('recoveryNewPassword').value;

            if (!codigo || !nuevaContrasena) {
                showFormError(errorBox, 'Debe completar todos los campos');
                return;
            }

            if (!isValidRecoveryCode(codigo)) {
                showFormError(errorBox, 'El código debe tener 6 dígitos');
                return;
            }

            if (!isValidPassword(nuevaContrasena)) {
                showFormError(errorBox, 'La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula y un número');
                return;
            }

            const datos = new FormData();
            datos.append('accion', 'recuperar_confirmacion');
            datos.append('correo', correo);
            datos.append('codigo', codigo);
            datos.append('nueva_contrasena', nuevaContrasena);

            fetch('index.php', { method: 'POST', body: datos })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        hideRecovery();
                        const successBox = document.getElementById('loginSuccess');
                        successBox.textContent = 'Contraseña actualizada. Ya puede iniciar sesión.';
                        successBox.classList.remove('hidden');
                    } else {
                        showFormError(errorBox, data.message);
                    }
                })
                .catch(() => {
                    showFormError(errorBox, 'Error de conexión con el servidor.');
                });
        }

        //BLOQUE VISIBILIDAD PASSWORD
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const icon = document.getElementById('visibilityIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        //BLOQUE VALIDACIONES FORMULARIOS
        function showFormError(errorBox, message) {
            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
        }

        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) && email.length <= 150;
        }

        function isValidName(value) {
            return /^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{2,60}$/.test(value);
        }

        function isValidPassword(value) {
            return value.length >= 8 && /[A-Z]/.test(value) && /[a-z]/.test(value) && /[0-9]/.test(value);
        }

        function validateLoginForm(errorBox) {
            const correo = document.getElementById('emailInput').value.trim();
            const contrasena = document.getElementById('passwordInput').value;

            if (!correo || !contrasena) {
                showFormError(errorBox, 'Debe completar todos los campos');
                return false;
            }

            if (!isValidEmail(correo)) {
                showFormError(errorBox, 'Ingrese un correo electrónico válido');
                return false;
            }

            return true;
        }

        function validateSignupForm(errorBox) {
            const nombre = document.getElementById('signupNombre').value.trim();
            const apellido = document.getElementById('signupApellido').value.trim();
            const correo = document.getElementById('signupEmail').value.trim();
            const contrasena = document.getElementById('signupPassword').value;
            const roleInput = document.querySelector('input[name="role"]:checked');

            if (!nombre || !apellido || !correo || !contrasena) {
                showFormError(errorBox, 'Debe completar todos los campos');
                return false;
            }

            if (!isValidName(nombre)) {
                showFormError(errorBox, 'El nombre solo debe contener letras y espacios');
                return false;
            }

            if (!isValidName(apellido)) {
                showFormError(errorBox, 'El apellido solo debe contener letras y espacios');
                return false;
            }

            if (!roleInput) {
                showFormError(errorBox, 'Debe seleccionar un rol');
                return false;
            }

            if (!isValidEmail(correo)) {
                showFormError(errorBox, 'Ingrese un correo electrónico válido');
                return false;
            }

            if (!isValidPassword(contrasena)) {
                showFormError(errorBox, 'La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula y un número');
                return false;
            }

            return true;
        }

        //BLOQUE LOGIN
        function handleLogin(event) {
            event.preventDefault();
            const errorBox = document.getElementById('loginError');
            errorBox.classList.add('hidden');
            document.getElementById('loginSuccess').classList.add('hidden');

            if (!validateLoginForm(errorBox)) {
                return;
            }

            const datos = new FormData();
            datos.append('accion', 'login');
            datos.append('correo', document.getElementById('emailInput').value.trim());
            datos.append('contrasena', document.getElementById('passwordInput').value);

            fetch('index.php', { method: 'POST', body: datos })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = data.redirect;
                    } else {
                        errorBox.textContent = data.message;
                        errorBox.classList.remove('hidden');
                    }
                })
                .catch(() => {
                    errorBox.textContent = 'Error de conexión con el servidor.';
                    errorBox.classList.remove('hidden');
                });
        }

        //BLOQUE SIGNUP
        function handleSignup(event) {
            event.preventDefault();
            const errorBox = document.getElementById('signupError');
            errorBox.classList.add('hidden');

            if (!validateSignupForm(errorBox)) {
                return;
            }

            const roleInput = document.querySelector('input[name="role"]:checked');
            if (!roleInput) {
                showFormError(errorBox, 'Debe seleccionar un rol');
                return;
            }

            const datos = new FormData();
            datos.append('accion', 'registro');
            datos.append('nombre', document.getElementById('signupNombre').value.trim());
            datos.append('apellido', document.getElementById('signupApellido').value.trim());
            datos.append('correo', document.getElementById('signupEmail').value.trim());
            datos.append('contrasena', document.getElementById('signupPassword').value);
            datos.append('rol', roleInput.value);

            fetch('index.php', { method: 'POST', body: datos })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = data.redirect;
                    } else {
                        errorBox.textContent = data.message;
                        errorBox.classList.remove('hidden');
                    }
                })
                .catch(() => {
                    errorBox.textContent = 'Error de conexión con el servidor.';
                    errorBox.classList.remove('hidden');
                });
        }
    </script>
</body>

</html>
