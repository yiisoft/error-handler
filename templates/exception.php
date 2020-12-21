<?php
/* @var $throwable \Throwable */
/* @var $this \Yiisoft\ErrorHandler\HtmlRenderer */

use Yiisoft\FriendlyException\FriendlyExceptionInterface;

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>
        <?= $this->htmlEncode($this->getThrowableName($throwable)) ?>
    </title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700">
    <style type="text/css">
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

        body {
            font-family: 'Roboto', sans-serif;
            color: #505050;
        }
        /* end base */

        /* header */
        header {
            padding: 65px 100px 270px 100px;
            background: #EDEDED;
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
            background: #FAFAFA;
            border: 2px solid #D83C24;
            box-sizing: border-box;
            border-radius: 3px;
            padding: 40px 30px;
        }

        header .exception-class {
            margin-bottom: 30px;
            font-weight: 500;
            font-size: 36px;
            line-height: 42px;
            color: #D73721;
        }

        header .exception-class a {
            color: #e57373;
        }

        header .exception-message {
            font-size: 24px;
            line-height: 28px;
            color: #4B4B4B;
        }

        header .exception-class span,
        header .exception-class span a {
            color: #e51717;
        }

        header .solution {
            margin-top: 20px;
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

        header .previous p {
            font-size: 14px;
            color: #aaa;
        }

        #clipboard {
            position: absolute;
            top: -500px;
            right: 300px;
            width: 750px;
            height: 150px;
        }

        #copied {
            display: none;
            float: left;
            height: 25px;
            padding: 5px;
            margin-right: 5px;
        }
        /* end header */

        main {
            margin-left: 100px;
            margin-right: 100px;
        }

        .flex-1 {
            flex: 1;
        }

        /* call stack */
        .call-stack {
            margin-top: 30px;
            margin-bottom: 40px;
        }

        .call-stack ul li,
        .request {
            border: 2px solid #D0D0D0;
            box-shadow: 0px 15px 20px rgba(0, 0, 0, 0.05);
            background: #fff;
            margin-bottom: 20px;
        }

        .call-stack ul li .element-wrap {
            display: flex;
            cursor: pointer;
            padding: 20px 30px;
            font-weight: 500;
            font-size: 18px;
            line-height: 21px;
            color: #4B4B4B;
        }

        .call-stack ul li .element-wrap .file-name {
            color: #086EB6;
        }

        .call-stack ul li:first-child .element-wrap .file-name {
            color: #4B4B4B;
        }

        .call-stack ul li.application .element-wrap {
            border-bottom: 1px solid #D0D0D0;
        }

        .call-stack ul li .element-wrap:hover {
            background-color: #edf9ff;
        }

        .call-stack ul li a {
            color: #505050;
        }

        .call-stack ul li a:hover {
            color: #000;
        }

        .call-stack ul li .code-wrap {
            display: none;
            position: relative;
        }

        .call-stack ul li.application .code-wrap {
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
            min-width: 860px;
            /* 960px - 50px * 2 */
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

        .footer .timestamp {
            margin-bottom: 20px;
        }

        .footer p,
        .footer p a {
            font-size: 24px;
            line-height: 28px;
            color: #9C9C9C;
        }

        .footer p a:hover {
            color: #000;
        }

        .footer svg {
            margin-right: -50px;
        }
        /* end footer */

        /* highlight.js */
        .comment {
            color: #808080;
            font-style: italic;
        }

        .keyword {
            color: #000080;
        }

        .number {
            color: #00a;
        }

        .number {
            font-weight: normal;
        }

        .string,
        .value {
            color: #0a0;
        }

        .symbol,
        .char {
            color: #505050;
            background: #d0eded;
            font-style: italic;
        }

        .phpdoc {
            text-decoration: underline;
        }

        .variable {
            color: #a00;
        }

        body pre {
            pointer-events: none;
        }

        body.mousedown pre {
            pointer-events: auto;
        }
        /* end highlight.js */
    </style>
</head>

<body>
    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" display="none">
        <symbol id="new-window" viewBox="0 0 24 24">
            <g transform="scale(0.0234375 0.0234375)">
                <path d="M598 128h298v298h-86v-152l-418 418-60-60 418-418h-152v-86zM810 810v-298h86v298c0 46-40 86-86 86h-596c-48 0-86-40-86-86v-596c0-46 38-86 86-86h298v86h-298v596h596z"></path>
            </g>
        </symbol>
    </svg>

    <header>
        <div class="tools">
            <a href="#" title="Dark Mode">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M27.0597 19.06C24.5285 20.0421 21.7663 20.2669 19.1096 19.7072C16.4528 19.1474 14.0162 17.8273 12.0963 15.9074C10.1765 13.9876 8.85629 11.5509 8.29655 8.89416C7.73681 6.23742 7.96166 3.47524 8.94373 0.944C5.90328 2.1289 3.37198 4.33824 1.78688 7.19057C0.201778 10.0429 -0.337628 13.3592 0.26179 16.5668C0.861209 19.7745 2.56183 22.6721 5.07003 24.7596C7.57822 26.847 10.7366 27.9931 13.9997 28C16.8243 28.0007 19.583 27.1473 21.9138 25.5519C24.2445 23.9564 26.0383 21.6934 27.0597 19.06Z" fill="#787878"/>
                </svg>
            </a>

            <a href="https://stackoverflow.com/search?<?= http_build_query(['q' => $throwable->getMessage()]) ?>" title="Search error on Stackoverflow" target="_blank">
                <svg width="28" height="32" viewBox="0 0 28 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23.312 29.151V20.615H26.161V32H0.458008V20.615H3.29701V29.151H23.312ZM6.14501 26.307H20.469V23.459H6.14501V26.307ZM6.49501 19.839L20.47 22.755L21.069 19.995L7.10001 17.083L6.49501 19.839ZM8.30701 13.099L21.246 19.136L22.449 16.536L9.51201 10.495L8.30801 13.079L8.30701 13.099ZM11.927 6.719L22.88 15.86L24.693 13.697L13.74 4.562L11.937 6.713L11.927 6.719ZM19 0L16.672 1.724L25.213 13.197L27.541 11.473L19 0Z" fill="#787878"/>
                </svg>
            </a>
            <a href="https://www.google.com/search?<?= http_build_query(['q' => $throwable->getMessage()]) ?>" title="Search error on Google" target="_blank">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23.5313 9.825H12.2407V14.4656H18.6907C18.4126 15.9656 17.5688 17.2344 16.297 18.0844C15.222 18.8031 13.8501 19.2281 12.2376 19.2281C9.11572 19.2281 6.4751 17.1187 5.53135 14.2844C5.29385 13.5656 5.15635 12.7969 5.15635 12.0063C5.15635 11.2156 5.29385 10.4469 5.53135 9.72812C6.47822 6.89687 9.11885 4.7875 12.2407 4.7875C14.0001 4.7875 15.5782 5.39375 16.822 6.58125L20.2595 3.14062C18.1813 1.20312 15.472 0.015625 12.2407 0.015625C7.55635 0.015625 3.50322 2.70312 1.53135 6.62187C0.718848 8.24062 0.256348 10.0719 0.256348 12.0094C0.256348 13.9469 0.718848 15.775 1.53135 17.3937C3.50322 21.3125 7.55635 24 12.2407 24C15.4782 24 18.1907 22.925 20.172 21.0938C22.4376 19.0063 23.747 15.9313 23.747 12.2781C23.747 11.4281 23.672 10.6125 23.5313 9.825Z" fill="#787878"/>
                </svg>
            </a>
        </div>

        <div class="exception-card">
            <div class="flex-1">
                <div class="exception-class">
                    <?php if ($throwable instanceof FriendlyExceptionInterface): ?>
                        <span><?= $this->htmlEncode($throwable->getName())?></span>
                        <span> &mdash; </span>
                        <?= $this->addTypeLinks(get_class($throwable)) ?>
                    <?php else: ?>
                        <span><?= $this->addTypeLinks(get_class($throwable)) ?></span>
                    <?php endif ?>
                </div>

                <div class="exception-message">
                    <?= nl2br($this->htmlEncode($throwable->getMessage())) ?>
                </div>

                <?php if ($throwable instanceof FriendlyExceptionInterface && $throwable->getSolution() !== null): ?>
                    <div class="solution">
                        <?= nl2br($this->htmlEncode($throwable->getSolution())) ?>
                    </div>
                <?php endif ?>

                <?= $this->renderPreviousExceptions($throwable) ?>
            </div>
            <div>
                <textarea id="clipboard"><?= $this->htmlEncode($throwable) ?></textarea>
                <span id="copied">Copied!</span>

                <a href="#" id="copy-stacktrace" title="Copy the stacktrace for use in a bug report or pastebin">
                    <svg width="26" height="30" viewBox="0 0 26 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.9998 0.333344H3.33317C1.8665 0.333344 0.666504 1.53334 0.666504 3.00001V20.3333C0.666504 21.0667 1.2665 21.6667 1.99984 21.6667C2.73317 21.6667 3.33317 21.0667 3.33317 20.3333V4.33334C3.33317 3.60001 3.93317 3.00001 4.6665 3.00001H17.9998C18.7332 3.00001 19.3332 2.40001 19.3332 1.66668C19.3332 0.933343 18.7332 0.333344 17.9998 0.333344ZM23.3332 5.66668H8.6665C7.19984 5.66668 5.99984 6.86668 5.99984 8.33334V27C5.99984 28.4667 7.19984 29.6667 8.6665 29.6667H23.3332C24.7998 29.6667 25.9998 28.4667 25.9998 27V8.33334C25.9998 6.86668 24.7998 5.66668 23.3332 5.66668ZM21.9998 27H9.99984C9.26651 27 8.6665 26.4 8.6665 25.6667V9.66668C8.6665 8.93334 9.26651 8.33334 9.99984 8.33334H21.9998C22.7332 8.33334 23.3332 8.93334 23.3332 9.66668V25.6667C23.3332 26.4 22.7332 27 21.9998 27Z" fill="#787878"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="call-stack">
            <?= $this->renderCallStack($throwable) ?>
        </div>
        <div class="request">
            <?= $this->renderRequest() ?>
        </div>
        <div class="request">
            <?= $this->renderCurl() ?>
        </div>
        <div class="footer">
            <div class="flex-1">
                <p class="timestamp">
                    <?= date('Y-m-d, H:i:s') ?>
                </p>
                <p>
                    <?= $this->createServerInformationLink() ?>
                </p>
                <p><a href="https://www.yiiframework.com/">Yii Framework</a>/
                    <?= $this->createFrameworkVersionLink() ?>
                </p>
            </div>

            <svg width="256" height="224" viewBox="0 0 256 224" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g opacity="0.54">
                    <path d="M221.829 0.00415039C240.43 9.48202 256.405 34.3532 255.604 68.1036C253.173 109.689 223.022 145.528 190.217 178.548C188.38 138.279 179.625 112.616 165.953 76.5289C153.045 40.1613 190.783 1.16824 221.829 0.00415039Z" fill="url(#paint0_linear)"/>
                    <path d="M121.218 177.132C131.205 164.708 138.284 153.644 143.224 143.648C177.065 161.341 169.08 158.037 185.401 176.888C185.456 177.475 185.508 178.06 185.557 178.643C186.91 204.781 167.843 265.207 92.7634 273.488C87.5663 232.885 104.462 198.251 121.218 177.132Z" fill="url(#paint1_linear)"/>
                    <path d="M0.391188 25.2468C53.8858 4.07847 111.459 25.7628 144.698 70.9437C147.913 75.3135 150.962 79.8532 153.838 84.5259C159.032 117.681 155.072 136.094 144.962 145.855C138.315 142.501 131.224 139.527 123.653 136.777C106.051 130.383 89.8076 126.151 66.6083 119.282C11.5203 104.248 -2.06497 57.0117 0.391188 25.2468Z" fill="url(#paint2_linear)"/>
                    <path d="M151.793 81.2802C152.144 81.825 152.491 82.3658 152.834 82.9148L153.255 83.5918C154.113 84.9706 154.955 86.3576 155.785 87.7611L155.925 87.9964L156.148 88.3762L156.351 88.7271L156.945 89.7467L156.986 89.821L157.791 91.2327L157.816 91.274C158.398 92.3019 158.972 93.3339 159.537 94.3741L159.608 94.5062L160.235 95.6703L160.252 95.6992L160.941 96.9954L161.139 97.3752L161.523 98.1141L161.994 99.0264L162.448 99.9098C162.91 100.826 163.368 101.743 163.818 102.659L163.934 102.886C164.747 104.55 165.535 106.217 166.307 107.897L166.894 109.181L166.951 109.309L167.327 110.143L167.765 111.13L168.012 111.699L168.408 112.612L168.693 113.268L169.292 114.68L169.333 114.779L169.973 116.314L170.051 116.5C170.733 118.16 171.393 119.819 172.033 121.491L172.095 121.644L172.623 123.043L172.656 123.121L172.962 123.947C173.3 124.863 173.635 125.78 173.965 126.7L174.093 127.06C174.456 128.087 174.815 129.119 175.162 130.147L175.273 130.473C175.599 131.439 175.913 132.405 176.227 133.371L176.38 133.846L176.417 133.957L176.912 135.522L176.933 135.592L177.391 137.086L177.849 138.626L177.862 138.659L178.241 139.96L178.361 140.381L178.592 141.186C178.852 142.114 179.108 143.039 179.356 143.968L179.542 144.661L179.901 146.032L179.917 146.098C180.355 147.782 180.768 149.466 181.16 151.146L181.197 151.32L181.238 151.497C181.75 153.714 182.221 155.923 182.654 158.123L182.695 158.342C183.364 161.764 183.938 165.165 184.413 168.538L184.421 168.583C184.57 169.623 184.706 170.659 184.834 171.696L184.887 172.141L185.061 173.623V173.627L185.209 174.998L185.259 175.452L185.366 176.538L185.408 176.975L185.424 177.153L185.556 178.643C173.738 163.423 159.62 152.934 142.679 144.727C156.351 117.697 154.431 98.4361 151.793 81.2802Z" fill="url(#paint3_linear)"/>
                </g>
                <defs>
                    <linearGradient id="paint0_linear" x1="209.492" y1="0.00415039" x2="209.492" y2="178.548" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#73B723"/>
                        <stop offset="1" stop-color="#D8EE61"/>
                    </linearGradient>
                    <linearGradient id="paint1_linear" x1="171.417" y1="177.732" x2="82.6039" y2="244.133" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#98C9EA"/>
                        <stop offset="0.688649" stop-color="#026FB2"/>
                        <stop offset="1" stop-color="#086EB6"/>
                    </linearGradient>
                    <linearGradient id="paint2_linear" x1="0.119629" y1="25.2237" x2="115.057" y2="128.362" gradientUnits="userSpaceOnUse">
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
    var hljs = new function() {
        function l(o) { return o.replace(/&/gm, "&amp;").replace(/</gm, "&lt;").replace(/>/gm, "&gt;") }

        function b(p) { for (var o = p.firstChild; o; o = o.nextSibling) { if (o.nodeName == "CODE") { return o } if (!(o.nodeType == 3 && o.nodeValue.match(/\s+/))) { break } } }

        function h(p, o) { return Array.prototype.map.call(p.childNodes, function(q) { if (q.nodeType == 3) { return o ? q.nodeValue.replace(/\n/g, "") : q.nodeValue } if (q.nodeName == "BR") { return "\n" } return h(q, o) }).join("") }

        function a(q) { var p = (q.className + " " + q.parentNode.className).split(/\s+/);
            p = p.map(function(r) { return r.replace(/^language-/, "") }); for (var o = 0; o < p.length; o++) { if (e[p[o]] || p[o] == "no-highlight") { return p[o] } } }

        function c(q) { var o = [];
            (function p(r, s) { for (var t = r.firstChild; t; t = t.nextSibling) { if (t.nodeType == 3) { s += t.nodeValue.length } else { if (t.nodeName == "BR") { s += 1 } else { if (t.nodeType == 1) { o.push({ event: "start", offset: s, node: t });
                                s = p(t, s);
                                o.push({ event: "stop", offset: s, node: t }) } } } } return s })(q, 0); return o }

        function j(x, v, w) { var p = 0; var y = ""; var r = [];

            function t() { if (x.length && v.length) { if (x[0].offset != v[0].offset) { return (x[0].offset < v[0].offset) ? x : v } else { return v[0].event == "start" ? x : v } } else { return x.length ? x : v } }

            function s(A) {
                function z(B) { return " " + B.nodeName + '="' + l(B.value) + '"' } return "<" + A.nodeName + Array.prototype.map.call(A.attributes, z).join("") + ">" } while (x.length || v.length) { var u = t().splice(0, 1)[0];
                y += l(w.substr(p, u.offset - p));
                p = u.offset; if (u.event == "start") { y += s(u.node);
                    r.push(u.node) } else { if (u.event == "stop") { var o, q = r.length;
                        do { q--;
                            o = r[q];
                            y += ("</" + o.nodeName.toLowerCase() + ">") } while (o != u.node);
                        r.splice(q, 1); while (q < r.length) { y += s(r[q]);
                            q++ } } } } return y + l(w.substr(p)) }

        function f(q) {
            function o(s, r) { return RegExp(s, "m" + (q.cI ? "i" : "") + (r ? "g" : "")) }

            function p(y, w) { if (y.compiled) { return } y.compiled = true; var s = []; if (y.k) { var r = {};

                    function z(A, t) { t.split(" ").forEach(function(B) { var C = B.split("|");
                            r[C[0]] = [A, C[1] ? Number(C[1]) : 1];
                            s.push(C[0]) }) } y.lR = o(y.l || hljs.IR, true); if (typeof y.k == "string") { z("keyword", y.k) } else { for (var x in y.k) { if (!y.k.hasOwnProperty(x)) { continue } z(x, y.k[x]) } } y.k = r } if (w) { if (y.bWK) { y.b = "\\b(" + s.join("|") + ")\\s" } y.bR = o(y.b ? y.b : "\\B|\\b"); if (!y.e && !y.eW) { y.e = "\\B|\\b" } if (y.e) { y.eR = o(y.e) } y.tE = y.e || ""; if (y.eW && w.tE) { y.tE += (y.e ? "|" : "") + w.tE } } if (y.i) { y.iR = o(y.i) } if (y.r === undefined) { y.r = 1 } if (!y.c) { y.c = [] } for (var v = 0; v < y.c.length; v++) { if (y.c[v] == "self") { y.c[v] = y } p(y.c[v], y) } if (y.starts) { p(y.starts, w) } var u = []; for (var v = 0; v < y.c.length; v++) { u.push(y.c[v].b) } if (y.tE) { u.push(y.tE) } if (y.i) { u.push(y.i) } y.t = u.length ? o(u.join("|"), true) : { exec: function(t) { return null } } } p(q) }

        function d(D, E) {
            function o(r, M) { for (var L = 0; L < M.c.length; L++) { var K = M.c[L].bR.exec(r); if (K && K.index == 0) { return M.c[L] } } }

            function s(K, r) { if (K.e && K.eR.test(r)) { return K } if (K.eW) { return s(K.parent, r) } }

            function t(r, K) { return K.i && K.iR.test(r) }

            function y(L, r) { var K = F.cI ? r[0].toLowerCase() : r[0]; return L.k.hasOwnProperty(K) && L.k[K] }

            function G() { var K = l(w); if (!A.k) { return K } var r = ""; var N = 0;
                A.lR.lastIndex = 0; var L = A.lR.exec(K); while (L) { r += K.substr(N, L.index - N); var M = y(A, L); if (M) { v += M[1];
                        r += '<span class="' + M[0] + '">' + L[0] + "</span>" } else { r += L[0] } N = A.lR.lastIndex;
                    L = A.lR.exec(K) } return r + K.substr(N) }

            function z() { if (A.sL && !e[A.sL]) { return l(w) } var r = A.sL ? d(A.sL, w) : g(w); if (A.r > 0) { v += r.keyword_count;
                    B += r.r } return '<span class="' + r.language + '">' + r.value + "</span>" }

            function J() { return A.sL !== undefined ? z() : G() }

            function I(L, r) { var K = L.cN ? '<span class="' + L.cN + '">' : ""; if (L.rB) { x += K;
                    w = "" } else { if (L.eB) { x += l(r) + K;
                        w = "" } else { x += K;
                        w = r } } A = Object.create(L, { parent: { value: A } });
                B += L.r }

            function C(K, r) { w += K; if (r === undefined) { x += J(); return 0 } var L = o(r, A); if (L) { x += J();
                    I(L, r); return L.rB ? 0 : r.length } var M = s(A, r); if (M) { if (!(M.rE || M.eE)) { w += r } x += J();
                    do { if (A.cN) { x += "</span>" } A = A.parent } while (A != M.parent); if (M.eE) { x += l(r) } w = ""; if (M.starts) { I(M.starts, "") } return M.rE ? 0 : r.length } if (t(r, A)) { throw "Illegal" } w += r; return r.length || 1 } var F = e[D];
            f(F); var A = F; var w = ""; var B = 0; var v = 0; var x = ""; try { var u, q, p = 0; while (true) { A.t.lastIndex = p;
                    u = A.t.exec(E); if (!u) { break } q = C(E.substr(p, u.index - p), u[0]);
                    p = u.index + q } C(E.substr(p)); return { r: B, keyword_count: v, value: x, language: D } } catch (H) { if (H == "Illegal") { return { r: 0, keyword_count: 0, value: l(E) } } else { throw H } } }

        function g(s) { var o = { keyword_count: 0, r: 0, value: l(s) }; var q = o; for (var p in e) { if (!e.hasOwnProperty(p)) { continue } var r = d(p, s);
                r.language = p; if (r.keyword_count + r.r > q.keyword_count + q.r) { q = r } if (r.keyword_count + r.r > o.keyword_count + o.r) { q = o;
                    o = r } } if (q.language) { o.second_best = q } return o }

        function i(q, p, o) { if (p) { q = q.replace(/^((<[^>]+>|\t)+)/gm, function(r, v, u, t) { return v.replace(/\t/g, p) }) } if (o) { q = q.replace(/\n/g, "<br>") } return q }

        function m(r, u, p) { var v = h(r, p); var t = a(r); if (t == "no-highlight") { return } var w = t ? d(t, v) : g(v);
            t = w.language; var o = c(r); if (o.length) { var q = document.createElement("pre");
                q.innerHTML = w.value;
                w.value = j(o, c(q), v) } w.value = i(w.value, u, p); var s = r.className; if (!s.match("(\\s|^)(language-)?" + t + "(\\s|$)")) { s = s ? (s + " " + t) : t } r.innerHTML = w.value;
            r.className = s;
            r.result = { language: t, kw: w.keyword_count, re: w.r }; if (w.second_best) { r.second_best = { language: w.second_best.language, kw: w.second_best.keyword_count, re: w.second_best.r } } }

        function n() { if (n.called) { return } n.called = true;
            Array.prototype.map.call(document.getElementsByTagName("pre"), b).filter(Boolean).forEach(function(o) { m(o, hljs.tabReplace) }) }

        function k() { window.addEventListener("DOMContentLoaded", n, false);
            window.addEventListener("load", n, false) } var e = {};
        this.LANGUAGES = e;
        this.highlight = d;
        this.highlightAuto = g;
        this.fixMarkup = i;
        this.highlightBlock = m;
        this.initHighlighting = n;
        this.initHighlightingOnLoad = k;
        this.IR = "[a-zA-Z][a-zA-Z0-9_]*";
        this.UIR = "[a-zA-Z_][a-zA-Z0-9_]*";
        this.NR = "\\b\\d+(\\.\\d+)?";
        this.CNR = "(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)";
        this.BNR = "\\b(0b[01]+)";
        this.RSR = "!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|\\.|-|-=|/|/=|:|;|<|<<|<<=|<=|=|==|===|>|>=|>>|>>=|>>>|>>>=|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~";
        this.BE = { b: "\\\\[\\s\\S]", r: 0 };
        this.ASM = { cN: "string", b: "'", e: "'", i: "\\n", c: [this.BE], r: 0 };
        this.QSM = { cN: "string", b: '"', e: '"', i: "\\n", c: [this.BE], r: 0 };
        this.CLCM = { cN: "comment", b: "//", e: "$" };
        this.CBLCLM = { cN: "comment", b: "/\\*", e: "\\*/" };
        this.HCM = { cN: "comment", b: "#", e: "$" };
        this.NM = { cN: "number", b: this.NR, r: 0 };
        this.CNM = { cN: "number", b: this.CNR, r: 0 };
        this.BNM = { cN: "number", b: this.BNR, r: 0 };
        this.inherit = function(q, r) { var o = {}; for (var p in q) { o[p] = q[p] } if (r) { for (var p in r) { o[p] = r[p] } } return o } }();
    hljs.LANGUAGES.php = function(a) { var e = { cN: "variable", b: "\\$+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*" }; var b = [a.inherit(a.ASM, { i: null }), a.inherit(a.QSM, { i: null }), { cN: "string", b: 'b"', e: '"', c: [a.BE] }, { cN: "string", b: "b'", e: "'", c: [a.BE] }]; var c = [a.BNM, a.CNM]; var d = { cN: "title", b: a.UIR }; return { cI: true, k: "and include_once list abstract global private echo interface as static endswitch array null if endwhile or const for endforeach self var while isset public protected exit foreach throw elseif include __FILE__ empty require_once do xor return implements parent clone use __CLASS__ __LINE__ else break print eval new catch __METHOD__ case exception php_user_filter default die require __FUNCTION__ enddeclare final try this switch continue endfor endif declare unset true false namespace trait goto instanceof insteadof __DIR__ __NAMESPACE__ __halt_compiler", c: [a.CLCM, a.HCM, { cN: "comment", b: "/\\*", e: "\\*/", c: [{ cN: "phpdoc", b: "\\s@[A-Za-z]+" }] }, { cN: "comment", eB: true, b: "__halt_compiler.+?;", eW: true }, { cN: "string", b: "<<<['\"]?\\w+['\"]?$", e: "^\\w+;", c: [a.BE] }, { cN: "preprocessor", b: "<\\?php", r: 10 }, { cN: "preprocessor", b: "\\?>" }, e, { cN: "function", bWK: true, e: "{", k: "function", i: "\\$|\\[|%", c: [d, { cN: "params", b: "\\(", e: "\\)", c: ["self", e, a.CBLCLM].concat(b).concat(c) }] }, { cN: "class", bWK: true, e: "{", k: "class", i: "[:\\(\\$]", c: [{ bWK: true, eW: true, k: "extends", c: [d] }, d] }, { b: "=>" }].concat(b).concat(c) } }(hljs);

    </script>
    <script>
    window.onload = function() {
        var codeBlocks = document.getElementsByTagName('pre'),
            callStackItems = document.getElementsByClassName('call-stack-item');

        // highlight code blocks
        for (var i = 0, imax = codeBlocks.length; i < imax; ++i) {
            hljs.highlightBlock(codeBlocks[i], '    ');
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

                if (parseInt(callStackItem.getAttribute('data-line')) == i) {
                    errorLine.style.top = parseInt(lines[i].top - top) + 'px';
                    errorLine.style.height = parseInt(lines[i].bottom - lines[i].top + 6) + 'px';
                }
            }
        };

        var firstCallStackItem = callStackItems[0];

        firstCallStackItem.style.marginTop = '-' + (firstCallStackItem.offsetHeight / 2) + 'px';

        for (var i = 0, imax = callStackItems.length; i < imax; ++i) {
            refreshCallStackItemCode(callStackItems[i]);

            // toggle code block visibility
            callStackItems[i].getElementsByClassName('element-wrap')[0].addEventListener('click', function(event) {
                if (event.target.nodeName.toLowerCase() === 'a') {
                    return;
                }

                var callStackItem = this.parentNode,
                    code = callStackItem.getElementsByClassName('code-wrap')[0];

                if (typeof code !== 'undefined') {
                    code.style.display = window.getComputedStyle(code).display === 'block' ? 'none' : 'block';
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
    };

    // Highlight lines that have text in them but still support text selection:
    document.onmousedown = function() { document.getElementsByTagName('body')[0].classList.add('mousedown'); }
    document.onmouseup = function() { document.getElementsByTagName('body')[0].classList.remove('mousedown'); }

    </script>
</body>

</html>
