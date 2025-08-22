<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    >
    <style>
      /* Base sizing for projector legibility */
      body { font-size: 1.35rem; }
      @media (min-width: 992px){ body { font-size:1.5rem; } }

      .hero { min-height: 85vh; display:flex; align-items:center; }
      .display-icon { font-size: clamp(4rem, 10vw, 8rem); line-height:1; }
      .action-btn { padding-top:1.2rem; padding-bottom:1.2rem; font-size:1.4rem; }

      /* High-contrast panels */
      .panel-contrast {
        background: #ffffff;
        background-image: linear-gradient(145deg,#ffffff 0%, #f1f5f9 60%, #e2e8f0 100%);
        color:#0b0f19;
        border: 3px solid #0d6efd; /* strong accent border for visibility */
        box-shadow: 0 0.75rem 2rem -0.5rem rgba(13,110,253,.35), 0 0 0 4px rgba(255,255,255,0.4);
      }
      .panel-contrast h1, .panel-contrast h2, .panel-contrast h3 { color:#0b0f19; }
      .panel-contrast p.lead { color:#1c2533; }

      .stats-panel {
        background:#ffffff;
        background-image: linear-gradient(165deg,#ffffff 0%, #f8fafc 55%, #eef2f6 100%);
        color:#0b0f19;
        border:2px solid #6366f1;
        box-shadow: 0 0.5rem 1.5rem -0.5rem rgba(99,102,241,.4);
      }
      .stats-panel h2 { color:#1e293b; }
      .stats-panel ul li span.fw-bold { color:#0f172a; }
      .stats-badge { font-size:1.05em; padding:.6rem 1rem; }

      .card-big { border:0; }
      a.big-link { text-decoration:none; }
      .logout-link { font-size:1.1rem; }

      /* Improve outline buttons contrast on light panels */
      .panel-contrast .btn-outline-dark { color:#111; border-color:#111; }
      .panel-contrast .btn-outline-dark:hover { background:#111; color:#fff; }

      /* Focus ring enhancement for accessibility */
      .action-btn:focus, .panel-contrast a.btn:focus { box-shadow:0 0 0 .3rem rgba(13,110,253,.5); }

      /* Dark page background subtle texture */
      body.bg-dark { background: radial-gradient(circle at 25% 20%, #1a1f29 0%, #0f1218 70%) fixed; }
    </style>
</head>
<body class="bg-dark text-light">
  <header class="py-4 border-bottom border-secondary-subtle mb-4">
    <div class="container d-flex align-items-center justify-content-between">
      <span class="fs-2 fw-semibold">Demo App</span>
      <nav class="d-flex gap-3">
        <span class="text-secondary-emphasis small d-none d-md-inline">Signed in as</span>
        <span class="fw-semibold"><?= esc($user['email']) ?></span>
        <span class="vr d-none d-md-inline"></span>
        <?= anchor('/logout', 'Log Out', ['class' => 'btn btn-outline-danger btn-lg fw-semibold']) ?>
      </nav>
    </div>
  </header>

  <main class="container hero">
    <div class="row g-5 w-100 align-items-stretch">
      <div class="col-xl-8">
        <div class="card card-big panel-contrast shadow-lg p-5 rounded-5" role="region" aria-labelledby="accountHeading">
          <div class="row g-4 align-items-center">
            <div class="col-md-4 text-center">
              <div class="display-icon">👤</div>
            </div>
            <div class="col-md-8">
              <h1 id="accountHeading" class="display-4 fw-bold mb-4">Your Account</h1>
              <p class="lead mb-4 mb-lg-5">
                You are logged in as <span class="fw-bold text-primary"><?= esc($user['email']) ?></span>.
              </p>
              <div class="d-grid gap-4">
                <!-- Example primary action -->
                <form method="post" action="<?= site_url('files/create') ?>">
                  <button type="submit" class="btn btn-primary btn-lg action-btn w-100 fw-semibold shadow-sm">
                    <span class="me-2">📄</span> Create New File
                  </button>
                </form>

                <a href="<?= site_url('files') ?>" class="btn btn-outline-primary btn-lg action-btn w-100 fw-semibold shadow-sm">
                  <span class="me-2">🗂️</span> View Your Files
                </a>

                <a href="<?= site_url('profile') ?>" class="btn btn-outline-dark btn-lg action-btn w-100 fw-semibold shadow-sm">
                  <span class="me-2">✏️</span> Edit Profile
                </a>
              </div>
              <div class="mt-5">
                <a class="logout-link text-danger fw-semibold" href="<?= site_url('logout') ?>" aria-label="Log out of your account">
                  ← Log Out
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-4 d-flex">
        <div class="w-100 d-flex flex-column justify-content-between">
          <div class="p-5 rounded-5 stats-panel shadow-lg h-100 d-flex flex-column" role="region" aria-labelledby="quickStatsHeading">
            <h2 id="quickStatsHeading" class="h1 fw-bold mb-4 text-center">Quick Stats</h2>
            <ul class="list-unstyled fs-4 flex-grow-1 d-flex flex-column justify-content-center gap-4 mb-4" aria-live="polite">
              <li class="d-flex align-items-center justify-content-between border-bottom pb-2">
                <span class="fw-semibold text-uppercase small tracking-wide text-secondary">Files</span>
                <span class="fw-bold display-6 mb-0 lh-1"><?= number_format($file_count) ?></span>
              </li>
              <li class="d-flex align-items-center justify-content-between border-bottom pb-2">
                <span class="fw-semibold text-uppercase small text-secondary">Last Login</span>
                <span class="fw-bold">Just now</span>
              </li>
              <li class="d-flex align-items-center justify-content-between">
                <span class="fw-semibold text-uppercase small text-secondary">Status</span>
                <span class="badge text-bg-success stats-badge">Active</span>
              </li>
            </ul>
            <div class="text-center small text-secondary fw-semibold">
              Data updates in <span class="text-primary">real time</span>.
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>

  <footer class="mt-5 py-5 text-center text-secondary-emphasis small">
    &copy; <?= date('Y') ?> Demo App
  </footer>

  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"
  ></script>
</body>
</html>
