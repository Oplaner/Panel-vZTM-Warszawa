        <div id="pagination">
<?php

            if (!$paginationInfo->isFirstPage()):

?>
            <a href="<?php echo PathBuilder::action("$basePath/page/{$paginationInfo->getPreviousPage()}") ?>">&lt;&nbsp;Poprzednia</a>
<?php

            else:

?>
            <div></div>
<?php

            endif;

?>
            <div>Strona <?php echo $paginationInfo->getCurrentPage() ?> z <?php echo $paginationInfo->getNumberOfPages() ?></div>
<?php

            if (!$paginationInfo->isLastPage()):

?>
            <a href="<?php echo PathBuilder::action("$basePath/page/{$paginationInfo->getNextPage()}") ?>">Następna&nbsp;&gt;</a>
<?php

            else:

?>
            <div></div>
<?php

            endif;

?>
        </div>