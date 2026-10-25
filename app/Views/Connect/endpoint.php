<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center pt-5">
    <div class="col-lg-7">
        <div class="endpoint-card">
            <div class="endpoint-head">
                <span class="shai-wordmark">SHAI<em>.</em>ENDPOINT</span>
                <span class="endpoint-badge"><?= $maintenance ? 'MAINTENANCE' : 'ONLINE' ?></span>
            </div>

            <div class="endpoint-body">
                <h1 class="endpoint-title">API Endpoint</h1>
                <p class="endpoint-lead">
                    License verification endpoint used by the client application to validate keys,
                    register devices and pull module settings.
                </p>

                <div class="endpoint-row">
                    <span class="endpoint-method">POST</span>
                    <span class="endpoint-note">only POST requests are accepted</span>
                </div>

                <div class="endpoint-grid">
                    <div class="endpoint-item">
                        <span class="endpoint-key">client</span>
                        <span class="endpoint-val"><?= esc($info['web_info']['_client']) ?></span>
                    </div>
                    <div class="endpoint-item">
                        <span class="endpoint-key">version</span>
                        <span class="endpoint-val"><?= esc($info['web_info']['version']) ?></span>
                    </div>
                    <div class="endpoint-item">
                        <span class="endpoint-key">license</span>
                        <span class="endpoint-val"><?= esc(trim($info['web_info']['license'])) ?></span>
                    </div>
                    <div class="endpoint-item">
                        <span class="endpoint-key">status</span>
                        <span class="endpoint-val"><?= $maintenance ? 'maintenance' : 'operational' ?></span>
                    </div>
                </div>

                <h2 class="endpoint-sub">Request parameters</h2>
                <div class="endpoint-code mono">
                    game=MLBB<br>
                    user_key=YOUR_KEY<br>
                    serial=DEVICE_SERIAL
                </div>

                <h2 class="endpoint-sub">Example</h2>
                <div class="endpoint-code mono">
                    curl -X POST <?= esc(site_url('connect')) ?> \<br>
                    &nbsp;&nbsp;-d 'game=MLBB&amp;user_key=YOUR_KEY&amp;serial=DEVICE_SERIAL'
                </div>

                <h2 class="endpoint-sub">Developers</h2>
                <div class="endpoint-links">
                    <a class="endpoint-link" href="https://t.me/Shaiaintshyy" target="_blank" rel="noopener">
                        <i class="bi bi-telegram"></i> Telegram
                    </a>
                    <a class="endpoint-link" href="<?= site_url('login') ?>">
                        <i class="bi bi-box-arrow-in-right"></i> Panel login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .endpoint-card {
        background: var(--shai-surface);
        border: 1.5px solid var(--shai-line);
    }
    .endpoint-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1.5px solid var(--shai-line);
    }
    .endpoint-head .shai-wordmark { font-size: .95rem; }
    .endpoint-badge {
        font-family: 'JetBrains Mono', monospace;
        font-size: .68rem;
        letter-spacing: .14em;
        padding: 3px 8px;
        border: 1px solid var(--shai-line-2);
        color: var(--shai-ok);
    }
    .endpoint-body { padding: 26px 22px 30px; }
    .endpoint-title { font-size: 1.5rem; margin: 0 0 10px; }
    .endpoint-lead { color: var(--shai-dim); font-size: .92rem; line-height: 1.6; margin: 0 0 22px; }
    .endpoint-row { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
    .endpoint-method {
        font-family: 'JetBrains Mono', monospace;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .1em;
        padding: 5px 12px;
        color: #12121a;
        background: var(--shai-ok);
    }
    .endpoint-note { color: var(--shai-dim); font-size: .85rem; }
    .endpoint-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1.5px;
        background: var(--shai-line);
        border: 1.5px solid var(--shai-line);
        margin-bottom: 26px;
    }
    .endpoint-item { background: var(--shai-surface); padding: 14px; }
    .endpoint-key {
        display: block;
        font-size: .68rem;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--shai-dim);
        margin-bottom: 6px;
    }
    .endpoint-val { font-family: 'JetBrains Mono', monospace; font-size: .85rem; }
    .endpoint-sub {
        font-size: .72rem;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--shai-dim);
        margin: 0 0 10px;
    }
    .endpoint-code {
        background: var(--shai-bg);
        border: 1.5px solid var(--shai-line);
        padding: 14px;
        font-size: .8rem;
        line-height: 1.7;
        margin-bottom: 26px;
        overflow-x: auto;
    }
    .endpoint-links { display: flex; flex-wrap: wrap; gap: 10px; }
    .endpoint-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: 1.5px solid var(--shai-line-2);
        font-size: .85rem;
        font-weight: 600;
        color: var(--shai-text) !important;
        transition: border-color .15s ease, color .15s ease, transform .1s ease;
    }
    .endpoint-link:hover {
        border-color: var(--shai-accent);
        color: var(--shai-accent-2) !important;
        transform: translateY(-1px);
    }
</style>
<?= $this->endSection() ?>
