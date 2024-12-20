<?php
if (!isset($total)) {
    flash("Note to Dev: The total variable is undefined", "danger");
    error_log("Note to Dev: The total variable is undefined");
    $total = 0;
}

$per_page = (int)se($_GET, "limit", 10, false);
$page = (int)se($_GET, "page", 1, false);
$total_pages = ceil($total / $per_page);

// Handle edge cases
if ($total <= 0) {
    $total_pages = 1;
}
if ($page > $total_pages) {
    $page = $total_pages;
}

function persistQueryString($page) {
    $_GET["page"] = $page;
    return http_build_query($_GET);
}

function disable_prev($page) {
    echo $page <= 1 ? "disabled" : "";
}

function disable_next($page, $total_pages) {
    echo $page >= $total_pages ? "disabled" : "";
}

function set_active($page, $i) {
    echo $page == $i + 1 ? "active" : "";
}
?>

<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php disable_prev($page); ?>">
            <a class="page-link" href="?<?php echo persistQueryString($page - 1); ?>" tabindex="-1">Previous</a>
        </li>
        <?php for ($i = 0; $i < $total_pages; $i++) : ?>
            <li class="page-item <?php set_active($page, $i); ?>">
                <a class="page-link" href="?<?php echo persistQueryString($i + 1); ?>"><?php echo $i + 1; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php disable_next($page, $total_pages); ?>">
            <a class="page-link" href="?<?php echo persistQueryString($page + 1); ?>">Next</a>
        </li>
    </ul>
</nav>