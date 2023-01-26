<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>OTP QRCode</title>
        <meta name="description" content="Big Error">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
        <!-- Call App Mode on ios devices -->
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <!-- Remove Tap Highlight on Windows Phone IE -->
        <meta name="msapplication-tap-highlight" content="no">
        <!-- base css -->
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/vendors.bundle.css">
        <link rel="stylesheet" media="screen, print" href="<?=$assets_dir?>/css/app.bundle.css">
        <!-- Place favicon.ico in the root directory -->
        <link rel="apple-touch-icon" sizes="180x180" href="<?=$assets_dir?>/img/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="<?=$assets_dir?>/img/favicon/favicon-32x32.png">
        <link rel="mask-icon" href="<?=$assets_dir?>/img/favicon/safari-pinned-tab.svg" color="#5bbad5">
        <!-- Optional: page related CSS-->
    </head>
    <body>
        <!-- BEGIN Page Wrapper -->
        <div class="page-wrapper alt">
            <!-- BEGIN Page Content -->
            <!-- the #js-page-content id is needed for some plugins to initialize -->
            <main id="js-page-content" role="main" class="page-content">
                <div class="h-alt-f d-flex flex-column align-items-center justify-content-center text-center">
                    <h1 class="page-error color-fusion-500">
                        ERROR <span class="text-gradient">PAGE</span>
                        <small class="fw-500">
                            <?=$msg?>
                        </small>
                    </h1>
                    <?php /*?>
                    <h3 class="fw-500 mb-5">
                        You have experienced a technical error. We apologize.
                    </h3>
                    <?php */?>
                    <h4>
                        QRCode 는 발급기준 24시간이내 유효합니다.
                    </h4>
                </div>
            </main>
            <!-- END Page Content -->
            <!-- BEGIN Page Footer -->
            <footer class="page-footer" role="contentinfo">
                <div class="d-flex align-items-center flex-1 text-muted">
                    <span class="hidden-md-down fw-700">2022 © SECU-KMS by&nbsp;<?=$page_title?></span>
                </div>
            </footer>
            <!-- END Page Footer -->
        </div>
        <!-- END Page Wrapper -->

        <script src="<?=$assets_dir?>/js/vendors.bundle.js"></script>
        <script src="<?=$assets_dir?>/js/app.bundle.js"></script>
    </body>
</html>

