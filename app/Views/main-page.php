<!doctype html>
<html lang="en">
<head>
    <!-- ...existing code... -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Main Page</title>
    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    >
    <style>
      body { font-size: 1.3rem; }
      .hero { min-height: 70vh; display: flex; align-items: center; }
    </style>
    <script type="text/javascript" src="<?= base_url('/scripts/main-page.js') ?>"></script>
</head>
<body class="bg-dark text-light">
  <header class="mb-4 border-bottom border-secondary">
    <nav class="navbar navbar-dark bg-dark container">
      <span class="navbar-brand fs-2 fw-semibold">Demo App</span>
    </nav>
  </header>

  <main class="container hero">
    <div class="row w-100">
      <div class="col-lg-7">
        <div class="p-5 rounded-4 bg-secondary-subtle text-dark shadow">
          <h1 class="display-3 fw-bold mb-4 text-center">Welcome</h1>
          <p class="lead mb-5 text-center">Create a new account or log into an existing one.</p>

          <div class="row g-5">
            <div class="col-md-7">
              <h2 class="h3 fw-semibold mb-3">Create Account</h2>
              <?= form_open('', ['class' => 'needs-validation', 'novalidate' => true]) ?>
                <div class="mb-4">
                  <label for="email" class="form-label fs-5">Email address</label>
                  <?= form_input([
                      'name' => 'email',
                      'type' => 'email',
                      'required' => true,
                      'id' => 'email',
                      'class' => 'form-control form-control-lg',
                      'placeholder' => 'you@example.com'
                  ]) ?>
                  <div class="form-text">We will send a confirmation link.</div>
                  <div class="invalid-feedback fs-6">Please enter a valid email.</div>
                </div>
                <button type="submit" id="create-account-button" class="btn btn-primary btn-lg w-100 py-3">
                  Create Account
                </button>
              <?= form_close() ?>
            </div>
            <div class="col-md-5 d-flex flex-column">
              <h2 class="h3 fw-semibold mb-3">Already Registered?</h2>
              <a href="" id="login-btn" class="btn btn-outline-dark btn-lg py-3 mb-3">
                Log In
              </a>
              <div class="mt-auto small text-muted">
                Need help? Contact support.
              </div>
            </div>
          </div>

        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-flex align-items-center justify-content-center">
        <div class="text-center">
          <span class="display-1">🚀</span>
          <p class="mt-4 fs-4 fw-semibold">Fast onboarding. Secure access.</p>
        </div>
      </div>
    </div>
  </main>

  <footer class="mt-5 py-4 text-center text-secondary small">
    &copy; <?= date('Y') ?> Demo App
  </footer>

  <!-- Bootstrap JS Bundle -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"
  ></script>
  <script>
    // Simple client-side validation enhancement
    (function() {
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', e => {
          if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();
  </script>
</body>
</html>
