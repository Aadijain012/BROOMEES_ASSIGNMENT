<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Broomees REST API documentation and submission links.">
    <title>Broomees API</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; color: #eaf2ff; background: #07111d; font-family: ui-sans-serif, system-ui, sans-serif; }
        main { width: min(1050px, calc(100% - 36px)); margin: auto; padding: 42px 0 60px; }
        .brand { color: #9fb1c9; font-size: .78rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
        .brand b { display: inline-block; width: 10px; height: 10px; margin-right: 10px; border-radius: 50%; background: #2ee59d; box-shadow: 0 0 16px #2ee59d; }
        .hero, .panel { border: 1px solid #263549; border-radius: 18px; background: #0c1b2d; box-shadow: 0 18px 60px rgba(0, 0, 0, .22); }
        .hero { display: grid; grid-template-columns: 1.4fr .6fr; gap: 24px; margin-top: 28px; padding: clamp(28px, 6vw, 58px); }
        .eyebrow { color: #2ee59d; font-size: .75rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
        h1 { max-width: 720px; margin: 14px 0; font-size: clamp(2.3rem, 6vw, 4.7rem); line-height: 1.02; letter-spacing: -.06em; }
        h2 { margin: 0; font-size: 1.2rem; }
        p { color: #a9bad0; line-height: 1.65; }
        .status { align-self: stretch; padding: 24px; border-left: 1px solid #263549; }
        .online { color: #dfffee; font-weight: 800; } .online b { color: #2ee59d; }
        .actions, .grid { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 26px; }
        a.button, .card { border: 1px solid #32465e; border-radius: 10px; color: #eaf2ff; text-decoration: none; }
        a.button { padding: 12px 17px; font-weight: 750; } a.button.primary { color: #03150e; background: #2ee59d; border-color: #2ee59d; }
        .panel { margin-top: 24px; padding: 28px; }
        .card { flex: 1 1 200px; min-height: 132px; padding: 18px; background: #0a192a; }
        .card:hover { border-color: #2ee59d; } .card small { color: #6f859f; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        .card strong { display: block; margin-top: 10px; } .card span { display: block; margin-top: 7px; color: #a9bad0; font-size: .86rem; line-height: 1.45; }
        code { display: block; margin-top: 16px; padding: 16px; overflow-x: auto; border: 1px solid #263549; border-radius: 10px; color: #bff8dd; background: #06101c; white-space: pre-wrap; }
        footer { margin-top: 24px; color: #6f859f; font-size: .84rem; text-align: center; }
        @media (max-width: 700px) { .hero { grid-template-columns: 1fr; } .status { border: 0; border-top: 1px solid #263549; } }
    </style>
</head>
<body>
    <main>
        <div class="brand"><b></b>Broomees · Submission API</div>
        <section class="hero">
            <div>
                <div class="eyebrow">Laravel REST API</div>
                <h1>Users, relationships, reputation, and access control.</h1>
                <p>This is the live Broomees backend submission. Review the interactive API documentation or import the OpenAPI file directly into Postman.</p>
                <div class="actions"><a class="button primary" href="/api/documentation">Open Swagger UI</a><a class="button" href="/api/openapi.yaml">Download OpenAPI YAML</a></div>
            </div>
            <aside class="status"><div class="eyebrow">Service status</div><p class="online"><b>●</b> API online</p><p><strong>Laravel 12 · PHP 8.3</strong><br>PostgreSQL · Redis-compatible cache<br>Bearer-token authentication</p></aside>
        </section>
        <section class="panel">
            <h2>Reviewer resources</h2>
            <div class="grid">
                <a class="card" href="/api/documentation"><small>Interactive docs</small><strong>Swagger UI</strong><span>Try every documented endpoint.</span></a>
                <a class="card" href="/api/openapi.yaml"><small>Import file</small><strong>OpenAPI 3.0</strong><span>Use this exact file in Postman.</span></a>
                <a class="card" href="/api/health"><small>Availability</small><strong>Health check</strong><span>Machine-readable service status.</span></a>
                <a class="card" href="https://github.com/Aadijain012/BROOMEES_ASSIGNMENT"><small>Source code</small><strong>GitHub repository</strong><span>README, migrations, tests, and Docker setup.</span></a>
            </div>
        </section>
        <section class="panel">
            <div class="eyebrow">Authentication</div><h2>Issue a token, then authorize protected endpoints.</h2>
            <p>Registration and token issuance are public. User, relationship, hobby, and metrics operations require a bearer token.</p>
            <code>POST /api/auth/token
Content-Type: application/json

{"username":"alice","password":"DemoPassword123!"}

Authorization: Bearer &lt;issued-token&gt;</code>
        </section>
        <footer>Broomees API · Documentation, setup guidance, migrations, and test instructions are in the linked repository.</footer>
    </main>
</body>
</html>
