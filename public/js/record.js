let currentRole = 'estudiante';

function setRole(role) {
    currentRole = role;
    
    // Actualizar botones
    document.getElementById('btn-estudiante').classList.remove('active');
    document.getElementById('btn-docente').classList.remove('active');
    document.getElementById(`btn-${role}`).classList.add('active');

    // Mostrar/ocultar elementos según rol
    const warning = document.getElementById('student-warning');
    const emailField = document.getElementById('email-field');
    const securityQuestions = document.getElementById('security-questions');

    // Obtener elementos de preguntas de seguridad
    const pregunta1 = document.getElementById('pregunta1');
    const respuesta1 = document.getElementById('respuesta1');
    const pregunta2 = document.getElementById('pregunta2');
    const respuesta2 = document.getElementById('respuesta2');
    const pregunta3 = document.getElementById('pregunta3');
    const respuesta3 = document.getElementById('respuesta3');
    const pregunta4 = document.getElementById('pregunta4');
    const respuesta4 = document.getElementById('respuesta4');
    const pregunta5 = document.getElementById('pregunta5');
    const respuesta5 = document.getElementById('respuesta5');
    const email = document.getElementById('email');

    if (role === 'estudiante') {
        warning.classList.remove('hidden');
        emailField.classList.add('hidden');
        securityQuestions.classList.remove('hidden');
        
        // Deshabilitar email y habilitar preguntas
        email.disabled = true;
        email.required = false;
        
        // Habilitar preguntas de seguridad
        pregunta1.disabled = false;
        respuesta1.disabled = false;
        pregunta2.disabled = false;
        respuesta2.disabled = false;
        pregunta3.disabled = false;
        respuesta3.disabled = false;
        pregunta4.disabled = false;
        respuesta4.disabled = false;
        pregunta5.disabled = false;
        respuesta5.disabled = false;
        
        pregunta1.required = true;
        respuesta1.required = true;
        pregunta2.required = true;
        respuesta2.required = true;
        pregunta3.required = true;
        respuesta3.required = true;
        pregunta4.required = true;
        respuesta4.required = true;
        pregunta5.required = true;
        respuesta5.required = true;
    } else {
        warning.classList.add('hidden');
        emailField.classList.remove('hidden');
        securityQuestions.classList.add('hidden');
        
        // Habilitar email y deshabilitar preguntas
        email.disabled = false;
        email.required = true;
        
        // Deshabilitar preguntas de seguridad
        pregunta1.disabled = true;
        respuesta1.disabled = true;
        pregunta2.disabled = true;
        respuesta2.disabled = true;
        pregunta3.disabled = true;
        respuesta3.disabled = true;
        pregunta4.disabled = true;
        respuesta4.disabled = true;
        pregunta5.disabled = true;
        respuesta5.disabled = true;
        
        pregunta1.required = false;
        respuesta1.required = false;
        pregunta2.required = false;
        respuesta2.required = false;
        pregunta3.required = false;
        respuesta3.required = false;
        pregunta4.required = false;
        respuesta4.required = false;
        pregunta5.required = false;
        respuesta5.required = false;
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    
    strengthBar.className = 'strength-bar-fill';
    
    switch(strength) {
        case 0:
        case 1:
            strengthBar.style.width = '20%';
            strengthBar.classList.add('weak');
            strengthText.textContent = 'Débil';
            break;
        case 2:
        case 3:
            strengthBar.style.width = '60%';
            strengthBar.classList.add('medium');
            strengthText.textContent = 'Media';
            break;
        case 4:
        case 5:
            strengthBar.style.width = '100%';
            strengthBar.classList.add('strong');
            strengthText.textContent = 'Fuerte';
            break;
    }
}

function validateForm(event) {
    event.preventDefault();
    
    const nombre = document.getElementById('name').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    // Validaciones básicas
    if (nombre.length < 3) {
        alert('El nombre debe tener al menos 3 caracteres');
        return false;
    }
    
    if (username.length < 4) {
        alert('El nombre de usuario debe tener al menos 4 caracteres');
        return false;
    }
    
    if (password.length < 8) {
        alert('La contraseña debe tener al menos 8 caracteres');
        return false;
    }

    // Crear FormData para enviar solo los campos activos
    const formData = new FormData();
    formData.append('role', currentRole);
    formData.append('name', nombre);
    formData.append('username', username);
    formData.append('password', password);

    // Validar según el rol
    if (currentRole === 'docente') {
        const email = document.getElementById('email').value;
        if (!email.includes('@') || !email.includes('.')) {
            alert('Por favor, ingresa un correo electrónico válido');
            return false;
        }
        
        formData.append('email', email);
        
        // Mostrar datos en consola y alerta
        const docenteData = {
            rol: 'docente',
            nombre: nombre,
            username: username,
            email: email,
            password: password
        };
        console.log('Registrando docente:', docenteData);
        alert('¡Docente registrado exitosamente!');
        
    } else {
        // Validar preguntas de seguridad (solo si están habilitadas)
        const pregunta1 = document.getElementById('pregunta1').value;
        const respuesta1 = document.getElementById('respuesta1').value.trim();
        const pregunta2 = document.getElementById('pregunta2').value;
        const respuesta2 = document.getElementById('respuesta2').value.trim();
        const pregunta3 = document.getElementById('pregunta3').value;
        const respuesta3 = document.getElementById('respuesta3').value.trim();
        
        if (!pregunta1 || !respuesta1 || !pregunta2 || !respuesta2 || !pregunta3 || !respuesta3) {
            alert('Por favor, completa todas las preguntas de seguridad');
            return false;
        }
        
        if (respuesta1.length < 2 || respuesta2.length < 2 || respuesta3.length < 2) {
            alert('Las respuestas deben tener al menos 2 caracteres');
            return false;
        }
        
        // Agregar preguntas de seguridad al FormData
        formData.append('pregunta1', pregunta1);
        formData.append('respuesta1', respuesta1);
        formData.append('pregunta2', pregunta2);
        formData.append('respuesta2', respuesta2);
        formData.append('pregunta3', pregunta3);
        formData.append('respuesta3', respuesta3);
        
        // Mostrar datos en consola y alerta
        const estudianteData = {
            rol: 'estudiante',
            nombre: nombre,
            username: username,
            password: password,
            preguntasSeguridad: [
                { pregunta: pregunta1, respuesta: respuesta1 },
                { pregunta: pregunta2, respuesta: respuesta2 },
                { pregunta: pregunta3, respuesta: respuesta3 }
            ]
        };
        console.log('Registrando estudiante:', estudianteData);
        alert('¡Estudiante registrado exitosamente!\n\nRecuerda guardar tus preguntas de seguridad en un lugar seguro.');
    }
    
    // Aquí puedes enviar el FormData a tu servidor
    // document.querySelector('form').submit();
    
    return true;
}

// Inicializar el rol por defecto (estudiante)
document.addEventListener('DOMContentLoaded', function() {
    setRole('estudiante');
});