<?php

use WebEd\Base\Pages\Models\Page;

if (!function_exists('get_page_link')) {
    /**
     * @param string|Page $page
     * @return string
     */
    function get_page_link($page)
    {
        $slug = is_string($page) ? $page : $page->slug;
        return route('front.web.resolve-pages.get', ['slug' => $slug]);
    }
}
