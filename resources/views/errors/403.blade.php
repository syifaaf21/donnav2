<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Forbidden</title>
    <style>
        :root {
            --bg-1: #f4f7fb;
            --bg-2: #eaf0f7;
            --ink-1: #0f172a;
            --ink-2: #475569;
            --line: #dbe3ee;
            --brand: #1d4ed8;
            --brand-2: #1e40af;
            --danger: #dc2626;
            --danger-soft: #fee2e2;
            --card: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Plus Jakarta Sans", "Manrope", "Segoe UI", sans-serif;
            color: var(--ink-1);
            background-color: var(--bg-1);
            background-image:
                radial-gradient(920px 360px at 0% 0%, rgba(30, 64, 175, 0.12) 0%, transparent 62%),
                radial-gradient(780px 340px at 100% 0%, rgba(220, 38, 38, 0.1) 0%, transparent 60%),
                linear-gradient(150deg, var(--bg-1), var(--bg-2));
            display: grid;
            place-items: center;
            padding: 20px;
        }

        .forbidden-wrap {
            width: min(820px, 100%);
            position: relative;
        }

        .forbidden-wrap::before {
            content: "";
            position: absolute;
            inset: -12px;
            border-radius: 26px;
            background: linear-gradient(130deg, rgba(37, 99, 235, 0.22), rgba(220, 38, 38, 0.14));
            filter: blur(14px);
            opacity: 0.7;
            z-index: 0;
        }

        .forbidden-shell {
            position: relative;
            z-index: 1;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            box-shadow: 0 22px 54px rgba(15, 23, 42, 0.14);
            overflow: hidden;
            animation: panelIn 380ms ease-out;
        }

        .forbidden-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 18px 22px;
            background: linear-gradient(120deg, #ffffff, #f8fbff);
            border-bottom: 1px solid var(--line);
        }

        .head-left {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .shield {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--danger);
            background: var(--danger-soft);
            font-weight: 700;
        }

        .head-title {
            margin: 0;
            font-size: 0.96rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #334155;
        }

        .status-pill {
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            background: #eff6ff;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        .forbidden-body {
            padding: 28px 24px 24px;
        }

        .kicker {
            margin: 0;
            color: #334155;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            font-weight: 700;
        }

        .code {
            margin: 8px 0 0;
            color: var(--danger);
            font-size: clamp(2.4rem, 5.8vw, 4rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0.01em;
        }

        .title {
            margin: 10px 0 10px;
            font-size: clamp(1.12rem, 2.8vw, 1.45rem);
            font-weight: 700;
            line-height: 1.3;
        }

        .desc {
            margin: 0;
            color: var(--ink-2);
            line-height: 1.68;
            max-width: 62ch;
        }

        .detail {
            margin-top: 16px;
            padding: 11px 13px;
            border: 1px solid #fecaca;
            background: #fff7f7;
            border-radius: 12px;
            color: #7f1d1d;
            font-size: 0.9rem;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 22px;
        }

        .btn {
            border: 1px solid transparent;
            border-radius: 12px;
            padding: 10px 15px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            box-shadow: 0 10px 20px rgba(29, 78, 216, 0.28);
        }

        .btn-secondary {
            color: #0f172a;
            border-color: #cfd9e6;
            background: #fff;
        }

        .footnote {
            margin-top: 16px;
            color: #64748b;
            font-size: 0.82rem;
        }

        @keyframes panelIn {
            from {
                opacity: 0;
                transform: translateY(8px) scale(0.99);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 640px) {
            .forbidden-head {
                padding: 14px 16px;
            }

            .forbidden-body {
                padding: 22px 16px 18px;
            }

            .status-pill {
                font-size: 0.7rem;
                padding: 5px 9px;
            }
        }
    </style>
</head>

<body>
    <div class="forbidden-wrap">
        <main class="forbidden-shell">
            <section class="forbidden-head">
                <div class="head-left">
                    <span class="shield" aria-hidden="true">!</span>
                    <h1 class="head-title">Security Gateway</h1>
                </div>
                <span class="status-pill">HTTP 403</span>
            </section>

            <section class="forbidden-body">
                <p class="kicker">Access Denied</p>
                <p class="code">403</p>
                <p class="title">You are not allowed to open this page.</p>
                <p class="desc">Your current role does not include permission for this menu or action. Contact an administrator if this access is required for your work.</p>

                @if (!empty($message))
                    <div class="detail">{{ $message }}</div>
                @endif

                <div class="actions">
                    <a class="btn btn-primary" href="{{ route('dashboard') }}">Back to Dashboard</a>
                    <a class="btn btn-secondary" href="javascript:history.back()">Go Back</a>
                </div>

                <div class="footnote">If you think this is unexpected, ask Admin or Super Admin to review your role permissions.</div>
            </section>
        </main>
    </div>
</body>

</html>
