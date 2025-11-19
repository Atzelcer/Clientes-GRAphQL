const API_URL = 'http://localhost:8000/graphql';
let token = localStorage.getItem('token');

if (token) {
    mostrarMain();
    cargarPersonas();
}

document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    await login();
});

document.getElementById('createForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    await crearPersona();
});

document.getElementById('editForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    await actualizarPersona();
});

async function graphqlRequest(query, variables = {}) {
    const headers = { 'Content-Type': 'application/json' };
    if (token) headers['Authorization'] = `Bearer ${token}`;

    const response = await fetch(API_URL, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify({ query, variables })
    });

    const result = await response.json();
    if (result.errors) throw new Error(result.errors[0].message);
    return result.data;
}

async function login() {
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    const query = `
        query Login($email: String!, $password: String!) {
            login(email: $email, password: $password)
        }
    `;

    try {
        const data = await graphqlRequest(query, { email, password });
        token = data.login;
        localStorage.setItem('token', token);
        mostrarMensaje('loginMessage', 'Login exitoso', 'success');
        setTimeout(() => {
            mostrarMain();
            cargarPersonas();
        }, 500);
    } catch (error) {
        mostrarMensaje('loginMessage', error.message, 'error');
    }
}

function logout() {
    token = null;
    localStorage.removeItem('token');
    document.getElementById('mainSection').classList.add('hidden');
    document.getElementById('loginSection').classList.remove('hidden');
}

function mostrarMain() {
    document.getElementById('loginSection').classList.add('hidden');
    document.getElementById('mainSection').classList.remove('hidden');
    document.getElementById('tokenInfo').innerHTML = '<strong>Token:</strong> ' + token.substring(0, 50) + '...';
}

async function crearPersona() {
    const mutation = `
        mutation CreatePersona($nombres: String!, $apellidos: String!, $ci: String!, $direccion: String, $telefono: String, $email: String) {
            createPersona(nombres: $nombres, apellidos: $apellidos, ci: $ci, direccion: $direccion, telefono: $telefono, email: $email) {
                id nombres apellidos ci
            }
        }
    `;

    const variables = {
        nombres: document.getElementById('nombres').value,
        apellidos: document.getElementById('apellidos').value,
        ci: document.getElementById('ci').value,
        direccion: document.getElementById('direccion').value,
        telefono: document.getElementById('telefono').value,
        email: document.getElementById('email').value
    };

    try {
        await graphqlRequest(mutation, variables);
        mostrarMensaje('message', 'Persona creada', 'success');
        document.getElementById('createForm').reset();
        cargarPersonas();
    } catch (error) {
        mostrarMensaje('message', error.message, 'error');
    }
}

async function cargarPersonas() {
    const query = `query { personas { id nombres apellidos ci direccion telefono email } }`;

    try {
        const data = await graphqlRequest(query);
        mostrarPersonas(data.personas);
    } catch (error) {
        mostrarMensaje('message', error.message, 'error');
    }
}

function mostrarPersonas(personas) {
    const tbody = document.getElementById('personasBody');
    tbody.innerHTML = '';

    personas.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${p.id}</td>
            <td>${p.nombres}</td>
            <td>${p.apellidos}</td>
            <td>${p.ci}</td>
            <td>${p.telefono || ''}</td>
            <td>${p.email || ''}</td>
            <td>${p.direccion || ''}</td>
            <td class="actions">
                <button onclick="editarPersona(${p.id})">Editar</button>
                <button onclick="eliminarPersona(${p.id})" class="btn-danger">Eliminar</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function editarPersona(id) {
    try {
        const data = await graphqlRequest(`query { personas { id nombres apellidos ci direccion telefono email } }`);
        const persona = data.personas.find(p => p.id == id);
        if (persona) {
            document.getElementById('editId').value = persona.id;
            document.getElementById('editNombres').value = persona.nombres;
            document.getElementById('editApellidos').value = persona.apellidos;
            document.getElementById('editCi').value = persona.ci;
            document.getElementById('editTelefono').value = persona.telefono || '';
            document.getElementById('editDireccion').value = persona.direccion || '';
            document.getElementById('editEmail').value = persona.email || '';
            document.getElementById('editModal').classList.remove('hidden');
        }
    } catch (error) {
        mostrarMensaje('message', error.message, 'error');
    }
}

async function actualizarPersona() {
    const mutation = `
        mutation UpdatePersona($id: Int!, $nombres: String, $apellidos: String, $ci: String, $direccion: String, $telefono: String, $email: String) {
            updatePersona(id: $id, nombres: $nombres, apellidos: $apellidos, ci: $ci, direccion: $direccion, telefono: $telefono, email: $email) {
                id nombres apellidos
            }
        }
    `;

    const variables = {
        id: parseInt(document.getElementById('editId').value),
        nombres: document.getElementById('editNombres').value,
        apellidos: document.getElementById('editApellidos').value,
        ci: document.getElementById('editCi').value,
        direccion: document.getElementById('editDireccion').value,
        telefono: document.getElementById('editTelefono').value,
        email: document.getElementById('editEmail').value
    };

    try {
        await graphqlRequest(mutation, variables);
        mostrarMensaje('message', 'Persona actualizada', 'success');
        cerrarModal();
        cargarPersonas();
    } catch (error) {
        mostrarMensaje('message', error.message, 'error');
    }
}

async function eliminarPersona(id) {
    if (!confirm('¿Eliminar persona?')) return;

    const mutation = `mutation DeletePersona($id: Int!) { deletePersona(id: $id) }`;

    try {
        await graphqlRequest(mutation, { id });
        mostrarMensaje('message', 'Persona eliminada', 'success');
        cargarPersonas();
    } catch (error) {
        mostrarMensaje('message', error.message, 'error');
    }
}

function cerrarModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function mostrarMensaje(elementId, mensaje, tipo) {
    const element = document.getElementById(elementId);
    element.innerHTML = `<div class="alert alert-${tipo}">${mensaje}</div>`;
    setTimeout(() => { element.innerHTML = ''; }, 3000);
}
