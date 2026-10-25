<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
      <!-- Browser Logo -->
  <link rel="shortcut icon"
    href="<?= site_url('getkey/img/logo.jpg') ?>"
    type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <title><?= BASE_NAME ?> - <?= isset($title) ? $title : 'Panel' ?></title>
    <?= $this->renderSection('css') ?>

    <?= link_tag('assets/css/shai.css') ?>
    <?= link_tag('assets/css/natacode.css') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
</head>
<style>

        .hacks {
          position: relative;
          display: inline-block;
          width: 100%;
          height: 20px;
          float: left;
          margin: 5%;
        }
        .switch {
          position: relative;
          display: inline-block;
          width: 40px;
          height: 20px;
          float: right-end;
          align-items: flex-end;
          margin: 5px 5px 5px 5px;
        }
        
        
        .switch input {
          opacity: 0;
          width: 0;
          height: 0;
        }
        
        
        .slider {
          position: absolute;
          cursor: pointer;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: #ccc;
          -webkit-transition: .4s;
          transition: .4s;
        }
        
        .slider:before {
          position: absolute;
          content: "";
          height: 12px;
          width: 12px;
          left: 4px;
          bottom: 4px;
          background-color: white;
          -webkit-transition: .4s;
          transition: .4s;
        }
        
        input:checked + .slider {
          background-color: #2196F3;
        }
        
        input:focus + .slider {
          box-shadow: 0 0 1px #2196F3;
        }
        
        input:checked + .slider:before {
          -webkit-transform: translateX(20px);
          -ms-transform: translateX(20px);
          transform: translateX(20px);
        }
        
        
        .slider.round {
          border-radius: 34px;
        }
        
        .slider.round:before {
          border-radius: 50%;
        }
    </style>
<body>
    <div id="shai-progress" aria-hidden="true"></div>
    <!-- Start menu -->
    <?= $this->include('Layout/Header') ?>
    <!-- End of menu -->
    <main>
        <div class="container p-3 py-4 mb-3" id="content">
            <!-- Start content -->
            <?= $this->renderSection('content') ?>

            <!-- End of content -->
        </div>
    </main>
    <a class="shai-tg" href="https://t.me/Shaiaintshyy" target="_blank" rel="noopener" aria-label="Contact on Telegram">
        <i class="bi bi-telegram"></i>
        <span class="shai-tg-label">Telegram</span>
    </a>

    <footer class="fixed-bottom nb-footer">
        <div class="container py-2">
            <small>&copy; <?= date('Y') ?> <?= BASE_NAME ?></small>
        </div>
    </footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.2"></script>
    <!-- script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.1.0/sweetalert2.all.min.js" integrity="sha512-0UUEaq/z58JSHpPgPv8bvdhHFRswZzxJUT9y+Kld5janc9EWgGEVGfWV1hXvIvAJ8MmsR5d4XV9lsuA90xXqUQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script -->

    <?= script_tag('assets/js/natacode.js') ?>

    <script>
        (function () {
            var bar = document.getElementById('shai-progress');
            if (!bar) { return; }

            window.addEventListener('pageshow', function () {
                bar.className = 'done';
                setTimeout(function () { bar.className = ''; }, 320);
            });

            function start() {
                bar.className = 'on';
            }

            document.addEventListener('click', function (e) {
                var a = e.target.closest ? e.target.closest('a') : null;
                if (!a || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank') { return; }
                var href = a.getAttribute('href') || '';
                if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) { return; }
                if (a.host && a.host !== window.location.host) { return; }
                start();
            });

            document.addEventListener('submit', function () { start(); }, true);
        })();
    </script>

    <?= $this->renderSection('js') ?>


<style>
    .shai-tg {
        position: fixed;
        right: 18px;
        bottom: 58px;
        z-index: 1045;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        height: 46px;
        padding: 0 16px 0 14px;
        background: var(--shai-accent);
        border: 1.5px solid var(--shai-accent);
        color: #fff !important;
        font-size: .85rem;
        font-weight: 600;
        box-shadow: 3px 3px 0 0 var(--shai-purple-dk, #5b21b6);
        transition: transform .12s ease, box-shadow .12s ease, background .15s ease;
        animation: shai-tg-in .5s cubic-bezier(.2, .8, .3, 1) both .6s;
    }
    .shai-tg i { font-size: 1.1rem; }
    .shai-tg-label { letter-spacing: .02em; }
    .shai-tg:hover {
        background: var(--shai-accent-2);
        color: #12121a !important;
        transform: translate(-1px, -1px);
        box-shadow: 5px 5px 0 0 var(--shai-accent);
    }
    .shai-tg::before {
        content: '';
        position: absolute;
        inset: -1.5px;
        border: 1.5px solid var(--shai-accent);
        animation: shai-tg-ring 2.4s ease-out infinite;
        pointer-events: none;
    }
    @keyframes shai-tg-ring {
        0%   { opacity: .7; transform: scale(1); }
        70%  { opacity: 0; transform: scale(1.35); }
        100% { opacity: 0; transform: scale(1.35); }
    }
    @keyframes shai-tg-in {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: none; }
    }
    @media (max-width: 575.98px) {
        .shai-tg { padding: 0 13px; }
        .shai-tg-label { display: none; }
    }
</style>
</body>

</html>

