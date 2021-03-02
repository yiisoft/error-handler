<?php
/* @var $applicationItems array */
/* @var $vendorItems array */
/* @var $this \Yiisoft\ErrorHandler\Renderer\HtmlRenderer */
$countVendorItems = count($vendorItems);
if ($countVendorItems > 1) {
    $firstIndex = array_key_first($vendorItems);
    $lastIndex = array_key_last($vendorItems);
    $vendorItemsToString = implode('', $vendorItems);
    $vendorGroupItems = <<<HTML
        <li class="call-stack-vendor-collapse">
            <div id="vendorCollapse" class="element-wrap">
                <div class="flex-1">
                    <span id="vendorCollapseState" class="call-stack-vendor-state">+</span>
                    <span class="file-name">{$firstIndex} - {$lastIndex} Vendor package files ({$countVendorItems})</span>
                </div>
            </div>
            <ul id="vendorCollapseItems">
                {$vendorItemsToString}
            </ul>
        </li>
    HTML;
    array_splice($applicationItems, $firstIndex - 1, 0, $vendorGroupItems);
} elseif ($countVendorItems === 1) {
    $index = array_key_first($vendorItems);
    array_splice($applicationItems, $index - 1, 0, $vendorItems[$index]);
}
?>
<ul>
    <?php foreach ($applicationItems as $applicationItem): ?>
        <?= $applicationItem ?>
    <?php endforeach; ?>
</ul>
