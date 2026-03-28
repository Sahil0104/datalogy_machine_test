<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'User Management App' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        :root { --ink: #0f172a; --muted: #64748b; --line: #dbe3ef; --panel: rgba(255,255,255,.92); --brand: #14532d; --brand-soft: #dcfce7; --brand-accent: #0f766e; --danger: #b91c1c; --danger-soft: #fee2e2; --shadow: 0 24px 60px rgba(15,23,42,.12); --radius: 24px; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--ink); background: radial-gradient(circle at top left, rgba(20,83,45,.18), transparent 30%), radial-gradient(circle at top right, rgba(15,118,110,.16), transparent 28%), linear-gradient(135deg, #eff6ff 0%, #f8fafc 45%, #f0fdf4 100%); min-height: 100vh; }
        a { color: inherit; text-decoration: none; }
        .page-shell { padding: 32px 18px 48px; }
        .container { max-width: 1180px; margin: 0 auto; }
        .card { background: var(--panel); backdrop-filter: blur(12px); border: 1px solid rgba(219,227,239,.85); border-radius: var(--radius); box-shadow: var(--shadow); }
        .auth-wrap { min-height: calc(100vh - 80px); display: flex; align-items: center; justify-content: center; }
        .auth-card { width: 100%; max-width: 960px; display: grid; grid-template-columns: 1.05fr .95fr; overflow: hidden; }
        .auth-side { padding: 48px; background: linear-gradient(160deg, #14532d, #0f766e); color: #fff; position: relative; }
        .auth-side::after { content: ''; position: absolute; inset: auto -60px -60px auto; width: 220px; height: 220px; border-radius: 50%; background: rgba(255,255,255,.12); }
        .auth-form { padding: 42px; background: rgba(255,255,255,.96); }
        .eyebrow { display: inline-flex; align-items: center; gap: 10px; padding: 8px 14px; border-radius: 999px; background: rgba(255,255,255,.15); font-size: 13px; letter-spacing: .04em; text-transform: uppercase; }
        h1, h2, h3, p { margin: 0; }
        .auth-side h1 { font-size: 38px; line-height: 1.12; margin-top: 22px; }
        .auth-side p { margin-top: 18px; color: rgba(255,255,255,.85); line-height: 1.75; }
        .feature-list { margin-top: 28px; display: grid; gap: 14px; }
        .feature-item { padding: 14px 16px; border-radius: 16px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.08); }
        .title-block h2 { font-size: 28px; margin-bottom: 10px; }
        .title-block p { color: var(--muted); line-height: 1.7; }
        .flash, .error-box, .ajax-message { padding: 14px 16px; border-radius: 14px; margin-bottom: 18px; font-size: 14px; }
        .flash.success, .ajax-message.success { background: var(--brand-soft); color: #166534; border: 1px solid #bbf7d0; }
        .flash.error, .error-box, .ajax-message.error { background: var(--danger-soft); color: var(--danger); border: 1px solid #fecaca; }
        .error-box ul { margin: 0; padding-left: 18px; }
        .ajax-message { display: none; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .form-group { margin-top: 18px; }
        .form-group.full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 600; }
        input { width: 100%; border: 1px solid var(--line); border-radius: 14px; padding: 14px 16px; font: inherit; color: var(--ink); background: #fff; transition: border-color .2s ease, box-shadow .2s ease; }
        input:focus { outline: none; border-color: #0f766e; box-shadow: 0 0 0 4px rgba(15,118,110,.12); }
        .field-error, label.error { display: block; margin-top: 8px; color: var(--danger); font-size: 13px; }
        .btn, button { border: 0; border-radius: 14px; padding: 14px 18px; font: inherit; font-weight: 700; cursor: pointer; transition: transform .2s ease, box-shadow .2s ease; }
        .btn:hover, button:hover { transform: translateY(-1px); }
        .btn-primary { color: #fff; background: linear-gradient(135deg, #14532d, #0f766e); box-shadow: 0 16px 32px rgba(20,83,45,.22); }
        .btn-light { background: #f8fafc; color: var(--ink); border: 1px solid var(--line); }
        .btn-danger { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
        .btn-sm { padding: 10px 14px; font-size: 13px; border-radius: 12px; }
        .auth-actions { margin-top: 24px; }
        .auth-footer { margin-top: 18px; color: var(--muted); font-size: 14px; }
        .auth-footer a { color: var(--brand-accent); font-weight: 700; }
        .topbar { display: flex; justify-content: space-between; gap: 18px; align-items: center; padding: 26px 28px; margin-bottom: 24px; }
        .brand h1 { font-size: 28px; }
        .brand p { margin-top: 8px; color: var(--muted); }
        .topbar-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; margin-bottom: 24px; }
        .stat-card, .panel { padding: 28px; }
        .stat-label { color: var(--muted); font-size: 14px; }
        .stat-value { margin-top: 10px; font-size: 40px; font-weight: 800; }
        .stat-note { margin-top: 10px; color: var(--muted); font-size: 14px; line-height: 1.65; }
        .panel-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
        .panel-head p { color: var(--muted); margin-top: 8px; }
        table.dataTable thead th { color: var(--muted); font-weight: 700; padding: 14px 12px; }
        table.dataTable tbody td { padding: 14px 12px; vertical-align: middle; }
        .actions { display: flex; gap: 8px; }
        .modal { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.58); z-index: 999; padding: 18px; overflow-y: auto; }
        .modal-dialog { max-width: 620px; margin: 60px auto; background: #fff; border-radius: 24px; box-shadow: var(--shadow); overflow: hidden; }
        .modal-header, .modal-body, .modal-footer { padding: 24px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eef2f7; }
        .modal-footer { border-top: 1px solid #eef2f7; display: flex; justify-content: flex-end; gap: 12px; }
        .close-modal { width: 42px; height: 42px; border-radius: 50%; padding: 0; background: #f8fafc; border: 1px solid var(--line); }
        @media (max-width: 900px) { .auth-card, .stats-grid, .form-grid { grid-template-columns: 1fr; } .auth-side, .auth-form, .topbar, .panel, .modal-header, .modal-body, .modal-footer { padding: 22px; } .topbar { align-items: flex-start; flex-direction: column; } }
    </style>
    @stack('styles')
</head>
<body>
    <div class="page-shell">
        <div class="container">
            @yield('content')
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
