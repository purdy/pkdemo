<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Page</title>
</head>
<body>
  <header>
    <h1>Your Account Page</h1>
  </header>
  <main>
    You are logged in as X.
    Would you like to create a file?
    Would you like to <?= anchor('/logout', 'log out') ?>?
  </main>
</body>
</html>
