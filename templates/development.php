<?php
/* @var $throwable \Throwable */
/* @var $request \Psr\Http\Message\ServerRequestInterface|null */
/* @var $this \Yiisoft\ErrorHandler\Renderer\HtmlRenderer */

use Yiisoft\FriendlyException\FriendlyExceptionInterface;

$theme = $_COOKIE['yii-exception-theme'] ?? '';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $this->htmlEncode($this->getThrowableName($throwable)) ?>
    </title>
    <style>
        /* reset */
        html,
        body,
        div,
        span,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        pre,
        a,
        code,
        em,
        img,
        strong,
        b,
        i,
        ul,
        li {
            margin: 0;
            padding: 0;
            border: 0;
            font: inherit;
            vertical-align: baseline;
        }

        body {
            font-family: 'Roboto', sans-serif;
            min-width: 800px;
            color: #505050;
            line-height: 1;
        }

        ul {
            list-style: none;
        }
        /* end reset */

        /* base */
        a {
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
        /* end base */

        /* header */
        header {
            padding: 65px 100px 270px 100px;
            background: #ededed;
        }

        header .tools {
            margin-bottom: 50px;
            text-align: right;
        }

        header .tools a {
            margin-left: 45px;
            text-decoration: none;
        }

        header .tools a:hover svg path {
            fill: #000;
        }

        header .exception-card {
            display: flex;
            background: #fafafa;
            border: 2px solid #d83c24;
            box-sizing: border-box;
            border-radius: 3px;
            padding: 40px 30px;
            word-break: break-word;
        }

        header .exception-class {
            margin-bottom: 30px;
            font-weight: 500;
            font-size: 36px;
            line-height: 42px;
            color: #E79185;
        }

        header .exception-class a {
            color: #E57373;
        }

        header .exception-message {
            font-size: 24px;
            line-height: 28px;
            color: #4b4b4b;
        }

        header .exception-class span,
        header .exception-class span a {
            color: #e51717;
        }

        header .solution {
            margin-top: 20px;
            white-space: pre;
            font-size: 16px;
            line-height: 22px;
        }

        header .previous {
            display: flex;
            margin-top: 20px;
        }

        header .previous .arrow {
            display: inline-block;
            transform: scale(-1, 1);
            font-size: 26px;
            color: #e51717;
            margin-top: -5px;
            margin-right: 10px;
        }

        header .previous h2 {
            font-size: 20px;
            color: #e57373;
            margin-bottom: 10px;
        }

        header .previous h2 span {
            color: #e51717;
        }

        header .previous h3 {
            font-size: 14px;
            margin: 10px 0;
        }

        #clipboard {
            position: absolute;
            top: -500px;
            right: 300px;
            width: 750px;
            height: 150px;
        }

        #copy-stacktrace:hover svg path {
            fill: #000;
        }

        #copied {
            display: none;
            float: left;
            height: 25px;
            padding: 5px;
            margin-right: 5px;
        }

        #light-mode {
            display: none;
        }
        /* end header */

        main {
            margin-left: 100px;
            margin-right: 100px;
        }

        @media screen and (max-width: 1200px) {
            header {
                padding-left: 50px;
                padding-right: 50px;
            }

            main {
                margin-left: 50px;
                margin-right: 50px;
            }
        }

        .flex-1 {
            flex: 1;
        }

        /* call stack */
        .call-stack ul li,
        .request {
            border: 2px solid #d0d0d0;
            box-shadow: 0 15px 20px rgba(0, 0, 0, 0.05);
            background: #fff;
            margin-bottom: 30px;
            border-radius: 3px;
        }

        .call-stack > ul > li:first-child {
            margin-top: -200px;
        }

        .call-stack > ul > li:last-child {
            margin-bottom: 50px;
        }

        .call-stack > ul > li.call-stack-vendor-group {
            background: #ededed;
        }

        .call-stack > ul > li.call-stack-vendor-group .call-stack-vendor-state {
            display: inline-block;
            height: 22px;
            width: 22px;
            font-size: 20px;
            color: #fff;
            background: #999;
            border-radius: 3px;
            text-align: center;
            margin-right: 15px;
        }

        .call-stack > ul > li.call-stack-vendor-group > ul {
            display: none;
            background: #ededed;
        }

        .call-stack > ul > li.call-stack-vendor-group > ul > li {
            border-left: 0;
            border-right: 0;
            box-shadow: none;
        }

        .call-stack > ul > li.call-stack-vendor-group > ul > li:last-child {
            border-bottom: 2px solid transparent;
            margin-bottom: 0;
        }

        .call-stack ul li .element-wrap {
            display: flex;
            cursor: pointer;
            padding: 20px 30px;
            font-weight: 500;
            font-size: 18px;
            line-height: 21px;
            color: #4b4b4b;
        }

        .call-stack ul li .element-wrap .file-name {
            color: #4b4b4b;
        }

        .call-stack ul li .element-wrap:hover .file-name,
        .call-stack ul li .element-code-wrap .code-wrap .lines-item:hover {
            color: #086eb6;
        }

        .call-stack ul li .element-wrap .function-info {
            display: inline-block;
        }

        .call-stack ul li.application .element-wrap {
            border-bottom: 1px solid #d0d0d0;
        }

        .call-stack ul li a {
            color: #505050;
        }

        .call-stack ul li a:hover {
            color: #000;
        }

        .call-stack ul li a .external-link {
            vertical-align: middle;
        }

        .call-stack ul li a .external-link:hover path {
            fill: #000;
        }

        .call-stack ul li a .external-link path {
            fill: #505050;
        }

        .call-stack ul li .element-code-wrap {
            overflow-x: auto;
        }

        .call-stack ul li .element-code-wrap .code-wrap {
            display: none;
            position: relative;
        }

        .call-stack ul li.application .element-code-wrap .code-wrap {
            display: block;
        }

        .call-stack ul li .error-line,
        .call-stack ul li .hover-line {
            background-color: #ffebeb;
            position: absolute;
            width: 100%;
            z-index: 100;
            margin-top: 0;
        }

        .call-stack ul li .hover-line {
            background: none;
        }

        .call-stack ul li .hover-line.hover,
        .call-stack ul li .hover-line:hover {
            background: #edf9ff !important;
        }

        .call-stack ul li .code {
            min-width: 700px;
            /* 800px - 50px * 2 */
            margin: 15px auto;
            padding: 0 50px;
            position: relative;
        }

        .call-stack ul li .code .lines-item {
            position: absolute;
            z-index: 200;
            display: block;
            width: 25px;
            text-align: right;
            color: #aaa;
            line-height: 20px;
            font-size: 12px;
            margin-top: 1px;
            font-family: JetBrains Mono, Consolas, monospace;
        }

        .call-stack ul li .code pre {
            position: relative;
            z-index: 200;
            left: 50px;
            line-height: 20px;
            font-size: 12px;
            font-family: JetBrains Mono, Consolas, monospace;
            display: inline;
        }

        @-moz-document url-prefix() {
            .call-stack ul li .code pre {
                line-height: 20px;
            }
        }
        /* end call stack */

        /* request */
        .request {
            padding: 20px 30px;
            font-size: 14px;
            line-height: 18px;
            overflow-x: auto;
            font-family: JetBrains Mono, Consolas, monospace;
        }
        /* end request */

        /* footer */
        .footer {
            display: flex;
        }

        .footer div {
            align-self: center;
        }

        .footer .timestamp,
        .footer .server {
            margin-bottom: 20px;
        }

        .footer p,
        .footer p a {
            font-size: 24px;
            line-height: 28px;
            color: #9c9c9c;
        }

        .footer p a:hover {
            color: #000;
        }

        .footer svg {
            margin-right: -50px;
        }
        /* end footer */

        /* highlight.js */
        .hljs {
            display: block;
            color: #2f3337;
        }

        .hljs-comment {
            color: #656e77;
        }

        .hljs-keyword,
        .hljs-selector-tag,
        .hljs-meta-keyword,
        .hljs-doctag,
        .hljs-section,
        .hljs-selector-class,
        .hljs-meta,
        .hljs-selector-pseudo,
        .hljs-attr {
            color: #015692;
        }

        .hljs-attribute {
            color: #803378;
        }

        .hljs-name,
        .hljs-type,
        .hljs-number,
        .hljs-selector-id,
        .hljs-quote,
        .hljs-template-tag,
        .hljs-built_in,
        .hljs-title,
        .hljs-literal {
            color: #b75501;
        }

        .hljs-string,
        .hljs-regexp,
        .hljs-symbol,
        .hljs-variable,
        .hljs-template-variable,
        .hljs-link,
        .hljs-selector-attr,
        .hljs-meta-string {
            color: #54790d;
        }

        .hljs-bullet,
        .hljs-code {
            color: #535a60;
        }

        .hljs-deletion {
            color: #c02d2e;
        }

        .hljs-addition {
            color: #2f6f44;
        }

        .hljs-emphasis {
            font-style: italic;
        }

        .hljs-strong {
            font-weight: bold;
        }
        /* end highlight.js */

        /* start dark-theme */
        .dark-theme {
            background: rgba(46,46,46, 0.9);
            color: #fff;
        }

        .dark-theme header {
            background: #2e2e2e;
        }

        .dark-theme .exception-card {
            background: #222;
            border: 3px solid #591e15;
        }

        .dark-theme .exception-class,
        .dark-theme .call-stack ul li a {
            color: rgba(255, 255, 255, 0.5);
        }

        .dark-theme .exception-class span,
        .dark-theme .exception-class span a,
        .dark-theme .previous .arrow,
        .dark-theme .previous h2 span {
            color: #fff;
        }

        .dark-theme .exception-message,
        .dark-theme .solution,
        .dark-theme .previous {
            color: rgba(255, 255, 255, 0.8);
        }

        .dark-theme .call-stack ul li,
        .dark-theme .request {
            background: #1e1e1e;
            border: 2px solid transparent;
            box-shadow: 0 13px 20px rgba(0, 0, 0, 0.25);
        }

        .dark-theme .call-stack ul li.call-stack-vendor-group {
            background: rgba(46,46,46, 0.9);
            border: 2px solid #666;
        }

        .dark-theme .call-stack > ul > li.call-stack-vendor-group .call-stack-vendor-state {
            background: #666;
        }

        .dark-theme .call-stack > ul > li.call-stack-vendor-group > ul {
            background: rgba(46,46,46, 0.9);
        }

        .dark-theme .call-stack > ul > li.call-stack-vendor-group > ul > li {
            border-top: 2px solid #666;
            border-bottom: 2px solid #666;
            border-left: 0;
            border-right: 0;
            box-shadow: none;
        }

        .dark-theme .call-stack > ul > li.call-stack-vendor-group > ul > li:last-child {
            border-bottom: 2px solid transparent;
        }

        .dark-theme .call-stack ul li .error-line {
            background-color: #422c2c;
        }

        .dark-theme .call-stack ul li .hover-line.hover,
        .dark-theme .call-stack ul li .hover-line:hover {
            background: #292929 !important;
        }

        .dark-theme li.application .element-wrap {
            border-bottom: 1px solid #141414 !important;
        }

        .dark-theme .element-wrap,
        .dark-theme .element-wrap .file-name {
            color: #fff !important;
        }

        .dark-theme .element-wrap:hover .file-name,
        .dark-theme .call-stack ul li .element-code-wrap .code-wrap .lines-item:hover {
            color: #9cdcfe !important;
        }

        .dark-theme header .tools a:hover svg path,
        .dark-theme #copy-stacktrace:hover svg path,
        .dark-theme .call-stack ul li a .external-link:hover path {
            fill: #fff;
        }

        .dark-theme #copy-stacktrace svg path,
        .dark-theme header .tools a svg path,
        .dark-theme .call-stack ul li a .external-link path {
            fill: #989898;
        }

        .dark-theme #dark-mode {
            display: none;
        }

        .dark-theme #light-mode {
            display: inline;
        }

        .dark-theme .hljs {
            color: #fff;
        }

        .dark-theme .hljs-comment {
            color: #999;
        }

        .dark-theme .hljs-keyword,
        .dark-theme .hljs-selector-tag,
        .dark-theme .hljs-meta-keyword,
        .dark-theme .hljs-doctag,
        .dark-theme .hljs-section,
        .dark-theme .hljs-selector-class,
        .dark-theme .hljs-meta,
        .dark-theme .hljs-selector-pseudo,
        .dark-theme .hljs-attr {
            color: #88aece;
        }

        .dark-theme .hljs-attribute {
            color: #c59bc1;
        }

        .dark-theme .hljs-name,
        .dark-theme .hljs-type,
        .dark-theme .hljs-number,
        .dark-theme .hljs-selector-id,
        .dark-theme .hljs-quote,
        .dark-theme .hljs-template-tag,
        .dark-theme .hljs-built_in,
        .dark-theme .hljs-title,
        .dark-theme .hljs-literal {
            color: #f08d49;
        }

        .dark-theme .hljs-string,
        .dark-theme .hljs-regexp,
        .dark-theme .hljs-symbol,
        .dark-theme .hljs-variable,
        .dark-theme .hljs-template-variable,
        .dark-theme .hljs-link,
        .dark-theme .hljs-selector-attr,
        .dark-theme .hljs-meta-string {
            color: #b5bd68;
        }

        .dark-theme .hljs-bullet,
        .dark-theme .hljs-code {
            color: #cccccc;
        }

        .dark-theme .hljs-deletion {
            color: #de7176;
        }

        .dark-theme .hljs-addition {
            color: #76c490;
        }
        /* end dark-theme */
    </style>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700">
</head>
<body<?= !empty($theme) ? " class=\"{$this->htmlEncode($theme)}\"" : '' ?>>
<header>
    <div class="tools">
        <a href="#" title="Dark Mode" id="dark-mode">
            <svg width="28" height="28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M27.0597 19.06c-2.5312.9821-5.2934 1.2069-7.9501.6472-2.6568-.5598-5.0934-1.8799-7.0133-3.7998-1.9198-1.9198-3.24001-4.3565-3.79975-7.01324-.55974-2.65674-.33489-5.41892.64718-7.95016-3.04045 1.1849-5.57175 3.39424-7.15685 6.24657C.201778 10.0429-.337628 13.3592.26179 16.5668c.599419 3.2077 2.30004 6.1053 4.80824 8.1928C7.57822 26.847 10.7366 27.9931 13.9997 28c2.8246.0007 5.5833-.8527 7.9141-2.4481 2.3307-1.5955 4.1245-3.8585 5.1459-6.4919z" fill="#787878"/>
            </svg>
        </a>

        <a href="#" title="Light Mode" id="light-mode">
            <svg width="32" height="32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 2h2v5h-2V2zM21.6875 8.9l3.506-3.50699 1.414 1.415-3.506 3.50599-1.414-1.414zM25 15h5v2h-5v-2zM21.6875 23.1l1.414-1.414 3.506 3.506-1.414 1.415-3.506-3.507zM15 25h2v5h-2v-5zM5.39453 25.192l3.506-3.506 1.41397 1.415-3.50597 3.506-1.414-1.415zM2 15h5v2H2v-2zM5.39453 6.80801l1.414-1.415L10.3145 8.9l-1.41397 1.414-3.506-3.50599zM16 10c-1.1867 0-2.3467.3519-3.3334 1.0112-.9867.6593-1.7557 1.5963-2.2099 2.6927-.4541 1.0964-.57292 2.3028-.3414 3.4666.2315 1.1639.8029 2.233 1.6421 3.0721.8391.8392 1.9082 1.4106 3.0721 1.6421 1.1638.2315 2.3702.1127 3.4666-.3414 1.0964-.4541 2.0334-1.2232 2.6927-2.2099C21.6481 18.3467 22 17.1867 22 16c0-1.5913-.6321-3.1174-1.7574-4.2426C19.1174 10.6321 17.5913 10 16 10z" fill="#989898"/>
            </svg>
        </a>

        <a href="https://stackoverflow.com/search?<?= http_build_query(['q' => $throwable->getMessage()]) ?>" title="Search error on Stackoverflow" target="_blank">
            <svg width="28" height="32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M23.312 29.151v-8.536h2.849V32H.458008V20.615H3.29701v8.536H23.312zM6.14501 26.307H20.469v-2.848H6.14501v2.848zm.35-6.468L20.47 22.755l.599-2.76-13.96899-2.912-.605 2.756zm1.812-6.74L21.246 19.136l1.203-2.6-12.93699-6.041-1.204 2.584-.001.02zm3.61999-6.38L22.88 15.86l1.813-2.163L13.74 4.562l-1.803 2.151-.01.006zM19 0l-2.328 1.724 8.541 11.473 2.328-1.724L19 0z" fill="#787878"/>
            </svg>
        </a>
        <a href="https://www.google.com/search?<?= http_build_query(['q' => $throwable->getMessage()]) ?>" title="Search error on Google" target="_blank">
            <svg width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M23.5313 9.825H12.2407v4.6406h6.45c-.2781 1.5-1.1219 2.7688-2.3937 3.6188-1.075.7187-2.4469 1.1437-4.0594 1.1437-3.12188 0-5.7625-2.1094-6.70625-4.9437-.2375-.7188-.375-1.4875-.375-2.2781 0-.7907.1375-1.5594.375-2.27818.94687-2.83125 3.5875-4.94062 6.70935-4.94062 1.7594 0 3.3375.60625 4.5813 1.79375l3.4375-3.44063C18.1813 1.20312 15.472.015625 12.2407.015625c-4.68435 0-8.73748 2.687495-10.70935 6.606245C.718848 8.24062.256348 10.0719.256348 12.0094s.4625 3.7656 1.275002 5.3843C3.50322 21.3125 7.55635 24 12.2407 24c3.2375 0 5.95-1.075 7.9313-2.9062 2.2656-2.0875 3.575-5.1625 3.575-8.8157 0-.85-.075-1.6656-.2157-2.4531z" fill="#787878"/>
            </svg>
        </a>
    </div>

    <div class="exception-card">
        <div class="flex-1">
            <div class="exception-class">
                <?php if ($throwable instanceof FriendlyExceptionInterface): ?>
                    <span><?= $this->htmlEncode($throwable->getName())?></span>
                    &mdash;
                    <?= get_class($throwable) ?>
                <?php else: ?>
                    <span><?= get_class($throwable) ?></span>
                <?php endif ?>
            </div>

            <div class="exception-message">
                <?= nl2br($this->htmlEncode($throwable->getMessage())) ?>
            </div>

            <?php if ($throwable instanceof FriendlyExceptionInterface && $throwable->getSolution() !== null): ?>
                <div class="solution"><?= $this->htmlEncode($throwable->getSolution()) ?></div>
            <?php endif ?>

            <?= $this->renderPreviousExceptions($throwable) ?>
        </div>
        <div>
            <textarea id="clipboard"><?= $this->htmlEncode($throwable) ?></textarea>
            <span id="copied">Copied!</span>

            <a href="#" id="copy-stacktrace" title="Copy the stacktrace for use in a bug report or pastebin">
                <svg width="26" height="30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.9998.333344H3.33317C1.8665.333344.666504 1.53334.666504 3.00001V20.3333c0 .7334.599996 1.3334 1.333336 1.3334.73333 0 1.33333-.6 1.33333-1.3334V4.33334c0-.73333.6-1.33333 1.33333-1.33333h13.3333c.7334 0 1.3334-.6 1.3334-1.33333 0-.733337-.6-1.333336-1.3334-1.333336zm5.3334 5.333336H8.6665c-1.46666 0-2.66666 1.2-2.66666 2.66666V27c0 1.4667 1.2 2.6667 2.66666 2.6667h14.6667c1.4666 0 2.6666-1.2 2.6666-2.6667V8.33334c0-1.46666-1.2-2.66666-2.6666-2.66666zM21.9998 27H9.99984c-.73333 0-1.33334-.6-1.33334-1.3333V9.66668c0-.73334.60001-1.33334 1.33334-1.33334H21.9998c.7334 0 1.3334.6 1.3334 1.33334V25.6667c0 .7333-.6 1.3333-1.3334 1.3333z" fill="#787878"/>
                </svg>
            </a>
        </div>
    </div>
</header>

<main>
    <div class="call-stack">
        <?= $this->renderCallStack($throwable) ?>
    </div>
    <?php if ($request && ($requestInfo = $this->renderRequest($request)) !== ''): ?>
        <div class="request">
            <?= $requestInfo ?>
        </div>
    <?php endif ?>
    <?php if ($request && ($curlInfo = $this->renderCurl($request)) !== 'curl'): ?>
        <div class="request">
            <?= $curlInfo ?>
        </div>
    <?php endif ?>
    <div class="footer">
        <div class="flex-1">
            <p class="timestamp">
                <?= date('Y-m-d, H:i:s') ?>
            </p>
            <?php if ($request): ?>
                <p class="server">
                    <?= $this->createServerInformationLink($request) ?>
                </p>
            <?php endif ?>
            <p>
                <a href="https://www.yiiframework.com/" target="_blank" rel="noopener noreferrer">Yii Framework</a>
                /
                <a href="https://github.com/yiisoft/docs/blob/master/guide/en/runtime/handling-errors.md" target="_blank" rel="noopener noreferrer">Error Handling Guide</a>
            </p>
        </div>

        <svg width="256" height="224" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g opacity=".54">
                <path d="M221.829.00415039C240.43 9.48202 256.405 34.3532 255.604 68.1036c-2.431 41.5854-32.582 77.4244-65.387 110.4444-1.837-40.269-10.592-65.932-24.264-102.0191-12.908-36.3676 24.83-75.36066 55.876-76.52474961z" fill="url(#paint0_linear)"/>
                <path d="M121.218 177.132c9.987-12.424 17.066-23.488 22.006-33.484 33.841 17.693 25.856 14.389 42.177 33.24.055.587.107 1.172.156 1.755 1.353 26.138-17.714 86.564-92.7936 94.845-5.1971-40.603 11.6986-75.237 28.4546-96.356z" fill="url(#paint1_linear)"/>
                <path d="M.391188 25.2468C53.8858 4.07847 111.459 25.7628 144.698 70.9437c3.215 4.3698 6.264 8.9095 9.14 13.5822 5.194 33.1551 1.234 51.5681-8.876 61.3291-6.647-3.354-13.738-6.328-21.309-9.078-17.602-6.394-33.8454-10.626-57.0447-17.495C11.5203 104.248-2.06497 57.0117.391188 25.2468z" fill="url(#paint2_linear)"/>
                <path d="M151.793 81.2802c.351.5448.698 1.0856 1.041 1.6346l.421.677c.858 1.3788 1.7 2.7658 2.53 4.1693l.14.2353.223.3798.203.3509.594 1.0196.041.0743.805 1.4117.025.0413c.582 1.0279 1.156 2.0599 1.721 3.1001l.071.1321.627 1.1641.017.0289.689 1.2962.198.3798.384.7389.471.9123.454.8834c.462.9162.92 1.8332 1.37 2.7492l.116.227c.813 1.664 1.601 3.331 2.373 5.011l.587 1.284.057.128.376.834.438.987.247.569.396.913.285.656.599 1.412.041.099.64 1.535.078.186c.682 1.66 1.342 3.319 1.982 4.991l.062.153.528 1.399.033.078.306.826c.338.916.673 1.833 1.003 2.753l.128.36c.363 1.027.722 2.059 1.069 3.087l.111.326c.326.966.64 1.932.954 2.898l.153.475.037.111.495 1.565.021.07.458 1.494.458 1.54.013.033.379 1.301.12.421.231.805c.26.928.516 1.853.764 2.782l.186.693.359 1.371.016.066c.438 1.684.851 3.368 1.243 5.048l.037.174.041.177c.512 2.217.983 4.426 1.416 6.626l.041.219c.669 3.422 1.243 6.823 1.718 10.196l.008.045c.149 1.04.285 2.076.413 3.113l.053.445.174 1.482v.004l.148 1.371.05.454.107 1.086.042.437.016.178.132 1.49c-11.818-15.22-25.936-25.709-42.877-33.916 13.672-27.03 11.752-46.2909 9.114-63.4468z" fill="url(#paint3_linear)"/>
            </g>
            <defs>
                <linearGradient id="paint0_linear" x1="209.492" y1=".00415039" x2="209.492" y2="178.548" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#73B723"/>
                    <stop offset="1" stop-color="#D8EE61"/>
                </linearGradient>
                <linearGradient id="paint1_linear" x1="171.417" y1="177.732" x2="82.6039" y2="244.133" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#98C9EA"/>
                    <stop offset=".688649" stop-color="#026FB2"/>
                    <stop offset="1" stop-color="#086EB6"/>
                </linearGradient>
                <linearGradient id="paint2_linear" x1=".119629" y1="25.2237" x2="115.057" y2="128.362" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#D73721"/>
                    <stop offset="1" stop-color="#F7D768"/>
                </linearGradient>
                <linearGradient id="paint3_linear" x1="155.954" y1="111.456" x2="193.631" y2="126.545" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#D4C883"/>
                    <stop offset="1" stop-color="#A1D1A7"/>
                </linearGradient>
            </defs>
        </svg>
    </div>
</main>

<script>
    var hljs=function(){"use strict";function e(t){
            return t instanceof Map?t.clear=t.delete=t.set=()=>{
                throw Error("map is read-only")}:t instanceof Set&&(t.add=t.clear=t.delete=()=>{
                throw Error("set is read-only")
            }),Object.freeze(t),Object.getOwnPropertyNames(t).forEach((n=>{var s=t[n]
            ;"object"!=typeof s||Object.isFrozen(s)||e(s)})),t}var t=e,n=e;t.default=n
        ;class s{constructor(e){void 0===e.data&&(e.data={}),this.data=e.data}
            ignoreMatch(){this.ignore=!0}}function r(e){
            return e.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#x27;")
        }function a(e,...t){const n=Object.create(null);for(const t in e)n[t]=e[t]
        ;return t.forEach((e=>{for(const t in e)n[t]=e[t]})),n}function i(e){
            return e.nodeName.toLowerCase()}var o=Object.freeze({__proto__:null,
            escapeHTML:r,inherit:a,nodeStream:e=>{const t=[];return function e(n,s){
                for(let r=n.firstChild;r;r=r.nextSibling)3===r.nodeType?s+=r.nodeValue.length:1===r.nodeType&&(t.push({
                    event:"start",offset:s,node:r}),s=e(r,s),i(r).match(/br|hr|img|input/)||t.push({
                    event:"stop",offset:s,node:r}));return s}(e,0),t},mergeStreams:(e,t,n)=>{
                let s=0,a="";const o=[];function l(){
                    return e.length&&t.length?e[0].offset!==t[0].offset?e[0].offset<t[0].offset?e:t:"start"===t[0].event?e:t:e.length?e:t
                }function c(e){
                    a+="<"+i(e)+[].map.call(e.attributes,(e=>" "+e.nodeName+'="'+r(e.value)+'"')).join("")+">"
                }function u(e){a+="</"+i(e)+">"}function g(e){("start"===e.event?c:u)(e.node)}
                for(;e.length||t.length;){let t=l()
                ;if(a+=r(n.substring(s,t[0].offset)),s=t[0].offset,t===e){o.reverse().forEach(u)
                ;do{g(t.splice(0,1)[0]),t=l()}while(t===e&&t.length&&t[0].offset===s)
                    ;o.reverse().forEach(c)
                }else"start"===t[0].event?o.push(t[0].node):o.pop(),g(t.splice(0,1)[0])}
                return a+r(n.substr(s))}});const l=e=>!!e.kind;class c{constructor(e,t){
            this.buffer="",this.classPrefix=t.classPrefix,e.walk(this)}addText(e){
            this.buffer+=r(e)}openNode(e){if(!l(e))return;let t=e.kind
        ;e.sublanguage||(t=`${this.classPrefix}${t}`),this.span(t)}closeNode(e){
            l(e)&&(this.buffer+="</span>")}value(){return this.buffer}span(e){
            this.buffer+=`<span class="${e}">`}}class u{constructor(){this.rootNode={
            children:[]},this.stack=[this.rootNode]}get top(){
            return this.stack[this.stack.length-1]}get root(){return this.rootNode}add(e){
            this.top.children.push(e)}openNode(e){const t={kind:e,children:[]}
        ;this.add(t),this.stack.push(t)}closeNode(){
            if(this.stack.length>1)return this.stack.pop()}closeAllNodes(){
            for(;this.closeNode(););}toJSON(){return JSON.stringify(this.rootNode,null,4)}
            walk(e){return this.constructor._walk(e,this.rootNode)}static _walk(e,t){
                return"string"==typeof t?e.addText(t):t.children&&(e.openNode(t),
                    t.children.forEach((t=>this._walk(e,t))),e.closeNode(t)),e}static _collapse(e){
                "string"!=typeof e&&e.children&&(e.children.every((e=>"string"==typeof e))?e.children=[e.children.join("")]:e.children.forEach((e=>{
                    u._collapse(e)})))}}class g extends u{constructor(e){super(),this.options=e}
            addKeyword(e,t){""!==e&&(this.openNode(t),this.addText(e),this.closeNode())}
            addText(e){""!==e&&this.add(e)}addSublanguage(e,t){const n=e.root
            ;n.kind=t,n.sublanguage=!0,this.add(n)}toHTML(){
                return new c(this,this.options).value()}finalize(){return!0}}function d(e){
            return e?"string"==typeof e?e:e.source:null}
            const h="[a-zA-Z]\\w*",f="[a-zA-Z_]\\w*",p="\\b\\d+(\\.\\d+)?",m="(-?)(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)",b="\\b(0b[01]+)",x={
                begin:"\\\\[\\s\\S]",relevance:0},E={className:"string",begin:"'",end:"'",
                illegal:"\\n",contains:[x]},v={className:"string",begin:'"',end:'"',
                illegal:"\\n",contains:[x]},_={
                begin:/\b(a|an|the|are|I'm|isn't|don't|doesn't|won't|but|just|should|pretty|simply|enough|gonna|going|wtf|so|such|will|you|your|they|like|more)\b/
            },w=(e,t,n={})=>{const s=a({className:"comment",begin:e,end:t,contains:[]},n)
            ;return s.contains.push(_),s.contains.push({className:"doctag",
                begin:"(?:TODO|FIXME|NOTE|BUG|OPTIMIZE|HACK|XXX):",relevance:0}),s
            },N=w("//","$"),y=w("/\\*","\\*/"),R=w("#","$");var k=Object.freeze({
                __proto__:null,IDENT_RE:h,UNDERSCORE_IDENT_RE:f,NUMBER_RE:p,C_NUMBER_RE:m,
                BINARY_NUMBER_RE:b,
                RE_STARTERS_RE:"!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|-|-=|/=|/|:|;|<<|<<=|<=|<|===|==|=|>>>=|>>=|>=|>>>|>>|>|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~",
                SHEBANG:(e={})=>{const t=/^#![ ]*\//
                ;return e.binary&&(e.begin=((...e)=>e.map((e=>d(e))).join(""))(t,/.*\b/,e.binary,/\b.*/)),
                    a({className:"meta",begin:t,end:/$/,relevance:0,"on:begin":(e,t)=>{
                            0!==e.index&&t.ignoreMatch()}},e)},BACKSLASH_ESCAPE:x,APOS_STRING_MODE:E,
                QUOTE_STRING_MODE:v,PHRASAL_WORDS_MODE:_,COMMENT:w,C_LINE_COMMENT_MODE:N,
                C_BLOCK_COMMENT_MODE:y,HASH_COMMENT_MODE:R,NUMBER_MODE:{className:"number",
                    begin:p,relevance:0},C_NUMBER_MODE:{className:"number",begin:m,relevance:0},
                BINARY_NUMBER_MODE:{className:"number",begin:b,relevance:0},CSS_NUMBER_MODE:{
                    className:"number",
                    begin:p+"(%|em|ex|ch|rem|vw|vh|vmin|vmax|cm|mm|in|pt|pc|px|deg|grad|rad|turn|s|ms|Hz|kHz|dpi|dpcm|dppx)?",
                    relevance:0},REGEXP_MODE:{begin:/(?=\/[^/\n]*\/)/,contains:[{className:"regexp",
                        begin:/\//,end:/\/[gimuy]*/,illegal:/\n/,contains:[x,{begin:/\[/,end:/\]/,
                            relevance:0,contains:[x]}]}]},TITLE_MODE:{className:"title",begin:h,relevance:0
                },UNDERSCORE_TITLE_MODE:{className:"title",begin:f,relevance:0},METHOD_GUARD:{
                    begin:"\\.\\s*[a-zA-Z_]\\w*",relevance:0},END_SAME_AS_BEGIN:e=>Object.assign(e,{
                    "on:begin":(e,t)=>{t.data._beginMatch=e[1]},"on:end":(e,t)=>{
                        t.data._beginMatch!==e[1]&&t.ignoreMatch()}})})
            ;const M=["of","and","for","in","not","or","if","then","parent","list","value"]
            ;function O(e){function t(t,n){
                return RegExp(d(t),"m"+(e.case_insensitive?"i":"")+(n?"g":""))}class n{
                constructor(){
                    this.matchIndexes={},this.regexes=[],this.matchAt=1,this.position=0}
                addRule(e,t){
                    t.position=this.position++,this.matchIndexes[this.matchAt]=t,this.regexes.push([t,e]),
                        this.matchAt+=(e=>RegExp(e.toString()+"|").exec("").length-1)(e)+1}compile(){
                    0===this.regexes.length&&(this.exec=()=>null)
                    ;const e=this.regexes.map((e=>e[1]));this.matcherRe=t(((e,t="|")=>{
                        const n=/\[(?:[^\\\]]|\\.)*\]|\(\??|\\([1-9][0-9]*)|\\./;let s=0,r=""
                        ;for(let a=0;a<e.length;a++){s+=1;const i=s;let o=d(e[a])
                        ;for(a>0&&(r+=t),r+="(";o.length>0;){const e=n.exec(o);if(null==e){r+=o;break}
                            r+=o.substring(0,e.index),
                                o=o.substring(e.index+e[0].length),"\\"===e[0][0]&&e[1]?r+="\\"+(Number(e[1])+i):(r+=e[0],
                            "("===e[0]&&s++)}r+=")"}return r})(e),!0),this.lastIndex=0}exec(e){
                    this.matcherRe.lastIndex=this.lastIndex;const t=this.matcherRe.exec(e)
                    ;if(!t)return null
                        ;const n=t.findIndex(((e,t)=>t>0&&void 0!==e)),s=this.matchIndexes[n]
                    ;return t.splice(0,n),Object.assign(t,s)}}class s{constructor(){
                this.rules=[],this.multiRegexes=[],
                    this.count=0,this.lastIndex=0,this.regexIndex=0}getMatcher(e){
                if(this.multiRegexes[e])return this.multiRegexes[e];const t=new n
                ;return this.rules.slice(e).forEach((([e,n])=>t.addRule(e,n))),
                    t.compile(),this.multiRegexes[e]=t,t}resumingScanAtSamePosition(){
                return 0!==this.regexIndex}considerAll(){this.regexIndex=0}addRule(e,t){
                this.rules.push([e,t]),"begin"===t.type&&this.count++}exec(e){
                const t=this.getMatcher(this.regexIndex);t.lastIndex=this.lastIndex
                ;let n=t.exec(e)
                ;if(this.resumingScanAtSamePosition())if(n&&n.index===this.lastIndex);else{
                    const t=this.getMatcher(0);t.lastIndex=this.lastIndex+1,n=t.exec(e)}
                return n&&(this.regexIndex+=n.position+1,
                this.regexIndex===this.count&&this.considerAll()),n}}function r(e,t){
                "."===e.input[e.index-1]&&t.ignoreMatch()}
                if(e.contains&&e.contains.includes("self"))throw Error("ERR: contains `self` is not supported at the top-level of a language.  See documentation.")
                    ;return e.classNameAliases=a(e.classNameAliases||{}),function n(i,o){const l=i
                ;if(i.compiled)return l
                    ;i.compiled=!0,i.__beforeBegin=null,i.keywords=i.keywords||i.beginKeywords
                ;let c=null
                ;if("object"==typeof i.keywords&&(c=i.keywords.$pattern,delete i.keywords.$pattern),
                i.keywords&&(i.keywords=((e,t)=>{const n={}
                ;return"string"==typeof e?s("keyword",e):Object.keys(e).forEach((t=>{s(t,e[t])
                })),n;function s(e,s){t&&(s=s.toLowerCase()),s.split(" ").forEach((t=>{
                    const s=t.split("|");n[s[0]]=[e,A(s[0],s[1])]}))}
                })(i.keywords,e.case_insensitive)),
                i.lexemes&&c)throw Error("ERR: Prefer `keywords.$pattern` to `mode.lexemes`, BOTH are not allowed. (see mode reference) ")
                    ;return l.keywordPatternRe=t(i.lexemes||c||/\w+/,!0),
                o&&(i.beginKeywords&&(i.begin="\\b("+i.beginKeywords.split(" ").join("|")+")(?!\\.)(?=\\b|\\s)",
                    i.__beforeBegin=r),
                i.begin||(i.begin=/\B|\b/),l.beginRe=t(i.begin),i.endSameAsBegin&&(i.end=i.begin),
                i.end||i.endsWithParent||(i.end=/\B|\b/),
                i.end&&(l.endRe=t(i.end)),l.terminator_end=d(i.end)||"",
                i.endsWithParent&&o.terminator_end&&(l.terminator_end+=(i.end?"|":"")+o.terminator_end)),
                i.illegal&&(l.illegalRe=t(i.illegal)),
                void 0===i.relevance&&(i.relevance=1),i.contains||(i.contains=[]),
                    i.contains=[].concat(...i.contains.map((e=>(e=>(e.variants&&!e.cached_variants&&(e.cached_variants=e.variants.map((t=>a(e,{
                        variants:null},t)))),e.cached_variants?e.cached_variants:L(e)?a(e,{
                        starts:e.starts?a(e.starts):null
                    }):Object.isFrozen(e)?a(e):e))("self"===e?i:e)))),i.contains.forEach((e=>{n(e,l)
                })),i.starts&&n(i.starts,o),l.matcher=(e=>{const t=new s
                ;return e.contains.forEach((e=>t.addRule(e.begin,{rule:e,type:"begin"
                }))),e.terminator_end&&t.addRule(e.terminator_end,{type:"end"
                }),e.illegal&&t.addRule(e.illegal,{type:"illegal"}),t})(l),l}(e)}function L(e){
                return!!e&&(e.endsWithParent||L(e.starts))}function A(e,t){
                return t?Number(t):(e=>M.includes(e.toLowerCase()))(e)?0:1}function j(e){
                const t={props:["language","code","autodetect"],data:()=>({detectedLanguage:"",
                        unknownLanguage:!1}),computed:{className(){
                            return this.unknownLanguage?"":"hljs "+this.detectedLanguage},highlighted(){
                            if(!this.autoDetect&&!e.getLanguage(this.language))return console.warn(`The language "${this.language}" you specified could not be found.`),
                                this.unknownLanguage=!0,r(this.code);let t
                            ;return this.autoDetect?(t=e.highlightAuto(this.code),
                                this.detectedLanguage=t.language):(t=e.highlight(this.language,this.code,this.ignoreIllegals),
                                this.detectedLanguage=this.language),t.value},autoDetect(){
                            return!(this.language&&(e=this.autodetect,!e&&""!==e));var e},
                        ignoreIllegals:()=>!0},render(e){return e("pre",{},[e("code",{
                        class:this.className,domProps:{innerHTML:this.highlighted}})])}};return{
                    Component:t,VuePlugin:{install(e){e.component("highlightjs",t)}}}}
            const I=r,S=a,{nodeStream:T,mergeStreams:B}=o,P=Symbol("nomatch");return(e=>{
                const n=[],r=Object.create(null),a=Object.create(null),i=[];let o=!0
                ;const l=/(^(<[^>]+>|\t|)+|\n)/gm,c="Could not find the language '{}', did you forget to load/include a language module?",u={
                    disableAutodetect:!0,name:"Plain text",contains:[]};let d={
                    noHighlightRe:/^(no-?highlight)$/i,
                    languageDetectRe:/\blang(?:uage)?-([\w-]+)\b/i,classPrefix:"hljs-",
                    tabReplace:null,useBR:!1,languages:null,__emitter:g};function h(e){
                    return d.noHighlightRe.test(e)}function f(e,t,n,s){const r={code:t,language:e}
                ;N("before:highlight",r);const a=r.result?r.result:p(r.language,r.code,n,s)
                ;return a.code=r.code,N("after:highlight",a),a}function p(e,t,n,a){const i=t
                ;function l(e,t){const n=_.case_insensitive?t[0].toLowerCase():t[0]
                ;return Object.prototype.hasOwnProperty.call(e.keywords,n)&&e.keywords[n]}
                    function u(){null!=y.subLanguage?(()=>{if(""===M)return;let e=null
                    ;if("string"==typeof y.subLanguage){
                        if(!r[y.subLanguage])return void k.addText(M)
                            ;e=p(y.subLanguage,M,!0,R[y.subLanguage]),R[y.subLanguage]=e.top
                    }else e=m(M,y.subLanguage.length?y.subLanguage:null)
                    ;y.relevance>0&&(L+=e.relevance),k.addSublanguage(e.emitter,e.language)
                    })():(()=>{if(!y.keywords)return void k.addText(M);let e=0
                    ;y.keywordPatternRe.lastIndex=0;let t=y.keywordPatternRe.exec(M),n="";for(;t;){
                        n+=M.substring(e,t.index);const s=l(y,t);if(s){const[e,r]=s
                        ;k.addText(n),n="",L+=r;const a=_.classNameAliases[e]||e;k.addKeyword(t[0],a)
                        }else n+=t[0];e=y.keywordPatternRe.lastIndex,t=y.keywordPatternRe.exec(M)}
                        n+=M.substr(e),k.addText(n)})(),M=""}function g(e){
                        return e.className&&k.openNode(_.classNameAliases[e.className]||e.className),
                            y=Object.create(e,{parent:{value:y}}),y}function h(e,t,n){let r=((e,t)=>{
                        const n=e&&e.exec(t);return n&&0===n.index})(e.endRe,n);if(r){if(e["on:end"]){
                        const n=new s(e);e["on:end"](t,n),n.ignore&&(r=!1)}if(r){
                        for(;e.endsParent&&e.parent;)e=e.parent;return e}}
                        if(e.endsWithParent)return h(e.parent,t,n)}function f(e){
                        return 0===y.matcher.regexIndex?(M+=e[0],1):(S=!0,0)}function b(e){
                        const t=e[0],n=i.substr(e.index),s=h(y,e,n);if(!s)return P;const r=y
                        ;r.skip?M+=t:(r.returnEnd||r.excludeEnd||(M+=t),u(),r.excludeEnd&&(M=t));do{
                            y.className&&k.closeNode(),y.skip||y.subLanguage||(L+=y.relevance),y=y.parent
                        }while(y!==s.parent)
                            ;return s.starts&&(s.endSameAsBegin&&(s.starts.endRe=s.endRe),
                            g(s.starts)),r.returnEnd?0:t.length}let x={};function E(t,r){const a=r&&r[0]
                    ;if(M+=t,null==a)return u(),0
                        ;if("begin"===x.type&&"end"===r.type&&x.index===r.index&&""===a){
                        if(M+=i.slice(r.index,r.index+1),!o){const t=Error("0 width match regex")
                        ;throw t.languageName=e,t.badRule=x.rule,t}return 1}
                        if(x=r,"begin"===r.type)return function(e){
                            const t=e[0],n=e.rule,r=new s(n),a=[n.__beforeBegin,n["on:begin"]]
                            ;for(const n of a)if(n&&(n(e,r),r.ignore))return f(t)
                                ;return n&&n.endSameAsBegin&&(n.endRe=RegExp(t.replace(/[-/\\^$*+?.()|[\]{}]/g,"\\$&"),"m")),
                                n.skip?M+=t:(n.excludeBegin&&(M+=t),
                                    u(),n.returnBegin||n.excludeBegin||(M=t)),g(n),n.returnBegin?0:t.length}(r)
                            ;if("illegal"===r.type&&!n){
                            const e=Error('Illegal lexeme "'+a+'" for mode "'+(y.className||"<unnamed>")+'"')
                            ;throw e.mode=y,e}if("end"===r.type){const e=b(r);if(e!==P)return e}
                        if("illegal"===r.type&&""===a)return 1
                            ;if(j>1e5&&j>3*r.index)throw Error("potential infinite loop, way more iterations than matches")
                            ;return M+=a,a.length}const _=v(e);if(!_)throw console.error(c.replace("{}",e)),
                        Error('Unknown language: "'+e+'"');const w=O(_);let N="",y=a||w
                    ;const R={},k=new d.__emitter(d);(()=>{const e=[]
                    ;for(let t=y;t!==_;t=t.parent)t.className&&e.unshift(t.className)
                    ;e.forEach((e=>k.openNode(e)))})();let M="",L=0,A=0,j=0,S=!1;try{
                        for(y.matcher.considerAll();;){
                            j++,S?S=!1:y.matcher.considerAll(),y.matcher.lastIndex=A
                            ;const e=y.matcher.exec(i);if(!e)break;const t=E(i.substring(A,e.index),e)
                            ;A=e.index+t}return E(i.substr(A)),k.closeAllNodes(),k.finalize(),N=k.toHTML(),{
                            relevance:L,value:N,language:e,illegal:!1,emitter:k,top:y}}catch(t){
                        if(t.message&&t.message.includes("Illegal"))return{illegal:!0,illegalBy:{
                                msg:t.message,context:i.slice(A-100,A+100),mode:t.mode},sofar:N,relevance:0,
                            value:I(i),emitter:k};if(o)return{illegal:!1,relevance:0,value:I(i),emitter:k,
                            language:e,top:y,errorRaised:t};throw t}}function m(e,t){
                    t=t||d.languages||Object.keys(r);const n=(e=>{const t={relevance:0,
                        emitter:new d.__emitter(d),value:I(e),illegal:!1,top:u}
                    ;return t.emitter.addText(e),t})(e),s=t.filter(v).filter(w).map((t=>p(t,e,!1)))
                    ;s.unshift(n);const a=s.sort(((e,t)=>{
                        if(e.relevance!==t.relevance)return t.relevance-e.relevance
                            ;if(e.language&&t.language){if(v(e.language).supersetOf===t.language)return 1
                            ;if(v(t.language).supersetOf===e.language)return-1}return 0})),[i,o]=a,l=i
                    ;return l.second_best=o,l}function b(e){
                    return d.tabReplace||d.useBR?e.replace(l,(e=>"\n"===e?d.useBR?"<br>":e:d.tabReplace?e.replace(/\t/g,d.tabReplace):e)):e
                }function x(e){let t=null;const n=(e=>{let t=e.className+" "
                ;t+=e.parentNode?e.parentNode.className:"";const n=d.languageDetectRe.exec(t)
                ;if(n){const t=v(n[1])
                ;return t||(console.warn(c.replace("{}",n[1])),console.warn("Falling back to no-highlight mode for this block.",e)),
                    t?n[1]:"no-highlight"}return t.split(/\s+/).find((e=>h(e)||v(e)))})(e)
                ;if(h(n))return;N("before:highlightBlock",{block:e,language:n
                }),d.useBR?(t=document.createElement("div"),
                    t.innerHTML=e.innerHTML.replace(/\n/g,"").replace(/<br[ /]*>/g,"\n")):t=e
                ;const s=t.textContent,r=n?f(n,s,!0):m(s),i=T(t);if(i.length){
                    const e=document.createElement("div");e.innerHTML=r.value,r.value=B(i,T(e),s)}
                    r.value=b(r.value),N("after:highlightBlock",{block:e,result:r
                    }),e.innerHTML=r.value,e.className=((e,t,n)=>{const s=t?a[t]:n,r=[e.trim()]
                    ;return e.match(/\bhljs\b/)||r.push("hljs"),
                    e.includes(s)||r.push(s),r.join(" ").trim()
                    })(e.className,n,r.language),e.result={language:r.language,re:r.relevance,
                        relavance:r.relevance},r.second_best&&(e.second_best={
                        language:r.second_best.language,re:r.second_best.relevance,
                        relavance:r.second_best.relevance})}const E=()=>{if(E.called)return;E.called=!0
                ;const e=document.querySelectorAll("pre code");n.forEach.call(e,x)}
                ;function v(e){return e=(e||"").toLowerCase(),r[e]||r[a[e]]}
                function _(e,{languageName:t}){"string"==typeof e&&(e=[e]),e.forEach((e=>{a[e]=t
                }))}function w(e){const t=v(e);return t&&!t.disableAutodetect}function N(e,t){
                    const n=e;i.forEach((e=>{e[n]&&e[n](t)}))}Object.assign(e,{highlight:f,
                    highlightAuto:m,
                    fixMarkup:e=>(console.warn("fixMarkup is deprecated and will be removed entirely in v11.0"),
                        console.warn("Please see https://github.com/highlightjs/highlight.js/issues/2534"),
                        b(e)),highlightBlock:x,configure:e=>{
                        e.useBR&&(console.warn("'useBR' option is deprecated and will be removed entirely in v11.0"),
                            console.warn("Please see https://github.com/highlightjs/highlight.js/issues/2559")),
                            d=S(d,e)},initHighlighting:E,initHighlightingOnLoad:()=>{
                        window.addEventListener("DOMContentLoaded",E,!1)},registerLanguage:(t,n)=>{
                        let s=null;try{s=n(e)}catch(e){
                            if(console.error("Language definition for '{}' could not be registered.".replace("{}",t)),
                                !o)throw e;console.error(e),s=u}
                        s.name||(s.name=t),r[t]=s,s.rawDefinition=n.bind(null,e),
                        s.aliases&&_(s.aliases,{languageName:t})},listLanguages:()=>Object.keys(r),
                    getLanguage:v,registerAliases:_,requireLanguage:e=>{
                        console.warn("requireLanguage is deprecated and will be removed entirely in the future."),
                            console.warn("Please see https://github.com/highlightjs/highlight.js/pull/2844")
                        ;const t=v(e);if(t)return t
                            ;throw Error("The '{}' language is required, but not loaded.".replace("{}",e))},
                    autoDetection:w,inherit:S,addPlugin:e=>{i.push(e)},vuePlugin:j(e).VuePlugin
                }),e.debugMode=()=>{o=!1},e.safeMode=()=>{o=!0},e.versionString="10.4.1"
                ;for(const e in k)"object"==typeof k[e]&&t(k[e]);return Object.assign(e,k),e
            })({})}()
    ;"object"==typeof exports&&"undefined"!=typeof module&&(module.exports=hljs);hljs.registerLanguage("php",(()=>{"use strict";return e=>{const r={
        className:"variable",
        begin:"\\$+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?![A-Za-z0-9])(?![$])"},t={
        className:"meta",variants:[{begin:/<\?php/,relevance:10},{begin:/<\?[=]?/},{
            begin:/\?>/}]},a={className:"subst",variants:[{begin:/\$\w+/},{begin:/\{\$/,
            end:/\}/}]},n=e.inherit(e.APOS_STRING_MODE,{illegal:null
    }),i=e.inherit(e.QUOTE_STRING_MODE,{illegal:null,
        contains:e.QUOTE_STRING_MODE.contains.concat(a)}),o=e.END_SAME_AS_BEGIN({
        begin:/<<<[ \t]*(\w+)\n/,end:/[ \t]*(\w+)\b/,
        contains:e.QUOTE_STRING_MODE.contains.concat(a)}),l={className:"string",
        contains:[e.BACKSLASH_ESCAPE,t],variants:[e.inherit(n,{begin:"b'",end:"'"
        }),e.inherit(i,{begin:'b"',end:'"'}),i,n,o]},c={
        variants:[e.BINARY_NUMBER_MODE,e.C_NUMBER_MODE]},s={
        keyword:"__CLASS__ __DIR__ __FILE__ __FUNCTION__ __LINE__ __METHOD__ __NAMESPACE__ __TRAIT__ die echo exit include include_once print require require_once array abstract and as binary bool boolean break callable case catch class clone const continue declare default do double else elseif empty enddeclare endfor endforeach endif endswitch endwhile eval extends final finally float for foreach from global goto if implements instanceof insteadof int integer interface isset iterable list match|0 new object or private protected public real return string switch throw trait try unset use var void while xor yield",
        literal:"false null true",
        built_in:"Error|0 AppendIterator ArgumentCountError ArithmeticError ArrayIterator ArrayObject AssertionError BadFunctionCallException BadMethodCallException CachingIterator CallbackFilterIterator CompileError Countable DirectoryIterator DivisionByZeroError DomainException EmptyIterator ErrorException Exception FilesystemIterator FilterIterator GlobIterator InfiniteIterator InvalidArgumentException IteratorIterator LengthException LimitIterator LogicException MultipleIterator NoRewindIterator OutOfBoundsException OutOfRangeException OuterIterator OverflowException ParentIterator ParseError RangeException RecursiveArrayIterator RecursiveCachingIterator RecursiveCallbackFilterIterator RecursiveDirectoryIterator RecursiveFilterIterator RecursiveIterator RecursiveIteratorIterator RecursiveRegexIterator RecursiveTreeIterator RegexIterator RuntimeException SeekableIterator SplDoublyLinkedList SplFileInfo SplFileObject SplFixedArray SplHeap SplMaxHeap SplMinHeap SplObjectStorage SplObserver SplObserver SplPriorityQueue SplQueue SplStack SplSubject SplSubject SplTempFileObject TypeError UnderflowException UnexpectedValueException ArrayAccess Closure Generator Iterator IteratorAggregate Serializable Throwable Traversable WeakReference Directory __PHP_Incomplete_Class parent php_user_filter self static stdClass"
    };return{aliases:["php","php3","php4","php5","php6","php7","php8"],
        case_insensitive:!0,keywords:s,
        contains:[e.HASH_COMMENT_MODE,e.COMMENT("//","$",{contains:[t]
        }),e.COMMENT("/\\*","\\*/",{contains:[{className:"doctag",begin:"@[A-Za-z]+"}]
        }),e.COMMENT("__halt_compiler.+?;",!1,{endsWithParent:!0,
            keywords:"__halt_compiler"}),t,{className:"keyword",begin:/\$this\b/},r,{
            begin:/(::|->)+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/},{className:"function",
            relevance:0,beginKeywords:"fn function",end:/[;{]/,excludeEnd:!0,
            illegal:"[$%\\[]",contains:[e.UNDERSCORE_TITLE_MODE,{begin:"=>"},{
                className:"params",begin:"\\(",end:"\\)",excludeBegin:!0,excludeEnd:!0,
                keywords:s,contains:["self",r,e.C_BLOCK_COMMENT_MODE,l,c]}]},{className:"class",
            beginKeywords:"class interface",relevance:0,end:/\{/,excludeEnd:!0,
            illegal:/[:($"]/,contains:[{beginKeywords:"extends implements"
            },e.UNDERSCORE_TITLE_MODE]},{beginKeywords:"namespace",relevance:0,end:";",
            illegal:/[.']/,contains:[e.UNDERSCORE_TITLE_MODE]},{beginKeywords:"use",
            relevance:0,end:";",contains:[e.UNDERSCORE_TITLE_MODE]},l,c]}}})());hljs.registerLanguage("xml",(()=>{"use strict";function e(e){
        return e?"string"==typeof e?e:e.source:null}function n(e){return a("(?=",e,")")}
        function a(...n){return n.map((n=>e(n))).join("")}function s(...n){
            return"("+n.map((n=>e(n))).join("|")+")"}return e=>{
            const t=a(/[A-Z_]/,a("(",/[A-Z0-9_.-]+:/,")?"),/[A-Z0-9_.-]*/),i={
                className:"symbol",begin:"&[a-z]+;|&#[0-9]+;|&#x[a-f0-9]+;"},c={begin:"\\s",
                contains:[{className:"meta-keyword",begin:"#?[a-z_][a-z1-9_-]+",illegal:"\\n"}]
            },r=e.inherit(c,{begin:"\\(",end:"\\)"}),l=e.inherit(e.APOS_STRING_MODE,{
                className:"meta-string"}),g=e.inherit(e.QUOTE_STRING_MODE,{
                className:"meta-string"}),m={endsWithParent:!0,illegal:/</,relevance:0,
                contains:[{className:"attr",begin:"[A-Za-z0-9\\._:-]+",relevance:0},{
                    begin:/=\s*/,relevance:0,contains:[{className:"string",endsParent:!0,variants:[{
                            begin:/"/,end:/"/,contains:[i]},{begin:/'/,end:/'/,contains:[i]},{
                            begin:/[^\s"'=<>`]+/}]}]}]};return{name:"HTML, XML",
                aliases:["html","xhtml","rss","atom","xjb","xsd","xsl","plist","wsf","svg"],
                case_insensitive:!0,contains:[{className:"meta",begin:"<![a-z]",end:">",
                    relevance:10,contains:[c,g,l,r,{begin:"\\[",end:"\\]",contains:[{
                            className:"meta",begin:"<![a-z]",end:">",contains:[c,r,g,l]}]}]
                },e.COMMENT("\x3c!--","--\x3e",{relevance:10}),{begin:"<!\\[CDATA\\[",
                    end:"\\]\\]>",relevance:10},i,{className:"meta",begin:/<\?xml/,end:/\?>/,
                    relevance:10},{className:"tag",begin:"<style(?=\\s|>)",end:">",keywords:{
                        name:"style"},contains:[m],starts:{end:"</style>",returnEnd:!0,
                        subLanguage:["css","xml"]}},{className:"tag",begin:"<script(?=\\s|>)",end:">",
                    keywords:{name:"script"},contains:[m],starts:{end:/<\/script>/,returnEnd:!0,
                        subLanguage:["javascript","handlebars","xml"]}},{className:"tag",begin:/<>|<\/>/
                },{className:"tag",begin:a(/</,n(a(t,s(/\/>/,/>/,/\s/)))),end:/\/?>/,contains:[{
                        className:"name",begin:t,relevance:0,starts:m}]},{className:"tag",
                    begin:a(/<\//,n(a(t,/>/))),contains:[{className:"name",begin:t,relevance:0},{
                        begin:/>/,relevance:0}]}]}}})());hljs.registerLanguage("php-template",(()=>{"use strict";return n=>({
        name:"PHP template",subLanguage:"xml",contains:[{begin:/<\?(php|=)?/,end:/\?>/,
            subLanguage:"php",contains:[{begin:"/\\*",end:"\\*/",skip:!0},{begin:'b"',
                end:'"',skip:!0},{begin:"b'",end:"'",skip:!0},n.inherit(n.APOS_STRING_MODE,{
                illegal:null,className:null,contains:null,skip:!0
            }),n.inherit(n.QUOTE_STRING_MODE,{illegal:null,className:null,contains:null,
                skip:!0})]}]})})());hljs.registerLanguage("plaintext",(()=>{"use strict";return t=>({
        name:"Plain text",aliases:["text","txt"],disableAutodetect:!0})})());
</script>
<script>
    window.onload = function() {
        var codeBlocks = document.getElementsByTagName('pre'),
            callStackItems = document.getElementsByClassName('call-stack-item');

        // If there are grouped vendor package files
        var vendorCollapse = document.getElementsByClassName('call-stack-vendor-collapse');
        for (var i = 0, imax = vendorCollapse.length; i < imax; ++i) {
            vendorCollapse[i].addEventListener('click', function (event) {
                var vendorCollapseState = this.getElementsByClassName('call-stack-vendor-state')[0];
                var vendorCollapseItems = this.parentElement.getElementsByClassName('call-stack-vendor-items')[0];

                if (vendorCollapseItems.style.display === 'block') {
                    vendorCollapseItems.style.display = 'none';
                    vendorCollapseState.innerText = '+';
                } else {
                    vendorCollapseItems.style.display = 'block';
                    vendorCollapseState.innerText = '–';
                }
            });
        }

        // highlight code blocks
        for (var i = 0, imax = codeBlocks.length; i < imax; ++i) {
            hljs.highlightBlock(codeBlocks[i]);
        }

        var refreshCallStackItemCode = function(callStackItem) {
            if (!callStackItem.getElementsByTagName('pre')[0]) {
                return;
            }
            var top = callStackItem.getElementsByClassName('code-wrap')[0].offsetTop - window.pageYOffset + 3,
                lines = callStackItem.getElementsByTagName('pre')[0].getClientRects(),
                lineNumbers = callStackItem.getElementsByClassName('lines-item'),
                errorLine = callStackItem.getElementsByClassName('error-line')[0],
                hoverLines = callStackItem.getElementsByClassName('hover-line');

            for (var i = 0, imax = lines.length; i < imax; ++i) {
                if (!lineNumbers[i]) {
                    continue;
                }

                lineNumbers[i].style.top = parseInt(lines[i].top - top) + 'px';
                hoverLines[i].style.top = parseInt(lines[i].top - top) + 'px';
                hoverLines[i].style.height = parseInt(lines[i].bottom - lines[i].top + 6) + 'px';
                hoverLines[i].style.width = hoverLines[i].parentElement.parentElement.scrollWidth + 'px'

                if (parseInt(callStackItem.getAttribute('data-line')) == i) {
                    errorLine.style.top = parseInt(lines[i].top - top) + 'px';
                    errorLine.style.height = parseInt(lines[i].bottom - lines[i].top + 6) + 'px';
                    errorLine.style.width = errorLine.parentElement.parentElement.scrollWidth + 'px';
                }
            }
        };

        for (var i = 0, imax = callStackItems.length; i < imax; ++i) {
            refreshCallStackItemCode(callStackItems[i]);

            // toggle code block visibility
            callStackItems[i].getElementsByClassName('element-wrap')[0].addEventListener('click', function (event) {
                if (event.target.nodeName.toLowerCase() === 'a') {
                    return;
                }

                var callStackItem = this.parentNode,
                    code = callStackItem.getElementsByClassName('code-wrap')[0];

                if (typeof code !== 'undefined') {
                    code.style.display = window.getComputedStyle(code).display === 'block' ? 'none' : 'block';
                    if (code.style.display === 'block') {
                        this.style.borderBottom = document.body.classList.contains('dark-theme')
                            ? '1px solid #141414'
                            : '1px solid #d0d0d0'
                        ;
                    } else {
                        this.style.borderBottom = 'none';
                    }
                    refreshCallStackItemCode(callStackItem);
                }
            });
        }

        // handle copy stacktrace action on clipboard button
        document.getElementById('copy-stacktrace').onclick = function(e) {
            e.preventDefault();
            var textarea = document.getElementById('clipboard');
            textarea.focus();
            textarea.select();

            var succeeded;
            try {
                succeeded = document.execCommand('copy');
            } catch (err) {
                succeeded = false;
            }
            if (succeeded) {
                var hint = document.getElementById('copied');
                hint.style.display = 'block';
                setTimeout(function () {
                    hint.style.display = 'none';
                }, 2000);
            } else {
                // fallback: show textarea if browser does not support copying directly
                textarea.style.top = 0;
            }
        }

        // handle theme change
        document.getElementById('dark-mode').onclick = function(e) {
            e.preventDefault();

            document.body.classList.add('dark-theme');

            setCookie('yii-exception-theme', 'dark-theme');
        }

        document.getElementById('light-mode').onclick = function(e) {
            e.preventDefault();

            document.body.classList.remove('dark-theme');

            eraseCookie('yii-exception-theme');
        }
    };

    // Highlight lines that have text in them but still support text selection:
    document.onmousedown = function() { document.getElementsByTagName('body')[0].classList.add('mousedown'); }
    document.onmouseup = function() { document.getElementsByTagName('body')[0].classList.remove('mousedown'); }

    <?php if (empty($theme)): ?>
    var theme = getCookie('yii-exception-theme');

    if (theme) {
        document.body.classList.add(theme);
    }
    <?php endif; ?>

    function setCookie(name, value) {
        var date = new Date(2100, 0, 1);
        var expires = "; expires=" + date.toUTCString();

        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');

        for (var i=0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }

        return null;
    }

    function eraseCookie(name) {
        document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
</script>
</body>

</html>
