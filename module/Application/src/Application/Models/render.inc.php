<?php
    // total page count calculation
    $pages = ((int) ceil($total / $rpp));
    // if it's an invalid page request
    if ($current < 1) {
        return;
    } elseif ($current > $pages) {
        return;
    }
    // if there are pages to be shown
    if ($pages > 1 || $alwaysShowPagination === true) {
?>
<ul class="<?= implode(' ', $classes) ?>">
<?php
        /**
         * Previous Link
         */
        // anchor classes and target
        $classes = array('page-item', 'prev');
        $params = $get;
        $params[$key] = ($current - 1);
        $href = ($target) . '?' . http_build_query($params);
        $href = preg_replace(
            array('/=$/', '/=&/'),
            array('', '&'),
            $href
        );
        if ($current === 1) {
            $href = '#';
            array_push($classes, 'disabled');
        }
?>
    <li class="<?= implode(' ', $classes) ?>"><a href="<?= ($href) ?>" class="page-link"><?= ($previous) ?></a></li>
<?php
        /**
         * if this isn't a clean output for pagination (eg. show numerical
         * links)
         */
        if (!$clean) {
            /**
             * Calculates the number of leading page crumbs based on the minimum
             *     and maximum possible leading pages.
             */
            $max = min($pages, $crumbs);
            $limit = ((int) floor($max / 2));
            $leading = $limit;
            for ($x = 0; $x < $limit; ++$x) {
                if ($current === ($x + 1)) {
                    $leading = $x;
                    break;
                }
            }
            for ($x = $pages - $limit; $x < $pages; ++$x) {
                if ($current === ($x + 1)) {
                    $leading = $max - ($pages - $x);
                    break;
                }
            }
            // calculate trailing crumb count based on inverse of leading
            $trailing = $max - $leading - 1;
            // generate/render leading crumbs
            for ($x = 0; $x < $leading; ++$x) {
                // class/href setup
                $params = $get;
                $params[$key] = ($current + $x - $leading);
                $href = ($target) . '?' . http_build_query($params);
                $href = preg_replace(
                    array('/=$/', '/=&/'),
                    array('', '&'),
                    $href
                );
?>
    <li class="page-item"><a data-pagenumber="<?= ($current + $x - $leading) ?>" href="<?= ($href) ?>" class="page-link"><?= ($current + $x - $leading) ?></a></li>
<?php
            }
            // print current page
?>
    <li class="page-item active"><a data-pagenumber="<?= ($current) ?>" href="#" class="page-link"><?= ($current) ?></a></li>
<?php
            // generate/render trailing crumbs
            for ($x = 0; $x < $trailing; ++$x) {
                // class/href setup
                $params = $get;
                $params[$key] = ($current + $x + 1);
                $href = ($target) . '?' . http_build_query($params);
                $href = preg_replace(
                    array('/=$/', '/=&/'),
                    array('', '&'),
                    $href
                );
?>
    <li class="page-item"><a data-pagenumber="<?= ($current + $x + 1) ?>" href="<?= ($href) ?>" class="page-link"><?= ($current + $x + 1) ?></a></li>
<?php
            }
        }
        /**
         * Next Link
         */
        // anchor classes and target
        $classes = array('page-item', 'next');
        $params = $get;
        $params[$key] = ($current + 1);
        $href = ($target) . '?' . http_build_query($params);
        $href = preg_replace(
            array('/=$/', '/=&/'),
            array('', '&'),
            $href
        );
        if ($current === $pages) {
            $href = '#';
            array_push($classes, 'disabled');
        }
?>
    <li class="<?= implode(' ', $classes) ?>"><a href="<?= ($href) ?>" class="page-link"><?= ($next) ?></a></li>
</ul>
<?php
}
?> 