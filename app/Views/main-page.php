<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <script type="text/javascript" src="<?= base_url('/scripts/main-page.js') ?>"></script>
</head>
<body>
  <header>
    <h1>Welcome to the Main Page</h1>
  </header>
  <main>
    Would you like to create an account?
    <?= form_open() ?>
    Email: <?= form_input(['name' => 'email', 'type' => 'email', 'required' => true, 'id' => 'email']) ?>
    <input type="submit" value="Create Account" id="create-account-button">
    <?= form_close() ?>
    Or log in to an existing account?
    <a href="" id="login-btn" class="btn">Log In</a>
  </main>
</body>
</html>
