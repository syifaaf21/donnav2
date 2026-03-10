<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Forbidden</title>
    <style>
        :root {
            --bg-1: #f8fafc;
            --bg-2: #eef2ff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --accent: #dc2626;
            --accent-soft: #fee2e2;
            --card: #ffffff;
            --line: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-main);
            background:
                radial-gradient(900px 320px at 8% 8%, #dbeafe 0%, transparent 55%),
                radial-gradient(760px 340px at 92% 0%, #fee2e2 0%, transparent 60%),
                linear-gradient(145deg, var(--bg-1), var(--bg-2));
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .forbidden-shell {
            width: min(760px, 100%);
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(6px);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        .forbidden-head {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 22px 26px;
            background: linear-gradient(120deg, #fff, #fef2f2);
            border-bottom: 1px solid var(--line);
        }

        .badge {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            color: var(--accent);
            background: var(--accent-soft);
            font-weight: 700;
        }

        .forbidden-head h1 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .forbidden-body {
            padding: 28px 26px 24px;
        }

        .code {
            margin: 0;
            color: var(--accent);
            font-size: 2.6rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0.02em;
        }

        .title {
            margin: 8px 0 10px;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .desc {
            margin: 0;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .detail {
            margin-top: 14px;
            padding: 11px 12px;
            border: 1px solid #fecaca;
            background: #fff7ed;
            border-radius: 10px;
            color: #7f1d1d;
            font-size: 0.92rem;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 22px;
        }

        .btn {
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 10px 14px;
            text-decoration: none;
            font-size: 0.93rem;
            font-weight: 600;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 10px 18px rgba(37, 99, 235, 0.24);
        }

        .btn-secondary {
            color: #1e293b;
            border-color: #cbd5e1;
            background: #fff;
        }

        .footnote {
            margin-top: 14px;
            color: #94a3b8;
            font-size: 0.82rem;
        }
    </style>
</head>

<body>
    <main class="forbidden-shell">
        <section class="forbidden-head">
            <div class="badge">!</div>
            <h1>Access Forbidden</h1>
        </section>

        <section class="forbidden-body">
            <p class="code">403</p>
            <p class="title">You do not have permission to access this page.</p>
            <p class="desc">Your account role is not authorized for this menu or action. If you need access, please contact an administrator.</p>

            @if (!empty($message))
                <div class="detail">{{ $message }}</div>
            @endif

            <div class="actions">
                <a class="btn btn-primary" href="{{ route('dashboard') }}">Back to Dashboard</a>
                <a class="btn btn-secondary" href="javascript:history.back()">Go Back</a>
            </div>

            <div class="footnote">If you think this is a mistake, ask Admin/Super Admin to verify your role permissions.</div>
        </section>
    </main>
</body>

</html>
