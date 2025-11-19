<?php
session_start();

function graphqlRequest($query, $variables = [], $token = null) {
    $apiUrl = 'http://localhost:8000/graphql';
    
    $payload = [
        'query' => $query,
        'variables' => $variables
    ];
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function handleLogin($email, $password) {
    $query = 'query Login($email: String!, $password: String!) { login(email: $email, password: $password) }';
    $variables = ['email' => $email, 'password' => $password];
    
    $result = graphqlRequest($query, $variables);
    
    if (isset($result['data']['login'])) {
        $_SESSION['token'] = $result['data']['login'];
        return true;
    }
    return false;
}

function handleLogout() {
    session_destroy();
}

function handleCrear($data, $token) {
    $mutation = 'mutation CreatePersona($nombres: String!, $apellidos: String!, $ci: String!, $direccion: String, $telefono: String, $email: String) {
        createPersona(nombres: $nombres, apellidos: $apellidos, ci: $ci, direccion: $direccion, telefono: $telefono, email: $email) {
            id nombres apellidos ci
        }
    }';
    
    graphqlRequest($mutation, $data, $token);
}

function handleActualizar($data, $token) {
    $mutation = 'mutation UpdatePersona($id: Int!, $nombres: String!, $apellidos: String!, $ci: String!, $direccion: String, $telefono: String, $email: String) {
        updatePersona(id: $id, nombres: $nombres, apellidos: $apellidos, ci: $ci, direccion: $direccion, telefono: $telefono, email: $email) {
            id nombres apellidos
        }
    }';
    
    graphqlRequest($mutation, $data, $token);
}

function handleEliminar($id, $token) {
    $mutation = 'mutation DeletePersona($id: Int!) { deletePersona(id: $id) }';
    $variables = ['id' => (int)$id];
    
    graphqlRequest($mutation, $variables, $token);
}

function getPersonas($token) {
    $query = 'query { personas { id nombres apellidos ci direccion telefono email } }';
    $result = graphqlRequest($query, [], $token);
    
    if (isset($result['data']['personas'])) {
        return $result['data']['personas'];
    }
    return [];
}

function findPersonaById($personas, $id) {
    foreach ($personas as $p) {
        if ($p['id'] == $id) {
            return $p;
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'login') {
        if (handleLogin($_POST['email'] ?? '', $_POST['password'] ?? '')) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Error en el login';
        }
    }
    
    if ($action === 'logout') {
        handleLogout();
        header('Location: index.php');
        exit;
    }
    
    if ($action === 'crear' && isset($_SESSION['token'])) {
        $data = [
            'nombres' => $_POST['nombres'] ?? '',
            'apellidos' => $_POST['apellidos'] ?? '',
            'ci' => $_POST['ci'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];
        handleCrear($data, $_SESSION['token']);
        header('Location: index.php');
        exit;
    }
    
    if ($action === 'actualizar' && isset($_SESSION['token'])) {
        $data = [
            'id' => (int)$_POST['id'],
            'nombres' => $_POST['nombres'] ?? '',
            'apellidos' => $_POST['apellidos'] ?? '',
            'ci' => $_POST['ci'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];
        handleActualizar($data, $_SESSION['token']);
        header('Location: index.php');
        exit;
    }
    
    if ($action === 'eliminar' && isset($_SESSION['token'])) {
        handleEliminar($_POST['id'], $_SESSION['token']);
        header('Location: index.php');
        exit;
    }
}

$personas = [];
if (isset($_SESSION['token'])) {
    $personas = getPersonas($_SESSION['token']);
}

$editPersona = null;
if (isset($_GET['edit']) && isset($_SESSION['token'])) {
    $editPersona = findPersonaById($personas, (int)$_GET['edit']);
}

function renderStyles() {
    return '
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            min-height: 100vh;
            padding: 20px;
        }

        .card {
            background: white;
            border: 1px solid #ddd;
            padding: 20px;
            max-width: 1200px;
            margin: 20px auto;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 90vh;
        }

        .login-card {
            max-width: 400px;
            width: 100%;
        }

        h1, h2 {
            color: #333;
            margin-bottom: 20px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #666;
        }

        button {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            background: #555;
            color: white;
            border: none;
            font-size: 14px;
            cursor: pointer;
        }

        button:hover {
            background: #333;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            max-width: 1200px;
            margin: 0 auto 20px auto;
        }

        .status {
            color: #27ae60;
            font-weight: bold;
        }

        .btn-logout {
            width: auto;
            padding: 10px 20px;
            margin: 0;
            background: #e74c3c;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-grid > div {
            display: flex;
            flex-direction: column;
        }

        .form-grid label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #666;
            color: white;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        tbody tr:hover {
            background: #f5f5f5;
        }

        .btn-action {
            width: auto;
            padding: 6px 12px;
            margin: 2px;
            font-size: 12px;
        }

        .btn-edit {
            background: #f39c12;
        }

        .btn-delete {
            background: #e74c3c;
        }

        .error {
            color: #e74c3c;
            padding: 10px;
            margin-bottom: 10px;
            background: #ffebee;
            border-radius: 5px;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
            text-decoration: none;
        }

        .close:hover {
            color: #333;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 10px;
            }
        }
    ';
}

function renderLoginPage($error = null) {
    ?>
    <div class="login-container">
        <div class="card login-card">
            <h1>Login</h1>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="Email" value="admin@example.com" required>
                <input type="password" name="password" placeholder="Password" value="password" required>
                <button type="submit">Iniciar Sesión</button>
            </form>
        </div>
    </div>
    <?php
}

function renderHeader($token) {
    ?>
    <div class="header">
        <p class="status">Token: <?php echo htmlspecialchars(substr($token, 0, 50)); ?>...</p>
        <form method="POST" style="margin: 0;">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="btn-logout">Cerrar Sesión</button>
        </form>
    </div>
    <?php
}

function renderCreateForm() {
    ?>
    <div class="card">
        <h2>Crear Persona</h2>
        <form method="POST">
            <input type="hidden" name="action" value="crear">
            <div class="form-grid">
                <?php 
                $fields = [
                    ['name' => 'nombres', 'label' => 'Nombres', 'type' => 'text', 'required' => true],
                    ['name' => 'apellidos', 'label' => 'Apellidos', 'type' => 'text', 'required' => true],
                    ['name' => 'ci', 'label' => 'CI', 'type' => 'text', 'required' => true],
                    ['name' => 'telefono', 'label' => 'Teléfono', 'type' => 'text', 'required' => false],
                    ['name' => 'direccion', 'label' => 'Dirección', 'type' => 'text', 'required' => false],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => false]
                ];
                
                foreach ($fields as $field) {
                    renderFormField($field);
                }
                ?>
            </div>
            <button type="submit">Crear</button>
        </form>
    </div>
    <?php
}

function renderFormField($field) {
    ?>
    <div>
        <label><?php echo htmlspecialchars($field['label']); ?></label>
        <input type="<?php echo $field['type']; ?>" 
               name="<?php echo $field['name']; ?>" 
               placeholder="<?php echo htmlspecialchars($field['label']); ?>"
               <?php echo $field['required'] ? 'required' : ''; ?>>
    </div>
    <?php
}

function renderPersonasTable($personas) {
    ?>
    <div class="card">
        <h2>Lista de Personas</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <?php 
                        $headers = ['ID', 'Nombres', 'Apellidos', 'CI', 'Teléfono', 'Email', 'Dirección', 'Acciones'];
                        foreach ($headers as $header) {
                            echo '<th>' . htmlspecialchars($header) . '</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($personas as $persona): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($persona['id']); ?></td>
                            <td><?php echo htmlspecialchars($persona['nombres']); ?></td>
                            <td><?php echo htmlspecialchars($persona['apellidos']); ?></td>
                            <td><?php echo htmlspecialchars($persona['ci']); ?></td>
                            <td><?php echo htmlspecialchars($persona['telefono'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($persona['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($persona['direccion'] ?? ''); ?></td>
                            <td>
                                <a href="?edit=<?php echo $persona['id']; ?>" 
                                   class="btn-action btn-edit" 
                                   style="display: inline-block; text-decoration: none; text-align: center;">Editar</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="eliminar">
                                    <input type="hidden" name="id" value="<?php echo $persona['id']; ?>">
                                    <button type="submit" 
                                            class="btn-action btn-delete" 
                                            onclick="return confirm('¿Eliminar esta persona?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

function renderEditModal($persona) {
    if (!$persona) return;
    ?>
    <div class="modal">
        <div class="modal-content">
            <a href="index.php" class="close">&times;</a>
            <h2>Editar Persona</h2>
            <form method="POST">
                <input type="hidden" name="action" value="actualizar">
                <input type="hidden" name="id" value="<?php echo $persona['id']; ?>">
                <div class="form-grid">
                    <?php 
                    $fields = [
                        ['name' => 'nombres', 'label' => 'Nombres', 'type' => 'text', 'required' => true],
                        ['name' => 'apellidos', 'label' => 'Apellidos', 'type' => 'text', 'required' => true],
                        ['name' => 'ci', 'label' => 'CI', 'type' => 'text', 'required' => true],
                        ['name' => 'telefono', 'label' => 'Teléfono', 'type' => 'text', 'required' => false],
                        ['name' => 'direccion', 'label' => 'Dirección', 'type' => 'text', 'required' => false],
                        ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => false]
                    ];
                    
                    foreach ($fields as $field) {
                        renderEditField($field, $persona);
                    }
                    ?>
                </div>
                <button type="submit">Actualizar</button>
            </form>
        </div>
    </div>
    <?php
}

function renderEditField($field, $persona) {
    $value = $persona[$field['name']] ?? '';
    ?>
    <div>
        <label><?php echo htmlspecialchars($field['label']); ?></label>
        <input type="<?php echo $field['type']; ?>" 
               name="<?php echo $field['name']; ?>" 
               value="<?php echo htmlspecialchars($value); ?>"
               <?php echo $field['required'] ? 'required' : ''; ?>>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GraphQL Client - Personas</title>
    <style><?php echo renderStyles(); ?></style>
</head>
<body>
    <?php if (!isset($_SESSION['token'])): ?>
        <?php renderLoginPage($error ?? null); ?>
    <?php else: ?>
        <?php renderHeader($_SESSION['token']); ?>
        <?php renderCreateForm(); ?>
        <?php renderPersonasTable($personas); ?>
        <?php renderEditModal($editPersona); ?>
    <?php endif; ?>
</body>
</html>
