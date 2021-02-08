<?php
/* @var $file string|null */
/* @var $line int|null */
/* @var $class string|null */
/* @var $method string|null */
/* @var $index int */
/* @var $lines string[] */
/* @var $begin int */
/* @var $end int */
/* @var $args array */
/* @var $this \Yiisoft\ErrorHandler\Renderer\HtmlRenderer */

$html = <<<HTML
IDE
<svg class="icon icon--new-window" focusable="false" aria-hidden="true" width="16" height="16">
    <use href="#new-window"></use>
</svg>
HTML;
?>
<li class="<?=!empty($lines) ? 'application' : '' ?> call-stack-item"
    data-line="<?= (int) ($line - $begin) ?>">
    <div class="element-wrap">
        <div class="flex-1">
            <?php if ($file !== null): ?>
                <span class="file-name">
                    <?= sprintf('%d. %s', (int) $index, 'in ' . $this->htmlEncode($file))  ?>
                </span>
            <?php endif ?>

            <?php if ($this->traceLine !== '{html}') : ?>
                <span> &ndash; </span>
                <?= strtr($this->traceLine, ['{file}' => $file, '{line}' => $line + 1, '{html}' => $html]) ?>
            <?php endif; ?>

            <?php if ($method !== null) : ?>
                <span>
                    <?= $file !== null ? '&ndash;' : '' ?>
                    <?= ($class !== null ? $this->addTypeLinks("$class::$method") : $this->htmlEncode($method)) . '(' . $this->argumentsToString($args) . ')' ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($line !== null): ?>
            <div><?= sprintf('at line %d', $line + 1) ?></div>
        <?php endif ?>
    </div>
    <?php if (!empty($lines)) : ?>
        <div class="code-wrap">
            <div class="error-line"></div>
            <?php for ($i = $begin; $i <= $end; ++$i) : ?><div class="hover-line"></div><?php endfor; ?>
            <div class="code">
                <?php for ($i = $begin; $i <= $end; ++$i) : ?><span class="lines-item"><?= (int) ($i + 1) ?></span><?php endfor; ?>
                <pre><?php
                    // fill empty lines with a whitespace to avoid rendering problems in opera
                    for ($i = $begin; $i <= $end; ++$i) {
                        echo (trim($lines[$i]) === '') ? " \n" : $this->htmlEncode($lines[$i]);
                    }
                    ?></pre>
            </div>
        </div>
    <?php endif; ?>
</li>
