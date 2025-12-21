<?php
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GEU</title>
    <link rel="stylesheet" href="css/login.css">
    <style>
        .msg-erro {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
    <section>
        <div class="divImg">
            <img src="img/img_Login.png" alt="Imagem Login">
        </div>
        <div class="divFormLogin">
            <img src="img/Logo.png" alt="Logotipo" class="logo-login">
            <h2>Sign in</h2>

            <form action="processarLogin.php" method="POST">
                
                <?php if (isset($_GET['erro'])): ?>
                    <div class="msg-erro">
                        <?php 
                            if($_GET['erro'] == 1) echo "Email ou password incorretos.";
                            if($_GET['erro'] == 2) echo "A sua conta está desativada.";
                            if($_GET['erro'] == 3) echo "Preencha todos os campos.";
                        ?>
                    </div>
                <?php endif; ?>

                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="Email de utilizador" required>
                
                <div class="password">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>

                <div class="form-row">
                    <label class="show-password-label">
                        <input type="checkbox" id="togglePassword" onclick="verSenha()"> Ver password
                    </label>
                </div>

                <button class="btn" type="submit">Login</button>
            </form>

            <footer class="funding">
                <img src="img/img_confinanciado.png" alt="Confinanciado por:">
            </footer>
        </div>
    </section>

    <script>
        function verSenha() {
            const passwordInput = document.getElementById('password');
            const checkbox = document.getElementById('togglePassword');
            
            if (passwordInput && checkbox) {
                passwordInput.type = checkbox.checked ? 'text' : 'password';
            }
        }
    </script>
</body>
</html>