<?php
namespace GoatPen\ViewHelpers;

class Paginator
{
    const PAGE_SIZE = 50;

    protected $pages;
    protected $page;
    protected $previous;
    protected $next;

    public function __construct(int $items, int $page)
    {
        $this->pages = (int) ceil($items / static::PAGE_SIZE);

        if ($page < 1) {
            $this->page = 1;
        } elseif ($page > $this->pages) {
            $this->page = $this->pages;
        } else {
            $this->page = $page;
        }

        $this->previous = max($this->page - 1, 1);
        $this->next     = min($this->page + 1, $this->pages);
    }

    public function display()
    {
        if ($this->pages <= 1) {
            return;
        }
        ?>
        <nav class="text-center">
            <ul class="pagination">
                <li <?= ($this->page === $this->previous ? 'class="disabled"' : ''); ?>>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $this->previous])); ?>">
                        <span><i class="fa fa-angle-double-left"></i></span>
                    </a>
                </li>
                <?php
                if ($this->pages <= 10) {
                    $start = 1;
                    $end   = $this->pages;
                } else {
                    $start = max(1, ($this->page - 4));
                    $end   = min($this->pages, ($this->page + 5));

                    if ($start === 1) {
                        $end = 10;
                    } elseif ($end === $this->pages) {
                        $start = ($this->pages - 9);
                    }
                }

                for ($count = $start; $count <= $end; $count++) {
                    echo sprintf(
                        '<li %s><a href="?%s">%d</a></li>',
                        ($count === $this->page ? 'class="active"' : ''),
                        http_build_query(array_merge($_GET, ['page' => $count])),
                        $count
                    );
                }
                ?>
                <li <?= ($this->page === $this->next ? 'class="disabled"' : ''); ?>>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $this->next])); ?>">
                        <span><i class="fa fa-angle-double-right"></i></span>
                    </a>
                </li>
            </ul>
        </nav>
    <?php
    }
}
