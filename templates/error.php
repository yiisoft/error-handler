<?php
/* @var $throwable \Throwable */
/* @var $this \Yiisoft\ErrorHandler\HtmlRenderer */

use Yiisoft\ErrorHandler\UserException;

if ($throwable instanceof UserException) {
    $name = $this->getThrowableName($throwable);
    $message = $throwable->getMessage();
} else {
    $name = 'Error';
    $message = 'An internal server error occurred.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?= $this->htmlEncode($name) ?></title>

    <style>
        body {
            font: normal 9pt "Verdana";
            color: #000;
            background: #fff;
        }

        h1 {
            font: normal 18pt "Verdana";
            color: #f00;
            margin-bottom: .5em;
        }

        h2 {
            font: normal 14pt "Verdana";
            color: #800000;
            margin-bottom: .5em;
        }

        h3 {
            font: bold 11pt "Verdana";
        }

        p {
            font: normal 9pt "Verdana";
            color: #000;
        }

        .version {
            color: gray;
            font-size: 8pt;
            border-top: 1px solid #aaa;
            padding-top: 1em;
            margin-bottom: 1em;
        }
    </style>
</head>

<body>
    <h1><?= $this->htmlEncode($name) ?></h1>
    <h2><?= nl2br($this->htmlEncode($message)) ?></h2>
    <p>
        The above error occurred while the Web server was processing your request.
    </p>
    <p>
        Please contact us if you think this is a server error. Thank you.
    </p>
    <div class="version">
        <?= date('Y-m-d H:i:s') ?>
    </div>
</body>
</html>

