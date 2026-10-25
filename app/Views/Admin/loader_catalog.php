<?= $this->extend('Layout/Starter') ?>
<?= $this->section('content') ?>
<style>
.catalog-wrap{max-width:1140px;margin:2rem auto;color:#ecedff}
.catalog-hero{padding:1.7rem 2rem;border:1px solid #38344e;border-radius:16px;background:#201d2c}
.catalog-hero h1{font-size:1.65rem;font-weight:750;letter-spacing:-.025em;margin:0 0 .4rem}
.catalog-sub{color:#bfbcd1;font-size:.96rem;margin-bottom:0}
.catalog-box{background:#211e2e;border:1px solid #38344e;border-radius:16px;padding:1.5rem;margin-top:1.25rem}
.catalog-box h2{font-size:1.16rem;font-weight:700;margin-bottom:1.25rem}
.catalog-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,240px),1fr));gap:1rem}
.catalog-grid .field{min-width:0}
.catalog-grid label{display:block;font-size:.88rem;margin-bottom:.4rem;color:#cecada;font-weight:600}
.catalog-grid input,.catalog-grid select{background:#15131d;border:1px solid #484357;border-radius:9px;color:#faf9ff;padding:.72rem .78rem;width:100%;outline-offset:2px}
.catalog-grid input:focus,.catalog-grid select:focus{border-color:#b19cff;outline:2px solid #55418a}
.catalog-grid select option{background:#15131d}
.catalog-actions{display:flex;gap:.7rem;align-items:center;flex-wrap:wrap;margin-top:1.25rem}
.catalog-btn{display:inline-flex;align-items:center;justify-content:center;border-radius:9px;padding:.69rem 1rem;font-size:.9rem;font-weight:650;text-decoration:none;cursor:pointer;border:1px solid #61568e;background:#7f6bc4;color:white}
.catalog-btn:hover{background:#9c85ed;color:white}
.catalog-btn.secondary{background:#302b3e;border-color:#514963;color:#f1eefe}
.catalog-btn.danger{background:transparent;color:#ffada9;border-color:#994b51}
.catalog-btn.danger:hover{background:#50252c}
.catalog-table{width:100%;border-collapse:separate;border-spacing:0}
.catalog-table th,.catalog-table td{padding:1rem .75rem;text-align:left;border-bottom:1px solid #413a50;vertical-align:middle}
.catalog-table th{color:#b6b0c9;font-size:.8rem;text-transform:uppercase;letter-spacing:.06em}
.catalog-table td{font-size:.92rem}
.catalog-table .muted{color:#b6b0c9;font-size:.81rem}
.catalog-table code{color:#dac9ff}
.catalog-tag{border:1px solid #5b5572;border-radius:50px;padding:.22rem .55rem;font-size:.77rem}
.catalog-empty{border:1px dashed #554a6d;padding:1.4rem;text-align:center;border-radius:10px;color:#bfbcd1}
.catalog-feedback{margin-top:1rem;border-radius:9px;padding:.85rem 1.1rem;background:#34304c;border:1px solid #7263b2}
.catalog-feedback.error{background:#452a34;border-color:#9d4f67}
@media(max-width:700px){.catalog-hero,.catalog-box{padding:1.2rem}.catalog-table-wrap{overflow:auto}.catalog-table{min-width:650px}}
</style>
<div class="catalog-wrap">
  <div class="catalog-hero">
    <h1>Loader catalog</h1>
    <p class="catalog-sub">Manage the libraries displayed in ASTRAL LOADER. License validation continues through the existing /connect endpoint.</p>
  </div>
  <?php if (session()->getFlashdata('catalog_notice')): ?>
    <div class="catalog-feedback" role="status"><?= esc(session()->getFlashdata('catalog_notice')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('catalog_error')): ?>
    <div class="catalog-feedback error" role="alert"><?= esc(session()->getFlashdata('catalog_error')) ?></div>
  <?php endif; ?>
  <section class="catalog-box">
    <h2><?= $selected ? 'Edit library' : 'Add library' ?></h2>
    <form method="post" action="<?= site_url('admin/loader-catalog/save') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= esc((string) ($selected['id'] ?? 0)) ?>">
      <?php $value = static fn(string $key): string => esc((string) ($selected[$key] ?? ''), 'attr'); ?>
      <div class="catalog-grid">
        <div class="field"><label for="cat-name">Display name</label><input required maxlength="120" name="name" id="cat-name" value="<?= $value('name') ?>" placeholder="Product name"></div>
        <div class="field"><label for="cat-type">License game TYPE</label><input required maxlength="32" list="known-license-types" name="game_type" id="cat-type" value="<?= $value('game_type') ?>" placeholder="Must match keys_code.game"><datalist id="known-license-types"><?php foreach ($licenseGames as $row): ?><option value="<?= esc($row['game'], 'attr') ?>"><?php endforeach; ?></datalist></div>
        <div class="field"><label for="cat-package">Android package</label><input required maxlength="190" name="game_package" id="cat-package" value="<?= $value('game_package') ?>" placeholder="com.example.app"></div>
        <div class="field"><label for="cat-arch">Architecture</label><select name="architecture" id="cat-arch"><option value="64bit" <?= ($selected['architecture'] ?? '64bit') === '64bit' ? 'selected' : '' ?>>64bit</option><option value="32bit" <?= ($selected['architecture'] ?? '') === '32bit' ? 'selected' : '' ?>>32bit</option></select></div>
        <div class="field"><label for="cat-download">HTTPS file URL</label><input required type="url" maxlength="1024" name="download_url" id="cat-download" value="<?= $value('download_url') ?>" placeholder="https://example.org/file.so"></div>
        <div class="field"><label for="cat-jni">Library entry key (public metadata)</label><input maxlength="255" name="jni_key" id="cat-jni" value="<?= $value('jni_key') ?>" autocomplete="off" placeholder="Not a private secret"></div>
        <div class="field"><label for="cat-version">Version label</label><input maxlength="50" name="version_name" id="cat-version" value="<?= $value('version_name') ?>" placeholder="1.0"></div>
        <div class="field"><label for="cat-logo">HTTPS image URL (optional)</label><input type="url" maxlength="1024" name="logo_url" id="cat-logo" value="<?= $value('logo_url') ?>" placeholder="https://example.org/image.png"></div>
        <div class="field"><label for="cat-status">Visibility</label><select name="status" id="cat-status">
          <?php foreach (['ACTIVE' => 'Active', 'MAINTENANCE' => 'Maintenance', 'DISABLED' => 'Hidden'] as $status => $text): ?>
          <option value="<?= $status ?>" <?= ($selected['status'] ?? 'ACTIVE') === $status ? 'selected' : '' ?>><?= $text ?></option>
          <?php endforeach; ?>
        </select></div>
        <div class="field"><label for="cat-order">Sort order</label><input type="number" min="0" max="100000" name="sort_order" id="cat-order" value="<?= esc((string) ($selected['sort_order'] ?? 100), 'attr') ?>"></div>
      </div>
      <div class="catalog-actions"><button class="catalog-btn" type="submit">Save library</button><?php if ($selected): ?><a class="catalog-btn secondary" href="<?= site_url('admin/loader-catalog') ?>">Cancel editing</a><?php endif; ?></div>
    </form>
  </section>
  <section class="catalog-box">
    <h2>Library entries (<?= count($entries) ?>)</h2>
    <?php if (! $entries): ?><div class="catalog-empty">No entries yet. Add your first library above, then test the catalog URL.</div>
    <?php else: ?><div class="catalog-table-wrap"><table class="catalog-table"><thead><tr><th>Library</th><th>License type</th><th>Architecture</th><th>Visibility</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($entries as $entry): ?><tr>
        <td><strong><?= esc($entry['name']) ?></strong><div class="muted"><?= esc($entry['game_package']) ?></div></td>
        <td><code><?= esc($entry['game_type']) ?></code></td>
        <td><?= esc($entry['architecture']) ?></td>
        <td><span class="catalog-tag"><?= esc($entry['status']) ?></span></td>
        <td><div class="catalog-actions" style="margin:0"><a class="catalog-btn secondary" href="<?= site_url('admin/loader-catalog') . '?edit=' . (int) $entry['id'] ?>">Edit</a>
          <form method="post" action="<?= site_url('admin/loader-catalog/remove') ?>" onsubmit="return confirm('Remove this catalog entry?');">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $entry['id'] ?>"><button class="catalog-btn danger" type="submit">Remove</button>
          </form></div></td>
      </tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
    <div class="catalog-actions"><a class="catalog-btn secondary" href="<?= site_url('loader/catalog') ?>" target="_blank" rel="noopener noreferrer">View catalog response</a></div>
  </section>
</div>
<?= $this->endSection() ?>
