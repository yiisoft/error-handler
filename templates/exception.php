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
            padding: 100px 100px 270px 100px;
            background: #EDEDED;
        }

        header .tools {
            margin-bottom: 50px;
            text-align: right;
        }

        header .tools a {
            margin-left: 40px;
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
        }
        /* end header */

        /*corresponds to min-width of 860px for some elements (.header .footer .element ...)*/
        @media screen and (min-width: 960px) {

            html,
            body {
                overflow-x: hidden;
            }
        }

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
            font-family: Consolas, monospace;
        }

        .call-stack ul li .code pre {
            position: relative;
            z-index: 200;
            left: 50px;
            line-height: 20px;
            font-size: 12px;
            font-family: Consolas, monospace;
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
            font-family: Consolas, monospace;
        }
        /* end request */

        /* footer */
        .footer {
            padding-top: 40px;
            padding-bottom: 40px;
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
            <a href="https://stackoverflow.com/search?<?= http_build_query(['q' => $throwable->getMessage()]) ?>" title="Search error on Stackoverflow" target="_blank">
                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABcAAAAZCAYAAADaILXQAAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAc9JREFUSMe11MuLzlEcx/GXuTQpCzQYl3EL0UxNNDRNsWAht40UC7OzkT9BsrCQkI2ysJeSknssRsl9lFyGGrkUjRqDlEth2HzU0+SZeeZ5PN86nfP7ds7n+z3v7/f8qMw6MFEVbDt6cKTYhtoKxOuxAXPxFQ8rFa/D76z7s27PuIWBSlDsxmGMz3cNjgXPWUwoV3gh7kboBJrib8SV+A+Ui+UDHmEVpmMtHuAV+rAO8/ERveUwf4Nr6Ezm6/E2vnosxQpcx2Ap4u059BJD+IRLaEUzVifJ49k7E8txrhTxfdiGjRF/ji+4HN6LsSxIDgXXTdyoLaH15qWYk4JjMxrC+WoCdUS8DTtxEb9GEx/CbZzOQ1mQIO3YmvUZ3MPK3Kb77+FxYyxoAzahK2zhZ0TP437BIxuxW6ZhP2bn0XwOgl6cSgvOwlQswkm8H860mLWGcWeBrx9P8DjzjnBux7PhAoVYmjO/w49kvAYtCdRYpCYv0iU9mBEaA/heKN6TeUuuPNymJEhLxpKCf0kXnoZ7E3bhTt0YijmQTuguuPWcBOor1sfF7Gj6txS78C9njSraSJnvTV9XRXywGpnvwbcy9SaPJt5WDeYH/2MtX8Mf2fhjE3QWPKAAAAAASUVORK5CYII=" alt="Search Stackoverflow"/>
            </a>
            <a href="https://www.google.com/search?<?= http_build_query(['q' => $throwable->getMessage()]) ?>" title="Search error on Google" target="_blank">
                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAZCAYAAAAxFw7TAAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAZZJREFUOMvl1T9IlVEYBvCf37301+iPFgYtBhUYQUIQiGaEgVCDELg11l5jo2MNgTg5NYiTNN2GIKJIA0MwGlIMFQoirAjRoUiq5XxwOHz33rrq1APf8pznPO/7nu897+G/Q1ONtYPoxVkcxk58wxJe4g1+/41hK25gAKUaAecxjFcxmW44h1F0IqtTXSuuBN1MTpYjQRfu18mqCF+KSj6GMTQn4qd4iHdYx1FcxHXswxAqRVHuhbTz7zku1MjqCLqr/eWTGE/4W3jRSNtk6Eu4x42a5YbnE25iM41dRlvCva2irRRoU6xk2B8R6/ixiQQPZViNiOZwxRrFRhmf0BKRHZgtEE+E3ouxG4Nxk5cxjdMRea2K4YMC7lJiuJDhSSLqr9a0CXbgZsJNlfAVJ9AeLfRgEe9rmN0Noy3HGobyQTCHq0GYb+jH8SD8jo1wly+HO3wmCTKC2aYtmDYwidv4FW/+gNeh3F3/YPYMd/CzaMB+xCPswak6Q/ZzqGg4HEfdN+VAeFM6w5uyN5znchj703lW24o/8/BJk5VnLk0AAAAASUVORK5CYII=" alt="Search Google"/>
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
                <!-- Icon Credit: Font Awesome by Dave Gandy - http://fontawesome.io ; fa-clipboard, fa-stackoverflow, fa-google-->
                <a href="#" id="copy-stacktrace" title="Copy the stacktrace for use in a bug report or pastebin">
                    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAZCAYAAAArK+5dAAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAR1JREFUSMftlTFKxEAYhb8n2c7CarGR9RAWHkFsvMIqCy4iVpZ6AS9gIXqDbbdYLEUstLPwBt5AXQw+mxTDkkyymWwh+CAQMsP/zZt/XkbAEbAPiOVlYGr7rmqCgCcgo71y27tVg9lC8Wvbt3UVJV0CB0ENYoBQG5K2Gqx6vak9Ac8kyvZO1dgaK1YnAEnDpj2I6azk2wXQB04l9WzftAbYfixZ+Wfx+gocS2IR0lUPToCXAjLqHGD7o9jCB2AcQjo7RbbnwDlwH0Ky1LpFLyYluzKW9J7qYAbkwCB4wj/BYeMkx9JakY0JMPgbSf4HJAPeVgnIgBGwHXFzBWy2Btj+irmQNE91UKfvAtTmas1luy6Re8AQ6C1Z/AeY/gK6sUu5CuQ0NQAAAABJRU5ErkJggg==" alt="Copy Stacktrace" />
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
            <p class="timestamp">
                <?= date('Y-m-d, H:i:s') ?>
            </p>
            <p>
                <?= $this->createServerInformationLink() ?>
            </p>
            <p><a href="http://yiiframework.com/">Yii Framework</a>/
                <?= $this->createFrameworkVersionLink() ?>
            </p>
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
            try {
                succeeded = document.execCommand('copy');
            } catch (err) {
                succeeded = false;
            }
            if (succeeded) {
                document.getElementById('copied').style.display = 'block';
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
