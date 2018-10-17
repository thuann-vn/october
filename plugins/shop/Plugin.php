<?php namespace RainLab\Blog;

use Backend;
use Controller;
use RainLab\Blog\Models\Post;
use System\Classes\PluginBase;
use RainLab\Blog\Classes\TagProcessor;
use RainLab\Blog\Models\Category;
use Event;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'nframework.shop::lang.plugin.name',
            'description' => 'nframework.shop::lang.plugin.description',
            'author'      => 'Thua Nguyen, Viet Nam',
            'icon'        => 'icon-cart',
            'homepage'    => 'https://github.com/rainlab/blog-plugin'
        ];
    }

    public function registerComponents()
    {
        return [
            'RainLab\Blog\Components\Post'       => 'products',
            'RainLab\Blog\Components\Posts'      => 'productCategories',
            'RainLab\Blog\Components\Categories' => 'blogCategories',
            'RainLab\Blog\Components\RssFeed'    => 'blogRssFeed'
        ];
    }

    public function registerPermissions()
    {
        return [
            'nframework.shop.access_products' => [
                'tab'   => 'nframework.shop::lang.blog.tab',
                'label' => 'nframework.shop::lang.blog.access_posts'
            ],
            'nframework.shop.access_categories' => [
                'tab'   => 'nframework.shop::lang.blog.tab',
                'label' => 'nframework.shop::lang.blog.access_categories'
            ],
            'nframework.shop.access_other_posts' => [
                'tab'   => 'nframework.shop::lang.blog.tab',
                'label' => 'nframework.shop::lang.blog.access_other_posts'
            ],
            'nframework.shop.access_import_export' => [
                'tab'   => 'nframework.shop::lang.blog.tab',
                'label' => 'nframework.shop::lang.blog.access_import_export'
            ],
            'nframework.shop.access_publish' => [
                'tab'   => 'nframework.shop::lang.blog.tab',
                'label' => 'nframework.shop::lang.blog.access_publish'
            ]
        ];
    }

    public function registerNavigation()
    {
        return [
            'blog' => [
                'label'       => 'nframework.shop::lang.blog.menu_label',
                'url'         => Backend::url('rainlab/blog/posts'),
                'icon'        => 'icon-pencil',
                'iconSvg'     => 'plugins/rainlab/blog/assets/images/blog-icon.svg',
                'permissions' => ['nframework.shop.*'],
                'order'       => 300,

                'sideMenu' => [
                    'new_post' => [
                        'label'       => 'nframework.shop::lang.posts.new_post',
                        'icon'        => 'icon-plus',
                        'url'         => Backend::url('rainlab/blog/posts/create'),
                        'permissions' => ['nframework.shop.access_posts']
                    ],
                    'products' => [
                        'label'       => 'nframework.shop::lang.blog.posts',
                        'icon'        => 'icon-copy',
                        'url'         => Backend::url('rainlab/blog/posts'),
                        'permissions' => ['nframework.shop.access_posts']
                    ],
                    'productCategories' => [
                        'label'       => 'nframework.shop::lang.blog.categories',
                        'icon'        => 'icon-list-ul',
                        'url'         => Backend::url('rainlab/blog/categories'),
                        'permissions' => ['nframework.shop.access_categories']
                    ]
                ]
            ]
        ];
    }

    public function registerFormWidgets()
    {
        return [
            'RainLab\Blog\FormWidgets\Preview' => [
                'label' => 'Preview',
                'code'  => 'preview'
            ]
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register()
    {
        /*
         * Register the image tag processing callback
         */
        TagProcessor::instance()->registerCallback(function($input, $preview) {
            if (!$preview) {
                return $input;
            }

            return preg_replace('|\<img src="image" alt="([0-9]+)"([^>]*)\/>|m',
                '<span class="image-placeholder" data-index="$1">
                    <span class="upload-dropzone">
                        <span class="label">Click or drop an image...</span>
                        <span class="indicator"></span>
                    </span>
                </span>',
            $input);
        });
    }

    public function boot()
    {
        /*
         * Register menu items for the RainLab.Pages plugin
         */
        Event::listen('pages.menuitem.listTypes', function() {
            return [
                'blog-category'       => 'nframework.shop::lang.menuitem.blog_category',
                'all-blog-categories' => 'nframework.shop::lang.menuitem.all_blog_categories',
                'blog-post'           => 'nframework.shop::lang.menuitem.blog_post',
                'all-blog-posts'      => 'nframework.shop::lang.menuitem.all_blog_posts',
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function($type) {
            if ($type == 'blog-category' || $type == 'all-blog-categories') {
                return Category::getMenuTypeInfo($type);
            }
            elseif ($type == 'blog-post' || $type == 'all-blog-posts') {
                return Post::getMenuTypeInfo($type);
            }
        });

        Event::listen('pages.menuitem.resolveItem', function($type, $item, $url, $theme) {
            if ($type == 'blog-category' || $type == 'all-blog-categories') {
                return Category::resolveMenuItem($item, $url, $theme);
            }
            elseif ($type == 'blog-post' || $type == 'all-blog-posts') {
                return Post::resolveMenuItem($item, $url, $theme);
            }
        });
    }
}
